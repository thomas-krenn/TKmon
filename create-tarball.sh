#!/bin/sh

# Copyright (C) 2013 NETWAYS GmbH, http://netways.de
#
# This file is part of TKALERT (http://www.thomas-krenn.com/).
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 3 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program. If not, see <http://www.gnu.org/licenses/>.

set -o nounset

DIR=$(pwd)
VERSION=$(cat VERSION)
PACKAGE=tkalert-$VERSION
FILE=$DIR/$PACKAGE.tar.gz
GIT=$(which git)
GZ=$(which gzip)

if [ ! -x "$GIT" ]; then
    echo "Could not find git"
    exit 1
fi

if [ ! -x "$GZ" ]; then
    echo "Could not find gz"
    exit 1
fi

$GIT archive --format=tar --prefix=$PACKAGE/ HEAD | $GZ -c > $FILE

echo "Created $FILE"

exit 0
