#!/bin/bash -x
# install ocdev
sudo apt-get update
sudo apt-get -y install python3-jinja2 python3-setuptools
sudo easy_install3 requests
sudo easy_install3 ocdev
# set up postgresql
reateuser -U travis -s oc_autotest
# set up mysql
mysql -e 'create database oc_autotest;'
mysql -u root -e "CREATE USER 'oc_autotest'@'localhost' IDENTIFIED BY '';"
mysql -u root -e "grant all on oc_autotest.* to 'oc_autotest'@'localhost';"
# install owncloud
cd ..
ocdev setup core --dir owncloud --branch $BRANCH --no-history
mv jsxc.chat owncloud/apps/ojsxc
phpenv config-add owncloud/apps/ojsxc}/tests/travis/php.ini
cd owncloud
./occ maintenance:install --database-name oc_autotest --database-user oc_autotest --database-pass --admin-user admin --admin-pass admin --database $DB
./occ app:enable ojsxc
cd apps/ojsxc
