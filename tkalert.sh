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

SCRIPT=$(readlink -f "$0")
DIR=$(dirname "$SCRIPT")
ENV=/usr/bin/env
MODULE=tkalert.bin.alert
GNUPG_CONFIG=$DIR/gnupg/gnupg.conf

# Testing for package install
if [ -e /etc/tkalert/gnupg.conf ]; then
    GNUPG_CONFIG=/etc/tkalert/gnupg.conf
fi

$ENV PYTHONPATH=$DIR python -m $MODULE - --gnupg-config=$GNUPG_CONFIG "$@"

exit $?