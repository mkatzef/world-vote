"""
Treats the world map as an image array with integral indices
0,0 is the top-left cell, defined by its top-left point of [-180, 90]
"""

import argparse
import numpy as np
import os

from common import *


def add_a_to_b_using_keys(dict_a, key_a, dict_b, key_b):
    b_layer_lookup = dict([(k, i) for i, k in enumerate(key_b)])

    for a_layer_i, a_layer_key in enumerate(key_a):
        # Skip any layers that were present in the src data but not dst
        # Example: a tag was removed sometime that day
        if a_layer_key not in b_layer_lookup:
            continue

        b_layer_i = b_layer_lookup[a_layer_key]

        for dlabel in ['res_sums', 'res_counts']:
            dict_b[dlabel][:, :, b_layer_i] += dict_a[dlabel][:, :, a_layer_i]

    return dict_b


def main(in_dir, out_dir):
    src_files = next(os.walk(in_dir))[2]
    dst_files = set(next(os.walk(out_dir))[2])

    for stat_filename in src_files:
        if not stat_filename.endswith('.npy') or stat_filename.startswith('_'):
            print("Skipping '_' or non .npy file:", stat_filename)
            continue

        if stat_filename not in dst_files:
            print("No suitable destination file found for: ", stat_filename, "skipping")
            continue

        print("Combining:", stat_filename)
        src_path = os.path.join(in_dir, stat_filename)
        dst_path = os.path.join(out_dir, stat_filename)
        src_data = np.load(src_path, allow_pickle=True).tolist()
        dst_data = np.load(dst_path, allow_pickle=True).tolist()
        src_key = src_data['tag_key']
        dst_key = dst_data['tag_key']

        # modifies dst_data
        add_a_to_b_using_keys(src_data, src_key, dst_data, dst_key, missing_only)
        np.save(dst_path, dst_data)

    if not missing_only:  # TODO: add support for counts
        counts_filename = "_counts.npy"
        print("Combining:", counts_filename)
        src_counts_path = os.path.join(in_dir, counts_filename)
        dst_counts_path = os.path.join(out_dir, counts_filename)
        src_counts = np.load(src_counts_path, allow_pickle=True).tolist()
        dst_counts = np.load(dst_counts_path, allow_pickle=True).tolist()
        src_key = src_counts['tag_key']
        dst_key = dst_counts['tag_key']
        dst_layer_lookup = dict([(k, i) for i, k in enumerate(dst_key)])

        for src_p_id, src_p_dict in src_counts['counts'].items():
            if src_p_id not in dst_counts['counts']:
                continue
            for src_layer_i, src_layer_tag in enumerate(src_key):
                if src_layer_tag not in dst_layer_lookup:
                    continue
                dst_counts['counts'][src_p_id][:, dst_layer_lookup[src_layer_tag]] += src_p_dict[:, src_layer_i]
        np.save(dst_counts_path, dst_counts)


if __name__ == "__main__":
    parser = argparse.ArgumentParser(description='Adds base files from dir A to B')
    parser.add_argument('--in_dir', type=str, required=True, help='where base A can be found')
    parser.add_argument('--out_dir', type=str, required=True, help='where base B can be found')
    args = parser.parse_args()

    in_dir = args.in_dir
    assert os.path.exists(in_dir), "in_dir must exist" + str(in_dir)
    out_dir = args.out_dir
    assert os.path.exists(out_dir), "out_dir must exist" + str(out_dir)

    main(in_dir, out_dir)
