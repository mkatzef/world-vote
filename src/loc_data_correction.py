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
        {'db_key': 'access_token', 'format_spec': '%s', 'rng_gen': lambda c: "pLaceHolder" + str(c)},
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

CT_lnglat = (18.423300, -33.918861)
CT_col_row = get_xy(CT_lnglat)
melb_lnglat = (144.946457, -37.840935)
melb_col_row = get_xy(melb_lnglat)

hotspots = [
    # (n_users, ((row,std), (col,std), tag_profile, response_profile))
    #(100, [(CT_col_row[1], 2), (CT_col_row[0], 2), (('s_m', 0.7), ('s_f', 0.3)), ((8,(5,1)), (9,(5,1)))]),
    (320, [
        (melb_col_row[1], 2),
        (melb_col_row[0], 2),
        (
            ("s_m", 70/100),
            ("s_f", 30/100),
            ("s_nb", 5/100),
            ("b_nr", 40/100),
            ("b_c", 20/100),
            ("b_i", 10/100),
            ("b_h", 5/100),
            ("b_b", 2/100),
            ("b_j", 2/100),
            ("b_o", 5/100),
            ("a_u18", 10/100),
            ("a_1824", 20/100),
            ("a_2534", 50/100),
            ("a_35", 30/100),
            ("o_no", 10/100),
            ("o_hs", 20/100),
            ("o_ts", 20/100),
            ("o_bs", 30/100),
            ("o_pg", 10/100)
        ),
        (
            (1,  (2,3)),
            (2,  (1,2)),
            (3,  (8,4)),
            (4,  (3,4)),
            (5,  (9,2)),
            (6,  (4,4)),
            (7,  (5,4)),
            (8,  (2,3)),
            (9,  (8,2)),
            (10, (4,3)),
        )
    ]
    ),
    (130, [
        (CT_col_row[1], 2),
        (CT_col_row[0], 2),
        (
            ("s_m", 70/100),
            ("s_f", 30/100),
            ("s_nb", 5/100),
            ("b_nr", 40/100),
            ("b_c", 20/100),
            ("b_i", 10/100),
            ("b_h", 5/100),
            ("b_b", 2/100),
            ("b_j", 2/100),
            ("b_o", 5/100),
            ("a_u18", 10/100),
            ("a_1824", 20/100),
            ("a_2534", 50/100),
            ("a_35", 30/100),
            ("o_no", 10/100),
            ("o_hs", 20/100),
            ("o_ts", 20/100),
            ("o_bs", 30/100),
            ("o_pg", 10/100)
        ),
        (
            (1,  (8,3)),
            (2,  (7,2)),
            (3,  (2,4)),
            (4,  (5,4)),
            (5,  (2,2)),
            (6,  (5,4)),
            (7,  (5,4)),
            (8,  (8,3)),
            (9,  (2,2)),
            (10, (6,3)),
        )
    ]
)
]


if __name__ == "__main__":
    cursor = DB_CNX.cursor()

    c_col_range = [155, 180]
    c_row_range = [60, 80]

    min_id = 0
    max_id = 456

    kept_cells = [
      (169, 66),
      (170, 66),
      (172, 68),
      (173, 68),
      (174, 68),
      (175, 67),
      (175, 66),
      (176, 66),
      (176, 65),
      (174, 70),
      (173, 70),
      (169, 66),
      (170, 66),
      (177, 62),
      (157, 64),
      (157, 65),
      (158, 66),
      (157, 66)
    ]

    n_kept_cells = len(kept_cells)

    cursor.execute("SELECT id, access_token FROM users")
    changes = []
    """for u_obj in cursor:
        u_id, grid_row, grid_col = map(int, u_obj)
        if not (min_id <= u_id <= max_id):
            continue

        if c_col_range[0] <= grid_col <= c_col_range[1]:
            if c_row_range[0] <= grid_row <= c_row_range[1]:
                dst = kept_cells[random.randint(0, n_kept_cells-1)]
                changes.append((u_id, dst))
    """
    tc = 0
    cc = 0
    for u_obj in cursor:
        u_id = int(u_obj[0])
        access_token = u_obj[1]
        if not (min_id <= u_id <= max_id):
            continue

        changes.append((u_id, "pLaceHolder" + str(access_token)))

    print(len(changes), changes[:5])
    for change_id, change_at in changes:
        cursor.execute("UPDATE users SET access_token='%s' WHERE id=%d" % (change_at, change_id))


    DB_CNX.commit()
    cursor.close()
    DB_CNX.close()
