#!/bin/bash
# an alias to the command in the dev/bin dir
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd $SCRIPT_DIR
../../dev/bin/init-project-dev
