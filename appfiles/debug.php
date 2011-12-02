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

/**
 * Packs listed will be served in their raw form, not their compiled build form.
 * Useful for debugging since you dont need to re-compile the files each time.
 * Possible values are in config.assets.php
 */
// TODO remove in distribution. Enabled just for devs
$CONFIG['debug']['raw_assets'] = array();
//$CONFIG['debug']['raw_assets'][] = 'all -vendors';
//$CONFIG['debug']['raw_assets'][] = 'all';

/**
 * True to rename use .css from stylesheets/ instead of .less from stylesheets-less.
 * This is only useful if you are auto-compiling LESS stylesheets to CSS which
 * may be useful in development.
 */
//$CONFIG['debug']['less_use_css_dir'] = true;

$CONFIG['debug']['templates'] = array();

/**
 * When enabled, templates are never fetched from the database.
 */
//$CONFIG['debug']['templates']['disable_db_templates'] = true;

/**
 * These options are passed to JS handlers to enable various debug options.
 */
$CONFIG['debug']['js'] = array(

	/**
	 * Adds a fixed div in top right (overlaps user icon). right now only has button
	 * that reloads current tab.
	 */
	//'devbar' => true,

	/**
	 * The ajax polling interval for messages
	 */
	//'pollerInterval' => 10000,

	/**
	 * Show the 'test' section, and also disable the requirement of a listpane
	 * matching a section. When no match is found, it'll use the test
	 */
	//'useTestSection' => true,

	// Disables section handlers in the agent interface,
	//'disableSectionHandlers' => true,

	// When the above option is true, still enable these
	'enableSectionHandlers' => array(
		//'DeskPRO.Agent.WindowElement.Section.Test',
		//'DeskPRO.Agent.WindowElement.Section.Publish',
		//'DeskPRO.Agent.WindowElement.Section.Ideas',
		//'DeskPRO.Agent.WindowElement.Section.Tickets',
		//'DeskPRO.Agent.WindowElement.Section.Tasks',
		//'DeskPRO.Agent.WindowElement.Section.People',
		//'DeskPRO.Agent.WindowElement.Section.UserChat',
		//'DeskPRO.Agent.WindowElement.Section.AgentChat',
		//'DeskPRO.Agent.WindowElement.Section.Twitter',
		//'DeskPRO.Agent.WindowElement.Section.Deal',
	),

	// When selecting a new section, dont run the auto load route
	//'noAutoLoadList' => true,

	// Disables the AJAX that saves and relaods previous state
	//'disableSaveState' => true,

	// Disables use of URL fragments
	//'disableUrlFragments' => true,

	// Run these routes automatically when first entering the agent UI
	// array(array('ticket', 'route_name', array('param' => 'xxx')))
	'autoLoadRoutes' => array(
		//array('person', 'agent_people_view', array('person_id' => 20001)),
		//array('ticket', 'agent_ticket_view', array('ticket_id' => 21)),
		//array('listpane', 'agent_ticketsearch_runnamedfilter', array('filter_name' => 'all')),
		//array('page', 'agent_test_tab', array('rand' => mt_rand(1,1000000))),
	),

	// Write incoming client messages to console.log (see AbstractChanneler)
	//'logClientMessages' => true,
);
