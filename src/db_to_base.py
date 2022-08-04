# Open mysql database
# Read all tags
# Read all mapped prompts
# Iterate over all records, binning results into each raw array (sums, counts)
# save as separate .npy files

import json
import numpy as np
import os
import sys

from common import *
from db_common import DB_CNX

if len(sys.argv) < 2:
    print("Insufficient arguments, needs out_dir")
    sys.exit(1)

out_dir = sys.argv[-1]
if not os.path.exists(out_dir):
    try:
        os.makedirs(out_dir)
    except:
        print("Could not open or create given directory:", out_dir)


DB_CNX = mysql.connector.connect(user='root', password='',
                              host='127.0.0.1',
                              database='world_vote')
cursor = DB_CNX.cursor()

cursor.execute("SELECT slug FROM tags")
all_tags = list([e[0] for e in cursor])
N_TAGS = len(all_tags)

cursor.execute("SELECT id, is_mapped FROM prompts")
all_prompts = []
mapped_prompts = []
for p_id, is_mapped in cursor:
    all_prompts.append(p_id)
    if is_mapped:
        mapped_prompts.append(p_id)

class BaseData:
    def __init__(self, p_id, n_rows=MAX_ROWS, n_cols=MAX_COLS):
        self.p_id = p_id
        self.sums = np.empty((n_rows, n_cols), dtype=float)
        self.counts = np.empty((n_rows, n_cols), dtype=float)

    def save_as(self, out_path):
        save_base_data(out_path, MAX_ZOOM, self.sums/VOTE_MAX_STEP, self.counts)


map_data_dict = dict([(p_id, BaseData(p_id)) for p_id in mapped_prompts])
counts_dict = dict([(p_id, np.zeros((VOTE_MAX_STEP+1,))) for p_id in all_prompts])
# TODO: tags

query = ("SELECT grid_row, grid_col, tags, responses FROM users")
cursor.execute(query)
for grid_row, grid_col, tags, responses in cursor:
    grid_row = int(grid_row)
    grid_col = int(grid_col)
    #tags = json.loads(tags)
    responses = json.loads(responses)  # TODO: replace with regex for performance
    for p_id, val in responses.items():
        p_id = int(p_id)

        # Map data
        if p_id in map_data_dict:
            map_data_dict[p_id].sums[grid_row, grid_col] += val
            map_data_dict[p_id].counts[grid_row, grid_col] += 1

        counts_dict[p_id][val] += 1

for p_id, base_data in map_data_dict.items():
    base_data.save_as(os.path.join(out_dir, "prompt-%d.npy" % p_id))

np.save(os.path.join(out_dir, "_counts.npy"), np.array(counts_dict, dtype=object))

cursor.close()
DB_CNX.close()
