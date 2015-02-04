#
# This is the REST endpoint feeder script to fill database with random data
# author: Kamil Tunkiewicz <kamil.tunkiewicz@gmail.com>
# license: MIT
#

DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )

/bin/node $DIR/feeder.js

