

# Load data (preferably as a spatially-indexed dataset like R-Tree)

# Generate tiles using the following steps (bottom-up):
#  For current_zoom in range(max_zoom, min_zoom-1, -1)
#    For each square at current_zoom:
#      if current_zoom == max_zoom:
#        data = query data from source
#      else:
#        data = query data from children
#      % where data is a primitive data struct (sums, counts)
#      store data at cell

# Total surface area of earth: 510 million km2
# If we keep cells 1km2 min size,
#  that'll take tens of seconds just to process an EMPTY map
# With a dataset of tens of millions of points... R-Trees will save us


"""
Treats the world map as an image array with integral indices
0,0 is the top-left cell, defined by its top-left point of [-180, 90]
"""


import json
import random
import numpy as np
from skimage.measure import block_reduce


BASE_STEP_DEG = 30
DISPLAY_SIZE_DEGS = (360, 180)
assert all([d % BASE_STEP_DEG == 0 for d in DISPLAY_SIZE_DEGS]), "BASE_STEP_DEG must divide both display dimensions"
ORIGIN_COORD = (-180, 90)
COORD_ORDER = ((0, 0), (0, -1), (1, -1), (1, 0))

BASE_GEOJSON = json.loads("""{
  "type": "FeatureCollection",
  "features": []
}""")

BASE_POLY_STR = """{
  "type": "Feature",
  "geometry": {
    "type": "Polygon",
    "coordinates": []
  },
  "properties": {
      "stroke-opacity": 0,
      "fill": "#ffffff",
      "fill-opacity": 0.9
  }
}"""


def get_step_size_deg(zoom):
    return BASE_STEP_DEG / (2 ** zoom)


def n_cells_xy(zoom):
    """
    Returns the number of cells in (x, y) for the given zoom level
    Intended use: to simplify iterating over the world's pixels
    """
    step_size = get_step_size_deg(zoom)
    return tuple(map(lambda x: int(round(x / step_size)), DISPLAY_SIZE_DEGS))


def get_bbox_for_coord(lonlat, step_size):
    lon, lat = lonlat
    return [(lon + dx * step_size, lat + dy * step_size) for dx, dy in COORD_ORDER]


def get_bbox_for_cell(zoom, x, y, step_size=None):
    if step_size is None:
        step_size = get_step_size_deg(zoom)

    lon, lat = ORIGIN_COORD
    lon += x * step_size
    lat -= y * step_size
    return get_bbox_for_coord((lon, lat), step_size)


def get_score(bbox):
    # query
    return random.randint(0, 100)


def get_cells(zoom, score_func):
    nx, ny = n_cells_xy(zoom)

    cells = []
    for col in range(nx):
        for row in range(ny):
            bbox = get_bbox_for_cell(zoom, col, row)
            score = score_func(bbox)
            cells.append((bbox, score))

    return cells


def write_cells(cells, zoom):
    max_score = max([s for _, s in cells])
    def opacity_func(score):
        return score / max_score

    output = BASE_GEOJSON.copy()
    features = []
    for bbox, score in cells:
        cell_poly = json.loads(BASE_POLY_STR)
        cell_poly['geometry']['coordinates'] = [bbox + [bbox[0]]]
        cell_poly['properties']['fill-opacity'] = opacity_func(score)

        features.append(cell_poly)
    output['features'] = features

    with open('data/cells%02d.json' % zoom, 'w') as outfile:
        outfile.write(json.dumps(output))


def aggregate(cells):
    return block_reduce(cells, block_size=2, func=np.sum)


def main():
    for zoom in range(5):
        cells = get_cells(zoom, get_score)
        nx, ny = n_cells_xy(zoom)
        print("Zoom:", zoom)
        print("Cells:", nx, "*", ny, "=", nx * ny)
        print("Collected")
        write_cells(cells, zoom)
        print("Written")


if __name__ == "__main__":
    main()
