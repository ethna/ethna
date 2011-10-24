#!/bin/sh
#
#   ethna_make_package.sh
#
#   ...:(
#
#   $Id$
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

if [ "$1" = "-a" ]
then
    alpha=$1
    beta=$alpha
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
git checkout-index -a -f --prefix="$targetdir/"

#  create optional package
optpkg_dir="$targetdir/misc/optional_package"

# Smarty2
mkdir $optpkg_dir/Smarty/release
cd $optpkg_dir/Smarty/src
tar xvfz Smarty*.tar.gz
cd $optpkg_dir/Smarty/build
chmod +x ./build
./build
cp $optpkg_dir/Smarty/release/*.tgz $tmpdir

# Smarty3
mkdir $optpkg_dir/Smarty3/release
cd $optpkg_dir/Smarty3/src
tar xvfz Smarty*.tar.gz
cd $optpkg_dir/Smarty3/build
chmod +x ./build
./build
cp $optpkg_dir/Smarty3/release/*.tgz $tmpdir

# simpletest 1.0
mkdir $optpkg_dir/simpletest/release
cd $optpkg_dir/simpletest/src
tar xvfz simpletest*.tar.gz
cd $optpkg_dir/simpletest/build
chmod +x ./build
./build
cp $optpkg_dir/simpletest/release/*.tgz $tmpdir

# simpletest 1.1
mkdir $optpkg_dir/simpletest1.1/release
cd $optpkg_dir/simpletest1.1/src
tar xvfz simpletest*.tar.gz
cd $optpkg_dir/simpletest1.1/build
chmod +x ./build
./build
cp $optpkg_dir/simpletest1.1/release/*.tgz $tmpdir

rm -rf $optpkg_dir
cd $basedir

find $targetdir -name "CVS" -o -name ".svn" -o -name ".git*" | xargs rm -fr

# create package for php 5
php $basedir/bin/ethna_make_package.php $beta
cp -f $basedir/package.xml $tmpdir/
cd $tmpdir
tar zcvf Ethna-$version.tgz package.xml Ethna-$version
zip -r Ethna-$version.zip package.xml Ethna-$version
