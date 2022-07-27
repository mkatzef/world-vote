# Open mysql database
# Read all tags
# Read all mapped prompts
# Iterate over all records, binning results into each raw array (sums, counts)
# save as separate .npy files

import mysql.connector
import json
import numpy as np
import os
import sys

from common import *

if len(sys.argv) < 2:
    print("Insufficient arguments, needs out_dir")
    sys.exit(1)

out_dir = sys.argv[-1]
if not os.path.exists(out_dir):
    try:
        os.makedirs(out_dir)
    except:
        print("Could not open or create given directory:", out_dir)


cnx = mysql.connector.connect(user='root', password='',
                              host='127.0.0.1',
                              database='world_vote')
cursor = cnx.cursor()

cursor.execute("SELECT slug FROM tags")
all_tags = list([e[0] for e in cursor])
N_TAGS = len(all_tags)

cursor.execute("SELECT id FROM prompts WHERE is_mapped = 1")
mapped_prompts = list([e[0] for e in cursor])
N_PROMPTS = len(mapped_prompts)

class BaseData:
    def __init__(self, p_id, n_rows=MAX_ROWS, n_cols=MAX_COLS):
        self.p_id = p_id
        self.sums = np.empty((n_rows, n_cols), dtype=float)
        self.counts = np.empty((n_rows, n_cols), dtype=float)

    def as_np(self):
        return get_base_data(MAX_ZOOM, self.sums, self.counts)

    def save_as(self, out_path):
        save_base_data(out_path, MAX_ZOOM, self.sums, self.counts)


base_data_dict = dict([(p_id, BaseData(p_id)) for p_id in mapped_prompts])
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
        if p_id in base_data_dict:  # Mappable
            base_data_dict[p_id].sums[grid_row, grid_col] += val
            base_data_dict[p_id].counts[grid_row, grid_col] += 1

for p_id, base_data in base_data_dict.items():
    base_data.save_as(os.path.join(out_dir, "vote-layer-%d.npy" % p_id))

cursor.close()
cnx.close()
