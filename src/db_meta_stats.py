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


def print_meta_stats(tags, mapped_prompts, counted_prompts, users):
    tag_key = ['all'] + tags  # Order of data: starts with all, followed by each tag
    tag_dict = dict([(k, i) for i, k in enumerate(tags)])
    tag_counts = dict([(t, 0) for t in tag_key])
    prompt_counts = dict([(p, 0) for p in counted_prompts])

    for grid_row, grid_col, tags, responses in users:
        grid_row = int(grid_row)
        grid_col = int(grid_col)

        tags = json.loads(tags)
        for t in tags:
            tag_counts[t] += 1
        tag_counts['all'] += 1
        responses = json.loads(responses)

        if len(responses) == 0:  # TODO: fix default value as {} instead of []
            continue

        for p_id, val in responses.items():
            p_id = int(p_id)
            prompt_counts[p_id] += 1

    categories = [
        ("Age", "a_"),
        ("Sex", "s_"),
        ("Beliefs", "b_"),
        ("Education", "o_")
    ]
    cat_data = dict((c, []) for _, c in categories)

    for k, v in tag_counts.items():
        for cat_name, cat_prefix in categories:
            if k.startswith(cat_prefix):
                cat_data[cat_prefix].append((k, v))

    for cat_name, cat_prefix in categories:
        print(cat_name)
        print(cat_data[cat_prefix])

    return tag_counts


def main(out_dir, tags, mapped_prompts, counted_prompts, users):
    print_meta_stats(tags, mapped_prompts, counted_prompts, users)


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
