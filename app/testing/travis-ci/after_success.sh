#!/bin/bash

SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd $SCRIPT_DIR/../

./bin/coveralls --config=coveralls.yml --verbose --exclude-no-stmt