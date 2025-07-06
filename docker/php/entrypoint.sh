#!/bin/bash

# Set UID of user "dockeruser" to the current user's UID
if [ ! -z "${WWWUID}" ]; then
    usermod -u ${WWWUID} ${CONTAINER_APP_USER:-dockeruser}
fi

# Apacheをフォアグラウンドで実行
/usr/local/bin/apache2-foreground
