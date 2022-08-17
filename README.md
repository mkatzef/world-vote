# World Vote

## Structure
* `web/` laravel web app  
* `src/` bash and python utilities to process data for the web app  
* `web_uploads/` packaged versions of the laravel web app (for elastic beanstalk)  


## Data formats
* `base`: `.npy` containing raw data sums and counts; one per data channel e.g., `demo0.npy`  
* `zoom array`: `.npy` of the same name as the data channel, averaged, filtered, and binned for multiple zoom levels, e.g., `z04/demo0.npy`  
* `cells`: `.json` all `zoom array`s at the current zoom aggregated into geojson (one polygon per array cell), e.g., `z04/cells.pbf`
* `tiles`: `.mbtiles` directly from the `cells` file for that zoom level, e.g., `z04/tiles.mbtiles`  
* `tiles-comb`: `.mbtiles` combining each of the individual `tiles` files (this is the output file ready for mapbox), e.g., `tiles-comb.mbtiles`  

The tasks of reading from database, writing the above formats, and uploading are all described in [src/README.md](src/README.md)

## Web
Early versions had a simple `index.html` that could be loaded in a web browser directly.

We're not in Kansas anymore; navigate to `./web` **IMPORTANT** for both,
1. run `npm run dev` or `prod` to generate the js and css in `/public`  
2. run `php artisan serve` (OR `art serve` if you have the recommended `laravel` shell config)  

The locally-hosted URL will appear in your terminal when running. Navigate to that link in a web browser and continue debugging.


## Next steps
Required:  
* Add server-side response validation
* Add reminder to copy login code
* Revise prompts
* Add initial data
* Reduce text size in polls
* Schedule EC2 instance only when updating
* Obfuscate/minify code

Optional:
* Animate hammy
* Add user-submitted prompts
* Add prompt categories  
* Add prompt search  


## Useful tools
* `tippecanoe`: CLI convert geojson into mbtiles  
* `mb-util`: CLI convert mbtiles into vector tile directory structure (optional preprocessing for speed)  
* `mbtiles-serve`: CLI npm package to serve mbtiles locally (debugging)  
* `mbview`: CLI from mapbox to view locally  
