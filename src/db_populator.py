# Open mysql database
# Read all tags
# Read all mapped prompts
# Iterate over all records, binning results into each raw array (sums, counts)
# save as separate .npy files

import random
import json
import numpy as np

from common import *
from db_common import DB_CNX


def UNIFORM_01():
    return np.random.uniform()


def LUCKY(c):
    return UNIFORM_01() < c


def VOTE(v):
    return round(np.clip(v, 0, 10))


def NORMAL(m, std):
    return np.random.normal(m, std)


def get_sql_str(sql_parts):
    return "INSERT INTO users (%s) VALUES (%s)" % tuple([",".join([e[k] for e in sql_parts]) for k in ['db_key', 'format_spec']])


def get_sql_val(sql_parts, count):
    return tuple([e['rng_gen'](count) for e in sql_parts])


def get_rnd_tags(tag_prob):
    return json.dumps([t for t in all_tags if LUCKY(tag_prob)])


def get_rnd_responses(prompt_prob):
    return json.dumps(dict([(p, random.randint(0, 10)) for p in all_prompts if LUCKY(prompt_prob)]))


def add_n_users(n, cursor, start_token=0, sql_parts=None):
    if sql_parts is None:
        sql_parts = get_default_sql_parts()

    count = start_token
    for i in range(n):
        sql = get_sql_str(sql_parts)
        val = get_sql_val(sql_parts, count)
        count += 1
        cursor.execute(sql, val)


def get_default_sql_parts():
    return [
        {'db_key': 'access_token', 'format_spec': '%s', 'rng_gen': lambda c: str(c)},
        {'db_key': 'grid_row', 'format_spec': '%s', 'rng_gen': lambda c: str(random.randint(0, MAX_ROWS - 1))},
        {'db_key': 'grid_col', 'format_spec': '%s', 'rng_gen': lambda c: str(random.randint(0, MAX_COLS - 1))},
        {'db_key': 'tags', 'format_spec': '%s', 'rng_gen': lambda c: get_rnd_tags(3 / N_PROMPTS)},
        {'db_key': 'responses', 'format_spec': '%s', 'rng_gen': lambda c: get_rnd_responses(3 / N_PROMPTS)},
    ]


def get_hotspot_sql_parts(row_dist, col_dist, tag_profile, response_profile):
    return [
        {'db_key': 'access_token', 'format_spec': '%s', 'rng_gen': lambda c: str(c)},
        {'db_key': 'grid_row', 'format_spec': '%s', 'rng_gen': lambda c: str(int(NORMAL(*row_dist)))},
        {'db_key': 'grid_col', 'format_spec': '%s', 'rng_gen': lambda c: str(int(NORMAL(*col_dist)))},
        {
            'db_key': 'tags',
            'format_spec': '%s',
            'rng_gen': lambda c: json.dumps([t for t, t_prob in tag_profile if LUCKY(t_prob)])
        },
        {
            'db_key': 'responses',
            'format_spec': '%s',
            'rng_gen': lambda c: json.dumps(dict([(p, VOTE(NORMAL(*p_dist))) for p, p_dist in response_profile]))
        },
    ]

hotspots = [
    # (n_users, ((row,std), (col,std), tag_profile, response_profile))
    (1000, [(MAX_ROWS/2, 10), (MAX_COLS / 2, 15), (('s_m', 0.7), ('s_f', 0.3)), ((1,(5,3)), (2,(7,2)))]),
    (100, [(MAX_ROWS/3, 4), (2* MAX_COLS / 3, 4), (('s_m', 0.7), ('s_f', 0.3)), ((1,(5,3)), (2,(7,2)))]),
]


if __name__ == "__main__":
    cursor = DB_CNX.cursor()

    cursor.execute("SELECT slug FROM tags")
    all_tags = list([e[0] for e in cursor])
    N_TAGS = len(all_tags)

    cursor.execute("SELECT id FROM prompts")
    all_prompts = list([e[0] for e in cursor])
    N_PROMPTS = len(all_prompts)

    sql_parts = get_hotspot_sql_parts(*hotspots[0])
    add_n_users(5, cursor, start_token=1, sql_parts=sql_parts)

    DB_CNX.commit()
    cursor.close()
    DB_CNX.close()
