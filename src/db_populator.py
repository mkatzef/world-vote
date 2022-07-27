# Open mysql database
# Read all tags
# Read all mapped prompts
# Iterate over all records, binning results into each raw array (sums, counts)
# save as separate .npy files

import mysql.connector
import random
import json

from common import *

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

def get_sql_str(sql_parts):
    return "INSERT INTO users (%s) VALUES (%s)" % tuple([",".join([e[k] for e in sql_parts]) for k in ['db_key', 'format_spec']])

def get_sql_val(sql_parts):
    return tuple([e['rng_gen']() for e in sql_parts])

tag_prob = 3 / N_TAGS  # Assume 3 tags on average
def get_rnd_tags():
    return json.dumps([t for t in all_tags if random.uniform(0, 1) < tag_prob])

prompt_prob = 3 / N_PROMPTS  # Assume 1 mapped tag on average
def get_rnd_responses():
    return json.dumps(dict([(p, random.randint(0, 10)) for p in mapped_prompts if random.uniform(0, 1) < prompt_prob]))

COUNT = 0
sql_parts = [
    {'db_key': 'access_token', 'format_spec': '%s', 'rng_gen': lambda: str(COUNT)},
    {'db_key': 'share_token', 'format_spec': '%s', 'rng_gen': lambda: "s" + str(COUNT)},
    {'db_key': 'grid_row', 'format_spec': '%s', 'rng_gen': lambda: str(random.randint(0, MAX_ROWS - 1))},
    {'db_key': 'grid_col', 'format_spec': '%s', 'rng_gen': lambda: str(random.randint(0, MAX_COLS - 1))},
    {'db_key': 'tags', 'format_spec': '%s', 'rng_gen': get_rnd_tags},
    {'db_key': 'responses', 'format_spec': '%s', 'rng_gen': get_rnd_responses},
]

def add_n_users(n, cursor, cnx, start_token=0):
    global COUNT
    COUNT = start_token
    for i in range(n):
        sql = get_sql_str(sql_parts)
        val = get_sql_val(sql_parts)
        COUNT += 1
        cursor.execute(sql, val)
    cnx.commit()

add_n_users(0, cursor, cnx, start_token=50010)

cursor.close()
cnx.close()
