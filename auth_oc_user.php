#!/usr/bin/php
<?php

/*
   Copyright (c) <2014> Klaus Herberth <klaus@jsxc.org>

   Permission is hereby granted, free of charge, to any person obtaining a copy of
   this software andassociated documentation files (the "Software"), to deal in the
   Software without restriction, including without limitation the rights to use,
   copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the
   Software, and to permit persons to whom the Software is furnished to do so,
   subject to thefollowing conditions:

   The above copyright notice and this permission notice shall be included in all
   copies or substantial portions of the Software.

   THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
   IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
   FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
   COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
   IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
   CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

/**
  * This script authentificates a user against owncloud.
  *
  * Usage: ./auth_oc_user.php OC_PATH USER [PASSWORD]
  * 
  * If no password is given, we will check if the user exists, otherwise 
  * we check if the credentials are valid. 
  */

if ($argc === 1){
	exit;
}

$RUNTIME_NOAPPS = true;
require_once $argv[1].'lib/base.php';

$ocUser = new OCP\User();

if ($argc === 3){
	$ret = $ocUser->userExists($argv[2]);
} else if($argc === 4) {
	$ret = $ocUser->checkPassword($argv[2], $argv[3]);
	$ret = (strtolower($ret) === strtolower($argv[2]))? 1: 0;
} else {
	exit;
}

echo $ret;
?>
