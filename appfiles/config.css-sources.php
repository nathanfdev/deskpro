<?php

$CONFIG = array();

###############################################################################
# Agent
###############################################################################

$CONFIG['agent'] = array();

$CONFIG['agent']['deskpro-ui'] = array(
	'out' => 'deskpro-ui.css',
	'mode' => 'yui',
	'media' => 'screen,print',
	'files' => array(
		'javascripts/DeskPRO/UI/ui.css',
	)
);

$CONFIG['agent']['interface'] = array(
	'out' => 'agent-interface.css',
	'mode' => 'yui',
	'media' => 'screen,print',
	'files' => array(
		'dp-interface'         => 'stylesheets/agent/dp-interface.css',
		'dp-source-pane'       => 'stylesheets/agent/dp-source-pane.css',
		'dp-list-pane'         => 'stylesheets/agent/dp-list-pane.css',
		'dp-content-pane'      => 'stylesheets/agent/dp-content-pane.css',
		'dp-agent-chat'        => 'stylesheets/agent/dp-agent-chat.css',
		'navigation'           => 'stylesheets/agent/navigation.css',
		'header'               => 'stylesheets/agent/header.css',
		'overlayMacro'         => 'stylesheets/agent/overlayMacro.css',
		'overlayCreateTicket'  => 'stylesheets/agent/overlayCreateTicket.css',
	),
	'less_files' => array(
		'dp-interface'         => 'stylesheets-less/agent/dp-interface.less',
		'dp-source-pane'       => 'stylesheets-less/agent/dp-source-pane.less',
		'dp-list-pane'         => 'stylesheets-less/agent/dp-list-pane.less',
		'dp-content-pane'      => 'stylesheets-less/agent/dp-content-pane.less',
		'dp-agent-chat'        => 'stylesheets-less/agent/dp-agent-chat.less',
		'navigation'           => 'stylesheets-less/agent/navigation.less',
		'header'               => 'stylesheets-less/agent/header.less',
		'overlayMacro'         => 'stylesheets-less/agent/overlayMacro.less',
		'overlayCreateTicket'  => 'stylesheets-less/agent/overlayCreateTicket.less',
	)
);

$CONFIG['agent']['interface-print'] = array(
	'out' => 'agent-interface-print.css',
	'mode' => 'yui',
	'media' => 'print',
	'files' => array(
		'print'                => 'stylesheets/agent/print.css',
	),
	'less_files' => array(
		'print'                => 'stylesheets-less/agent/print.less',
	)
);

$CONFIG['agent']['vendors'] = array(
	'out' => 'agent-vendors.css',
	'mode' => 'yui',
	'media' => 'screen',
	'files' => array(
		'vendor/jquery/jquery-ui/css/dp-theme/jquery-ui.css',
		'vendor/jquery/tipped/css/tipped.css',
		'vendor/jquery/colorbox/colorbox.css',
		'vendor/jquery/jcrop/css/jquery.Jcrop.css',
		'vendor/jquery/chosen/chosen.css',

		'vendor/jquery/markitup/markitup/skins/simple/style.css',
		'vendor/jquery/markitup/markitup/sets/markdown/style.css',
		'vendor/jquery/token-field/token-field.css',
	)
);
