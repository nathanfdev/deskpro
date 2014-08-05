<?php if (!defined('DP_ROOT')) exit('No access');
echo 'HTTP_METHOD_' . strtoupper(@$_SERVER['REQUEST_METHOD']);
