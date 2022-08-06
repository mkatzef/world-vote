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

# TODO:
Map host storage to container, run end-to-end scripts
