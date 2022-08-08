"""
Treats the world map as an image array with integral indices
0,0 is the top-left cell, defined by its top-left point of [-180, 90]
"""

import argparse
import numpy as np
import os

from common import *


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
        dst_data['res_sums'] += src_data['res_sums']
        dst_data['res_counts'] += src_data['res_counts']

        np.save(dst_path, dst_data)

    counts_filename = "_counts.npy"
    print("Combining:", counts_filename)
    src_counts_path = os.path.join(in_dir, counts_filename)
    dst_counts_path = os.path.join(out_dir, counts_filename)
    src_counts = np.load(src_counts_path, allow_pickle=True).tolist()
    dst_counts = np.load(dst_counts_path, allow_pickle=True).tolist()
    for k, v in src_counts['counts'].items():
        dst_counts['counts'][k] += v
    np.save(dst_counts_path, dst_counts)


if __name__ == "__main__":
    parser = argparse.ArgumentParser(description='Adds base files from dir A to B')
    parser.add_argument('--in_dir', type=str, nargs=1, help='where base A can be found')
    parser.add_argument('--out_dir', type=str, nargs=1, help='where base B can be found')
    args = parser.parse_args()

    in_dir = args.in_dir[0]
    assert os.path.exists(in_dir), "in_dir must exist" + str(in_dir)
    out_dir = args.out_dir[0]
    assert os.path.exists(out_dir), "out_dir must exist" + str(out_dir)

    main(in_dir, out_dir)
