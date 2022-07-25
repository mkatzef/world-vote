# World Vote

## Structure
`index.html`: mapbox map to display vector tiles  
`geojson_gen.py`: create geojson files (placeholder data for now)  
`c2t.sh`: automated conversion process from `zoom array`s to `mbtiles`

## Data formats
* `base`: `.npy` containing raw data sums and counts; one per data channel e.g., `demo0.npy`  
* `zoom array`: `.npy` of the same name as the data channel, averaged, filtered, and binned for multiple zoom levels, e.g., `z04/demo0.npy`  
* `cells`: `.json` all `zoom array`s at the current zoom aggregated into geojson (one polygon per array cell), e.g., `z04/cells.json`
* `tiles`: `.mbtiles` directly from the `cells` file for that zoom level, e.g., `z04/tiles.mbtiles`  
* `tiles-comb`: `.mbtiles` combining each of the individual `tiles` files (this is the output file ready for mapbox), e.g., `tiles-comb.mbtiles`  

## Processing steps
The overall data flow in this project is as follows:
0. **a)** Read from a database to produce `base.npy` data, OR **b)** use `geojson_gen.py` to create placeholder `base.npy` data  
1. Convert the `base.npy` data into `zoom array`s containing statistics at multiple resolutions
2. Convert collections of `zoom array`s into `cells`


## Web
Early versions had a simple `index.html` that could be loaded in a web browser directly.

We're not in Kansas anymore; navigate to `./web` and run:  
`php artisan serve`  
OR  
`art serve`  
If you have the recommended `laravel` shell config.

The locally-hosted URL will appear in your terminal when running. Navigate to that link in a web browser and continue debugging.


## Next steps
* Get double-handle range slider
* Connect votes to db
* Add recall for modification
* Display user token when "logged in"
* Add google ads to web app  
* Move database processing to amazon  
* Add filters based on tags (show only the cells with tag ratio in [min, max])  
Optional:
* Add vote categories  
* Add vote search  


## Useful tools
* `tippecanoe`: CLI convert geojson into mbtiles  
* `mb-util`: CLI convert mbtiles into vector tile directory structure (optional preprocessing for speed)  
* `mbtiles-serve`: CLI npm package to serve mbtiles locally (debugging)  
* `mbview`: CLI from mapbox to view locally  
