<?php
// Developers: You can copy this file and name it dev_debug.php
// if you want, and update the path in config.php.
// That file pattern is ignored by git so you can modify the file easier.

$CONFIG['debug'] = array();

/**
 * Email a copy of errors to this address.
 */
//$CONFIG['email_on_error'] = 'example@email.com';

################################################################################
# Mail related
################################################################################

$CONFIG['debug']['mail'] = array();

/**
 * Enable logging all email sources to the filesystem.
 *
 * Possible values:
 * - true: Enable logging to sys/logs/emails
 * - string: Enable logging to a specific directory
 */
//$CONFIG['debug']['mail']['save_to_file'] = true;

/**
 * Rewrite the 'to' address on all emails to this address.
 */
//$CONFIG['debug']['mail']['force_to'] = 'example@example.com';

/**
 * Completely disable the sending of emails.
 */
//$CONFIG['debug']['mail']['disable_send'] = true;


################################################################################
# Templates
################################################################################

$CONFIG['debug']['templates'] = array();

/**
 * When enabled, templates are never fetched from the database.
 */
//$CONFIG['debug']['templates']['disable_db_templates'] = true;