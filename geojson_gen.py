"""
Treats the world map as an image array with integral indices
0,0 is the top-left cell, defined by its top-left point of [-180, 90]
"""

import json
import random
import numpy as np
from skimage.measure import block_reduce
import os

BASE_STEP_DEG = 30
DISPLAY_SIZE_DEGS = (360, 180)
assert all([d % BASE_STEP_DEG == 0 for d in DISPLAY_SIZE_DEGS]), "BASE_STEP_DEG must divide both display dimensions"
ORIGIN_COORD = (-180, 90)
COORD_ORDER = ((0, 0), (0, -1), (1, -1), (1, 0))


def BASE_GEOJSON():
    return {
         "type": "FeatureCollection",
         "features": []
    }

def BASE_POLY():
    return {
        "type": "Feature",
        "geometry": {
            "type": "Polygon",
            "coordinates": []
        },
        "properties": {}
    }


def generate_demo(demo_name='demo.npy', demo_zoom=4):
    nx, ny = n_cells_xy(demo_zoom)
    res_counts = np.random.randint(0, 1000, (ny, nx))
    rnd_ratios = np.random.uniform(0, 1, (ny, nx))
    res_sums = np.round(rnd_ratios * res_counts)
    contents = np.array({'properties': {'max_zoom': demo_zoom}, 'res_counts': res_counts, 'res_sums': res_sums}, dtype=object)
    np.save('stats/%s' % demo_name, contents)


def get_step_size_deg(zoom):
    return BASE_STEP_DEG / (2 ** zoom)


def n_cells_xy(zoom):
    """
    Returns the number of cells in (x, y) for the given zoom level
    Intended use: to simplify iterating over the world's pixels
    """
    step_size = get_step_size_deg(zoom)
    return tuple(map(lambda x: int(round(x / step_size)), DISPLAY_SIZE_DEGS))


def get_bbox_from_anchor(lonlat, step_size):
    lon, lat = lonlat
    return [(lon + dx * step_size, lat + dy * step_size) for dx, dy in COORD_ORDER]


def get_bbox_for_cell(zoom, x, y, step_size=None):
    if step_size is None:
        step_size = get_step_size_deg(zoom)

    lon, lat = ORIGIN_COORD
    lon += x * step_size
    lat -= y * step_size
    return get_bbox_from_anchor((lon, lat), step_size)


def bin_stats_to_zooms(stats_dir):
    for stat_filename in next(os.walk(stats_dir))[2]:
        print("File:", stat_filename, end=', ')
        if not stat_filename.endswith('.npy'):
            print("Skipping non .npy file:", stat_filename)
            continue

        # For each channel, we have a high res array
        base_data = np.load(os.path.join(stats_dir, stat_filename), allow_pickle=True).tolist()
        properties = base_data['properties']
        max_zoom = properties['max_zoom']
        agg_mode = 'absolute'  # absolute: per-cell ratios, relative: per-cell ratios AND min/max scaled
        sums = base_data['res_sums']
        counts = base_data['res_counts']

        threshold_count = 500  # only include results for counts > threshold_count
        assert threshold_count > 0, "Divide by zero can occur"
        sentinel_val = -1

        print("max zoom:", max_zoom)
        for zoom in range(max_zoom, -1, -1):
            pathname = os.path.join(stats_dir, "z%02d" % zoom)
            if not os.path.exists(pathname):
                os.makedirs(pathname)
            if zoom < max_zoom:
                sums = block_reduce(sums, block_size=(2, 2), func=np.sum)
                counts = block_reduce(counts, block_size=(2, 2), func=np.sum)

            kept_indices = counts > threshold_count
            # Sums
            filtered_data = np.where(kept_indices, sums, sentinel_val)  # hide low-volume data for privacy
            # Means
            filtered_data[kept_indices] /= counts[kept_indices]  # Guaranteed to avoid divide by 0

            if agg_mode == 'relative':
                # Useful in the case where values are low globally (like smaller religious groups)
                # Possibly more useful to use a detailed/mult-layered colour scale
                amin = sums.amin()
                amax = sums.amax()
                filtered_data[kept_indices] = (filtered_data[kept_indices] - amin) / (amax - amin)

            outname = os.path.join(pathname, stat_filename)
            np.save(outname, filtered_data)


def write_cells(stats_dir):
    for zoom_dir in next(os.walk(stats_dir))[1]:  # "z%02d"
        print("Entering:", zoom_dir)
        zoom = int(zoom_dir[1:])
        # Load all data at this zoom into memory
        stat_list = []  # (label, array) pairs
        zoom_path = os.path.join(stats_dir, zoom_dir)
        for stat_filename in next(os.walk(zoom_path))[2]:
            if not stat_filename.endswith('.npy'):
                print("Skipping non .npy file:", stat_filename)
                continue
            stat_label = stat_filename[:-4]
            stat_path = os.path.join(zoom_path, stat_filename)
            stat_list.append((stat_label, np.load(stat_path)))

        output = BASE_GEOJSON()
        features = []
        nx, ny = n_cells_xy(zoom)
        for row in range(ny):
            for col in range(nx):
                bbox = get_bbox_for_cell(zoom, col, row)
                cell_poly = BASE_POLY()
                cell_poly['geometry']['coordinates'] = [bbox + [bbox[0]]]
                for slabel, sarr in stat_list:
                    cell_poly['properties'][slabel] = float(sarr[row][col])

                features.append(cell_poly)
        output['features'] = features

        out_path = os.path.join(zoom_path, 'cells.json')
        with open(out_path, 'w') as outfile:
            outfile.write(json.dumps(output))


def main():
    bin_stats_to_zooms(stats_dir='stats')
    write_cells(stats_dir='stats')


if __name__ == "__main__":
    main()
    #('demo1.npy')
