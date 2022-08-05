# Start from ubuntu
FROM ubuntu:20.04

# Update repos and install dependencies
RUN apt-get update \
  && apt-get -y upgrade \
  && apt-get -y install build-essential libsqlite3-dev zlib1g-dev git

# Create a directory and copy in all files
RUN mkdir -p /tmp/tippecanoe-src
WORKDIR /tmp/tippecanoe-src
RUN git clone https://github.com/mapbox/tippecanoe.git .

# Build tippecanoe
RUN make \
  && make install

RUN apt-get install -y python3-pip
RUN pip3 install setuptools numpy mysql.connector
RUN pip3 install mapboxcli

RUN mkdir /world_vote_tiles
COPY *.py /world_vote_tiles
COPY ./prod/*.py /world_vote_tiles
COPY *.sh /world_vote_tiles
WORKDIR /world_vote_tiles
