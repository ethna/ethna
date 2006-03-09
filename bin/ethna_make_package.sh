#!/bin/sh
#
#	ethna_make_package.sh
#
#	...:(
#
#	$Id$
#
tmpdir="/tmp/ethna"

if [ ! -d $tmpdir ]
then
	mkdir -p $tmpdir
fi

if [ "$1" = "-b" ]
then
	beta=$1
fi

# chdir to basedir
cwd=`dirname $0`
basedir="$cwd/../"
cd $basedir
basedir=`pwd`

version=`php $basedir/bin/ethna_make_package.php $beta -v`
targetdir="$tmpdir/Ethna-$version"

rm -f $basedir/package.xml

rm -fr $targetdir
mkdir $targetdir
cp -a . "$targetdir/"

find $targetdir -name "CVS" | xargs rm -fr

# create package for php 5
php $basedir/bin/ethna_make_package.php $beta
cp -f $basedir/package.xml $tmpdir/
cd $tmpdir
tar zcvf Ethna-$version.tgz package.xml Ethna-$version

cd $basedir
php $basedir/bin/ethna_make_package.php $beta -o
cp -f $basedir/package.xml $tmpdir/
cd $tmpdir
tar zcvf Ethna-$version-dev.tgz package.xml Ethna-$version

