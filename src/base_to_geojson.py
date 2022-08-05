"""
Treats the world map as an image array with integral indices
0,0 is the top-left cell, defined by its top-left point of [-180, 90]
"""

import argparse
import json
import random
import numpy as np
from skimage.measure import block_reduce
import os
import sys

from common import *


def BASE_GEOJSON():
    return {
         "type": "FeatureCollection",
         "features": []
    }

def BASE_POLY():
    return {
        "type": "Feature",
        "geometry": {
            "type": "Polygon",
            "coordinates": []
        },
        "properties": {}
    }


def get_bbox_from_anchor(lonlat, step_size):
    lon, lat = lonlat
    return [(lon + dx * step_size, lat + dy * step_size) for dx, dy in COORD_ORDER]


def get_bbox_for_cell(zoom, x, y, step_size=None):
    if step_size is None:
        step_size = get_step_size_deg(zoom)

    lon, lat = ORIGIN_COORD
    lon += x * step_size
    lat -= y * step_size
    return get_bbox_from_anchor((lon, lat), step_size)


def bin_stats_to_zooms_single(base_dir, stat_filename, out_dir):
    # For each channel, we have a high res array
    base_data = np.load(os.path.join(base_dir, stat_filename), allow_pickle=True).tolist()
    properties = base_data['properties']
    max_zoom = properties['max_zoom']
    sums = base_data['res_sums']
    counts = base_data['res_counts']
    total_count = np.sum(counts[:, :, 0])  # the 'all' channel
    tag_key = base_data['tag_key']  # ['all' or the slug of each tag as the last dim of the data arrays]

    assert THRESHOLD_COUNT > 0, "Divide by zero can occur"
    sentinel_val = -1

    print("max zoom:", max_zoom)
    for zoom in range(max_zoom, -1, -1):
        pathname = os.path.join(out_dir, "z%02d" % zoom)
        if not os.path.exists(pathname):
            os.makedirs(pathname)
        if zoom < max_zoom:
            sums = block_reduce(sums, block_size=(2, 2, 1), func=np.sum)
            counts = block_reduce(counts, block_size=(2, 2, 1), func=np.sum)

        kept_indices = counts > THRESHOLD_COUNT
        # Sums
        filtered_data = np.where(kept_indices, sums, sentinel_val)  # hide low-volume data for privacy
        # Means
        filtered_data[kept_indices] /= counts[kept_indices]  # Guaranteed to avoid divide by 0

        outname = os.path.join(pathname, stat_filename)
        tmp_obj = np.array({
            'prompt_data': filtered_data,
            'tag_key': tag_key},
            dtype=object)
        np.save(outname, tmp_obj)
    return total_count


def bin_tags_to_zooms_single(base_dir, stat_filename, out_dir):
    # TODO: tags can be processed similarly.
    # Need to divide tag layers by their corresponding all layer to get frequency
    # Min/max scale each layer of frequency

    base_data = np.load(os.path.join(base_dir, stat_filename), allow_pickle=True).tolist()
    properties = base_data['properties']
    max_zoom = properties['max_zoom']
    counts = base_data['res_counts']
    tag_key = base_data['tag_key']  # ['all' or the slug of each tag as the last dim of the data arrays]

    assert THRESHOLD_COUNT > 0, "Divide by zero can occur"
    sentinel_val = -1

    for zoom in range(max_zoom, -1, -1):
        pathname = os.path.join(out_dir, "z%02d" % zoom)
        if not os.path.exists(pathname):
            os.makedirs(pathname)
        if zoom < max_zoom:
            counts = block_reduce(counts, block_size=(2, 2, 1), func=np.sum)

        # Ignore cells that have too few votes
        kept_indices = counts > THRESHOLD_COUNT
        filtered_counts = np.where(kept_indices, counts, sentinel_val).astype(float)
        # Perform 0 ('all') last
        for i in list(range(1, len(tag_key))) + [0]:
            if np.sum(kept_indices[:, :, i]) == 0:
                continue
            # Calculate the ratio of responses with each tag
            layer_data = filtered_counts[:, :, i][kept_indices[:, :, i]]
            if i > 0:
                layer_data /= filtered_counts[:, :, 0][kept_indices[:, :, i]]

            # Min/max scale each layer
            amin = np.amin(layer_data)
            amax = np.amax(layer_data)
            if amin == amax:
                layer_data = 1  # every cell with the threshold count was equal; set to max
            else:
                layer_data -= amin
                layer_data /= amax - amin
            filtered_counts[:, :, i][kept_indices[:, :, i]] = layer_data

        outname = os.path.join(pathname, '_tag_counts.npy')
        np.save(outname, filtered_counts)


