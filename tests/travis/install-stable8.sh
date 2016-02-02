#!/bin/bash -x
# install ocdev
sudo apt-get update
sudo apt-get -y install python3-jinja2 python3-setuptools
sudo easy_install3 requests
sudo easy_install3 ocdev
# set up postgresql
createuser -U travis -s oc_autotest
# set up mysql
mysql -e 'create database oc_autotest;'
mysql -u root -e "CREATE USER 'oc_autotest'@'localhost';"
mysql -u root -e "grant all on oc_autotest.* to 'oc_autotest'@'localhost';"
# install owncloud
cd ..
ocdev setup core --dir owncloud --branch $BRANCH --no-history
cd owncloud
ocdev ci $DB
#enable ojsxc
mv ../jsxc.chat apps/ojsxc
php -f console.php app:enable ojsxc

cd apps/ojsxc

