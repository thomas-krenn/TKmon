#!/bin/sh

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

# Upgrade needed locaes by the software
# Usage: sh update_system_locales.sh

OIFS=$IFS
IFS=","
GEN=$(which locale-gen)
SRV=$(which service)
LOCALELIST="de_DE,pl_PL,cs_CZ,nl_NL,it_IT,es_ES"

if [ ! -x $GEN ]; then
    echo "locale-gen command not found!"
    exit 1
fi

if [ ! -x $SRV ]; then
    echo "service command not found!"
    exit 1
fi

for I in $LOCALELIST; do
    $GEN $I.utf8
done

$SRV apache2 restart

exit 0
