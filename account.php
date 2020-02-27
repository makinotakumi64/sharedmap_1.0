<?php

    namespace SharedMap;

    class Account
    {
        private $_database;//使用するPDOクラスのインスタンス
        private $_userName;//ユーザー名
        private $_numberOfMarker;//マーク数
        private $_numberOfFollowUser;//フォロー数
        private $_numberOfFollowerUser;//フォロワー数

        public function __construct($userName)//ユーザ名を指定してインスタンスを生成
        {
            $this->_setUserName($userName);//名前を設定
            try {
                $option = [\PDO::ATTR_EMULATE_PREPARES=>false];//浮動小数点数をfloatで扱うための設定
                //接続
                $this->_database = new \PDO(PDO_DSN, DB_USERNAME, DB_PASSWORD, $option);//PDOクラスのインスタンスの作成
                $this->_database->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);//エラーが出た際にExceptionを出す設定
                $this->_database->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);//安全なSQLを実行するための属性の設定
                $this->_setNumberOfMarker($this->_userName);//ユーザーのマーカー数を設定
                $this->_setNumberOfFollowUser($this->_userName);//ユーザーのフォロー数を設定
                $this->_setNumberOfFollowerUser($this->_userName);//ユーザーのフォロワー数を設定
            } catch (\PDOException $e) {//例外処理
              echo $e->getMessage();
              exit;
            }
        }



        //ユーザー名を返す
        public function getUserName()
        {
            return $this->_userName;
        }



        //マーク数を返す
        public function getNumberOfMarker()
        {
            return $this->_numberOfMarker;
        }



        //フォロー数を返す
        public function getNumberOfFollowUser()
        {
            return $this->_numberOfFollowUser;
        }



        //フォロワー数を返す
        public function getNumberOffollowerUser()
        {
            return $this->_numberOfFollowerUser;
        }



        //ユーザーの名前とマーカーの座標のリストを返す
        public function getMarkerList($userName)
        {
            //ユーザ名をしていし、見つかったユーザ名、緯度、経度をリストで返す
            $statement = $this->_database->prepare("select username, lat, lng from markerlist where username = ?;");
            $statement->execute([$userName]);
            return $statement;
        }



        //ログアウト時にPDOクラスのインスタンスを初期化
        public function logout()
        {
            $this->_database = null;
            return true;
        }



        //ユーザーの名前をセット
        private function _setUserName($userName)
        {
            $this->_userName = $userName;
        }



        //ユーザーのマーカーの数をセット
        private function _setNumberOfMarker($userName)
        {
            $statement = $this->getMarkerList($userName);
            $this->_numberOfMarker = $statement->rowCount();
        }



        //ユーザーのフォロー数をセット
        private function _setNumberOfFollowUser($userName)
        {
            $statement = $this->_getFollowUserList($userName);
            $this->_numberOfFollowUser = $statement->rowCount();
        }



        //ユーザーのフォロワー数をセット
        private function _setNumberOfFollowerUser($userName)
        {
            $statement = $this->_getFollowerUserList($userName);
            $this->_numberOfFollowerUser = $statement->rowCount();
        }



        //フォローしているユーザーリストを返す
        private function _getFollowUserList($userName)
        {
            //ユーザー名を指定し、フォローしているユーザー名をリストで返す
            $statement = $this->_database->prepare("select followedname from followlist where username = ?;");
            $statement->execute([$userName]);
            return $statement;
        }



        //フォローされているユーザーのリストを返す
        private function _getFollowerUserList($userName)
        {
            //ユーザー名を指定し、そのユーザーをフォローしているユーザーをリストで返す
            $statement = $this->_database->prepare("select username from followlist where followedname = ?;");
            $statement->execute([$userName]);
            return $statement;
        }



        /*
            ここからはindex.phpで使用するメソッド
        */



        //アカウントを作成するためにデータベースへユーザ名、パスワードを書き込む
        private function _createAccount()
        {
            if (!isset($_POST['userName']) || $_POST['userName'] === '') {//ユーザー名が設定されているか判定
                throw new \Exception('ユーザー名が入力されていません！#_createAccount');
            }

            //同じ名前のユーザーが存在するか検索
            $statement = $this->_database->prepare("select * from users where username = ?");
            $statement->execute([$_POST['userName']]);

            if ($statement->rowCount() > 0) {//同じ名前のユーザーがいたとき
                return false;
            }

            //入力したユーザー名、パスワードをデータベースへ書き込む
            $order = sprintf("insert into users (username, password) values ('%s', '%s')", $_POST['userName'], $_POST['password']);
            $this->_database->exec($order);
            return true;//成功したらtrueで返す
        }



        //入力されたユーザー名とパスワードがデータベースに登録されているか確認
        private function _checkAccount()
        {
            if (!isset($_POST['userName']) || $_POST['userName'] === '') {//ユーザー名が設定されているか判定
                if (!isset($_POST['password']) || $_POST['password'] === '') {//パスワードが設定されているか判定
                    throw new \Exception('ユーザー名とパスワードが設定されていません！#_checkAccount');
                }
                throw new \Exception('ユーザー名が設定されていません！#_checkAccount');
            } elseif (!isset($_POST['password']) || $_POST['password'] === '') {
                throw new \Exception('パスワードが設定されていません！#_checkAccount');
            }

            //ユーザー名とパスワードを指定してデーターベースを検索
            $statement = $this->_database->prepare("select * from users where username = ? and password = ?");
            $statement->execute([$_POST['userName'], $_POST['password']]);

            if ($statement->rowCount() == 0) {//同じユーザー名とパスワードがなかったらtrueを返す
                return true;
            } else {
                return false;
            }
        }



        /*
            ここからはmain.jsで使用するメソッド
        */



        // マーカーを書き込む
        private function _setMarker()
        {
            if (!is_numeric($_POST['lat']) || !is_numeric($_POST['lng'])) {//緯度、経度が設定されているか判定
                throw new \InvalidArgumentException('マーカーの座標が正しく取得されてません！#account.php:setMarker');
            }

            //ユーザー名、緯度、経度を指定してデーターベースへ書き込む
            $statement = $this->_database->prepare("insert into markerlist (username, lat, lng) values (?, ?, ?)");
            $statement->execute([$this->_userName, $_POST['lat'], $_POST['lng']]);
        }



        //マーカーを削除
        private function _removeMarker()
        {
            if (!is_numeric($_POST['lat']) || !is_numeric($_POST['lng'])) {//緯度、経度が設定されているか判定
                throw new \InvalidArgumentException('マーカーの座標が正しく取得されてません！#account.php:removeMarker');
            }

            //ユーザー名、緯度、経度を指定してデーターベースのデータを削除
            $statement = $this->_database->prepare("delete from markerlist where username = ? and lat = ? and lng = ?;");
            $statement->execute([$this->_userName, $_POST["lat"], $_POST['lng']]);
        }



        //ユーザーの名前とマーカーの座標をリストで返す
        private function _readMarker()
        {
            $statement = $this->getMarkerList($this->_userName);//ユーザー名を指定してユーザーの名前とマーカーの座標をリストをもらう
            $positions = $statement->fetchAll(\PDO::FETCH_OBJ);
            return $positions;
        }



        //フォローしているユーザーの名前とマーカーの座標をリストで返す
        private function _readFollowMarker()
        {
            $followUserList = $this->_getFollowUserList($this->_userName);//ユーザー名を指定してファローしているユーザーのリストをもらう
            $positionsList=[];
            $positions=[];

            foreach ($followUserList as $value) {
                $statement = $this->getMarkerList($value['followedname']);//ユーザー名を指定してユーザーの名前とマーカーの座標をリストをもらう
                $positionsList[] = $statement->fetchAll(\PDO::FETCH_OBJ);
            }

            //JavaScriptで扱いやすいように格納
            foreach ($positionsList as $lst) {
                foreach ($lst as $value) {
                    $positions[] = $value;
                }
            }

            return $positions;
        }



        /*
            ここからはuser.phpで使用するメソッド
        */



        //フォローしている各ユーザーの名前、マーカー数、フォロー数、フォロワー数をデータベースから読み込んでリストで返す
        public function getFollowInfoList()
        {
            $list = $this->_getFollowUserList($this->_userName);//ユーザーのフォローリストを取得
            foreach ($list as $key => $value) {
                $marker = $this->getMarkerList($value['followedname']);//マーカーのリストをもらう
                $marker = $marker->rowCount();//マーカー数を設定
                $followUser = $this->_getFollowUserList($value['followedname']);//フォローしているユーザーのリストをもらう
                $followUser = $followUser->rowCount();//フォロー数を設定
                $followerUser = $this->_getFollowerUserList($value['followedname']);//フォロワーリストをもらう
                $followerUser = $followerUser->rowCount();//フォロワー数を設定

                //JavaScriptで扱いやすいように格納
                $infoList[$key] = ['userName' => $value['followedname'],
                                   'marker' => $marker,
                                   'followUser' => $followUser,
                                   'followerUser' => $followerUser
                                  ];
            }
            return $infoList;
        }



        //フォローしている各ユーザーの名前、マーカー数、フォロー数、フォロワー数をデータベースから読み込んでリストで返す
        public function getFollowerInfoList()
        {
            $list = $this->_getFollowerUserList($this->_userName);//ユーザーのフォロワーリストを取得
            foreach ($list as $key => $value) {
                $marker = $this->getMarkerList($value['username']);//マーカーのリストをもらう
                $marker = $marker->rowCount();//マーカー数を設定
                $followUser = $this->_getFollowUserList($value['username']);//フォローしているユーザーのリストをもらう
                $followUser = $followUser->rowCount();//フォロー数を設定
                $followerUser = $this->_getFollowerUserList($value['username']);//フォロワーリストをもらう
                $followerUser = $followerUser->rowCount();//フォロワー数を設定

                //JavaScriptで扱いやすいように格納
                $infoList[$key] = ['userName' => $value['username'],
                                   'marker' => $marker,
                                   'followUser' => $followUser,
                                   'followerUser' => $followerUser
                                  ];
            }
            return $infoList;
        }



        //渡されたユーザー名をデータベースから検索し、存在したらユーザー情報をリストで返す
        private function _searchAccount()
        {
            if (!isset($_POST['searchName']) || $_POST['searchName'] === '') {//ユーザー名が設定されているか判定
                throw new \Exception('ユーザー名が設定されていません！#_searchAccount');
            }

            //ユーザー名を指定しデータベースを検索
            $statement = $this->_database->prepare("select * from users where username = ?");
            $statement->execute([$_POST['searchName']]);

            if ($statement->rowCount() == 0) {//同じ名前のユーザーがいなかったらfalseを返す
                return ['result' => false];
            }

            //ユーザー名からインスタンスを生成し、ユーザー情報を返す
            $account = new \SharedMap\Account($_POST['searchName']);
            return ['searchName' => $account->_userName,
                    'marker' => $account->_numberOfMarker,
                    'followUser' => $account->_numberOfFollowUser,
                    'followerUser' => $account->_numberOfFollowerUser,
                   ];
        }



        //入力したユーザーをフォローしているか判定するメソッド
        private function _checkFollow()
        {
            if (!isset($_POST['searchName']) || $_POST['searchName'] === '') {//ユーザー名が設定されているか判定
                throw new \Exception('ユーザー名が設定されていません！#_checkFollow');
            }

            //ユーザー名、入力したユーザ名をしていしてデーターベースを検索
            $statement = $this->_database->prepare("select * from followlist where username = ? and followedname = ?");
            $statement->execute([$_POST['userName'], $_POST['searchName']]);

            if ($statement->rowCount() == 0) {//フォローしていないとき
                return true;
            } else {
                return false;//フォローしていた時
            }
        }



        //フォローしているユーザーのフォローを外すメソッド
        private function _removeFollowAccount()
        {
            if (!isset($_POST['userName']) || $_POST['userName'] === '') {//ユーザー名が設定されているか判定
                throw new \Exception('ユーザー名が設定されていません！#_removeFollowAccount');
            }
            if (!isset($_POST['followUserName']) || $_POST['followUserName'] === '') {//フォローしているユーザー名が設定されているか判定
                throw new \Exception('入力したユーザー名が設定されていません！#_removeFollowAccount');
            }

            //ユーザー名、入力したユーザー名をしていしてデータベースのデータを削除
            $statement = $this->_database->prepare("delete from followlist where username = ? and followedname = ?");
            $statement->execute([$_POST['userName'], $_POST['followUserName']]);
            return true;//成功したらtrueを返す
        }



        //入力したユーザーをフォローするメソッド
        private function _followAccount()
        {
            if (!isset($_POST['userName']) || $_POST['userName'] === '') {//ユーザー名が設定されているか判定
                throw new \Exception('ユーザー名が設定されていません！#_followAccount');
            }

            if (!isset($_POST['searchName']) || $_POST['searchName'] === '') {//入力したユーザー名が設定されているか判定
                throw new \Exception('入力したユーザー名が設定されていません！#_followAccount');
            }

            if ($this->_checkFollow() == true) {//フォローしていないとき
                //ユーザー名、入力したユーザー名を指定してデータベースへ書き込む
                $statement = $this->_database->prepare("insert into followlist (username, followedname) values (?, ?)");
                $statement->execute([$_POST['userName'], $_POST['searchName']]);
                return true;//成功したらtrueを返す
            } else {
                return false;//フォローしていた時
            }
        }



        /*
              _ajax.phpで使用するメソッド
        */



        //_ajax.phpからのそれぞれの指示を行う
        public function post()
        {
            if (!isset($_POST['mode'])) {//modeが設定されていないとき
                throw new \Exception('モードが設定されていません！#account.php:get');
            }

            switch ($_POST['mode']) {//modeの値によって行う処理をする
                case 'removeFollow'://フォローを外す処理
                    return $this->_removeFollowAccount();
                    //no break
                case 'follow'://フォローする処理
                    return $this->_followAccount();
                    //no break
                case 'search'://入力したユーザー名を検索する処理
                    return $this->_searchAccount();
                    //no break
                case 'create'://アカウントを作成する処理
                    return $this->_createAccount();
                    //no break
                case 'check'://ユーザー名とパスワードをチェックする処理
                    return $this->_checkAccount();
                    //no break
                case 'setMarker'://マーカーをセットする処理
                    $this->_setMarker();
                    break;
                case 'removeMarker'://マーカーを削除する処理
                    $this->_removeMarker();
                     break;
                case 'readMarker'://ユーザーのマーカーを読み込む処理
                    return $this->_readMarker();
                    //no break
                case 'readFollowMarker'://フォロワーのマーカーを読み込む処理
                    return $this->_readFollowMarker();
                    //no break
            }
            return;
        }
    }
