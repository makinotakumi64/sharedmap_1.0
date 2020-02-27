<?php

    //ファイルが読み込まれているかチェック
    require_once(__DIR__ . '/config.php');
    require_once(__DIR__ . '/account.php');

    //ログイン中のユーザ名でインスタンスを生成
    $account = new \SharedMap\Account($_POST['userName']);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {//リクエストメソッドがPOSTであるか判定
        try {
            // throw new \Exception('ユーザー名が入力されていません！#_createAccount');
            $response = $account->post();//postを呼びだし指定したmodeで処理を行う
            header('Content-Type: application/json');//レスポンスヘッダを指定
            echo json_encode($response);//応答を返す
            exit;
        } catch (\Exception $e) {//例外処理
            header($_SERVER['SERVER_PROTOCOL'] . '500 Internal Server Error', true, 500);
            echo $e->getMessage();
            exit;
        }
    }
