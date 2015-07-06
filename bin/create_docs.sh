#!/bin/bash

# -----------------------------------------------------------------------------
# This file is part of TKMON
#
# TKMON is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# TKMON is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with TKMON.  If not, see <http://www.gnu.org/licenses/>.
#
# @author Marius Hein <marius.hein@netways.de>
# @copyright 2012-2015 NETWAYS GmbH <info@netways.de>
# -----------------------------------------------------------------------------
#
# -----------------------------------------------------------------------------
# Usage:
#
# To create the docs in docs just type
# ./create_docs.sh
#
# If you want to use an other output directory:
# mkdir /tmp/output
# ./create_docs.sh /tmp/output
#
# -----------------------------------------------------------------------------

set -e

BIN=$(which asciidoc)
BASE=$(readlink -f $0)
COMMAND=$(basename $BASE)
ROOT=$(dirname $(dirname $BASE))
DOCDIR=$ROOT/docs
OUTPUT=$1

if [[ ! -x $BIN ]]; then
    echo "ascidoc is not installed"
    exit 1
fi

if [[ -n "$OUTPUT" && ! -d "$OUTPUT" ]]; then
    echo "Usage: $COMMAND [output directory]"
    exit 1
fi

for FILE in $DOCDIR/*.txt; do
    TARGET_FILE=$(basename $FILE .txt).html
    if [[ -d "$OUTPUT" ]]; then
        TARGET_DIR=$OUTPUT
    else
        TARGET_DIR=$(dirname $FILE)
    fi

    echo "DO $FILE -> $TARGET_DIR/$TARGET_FILE"

    $BIN --out-file=$TARGET_DIR/$TARGET_FILE $FILE
done

exit 0
