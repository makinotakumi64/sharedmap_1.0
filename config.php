<?php


//DSNはData Source Nameでデータベースシステムに応じて接続するための文字列
$url = parse_url(getenv('DATABASE_URL'));
define('DB_DATABASE', 'heroku_e1c24c8d39f0e17');
define('DB_HOST', 'us-cdbr-iron-east-04.cleardb.net');
define('PDO_DSN', 'mysql:host=' . DB_HOST . ';dbname=' . DB_DATABASE);
define('DB_USERNAME', 'bd5d556ed6275e');
define('DB_PASSWORD', '51cead58');
