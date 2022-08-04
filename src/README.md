# Overview
Started as an AWS lambda shenanigan but only parts of the process could be completed there
Moving to a docker container so that multiple hosting options are opened up.


# Installing new packages
New packages need to be present in the packages directory
Install `pkg` directly to the packages directory using:
`pip install --target ./package pkg'

# Preparing for upload
Run `./map_package.sh` to generate an `upload.zip` file containing source and package contents.
