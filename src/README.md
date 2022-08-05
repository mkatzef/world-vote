# Overview
Started as an AWS lambda shenanigan but only parts of the process could be completed there
Moving to a docker container so that multiple hosting options are opened up.

## Docker usage sequence
Create new, tagged image
`docker build -t world_vote .`

Run it as a daemon, take note of its container ID
`docker run -d -it world_vote`

Interact with your creation
`docker exec -it C_ID bash`

# TODO:
Map host storage to container, run end-to-end scripts
