import numpy as np
import matplotlib.pyplot as plt
import os

from common import *
import sys


LOC_DATA_DIR = "./loc_data"
o_lng, o_lat = ORIGIN_COORD


def flip(ll):
    return (ll[1], ll[0])


def get_country_from_latlngt(latlng):
    c = COUNTRY_CHECKER.getCountry(countries.Point(*latlng))
    if c is not None:
        return c.shape.GetField('NAME')


def get_country_from_lnglat(lnglat):
    return get_country_from_latlngt(flip(lnglat))


def get_xy(lnglat, zoom):
    z_step = get_step_size_deg(zoom)
    col = (lnglat[0] - o_lng) // z_step
    row = (o_lat - lnglat[1]) // z_step
    return col, row


def get_cell_set(lonlats, zoom=MAX_ZOOM):
    """
    Returns a list of all the cells that the given lonlats occupy
    """
    cell_set = set()
    for lonlat in lonlats:
        cell_set.add(get_xy(lonlat, zoom))

    return cell_set


def fill_in_cell_set(cell_set):
    """
    Returns a modified version of the given set of cells by adding in the cells
    that are contained by their border elements
    """
    cell_set = set(cell_set)
    start_cells = np.array(list(cell_set))
    mean_pos = np.round(np.mean(start_cells, axis=0))

    def dist(a, b):
        return np.linalg.norm(a - b)

    for pos in start_cells:
        if all(pos == mean_pos):
            continue
        dx, dy = mean_pos - pos
        dir_x = dx / abs(dx) if dx else 0
        dir_y = dy / abs(dy) if dy else 0
        while dist(mean_pos, pos):
            step_x = pos + np.array([dir_x, 0])
            step_y = pos + np.array([0, dir_y])
            if dist(mean_pos, step_x) < dist(mean_pos, step_y):
                pos = step_x
            else:
                pos = step_y

            cell = tuple(pos)
            if cell in cell_set:
                break
            cell_set.add(cell)

    return cell_set


def plot_cell_set(cell_set):
    cells = np.array(list(cell_set))
    min_x, min_y = np.min(cells, axis=0)
    max_x, max_y = np.max(cells, axis=0)

    img = np.zeros((int(max_y - min_y + 1), int(max_x - min_x + 1)))
    for x, y in cells:
        img[int(y - min_y)][int(x - min_x)] = 1

    plt.imshow(img)


def find_cells_from_outlines(loc_dir=LOC_DATA_DIR):
        # DATA SOURCE: https://gadm.org/index.html
    kml_paths = []
    for lfilename in next(os.walk(loc_dir))[2]:
        if lfilename.endswith('.kml'):
            kml_paths.append((lfilename[:-4], os.path.join(loc_dir, lfilename)))

    start_marker = "<coordinates>"
    sml = len(start_marker)
    end_marker = "</coordinates>"

    for kml_name, kml_path in kml_paths:
        with open(kml_path, 'r') as infile:
            kml_contents = infile.read()

        start_i = kml_contents.find(start_marker) + sml
        end_i = kml_contents.find(end_marker)
        max_lonlats = []
        while start_i >= 0 and end_i >= 0:
            coord_inner = kml_contents[start_i : end_i]
            lonlat_tokens = coord_inner.split(' ')
            lonlats = [tuple(map(float, lonlat_token.split(','))) for lonlat_token in lonlat_tokens]
            if len(lonlats) > len(max_lonlats):
                max_lonlats = lonlats
            kml_contents = kml_contents[end_i+1 :]
            start_i = kml_contents.find(start_marker) + sml
            end_i = kml_contents.find(end_marker)

        lonlats = max_lonlats
        cs = get_cell_set(lonlats, zoom=3)
        fcs = fill_in_cell_set(cs)
        plt.figure()
        plot_cell_set(cs)
        plt.figure()
        plot_cell_set(fcs)
        np.save(os.path.join(loc_dir, kml_name + ".npy"), np.array(list(fcs)))

    plt.show()


def get_all_country_cells(row_range, col_range, z_step):
    country_cells = {}  # name: list of cell indices
    n_rows = len(row_range)
    init_row = None
    for row in row_range:
        if init_row is None:
            init_row = row
        cell_center_lat = o_lat - (row + 0.5) * z_step
        print("%3d%%" % (100*(row - init_row) / n_rows))
        for col in col_range:
            cell = (row, col)
            cell_center_lng = o_lng + (col + 0.5) * z_step
            lnglat = (cell_center_lng, cell_center_lat)
            country = get_country_from_lnglat(lnglat)
            if country is None:
                continue

            if country in country_cells:
                country_cells[country].append(cell)
            else:
                country_cells[country] = [cell]
    return country_cells


def collect_in_parallel(out_dir="./loc_parts", n_workers=None, worker_index=None, zoom=MAX_ZOOM):
    if n_workers is None or worker_index is None:
        n_workers, worker_index = map(int, sys.argv[-2:])  # worker_index 0-indexed

    z_step = get_step_size_deg(zoom)
    n_cols, n_rows = n_cells_xy(zoom)
    print(n_cols, n_rows)

    rpw = int(np.ceil(n_rows / n_workers))  # rows per worker
    start_row = worker_index*rpw
    end_row = min((worker_index+1)*rpw, n_rows)
    row_range = range(start_row, end_row)
    country_cells = get_all_country_cells(row_range, range(n_cols), z_step)
    out_name = os.path.join(out_dir, "country_cells_z%d_n%d_w%d.npy" % (zoom, n_workers, worker_index))
    np.save(out_name, np.array(country_cells, dtype=object))


def merge_country_cells(src, dst):
    for k, v in src.items():
        if k in dst:
            dst[k] += v
        else:
            dst[k] = v
    return dst


def load_from_disk(in_dir="./loc_parts"):
    country_cells = {}
    for filename in next(os.walk(in_dir))[2]:
        if not filename.endswith('.npy'):
            print("Skipping non npy file:", filename)
            continue
        filepath = os.path.join(in_dir, filename)
        new_country_cells = np.load(filepath, allow_pickle=True).tolist()

        country_cells = merge_country_cells(new_country_cells, country_cells)
    return country_cells


if __name__ == "__main__":
    import countries.countries as countries
    COUNTRY_CHECKER = countries.CountryChecker(
        os.path.join(LOC_DATA_DIR, 'TM_WORLD_BORDERS-0.3', 'TM_WORLD_BORDERS-0.3.shp')
    )

    #collect_in_parallel()
    d = load_from_disk()
    print(sorted(list(d.keys())))
