<?php


//DSNはData Source Nameでデータベースシステムに応じて接続するための文字列
$url = parse_url(getenv('DATABASE_URL'));
define('DB_DATABASE', substr($url['path'], 1));
define('DB_HOST', $url['host']);
define('PDO_DSN', 'mysql:host=' . DB_HOST . ';dbname=' . DB_DATABASE);
define('DB_USERNAME', $url['user']);
define('DB_PASSWORD', $url['pass']);
