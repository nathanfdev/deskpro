#!/bin/bash

# the dir expression for "find" that matches the http_cache folder
DIR_ROOT="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )/../.."
DIR_HTTP_CACHE=$DIR_ROOT/data/cache/portal/prod/http_cache

# delete files older than 7 days
find $DIR_HTTP_CACHE -mtime +7 -type f -delete

# 3 times because of nested empty dirs. will spit out not found errors in some cases, which can be ignored.
find $DIR_HTTP_CACHE -type d -empty -exec rmdir {} \;
find $DIR_HTTP_CACHE -type d -empty -exec rmdir {} \;
find $DIR_HTTP_CACHE -type d -empty -exec rmdir {} \;