<?php
OCP\User::checkLoggedIn ();
OCP\JSON::callCheck ();

$limit = 10;
$offset = 0;$users = OC_User::getDisplayNames((string)$_GET['search'], $limit, $offset);

$users = OC_User::getDisplayNames((string)$_GET['search'], $limit, $offset);

OCP\JSON::encodedPrint ( $users );
?>
