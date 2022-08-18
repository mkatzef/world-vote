import numpy as np
import json

BASE_STEP_DEG = 15
DISPLAY_SIZE_DEGS = (360, 180)
assert all([d % BASE_STEP_DEG == 0 for d in DISPLAY_SIZE_DEGS]), "BASE_STEP_DEG must divide both display dimensions"
ORIGIN_COORD = (-180, 90)
COORD_ORDER = ((0, 0), (0, -1), (1, -1), (1, 0))
THRESHOLD_COUNT = 10  # only include results for counts >= THRESHOLD_COUNT
VOTE_MAX_STEP = 10


def get_step_size_deg(zoom):
    return BASE_STEP_DEG / (2 ** zoom)


def n_cells_xy(zoom):
    """
    Returns the number of cells in (x, y) for the given zoom level
    Intended use: to simplify iterating over the world's pixels
    """
    step_size = get_step_size_deg(zoom)
    return tuple(map(lambda x: int(round(x / step_size)), DISPLAY_SIZE_DEGS))


MAX_ZOOM = 3
MAX_COLS, MAX_ROWS = n_cells_xy(MAX_ZOOM)


def get_base_data(zoom, sums, counts, tag_key):
    return np.array({
            'properties': {
                'max_zoom': zoom
            },
            'res_sums': sums,
            'res_counts': counts,
            'tag_key': tag_key,
        }, dtype=object)


def save_base_data(out_path, zoom, sums, counts, tag_key):
    contents = get_base_data(zoom, sums, counts, tag_key)
    np.save(out_path, contents)


def compress_for_json(output, decimal_places):
    return json.loads(json.dumps(output), parse_float=lambda x: round(float(x), decimal_places))


def get_xy(lnglat, zoom=MAX_ZOOM):
    z_step = get_step_size_deg(zoom)
    col = (lnglat[0] - ORIGIN_COORD[0]) // z_step
    row = (ORIGIN_COORD[1] - lnglat[1]) // z_step
    return col, row
