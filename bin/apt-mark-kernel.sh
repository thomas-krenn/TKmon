#!/bin/bash
APTMARK=${APTMARK:-$(command -v apt-mark)}

if [[ ! -x "$APTMARK" ]]
then
    echo "apt-mark not found" > /dev/stderr
    exit 1
fi

$APTMARK auto \
    $(dpkg -l \
        | grep -e "^ii" \
        | awk '{print $2}' \
        | grep -e linux-signed-image-.*-generic -e linux-image-.*-generic -e linux-headers-.*-generic \
    )

