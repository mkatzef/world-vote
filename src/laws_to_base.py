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
import db_to_base
import loc_parser


def get_abortion_data(info):
    assert info["option0"] == "yes", "Data structure changed, expected 'yes' got " + str(info["option0"])
    assert info["option1"] == "no", "Data structure changed, expected 'no' got " + str(info["option1"])

    print("Reached")

if __name__ == "__main__":
    country_cells = loc_parser.load_from_disk()

    import matplotlib.pyplot as plt
    plt.figure()
    xs = []
    ys = []
    for c, v in country_cells.items():
        xs += [vv[1] for vv in v]
        ys += [-vv[0] for vv in v]
    plt.scatter(xs, ys)
    plt.show()
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
    }
    collected_data = {}
    for k, v in data_map.items():
        collected_data[k] = v["data_source"](v)
