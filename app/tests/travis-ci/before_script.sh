#!/bin/bash

echo "Creating test database"
mysql -e "CREATE DATABASE deskpro;"

echo "Creating config.php"
rm config.php
cp config.new.php config.php

sed -i tmp "s/define('DP_DATABASE_HOST', 'localhost')/define('DP_DATABASE_HOST', '127.0.0.1')/g" config.php
sed -i tmp "s/define('DP_DATABASE_USER', 'root')/define('DP_DATABASE_USER', 'travis')/" config.php

echo '$DP_CONFIG['"'"'debug'"'"']['"'"'dev'"'"']                     = true;' >> config.php
echo '$DP_CONFIG['"'"'debug'"'"']['"'"'raw_assets'"'"']              = array('"'"'all'"'"');' >> config.php
echo '$DP_CONFIG['"'"'debug'"'"']['"'"'no_report_errors'"'"']        = true;' >> config.php
echo '$DP_CONFIG['"'"'cache'"'"']['"'"'page_cache'"'"']['"'"'enable'"'"']    = false;' >> config.php
echo '$DP_CONFIG['"'"'debug'"'"']['"'"'mail'"'"']['"'"'save_to_file'"'"']    = true;' >> config.php
echo '$DP_CONFIG['"'"'debug'"'"']['"'"'mail'"'"']['"'"'enable_mail_log'"'"'] = true;' >> config.php
echo '$DP_CONFIG['"'"'debug'"'"']['"'"'mail'"'"']['"'"'disable_send'"'"']    = true;' >> config.php
echo '$DP_CONFIG['"'"'rewrite_urls'"'"'] = true;' >> config.php
echo '$DP_CONFIG['"'"'SETTINGS'"'"'] = array();' >> config.php
echo '$DP_CONFIG['"'"'SETTINGS'"'"']['"'"'core.use_mail_queue'"'"']    = '"'"'never'"'"';' >> config.php
echo '$DP_CONFIG['"'"'SETTINGS'"'"']['"'"'core.show_share_widget'"'"'] = false;' >> config.php
echo '$DP_CONFIG['"'"'SETTINGS'"'"']['"'"'core.use_gravatar'"'"']      = false;' >> config.php

echo "Creating config.testing.php"
echo '<?php require("config.php");' > config.testing.php