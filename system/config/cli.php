<?php

if (version_compare(VERSION, '4.0.0.0', '<')) {
    cli_output('Your OpenCart version is not supported.');
    exit;
}

// Site
$_['site_url']          = HTTP_SERVER;
$_['site_ssl']          = HTTP_SERVER;

// Database
$_['db_autostart']      = true;
$_['db_engine']         = DB_DRIVER;
$_['db_hostname']       = DB_HOSTNAME;
$_['db_username']       = DB_USERNAME;
$_['db_password']       = DB_PASSWORD;
$_['db_database']       = DB_DATABASE;
$_['db_port']           = DB_PORT;

// Session
$_['session_autostart'] = true;

// Template
$_['template_cache']    = true;

// Actions
$_['action_pre_action'] = array(
    'cli/startup',
    'startup/startup',
    'startup/error',
    'startup/event'
);

// Actions
$_['action_default'] = 'cli/welcome';
$_['action_router']  = 'cli/router';
$_['action_error']   = 'cli/not_found';

// Action Events
$_['action_event'] = array(
    'controller/*/before' => array(
        'event/language/before'
    ),
    'controller/*/after' => array(
        'event/language/after'
    ),
    'view/*/before' => array(
        999  => 'event/language',
        1000 => 'event/theme'
    ),
    'view/*/before' => array(
        'event/language'
    )
);
