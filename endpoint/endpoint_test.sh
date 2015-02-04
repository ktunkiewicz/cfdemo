#
# This is the REST endpoint test script
# author: Kamil Tunkiewicz <kamil.tunkiewicz@gmail.com>
# license: MIT
#

DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )

$DIR/../node_modules/jasmine-node/bin/jasmine-node $DIR/tests/test_spec.js

