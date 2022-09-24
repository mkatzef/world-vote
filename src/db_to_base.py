"""
Open mysql database
Take tag ids from command line [or read all tags]
Take mapped prompt ids from command line [or read all and filter]
Take counted prompt ids from command line [or read all]
Iterate over users from given table/where clause [or iterate over all records]
    binning results into each raw array (sums, counts)
Save as separate .npy files (one array per mapped prompt, one count per counted prompt)
"""
import argparse
import json
import numpy as np
import os
import sys

from common import *


class BaseData:
    def __init__(self, n_rows=MAX_ROWS, n_cols=MAX_COLS, tag_key=[]):
        self.tag_key = tag_key
        n_layers = len(tag_key)
        self.sums = np.empty((n_rows, n_cols, n_layers), dtype=float)
        self.counts = np.empty((n_rows, n_cols, n_layers), dtype=float)

    def save_as(self, out_path):
        save_base_data(out_path, MAX_ZOOM, self.sums/VOTE_MAX_STEP, self.counts, self.tag_key)


def get_base_data(tags, mapped_prompts, counted_prompts, users):
    tag_key = ['all'] + tags  # Order of data: starts with all, followed by each tag
    tag_dict = dict([(k, i+1) for i, k in enumerate(tags)])
    n_layers = len(tag_key)

    map_data_dict = dict([(p_id, BaseData(tag_key=tag_key)) for p_id in mapped_prompts])
    counts_dict = dict([(p_id, np.zeros((VOTE_MAX_STEP+1, n_layers))) for p_id in counted_prompts])

    for grid_row, grid_col, tags, responses in users:
        grid_row = int(grid_row)
        grid_col = int(grid_col)

        tags = json.loads(tags)
        tag_inds = [0] + [tag_dict[t] for t in tags]  # Tag layers to write to
        responses = json.loads(responses)

        if len(responses) == 0:  # TODO: fix default value as {} instead of []
            continue

        for p_id, val in responses.items():
            p_id = int(p_id)
            # Map data
            if p_id in map_data_dict:
                map_data_dict[p_id].sums[grid_row, grid_col, tag_inds] += val
                map_data_dict[p_id].counts[grid_row, grid_col, tag_inds] += 1

            counts_dict[p_id][val, tag_inds] += 1

    return map_data_dict, {'counts': counts_dict, 'tag_key': tag_key}


def main(out_dir, tags, mapped_prompts, counted_prompts, users):
    map_data_dict, counts_obj = get_base_data(tags, mapped_prompts, counted_prompts, users)
    for p_id, base_data in map_data_dict.items():
        base_data.save_as(os.path.join(out_dir, "prompt-%d.npy" % p_id))
    np.save(os.path.join(out_dir, "_counts.npy"), np.array(counts_obj, dtype=object))


if __name__ == "__main__":
    from db_common import DB_CNX
    parser = argparse.ArgumentParser(description='Process some integers.')
    parser.add_argument('--tags', type=str, nargs='*', default=[], help='tag slugs')
    parser.add_argument('--mapped_prompts', type=str, nargs='*', default=[], help='prompts being mapped')
    parser.add_argument('--counted_prompts', type=str, nargs='*', default=[], help='prompts being counted')
    parser.add_argument('--user_src', type=str, nargs='?', default='users', help='part of the database query')
    parser.add_argument('--out_dir', type=str, nargs='?', default='./out', help='where numpy files are to be saved')
    args = parser.parse_args()

    out_dir = args.out_dir
    if not os.path.exists(out_dir):
        os.makedirs(out_dir)

    cursor = DB_CNX.cursor()

    if len(args.tags) == 0:
        cursor.execute("SELECT slug FROM tags")
        tags = list([str(e[0]) for e in cursor])
    else:
        tags = args.tags

    if len(args.mapped_prompts) == 0 and len(args.counted_prompts) == 0:
        cursor.execute("SELECT id, is_mapped FROM prompts")
        counted_prompts = []
        mapped_prompts = []
        for p_id, is_mapped in cursor:
            counted_prompts.append(p_id)
            if is_mapped:
                mapped_prompts.append(p_id)
    elif args.counted_prompts[0] == 'same':
        mapped_prompts = args.mapped_prompts
        counted_prompts = args.mapped_prompts
    else:
        mapped_prompts = args.mapped_prompts
        counted_prompts = args.counted_prompts

    user_src = args.user_src or "users"  # can be "users WHERE ..."
    query = "SELECT grid_row, grid_col, tags, responses FROM " + user_src
    cursor.execute(query)
    main(out_dir, tags, mapped_prompts, counted_prompts, cursor)
    cursor.close()
    DB_CNX.close()
