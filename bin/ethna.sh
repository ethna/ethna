#!/bin/sh
#
#   ethna.sh
#
#   simple command line gateway
#
#   $Id$
#

if test -z "$ETHNA_HOME"
then
    if test "@PEAR-DIR@" = '@'PEAR-DIR'@'
    then
        ETHNA_HOME="/usr/share/php/Ethna"
    else
        ETHNA_HOME="@PEAR-DIR@/Ethna"
    fi
fi

if test -z "$PHP_COMMAND"
then
    if test "@PHP-BIN@" = '@'PHP-BIN'@'
    then
        PHP_COMMAND="php"
    else
        PHP_COMMAND="@PHP-BIN@/php"
    fi
    export PHP_COMMAND
fi

if test -z "$PHP_CLASSPATH"
then
    PHP_CLASSPATH="$ETHNA_HOME/class"
    export PHP_CLASSPATH
fi

$PHP_COMMAND -d html_errors=off -qC $ETHNA_HOME/bin/ethna_handle.php $*
