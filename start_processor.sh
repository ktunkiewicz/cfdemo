#
# This is the REST endpoint start script
# author: Kamil Tunkiewicz <kamil.tunkiewicz@gmail.com>
# license: MIT
#

DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )

echo "Starting processor in background"
nohup /usr/bin/php $DIR/artisan processor:start >> $DIR/app/storage/logs/processor.log 2>&1 &
