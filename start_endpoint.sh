#
# This is the REST endpoint start script
# author: Kamil Tunkiewicz <kamil.tunkiewicz@gmail.com>
# license: MIT
#

DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )

echo "Starting server.js in background"
nohup /bin/node $DIR/endpoint/server.js --quiet >> $DIR/app/storage/logs/endpoint.log 2>&1 &
