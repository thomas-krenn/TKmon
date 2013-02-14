#!/bin/sh

set -o nounset

SCRIPT=$(readlink -f "$0")
DIR=$(dirname "$SCRIPT")
ENV=/usr/bin/env
MODULE=tkalert.bin.alert


$ENV PYTHONPATH=$DIR python -m $MODULE - $@