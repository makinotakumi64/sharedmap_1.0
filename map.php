<?php

    require_once(__DIR__ . '/config.php');
    require_once(__DIR__ . '/account.php');
    require_once(__DIR__ . '/function.php');

    $account = new \SharedMap\Account($_REQUEST['userName']);
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
        <div type="hidden" id="userName" data-val="<?= h($account->getUserName()); ?>"></div>
        <header>
            <img class="header_icon" src="./img/header_icon.png">
          <h1 class="title">SHAREDMAP</h1>
        </header>
        <div class="main">
              <div class="user">
                  <h1>ユーザー: <?= h($account->getUserName()); ?></h1>
                  <li>
                      <h2>
                          マーカー: <span id="numberOfMarker"><?= h($account->getNumberOfMarker()); ?></span>
                      </h2>
                      <h2>フォロー: <?= h($account->getNumberOfFollowUser()); ?></h2>
                      <h2>フォロワー: <?= h($account->getNumberOfFollowerUser()); ?></h2>
                  </li>
              </div>
              <li class="menu">
                  <h1>ー検索して探すー</h1>
                  <input type="text" id="address" placeholder="住所または名前を検索">
                  <button class="button" id="search">探す</button>
                  <button class="button" id="marker">マーク</button>
              </li>
              <div id="map"></div>
              <h1>ーユーザーページへー</h1>
              <button class="button">
                  <a href="<?= h("./user.php?userName=" . $account->getUserName()); ?>" ><?= h($account->getUserName()); ?></a>
              </button>
        </div>
        <footer>
            <button class="button" id="logout">ログアウト</button>
        </footer>

        <script type="text/javascript">
            'usestrict';
            //ログアウトボタンをクリックしたときにログアウトする処理
            document.getElementById('logout').onclick = function() {
                var result = <?=
                                 h($account->logout());//サーバーでログアウト
                             ?>;
                if (result) {
                  alert('ログアウトしました！');
                  window.location.href = './index.php?';//ログインページへ移動
                } else {
                  alert('ログアウトできませんでした！');
                }
            }
        </script>
        <script src="./js/map.js"></script>
        <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBbk82sCWJsA5kLkyNQ9X1FVB2SEHzcGNg&callback=initMap"
        async defer></script>
    </body>
</html>
