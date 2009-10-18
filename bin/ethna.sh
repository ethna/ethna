#!/bin/sh
#
#   ethna.sh
#
#   simple command line gateway
#
#   $Id$
#

CUR_DIR="$PWD"

if test -z "$ETHNA_HOME"
then
    while [ 1 ];
    do
        if test -f ".ethna"
        then
            if test -d "$PWD""/lib/Ethna"
            then
                ETHNA_HOME="$PWD""/lib/Ethna"
                break
            fi
        fi
        if [ "$PWD" = "/" ];
        then
            if test "@PEAR-DIR@/pear" = '@'PEAR-DIR'@'
            then
                ETHNA_HOME="/usr/share/php/Ethna"
            else
                ETHNA_HOME="@PEAR-DIR@/Ethna"
            fi
            break
        fi
        cd ..
    done
fi

cd $CUR_DIR

if test -z "$PHP_COMMAND"
then
    if test "@PHP-BIN@" = '@'PHP-BIN'@'
    then
        PHP_COMMAND="php"
    else
        PHP_COMMAND="@PHP-BINARY@"
    fi
    export PHP_COMMAND
fi

if test -z "$PHP_CLASSPATH"
then
    PHP_CLASSPATH="$ETHNA_HOME/class"
    export PHP_CLASSPATH
fi

$PHP_COMMAND -d html_errors=off -qC $ETHNA_HOME/bin/ethna_handle.php $*
