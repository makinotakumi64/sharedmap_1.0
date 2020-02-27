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
