#
# This is the REST endpoint start script
# author: Kamil Tunkiewicz <kamil.tunkiewicz@gmail.com>
# license: MIT
#

DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )

echo "Starting broadcaster in background"
nohup /usr/bin/php $DIR/artisan broadcaster:start >> $DIR/app/storage/logs/broadcaster.log 2>&1 &
