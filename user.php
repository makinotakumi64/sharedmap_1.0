<?php

    require_once(__DIR__ . '/config.php');
    require_once(__DIR__ . '/account.php');
    require_once(__DIR__ . '/function.php');

    $account = new \SharedMap\Account($_REQUEST['userName']);//クエリ文からユーザ名を取得し、インスタンスを生成

    //データベースからフォローリストを取得
    $followInfoList = $account->getFollowInfoList();

    //データベースからフォロワーリストを取得
    $followerInfoList = $account->getFollowerInfoList();
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
            <button class="button">
                <a href="<?= h("./map.php?userName=" . $account->getUserName()); ?>">マップ</a>
            </button>
        </header>
        <div class="main">
            <div class="user">
                <h1>ユーザー: <?= h($account->getuserName()); ?></h1>
                <li>
                    <h2>マーカー: <?= h($account->getNumberOfMarker()); ?></h2>
                    <h2>フォロー: <span id="numberOfFollow"><?= h($account->getNumberOfFollowUser()); ?></span></h2>
                    <h2>フォロワー: <?= h($account->getNumberOfFollowerUser()); ?></h2>
                </li>
            </div>
            <div class="menu">
                <h1>ーユーザーを探すー</h1>
                <input type="text" id="searchName" placeholder="ユーザーを検索">
                <button class="button" id="search">検索</button>
            </div>

            <li class="userList" id="searchResult">
                <h1>ユーザー: </h1>
                <h1></h1>
                <button class="button" id="followButton">フォロー</button>
                <h2></h2>
                <h2></h2>
                <h2></h2>
            </li>

            <div class="list" id="followList">
                <h1>ーフォローリストー</h1>
                <?php foreach ($followInfoList as $key => $value): ?>
                    <li class="userList" id="info">
                        <h1>ユーザー: </h1>
                        <h1><?= h($value['userName']); ?></h1>
                        <button class="button" name="removeButton">フォロー解除</button>
                        <h2>マーカー: <?= h($value['marker']); ?></h2>
                        <h2>フォロー: <?= h($value['followUser']); ?></h2>
                        <h2>フォロワー: <?= h($value['followerUser']); ?></h2>
                    </li>
                <?php endforeach; ?>
            <div id="addList" ></div>
            </div>

            <div class="list" id="list">
                <h1>ーフォロワーリストー</h1>
                <?php foreach ($followerInfoList as $key => $value): ?>
                    <li class="userList" id="info">
                        <h1>ユーザー: </h1>
                        <h1><?= h($value['userName']); ?></h1>
                        <h2>マーカー: <?= h($value['marker']); ?></h2>
                        <h2>フォロー: <?= h($value['followUser']); ?></h2>
                        <h2>フォロワー: <?= h($value['followerUser']); ?></h2>
                    </li>
                <?php endforeach; ?>
            </div>
        </div>
        <footer>
            <button class="button" id="logout">ログアウト</button>
        </footer>

        <script type="text/javascript">
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
        <script type="text/javascript" src="./js/user.js"></script>
    </body>
</html>
