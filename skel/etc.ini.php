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

    // memcache
    // sample-1: single (or default) memcache
    // 'memcache_host' => 'localhost',
    // 'memcache_port' => 11211,
    // 'memcache_use_pconnect' => false,
    // 'memcache_retry' => 3,
    // 'memcache_timeout' => 3,
    //
    // sample-2: multiple memcache servers (distributing w/ namespace and ids)
    // 'memcache' => array(
    //     'namespace1' => array(
    //         0 => array(
    //             'memcache_host' => 'cache1.example.com',
    //             'memcache_port' => 11211,
    //         ),
    //         1 => array(
    //             'memcache_host' => 'cache2.example.com',
    //             'memcache_port' => 11211,
    //         ),
    //     ),
    // ),

    // i18n
    //'use_gettext' => false,
    
    // mail 
    //'mail_func_workaround' => false,

    // csrf
    // 'csrf' => 'Session',
);
?>
