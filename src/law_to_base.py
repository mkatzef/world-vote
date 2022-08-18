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
from db_to_base import BaseData
import loc_parser

law_data_dir = "./law_data"


def load_law_json(filename):
    with open(os.path.join(law_data_dir, filename), 'r') as infile:
        return json.load(infile)


def get_abortion_data(info, country_names):
    """
    Returns {country name : vote10}
    """
    assert info["option0"] == "yes", "Data structure changed, expected 'yes' got " + str(info["option0"])
    assert info["option1"] == "no", "Data structure changed, expected 'no' got " + str(info["option1"])

    country_descs = load_law_json("abortion.json")
    level_map = {
        'No restriction': 0,
        'To preserve physical/mental health': 3,
        'To preserve physical health': 4,
        'To preserve health/on socioeconomic grounds': 4,
        'Varies by state': 5,
        "To save a woman's life": 7,
        'Prohibited altogether': 10,
    }

    ret = {}
    for desc in country_descs:
        country = desc['country'].lower()
        if country not in country_names:
            continue
        level = desc['legality']
        ret[country] = level_map[level]

    return ret


def get_death_penalty_data(info, country_names):
    """
    Returns {country name : vote10}
    """
    assert info["option0"] == "yes", "Data structure changed, expected 'yes' got " + str(info["option0"])
    assert info["option1"] == "no", "Data structure changed, expected 'no' got " + str(info["option1"])

    country_descs = load_law_json("death_penalty.json")
    level_map = {
        'Legal': 0,
        'Extreme only': 4,
        'Suspended': 6,
        'Abolished': 10,
    }

    ret = {}
    for desc in country_descs:
        country = desc['country'].lower()
        if country not in country_names:
            continue
        level = desc['status']
        ret[country] = level_map[level]

    return ret


def get_gay_marriage_data(info, country_names):
    """
    Returns {country name : vote10}
    """
    assert info["option0"] == "yes", "Data structure changed, expected 'yes' got " + str(info["option0"])
    assert info["option1"] == "no", "Data structure changed, expected 'no' got " + str(info["option1"])

    country_descs = load_law_json("gay_marriage.json")

    ret = {}
    for desc in country_descs:
        country = desc['country'].lower()
        if country not in country_names:
            continue
        level = desc['legalizeYear']
        ret[country] = 0 if level > 0 else 10

    return ret


def get_euthanasia_data(info, country_names):
    """
    Returns {country name : vote10}
    """
    assert info["option0"] == "yes", "Data structure changed, expected 'yes' got " + str(info["option0"])
    assert info["option1"] == "no", "Data structure changed, expected 'no' got " + str(info["option1"])

    country_descs = load_law_json("euthanasia.json")
    level_map = {
        'activeLegal': 0,
        'passiveLegal': 3,
        'illegal': 10,
    }

    ret = {}
    for desc in country_descs:
        country = desc['country'].lower()
        if country not in country_names:
            continue
        level = desc['status']
        ret[country] = level_map[level]

    return ret


def get_weed_data(info, country_names):
    """
    Returns {country name : vote10}
    """
    assert info["option0"] == "yes", "Data structure changed, expected 'yes' got " + str(info["option0"])
    assert info["option1"] == "no", "Data structure changed, expected 'no' got " + str(info["option1"])

    country_descs = load_law_json("weed.json")

    ret = {}
    for desc in country_descs:
        country = desc['country'].lower()
        if country not in country_names:
            continue
        level = desc['recreationalUsage']
        ret[country] = 3 if "illegal" not in level.lower() else 8

    return ret


def write_law_base(out_dir, p_id, country_votes, country_cells, cell_count=THRESHOLD_COUNT):
    """
    Writes base data to the given directory
    """
    bd = BaseData(p_id, tag_key=['all'])
    tag_inds = 0
    bd.sums[:, :, :] = 0
    bd.counts[:, :, :] = 0

    for country, vote in country_votes.items():
        for grid_row, grid_col in country_cells[country]:
            bd.sums[grid_row, grid_col, tag_inds] = vote
            bd.counts[grid_row, grid_col, tag_inds] = cell_count

    bd.save_as(os.path.join(out_dir, "prompt-%d.npy" % p_id))


if __name__ == "__main__":
    country_cells = dict([(k.lower(), v) for k, v in loc_parser.load_from_disk().items()])
    country_names = set(country_cells.keys())

    """
    Needed information:
    Prompts
        ID
        Caption
        Option0
        Option1
    Data source (including how to read data source in as {country: vote10})
    """

    data_map = {
        "abortion": {
            "promptId": 4,
            "option0": "yes",
            "option1": "no",
            "data_source": get_abortion_data,
        },
        "death_penalty": {
            "promptId": 9,
            "option0": "yes",
            "option1": "no",
            "data_source": get_death_penalty_data,
        },
        "gay_marriage": {
            "promptId": 8,
            "option0": "yes",
            "option1": "no",
            "data_source": get_gay_marriage_data,
        },
        "euthanasia": {
            "promptId": 10,
            "option0": "yes",
            "option1": "no",
            "data_source": get_euthanasia_data,
        },
        "weed": {
            "promptId": 6,
            "option0": "yes",
            "option1": "no",
            "data_source": get_weed_data,
        },
    }

    out_dir = "./law_base"
    for k, v in data_map.items():
        country_votes = v["data_source"](v, country_names)
        write_law_base(out_dir, v["promptId"], country_votes, country_cells)