def bin_stats_to_zooms(base_dir, out_dir):
    max_response_prompt = (0, '')
    for stat_filename in next(os.walk(base_dir))[2]:
        print("File:", stat_filename, end=', ')
        if not stat_filename.endswith('.npy') or stat_filename.startswith('_'):
            print("Skipping '_' or non .npy file:", stat_filename)
            continue

        n_responses = bin_stats_to_zooms_single(base_dir, stat_filename, out_dir)
        if n_responses > max_response_prompt[0]:
            max_response_prompt = (n_responses, stat_filename)

    # Parse the most popular prompt for voter types and locations
    bin_tags_to_zooms_single(base_dir, max_response_prompt[1], out_dir)


def write_cells(in_dir, out_dir, compress_json_floats=False):
    for zoom_dir in next(os.walk(in_dir))[1]:  # "z%02d"
        print("Entering:", zoom_dir)
        zoom = int(zoom_dir[1:])
        # Load all data at this zoom into memory
        zoom_path = os.path.join(in_dir, zoom_dir)
        tag_data = np.load(os.path.join(zoom_path, '_tag_counts.npy'))
        stat_list = []  # (label, array) pairs
        for stat_filename in next(os.walk(zoom_path))[2]:
            if not stat_filename.endswith('.npy') or stat_filename.startswith('_'):
                print("Skipping non .npy file:", stat_filename)
                continue
            stat_label = stat_filename[:-4]
            stat_path = os.path.join(zoom_path, stat_filename)
            tmp_stats = np.load(stat_path, allow_pickle=True).tolist()
            stat_array = tmp_stats['prompt_data']
            stat_tags = tmp_stats['tag_key']
            stat_list.append((stat_label, stat_array, stat_tags))

        output = BASE_GEOJSON()
        features = []
        nx, ny = n_cells_xy(zoom)
        for row in range(ny):
            for col in range(nx):
                bbox = get_bbox_for_cell(zoom, col, row)
                cell_poly = BASE_POLY()
                cell_poly['geometry']['coordinates'] = [bbox + [bbox[0]]]
                for stats_label, stats_arr, tag_key in stat_list:
                    for tag_i, tag in enumerate(tag_key):
                        cell_poly['properties']["%s-%s" % (stats_label, tag)] = float(stats_arr[row][col][tag_i])
                for tag_i, tag in enumerate(tag_key):
                    cell_poly['properties']["tag-%s" % tag] = float(tag_data[row][col][tag_i])

                features.append(cell_poly)
        output['features'] = features

        # As per https://stackoverflow.com/a/29066406, python's json dump
        # doesn't have float formatting but its LOAD does
        if compress_json_floats:
            output = json.loads(json.dumps(output), parse_float=lambda x: round(float(x), 3))

        out_path = os.path.join(zoom_path, 'cells.json')
        with open(out_path, 'w') as outfile:
            json.dump(output, outfile)


def main(in_dir, tmp_dir, out_dir):
    bin_stats_to_zooms(base_dir=in_dir, out_dir=tmp_dir)
    write_cells(in_dir=tmp_dir, out_dir=out_dir, compress_json_floats=True)


if __name__ == "__main__":
    parser = argparse.ArgumentParser(description='Process some integers.')
    parser.add_argument('--in_dir', type=str, nargs='?', default='./out', help='where numpy "base" files can be found')
    parser.add_argument('--tmp_dir', type=str, nargs='?', default='./out', help='where numpy cell file will be written to for temp use')
    parser.add_argument('--out_dir', type=str, nargs='?', default='./out', help='where geoJSON files are to be saved')
    args = parser.parse_args()

    in_dir = args.in_dir
    if not os.path.exists(in_dir):
        os.makedirs(in_dir)

    tmp_dir = args.tmp_dir
    if not os.path.exists(tmp_dir):
        os.makedirs(tmp_dir)

    out_dir = args.out_dir
    if not os.path.exists(out_dir):
        os.makedirs(out_dir)

    main(in_dir, tmp_dir, out_dir)
