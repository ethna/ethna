<?php
/**
 *  DB_Test.php
 */

/**
 *  TestCase for Ethna_DB
 *
 *  @access public
 */
class Ethna_DB_Test extends Ethna_UnitTestBase
{
    public function test_parseDSN()
    {
        /**
         *  spec
         *
         *  phptype://username:password@protocol+hostspec:110//usr/db_file.db?mode=0644
         *  phptype://username:password@hostspec/database_name
         *  phptype://username:password@hostspec
         *  phptype://username@hostspec
         *  phptype://hostspec/database
         *  phptype://hostspec
         *  phptype(dbsyntax)
         *  phptype
         */
        $mock = new Ethna_DB_Mock(null, '', false);
        foreach ($this->dsnDataProvider() as $data) {
            $this->assertEqual($data['expected'], $mock->parseDSN($data['dsn']));
        }
    }

    protected function dsnDataProvider()
    {
        // TODO : DSN Test for SQLite etc.
        return array(
            array( // array definition
                'dsn' =>  array(
                    'phptype'  => 'mysql',
                    'username' => 'root',
                    'password' => 'password',
                    'hostspec' => 'localhost',
                    'database' => 'my_db',
                ),
                'expected' => array(
                    'phptype'  => 'mysql',
                    'dbsyntax' => 'mysql',
                    'username' => 'root',
                    'password' => 'password',
                    'protocol' => false,
                    'hostspec' => 'localhost',
                    'port'     => false,
                    'socket'   => false,
                    'database' => 'my_db',
                ),
            ),
            array( // complex password with port
                'dsn' => 'mysql://root:c@0mple+xpa_#4word@the.host.name.exampletld:3360/my_db',
                'expected' => array(
                    'phptype'  => 'mysql',
                    'dbsyntax' => 'mysql',
                    'username' => 'root',
                    'password' => 'c@0mple+xpa_#4word',
                    'protocol' => 'tcp',
                    'hostspec' => 'the.host.name.exampletld',
                    'port'     => 3360,
                    'socket'   => false,
                    'database' => 'my_db',
                ),
            ),
            array( // complex password with port
                'dsn' => 'mysql://root:c@0mple+xpa_#4word@the.host.name.exampletld/my_db',
                'expected' => array(
                    'phptype'  => 'mysql',
                    'dbsyntax' => 'mysql',
                    'username' => 'root',
                    'password' => 'c@0mple+xpa_#4word',
                    'protocol' => 'tcp',
                    'hostspec' => 'the.host.name.exampletld',
                    'port'     => false,
                    'socket'   => false,
                    'database' => 'my_db',
                ),
            ),
            array( // user and password
                'dsn' => 'mysql://root:password@localhost/my_db',
                'expected' => array(
                    'phptype'  => 'mysql',
                    'dbsyntax' => 'mysql',
                    'username' => 'root',
                    'password' => 'password',
                    'protocol' => 'tcp',
                    'hostspec' => 'localhost',
                    'port'     => false,
                    'socket'   => false,
                    'database' => 'my_db',
                ),
            ),
            array( // user, no password and db
                'dsn' => 'mysql://root@localhost/my_db',
                'expected' => array(
                    'phptype'  => 'mysql',
                    'dbsyntax' => 'mysql',
                    'username' => 'root',
                    'password' => false,
                    'protocol' => 'tcp',
                    'hostspec' => 'localhost',
                    'port'     => false,
                    'socket'   => false,
                    'database' => 'my_db',
                ),
            ),
            array( // host and db
                'dsn' => 'mysql://localhost/my_db',
                'expected' => array(
                    'phptype'  => 'mysql',
                    'dbsyntax' => 'mysql',
                    'username' => false,
                    'password' => false,
                    'protocol' => 'tcp',
                    'hostspec' => 'localhost',
                    'port'     => false,
                    'socket'   => false,
                    'database' => 'my_db',
                ),
            ),
            array( // host
                'dsn' => 'mysql://localhost',
                'expected' => array(
                    'phptype'  => 'mysql',
                    'dbsyntax' => 'mysql',
                    'username' => false,
                    'password' => false,
                    'protocol' => 'tcp',
                    'hostspec' => 'localhost',
                    'port'     => false,
                    'socket'   => false,
                    'database' => false,
                ),
            ),
        );
    }
}

class Ethna_DB_Mock extends Ethna_DB
{
}
