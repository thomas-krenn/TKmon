#!/bin/sh

set -o nounset

SCRIPT=$(readlink -f "$0")
DIR=$(dirname "$SCRIPT")
ENV=/usr/bin/env
MODULE=tkalert.bin.alert
GNUPG_CONFIG=$DIR/gnupg/gnupg.conf

$ENV PYTHONPATH=$DIR python -m $MODULE - --gnupg-config=$GNUPG_CONFIG $@