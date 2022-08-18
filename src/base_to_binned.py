"""
Treats the world map as an image array with integral indices
0,0 is the top-left cell, defined by its top-left point of [-180, 90]
"""

import argparse
import numpy as np
from skimage.measure import block_reduce
import os

from common import *


def bin_stats_to_zooms_single(base_dir, stat_filename, out_dir, preproc_dir=None):
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
        zoom_dir_name = "z%02d" % zoom
        pathname = os.path.join(out_dir, zoom_dir_name)
        if not os.path.exists(pathname):
            os.makedirs(pathname)
        if zoom < max_zoom:
            sums = block_reduce(sums, block_size=(2, 2, 1), func=np.sum)
            counts = block_reduce(counts, block_size=(2, 2, 1), func=np.sum)

        filled_indices = counts >= THRESHOLD_COUNT
        empty_indices = counts < THRESHOLD_COUNT

        filler_vals = sentinel_val
        if preproc_dir is not None:
            preproc_path = os.path.join(preproc_dir, zoom_dir_name, stat_filename)
            if os.path.exists(preproc_path):
                preproc_data = np.load(preproc_path, allow_pickle=True).tolist()
                filler_vals = preproc_data['prompt_data'][:, :, 0]

        # Sums
        filtered_data = np.empty(sums.shape)
        # Treat "all" data differently to individual tags
        filtered_data[:, :, 0] = np.where(filled_indices[:, :, 0], sums[:, :, 0], filler_vals)  # hide low-volume data for privacy
        filtered_data[:, :, 1:] = np.where(filled_indices[:, :, 1:], sums[:, :, 1:], sentinel_val)  # hide low-volume data for privacy
        # Means
        filtered_data[filled_indices] /= counts[filled_indices]  # Guaranteed to avoid divide by 0

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
    # If preproc_dir is given and it has a relevant stat_filename, use its data
    #   as FALLBACK.

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
        filled_indices = counts >= THRESHOLD_COUNT
        filtered_counts = np.where(filled_indices, counts, sentinel_val).astype(float)
        # Perform 0 ('all') last
        for i in list(range(1, len(tag_key))) + [0]:
            if np.sum(filled_indices[:, :, i]) == 0:
                continue
            # Calculate the ratio of responses with each tag
            layer_data = filtered_counts[:, :, i][filled_indices[:, :, i]]
            if i > 0:
                layer_data /= filtered_counts[:, :, 0][filled_indices[:, :, i]]

            # Min/max scale each layer
            amin = np.amin(layer_data)
            amax = np.amax(layer_data)
            if amin == amax:
                layer_data = 1  # every cell with the threshold count was equal; set to max
            else:
                layer_data -= amin
                layer_data /= amax - amin
            filtered_counts[:, :, i][filled_indices[:, :, i]] = layer_data

        outname = os.path.join(pathname, '_tag_counts.npy')
        np.save(outname, filtered_counts)


def bin_stats_to_zooms(base_dir, out_dir, preproc_dir=None):
    max_response_prompt = (0, None)
    for stat_filename in next(os.walk(base_dir))[2]:
        print("File:", stat_filename, end=', ')
        if not stat_filename.endswith('.npy') or stat_filename.startswith('_'):
            print("Skipping '_' or non .npy file:", stat_filename)
            continue

        n_responses = bin_stats_to_zooms_single(base_dir, stat_filename, out_dir, preproc_dir=preproc_dir)
        if n_responses > max_response_prompt[0]:
            max_response_prompt = (n_responses, stat_filename)

    # Parse the most popular prompt for voter types and locations
    bin_tags_to_zooms_single(base_dir, max_response_prompt[1], out_dir)


def main(in_dir, out_dir, preproc_dir=None):
    bin_stats_to_zooms(base_dir=in_dir, out_dir=out_dir, preproc_dir=preproc_dir)


if __name__ == "__main__":
    parser = argparse.ArgumentParser(description='Process some integers.')
    parser.add_argument('--in_dir', type=str, nargs='?', default='./out', help='where numpy "base" files can be found')
    parser.add_argument('--out_dir', type=str, nargs='?', default='./out', help='where binned files are to be saved')
    parser.add_argument('--preproc_dir', type=str, nargs='?', default='', help='binned numpy base files to be included without further processing')
    args = parser.parse_args()

    in_dir = args.in_dir
    if not os.path.exists(in_dir):
        os.makedirs(in_dir)

    out_dir = args.out_dir
    if not os.path.exists(out_dir):
        os.makedirs(out_dir)

    preproc_dir = args.preproc_dir
    if preproc_dir == '':
        preproc_dir = None
    else:
        assert os.path.exists(preproc_dir), 'Given preproc dir does not exist: ' + str(preproc_dir)

    main(in_dir, out_dir, preproc_dir)
