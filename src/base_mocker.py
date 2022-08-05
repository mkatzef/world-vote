from common import *
import base_to_geojson

def generate_demo(demo_path, demo_zoom=MAX_ZOOM):
    nx, ny = n_cells_xy(demo_zoom)
    tag_key = ['all', 's_m', 's_f', 's_nb', 'r_at', 'r_ag', 'r_sp', 'r_ch', 'r_jw', 'r_ms']
    n_tags = len(tag_key)
    res_counts = np.random.randint(0, 1000, (ny, nx, n_tags))
    res_counts[:, :, 0] = np.amax(res_counts[:, :, :], axis=2)
    rnd_ratios = np.random.uniform(0, 1, (ny, nx, n_tags))
    res_sums = np.round(rnd_ratios * res_counts)
    res_sums[:, :, 0] = np.amax(res_sums[:, :, :], axis=2)
    save_base_data(demo_path, demo_zoom, res_sums, res_counts, tag_key)

print('Starting gen')
for prompt_id in range(1, 6):
    print(prompt_id)
    generate_demo(demo_path='out/prompt-%d.npy' % prompt_id)
print('Done')
