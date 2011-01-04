<?php
/*
 * {$project_prefix}-ini.php
 *
 * update:
 */
$config = array(
    // site
    'url' => '',

    // debug
    // (to enable ethna_info and ethna_unittest, turn this true)
    'debug' => false,

    // db
    // sample-1: single db
    // 'dsn' => 'mysql://user:password@server/database',
    //
    // sample-2: single db w/ multiple users
    // 'dsn'   => 'mysql://rw_user:password@server/database', // read-write
    // 'dsn_r' => 'mysql://ro_user:password@server/database', // read-only
    //
    // sample-3: multiple db (slaves)
    // 'dsn'   => 'mysql://rw_user:password@master/database', // read-write(master)
    // 'dsn_r' => array(
    //     'mysql://ro_user:password@slave1/database',         // read-only(slave)
    //     'mysql://ro_user:password@slave2/database',         // read-only(slave)
    // ),

    // log
    // sample-1: sigile facility
    'log_facility'          => 'echo',
    'log_level'             => 'warning',
    'log_option'            => 'pid,function,pos',
    'log_filter_do'         => '',
    'log_filter_ignore'     => 'Undefined index.*%%.*tpl',

    // sample-2: mulitple facility
    //'log' => array(
    //    'echo'  => array(
    //        'level'         => 'warning',
    //    ),
    //    'file'  => array(
    //        'level'         => 'notice',
    //        'file'          => '/var/log/{$project_prefix}.log',
    //        'mode'          => 0666,
    //    ),
    //    'alertmail'  => array(
    //        'level'         => 'err',
    //        'mailaddress'   => 'alert@ml.example.jp',
    //    ),
    //),
    //'log_option'            => 'pid,function,pos',
    //'log_filter_do'         => '',
    //'log_filter_ignore'     => 'Undefined index.*%%.*tpl',

    'session' => array(
        'handler'   => 'files',
        'path'      => 'tmp',
        'check_remote_addr'      => true,
        //'cache_limiter' => 'private_no_expire',
        //'cache_expire'  => '180',
    ),

    // i18n
    //'use_gettext' => false,

    // mail
    //'mail_func_workaround' => false,

    // Smarty
    //'renderer' => array(
    //    'smarty' => array(
    //        'left_delimiter' => '{',
    //        'right_delimiter' => '}',
    //    ),
    //),

    // csrf
    // 'csrf' => 'Session',
);

$config['plugin'] = array(
    // plugin config
    //'type' => array(
    //    'name' => array(
    //    ),
    //),

    // memcache
    // sample-1: single (or default) memcache
    'cachemanager' => array(
        //'memcache' => array(
        //     'host' => 'localhost',
        //     'port' => 11211,
        //     'use_pconnect' => false,
        //     'retry' => 3,
        //     'timeout' => 3,

        //    // sample-2: multiple memcache servers (distributing w/ namespace and ids)
        //    //'info' => array(
        //    //   'namespace1' => array(
        //    //       0 => array(
        //    //           'host' => 'cache1.example.com',
        //    //           'port' => 11211,
        //    //       ),
        //    //       1 => array(
        //    //           'host' => 'cache2.example.com',
        //    //           'port' => 11211,
        //    //       ),
        //    //   ),
        //    //),
        //),
    ),
);
