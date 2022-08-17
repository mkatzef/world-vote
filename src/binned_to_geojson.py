"""
Treats the world map as an image array with integral indices
0,0 is the top-left cell, defined by its top-left point of [-180, 90]
"""

import argparse
import json
import numpy as np
import os

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
            output = compress_for_json(output, 3)

        out_path = os.path.join(zoom_path, 'cells.json')
        with open(out_path, 'w') as outfile:
            json.dump(output, outfile)


def main(in_dir, out_dir):
    write_cells(in_dir=in_dir, out_dir=out_dir, compress_json_floats=True)


if __name__ == "__main__":
    parser = argparse.ArgumentParser(description='Process some integers.')
    parser.add_argument('--in_dir', type=str, nargs='?', default='./out', help='where binned numpy "base" files can be found')
    parser.add_argument('--out_dir', type=str, nargs='?', default='./out', help='where geoJSON files are to be saved')
    args = parser.parse_args()

    in_dir = args.in_dir
    if not os.path.exists(in_dir):
        os.makedirs(in_dir)

    out_dir = args.out_dir
    if not os.path.exists(out_dir):
        os.makedirs(out_dir)

    main(in_dir, out_dir)
