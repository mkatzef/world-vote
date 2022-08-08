# Overview
Started as an AWS lambda shenanigan but only parts of the process could be completed there
Moving to a docker container so that multiple hosting options are opened up.

# Docker usage
## Build
Create new, tagged image
`docker build -t world_vote .`

## Debugging
Run it as a daemon, take note of its container ID
`docker run -d -it world_vote`

Interact with your creation
`docker exec -it C_ID bash`

# Execution
`docker run --mount type=bind,source=$(pwd),target=/src world_vote ./db_to_mbtiles.sh`
Maps pwd (which should be `src`) to `/src` inside the container, letting you call any script that's in `pwd` inside the container (which has the right dependencies installed!)

# Map data sequencing
The data shown to users is processed in the following steps:
Current time period: 1 hour
Consistency time period: 24 hours

## Outdated
Run the following once per time period (outdated, tick/tock based):
* Switch the active tileset to the one generated in the PREVIOUS period, e.g., tick
* Read entire database to collect base data + counts
* Bin data into zoom levels and corresponding geojson
* Generate mbtiles for each zoom level
* Combine mbtiles for each zoom level
* Upload combined mbtiles to the inactive tileset id, e.g., tock
* Update state variables to mark the staged tileset

## New
Run the following once per consistency time period (updated):
* Read entire database to collect base data + counts
* Bin data into zoom levels (the "master base data")
* Record the maximum user id used in processing

Run the following once per time period (updated):
* Set the active tileset to the previously staged tileset
* Set the deleted tileset to the active tileset
* Read user database (where user id > last collected) to collect base data + counts
* Bin data into zoom levels
* Add to this the master base data
* Generate corresponding geojson
* Generate mbtiles for each zoom level
* Combine mbtiles for each zoom level
* Upload combined mbtiles to mapbox-defined ID
* Delete 
