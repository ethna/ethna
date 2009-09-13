<?php

require_once dirname(__FILE__) . '/../app/{$project_id}_Controller.php';

/**
 * If you want to enable the UrlHandler, comment in following line,
 * and then you have to modify $action_map on app/{$project_id}_UrlHandler.php .
 *
 */
// $_SERVER['URL_HANDLER'] = 'index';

/**
 * Run application.
 */
{$project_id}_Controller::main('{$project_id}_Controller', 'index');

