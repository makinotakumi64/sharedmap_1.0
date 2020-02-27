<?php
try{
    $db=new PDO('mysql:dbname=heroku_e1c24c8d39f0e17;host=us-cdbr-iron-east-04.cleardb.net;charset=utf8','bd5d556ed6275e','51cead58');
    $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);//エラーが出た際にExceptionを出す設定
//     SELECT `list`.`id`,
//     `list`.`name`
// FROM `heroku_e1c24c8d39f0e17`.`list`;

  $stmt = $db->query("show tables");
  $stmt = $stmt->fetchAll(PDO::FETCH_ASSOC);
  echo $stmt;
}catch(PDOException $e){
    print('DB接続エラー:'.$e->getMessage());
}

 ?>

<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="utf-8">
        <title>SHAREDMAP</title>
        <link rel ="icon" href="./img/head_icon.png">
        <link rel="stylesheet" href="./css/styles.css">
    </head>
    <body>
        <header>
            <img class="header_icon" src="./img/header_icon.png">
            <h1 class="title">SHAREDMAP</h1>
        </header>
        <div class="content">
            <div class="login">
                <h1>ログイン</h1>
                <input type="text" id="userName_login" placeholder="ユーザ名">
                <input type="text" id="password_login" placeholder="パスワード">
                <button class="button" id="loginButton">ログイン</button>
            </div>
            <div class="create">
                <h1>アカウント作成し、ログイン</h1>
                <input type="text" id="userName_create" placeholder="ユーザ名">
                <input type="text" id="password_create" placeholder="パスワード">
                <button class="button" id="createButton">作成</button>
            </div>
            <div class="guest">
                <h1>ゲストユーザーとしてログイン</h1>
                <button class="button" id="guestButton">ログイン</button>
            </div>
        </div>
        <footer></footer>
        <script type="text/javascript" src="./js/index.js"></script>
    </body>
</html>
