<?php
/*
 * {$project_prefix}-ini.php
 *
 * update:
 */
$config = array(
    // debug
    'debug'	=> false,

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
    'log_facility'  => 'echo',
    'log_level' => 'warning',
    'log_option' => 'pid,function,pos',
    'log_alert_level' => 'crit',
    'log_alert_mailaddress' => '',
    'log_filter_do' => '',
    'log_filter_ignore' => 'Undefined index.*%%.*tpl',

    // memcache
    // sample-1: single (or default) memcache
    // 'memcache_host' => 'localhost',
    // 'memcache_port' => 11211,
    //
    // use connect or pconnect
    // 'memcache_use_connect' => false,
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
);
?>
