<?php

OCP\User::checkLoggedIn();
OCP\JSON::callCheck();

header('Content-Type: application/json; charset=utf-8');

$limit = 10;
$offset = 0;

$users = OCP\User::getDisplayNames((string) $_GET['search'], $limit, $offset);

echo json_encode($users);
