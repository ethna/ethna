#!/bin/sh
BIN_DIR=`dirname $0`
ETHNA_DIR="$BIN_DIR/.."
TEST_DIR="$ETHNA_DIR/test"
TEST_RUNNER="$BIN_DIR/ethna_run_test.php"

php $TEST_RUNNER $* < $TEST_DIR/run_test.stdin.txt
