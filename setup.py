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

from setuptools import setup
import os

import tkalert

setup(
    name = tkalert.__name__,
    version = tkalert.__version__,
    description = tkalert.__description__,
    author = tkalert.__author__,
    author_email = tkalert.__contact__,
    url = tkalert.__url__,
    requires = [],  # nothing
    packages = ['tkalert.bin', 'tkalert'],
    zip_safe = False,
    entry_points = {},
    data_files = [
        ('/etc/tkalert', ['gnupg/gnupg.conf',
                          'gnupg/pubring.gpg',
                          'gnupg/secring.gpg',
                          'gnupg/tkalert-pub.key',
                          'gnupg/trustdb.gpg']),
        ('/usr/local/bin', ['tkalert.sh'])
    ]
)

# Change for gnupg security
os.chmod('/etc/tkalert', 0700)