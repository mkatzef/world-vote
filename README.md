# World Vote

## Structure
`src/`
`base_to_geojson.py`: create geojson files  
`db_to_mbtiles.sh`: automated conversion process from database to `mbtiles`

## Data formats
* `base`: `.npy` containing raw data sums and counts; one per data channel e.g., `demo0.npy`  
* `zoom array`: `.npy` of the same name as the data channel, averaged, filtered, and binned for multiple zoom levels, e.g., `z04/demo0.npy`  
* `cells`: `.json` all `zoom array`s at the current zoom aggregated into geojson (one polygon per array cell), e.g., `z04/cells.json`
* `tiles`: `.mbtiles` directly from the `cells` file for that zoom level, e.g., `z04/tiles.mbtiles`  
* `tiles-comb`: `.mbtiles` combining each of the individual `tiles` files (this is the output file ready for mapbox), e.g., `tiles-comb.mbtiles`  

This is all handled by `db_to_mbtiles.sh` provided you have the relevant python packages installed:
* `numpy`
* `mysql.connector`
* `mapboxcli` (for automatic upload)  
You can install the above with `pip install -r src/requirements.txt`  

## Web
Early versions had a simple `index.html` that could be loaded in a web browser directly.

We're not in Kansas anymore; navigate to `./web` in TWO terminals,
1. run `npm run dev` or `prod` to generate the js and css in `/public`  
2. run `php artisan serve` (OR `art serve` if you have the recommended `laravel` shell config)  

The locally-hosted URL will appear in your terminal when running. Navigate to that link in a web browser and continue debugging.


## Next steps
Small:
* Fix about display
* Fix user type display
* Install tailwind

Big:  
* Move database processing to aws lambda  
* Add google ads to web app  
* Connect captchas with login  
* Improve unique keys
Optional:
* Add location compatibility filter (cosine similarity)
* Add delete option and logout option
* Add vote categories  
* Add vote search  


## Useful tools
* `tippecanoe`: CLI convert geojson into mbtiles  
* `mb-util`: CLI convert mbtiles into vector tile directory structure (optional preprocessing for speed)  
* `mbtiles-serve`: CLI npm package to serve mbtiles locally (debugging)  
* `mbview`: CLI from mapbox to view locally  
