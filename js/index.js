{
    'user strict';
    var url = './_ajax.php';//非同期処理で利用するURLを指定



    //ログインボタンをクリックしたときにログインする処理をサーバーへ指示
    document.getElementById('loginButton').onclick = function () {
        var userName = document.getElementById('userName_login').value;//ユーザー名を取得
        var password = document.getElementById('password_login').value;//パスワードを取得

        //ユーザー名、パスワードが入力されているか判定（未入力、空白の場合）
        if(!userName || !password || userName.match(/\s/) || password.match(/\s/)){
            alert('ユーザー名またはパスワードが入力されていません！');
            return;
        }

        try {
            var requests = 'userName=' + userName + '&password=' + password + '&mode=check&';//リクエストする内容
            var xhr = new XMLHttpRequest();//XMLHttpRequestインスタンスを生成
            xhr.open('POST', url, true);//POSTメソッドでオープン
            xhr.responseType = 'json';//json形式でレスポンスを受信
            xhr.setRequestHeader( 'Content-Type', 'application/x-www-form-urlencoded' );//リクエストヘッダを指定
            xhr.send(requests);//リクエスト受信

            xhr.onreadystatechange = function () {//readyStateが変化したときに作動
                if (this.readyState === 4 && this.status === 200) {//操作が完了し、成功
                    var res = this.response;//サーバーからのレスポンスを受け取る
                    if (res === false) {//ログインできたとき
                        alert('ログインしました！');
                        window.location.href = './map.php?userName=' + userName;//マップページへ移動
                    } else {
                        alert('入力された名前、パスワードは登録されていません！');
                    }
                }
            };
        } catch (e) {//例外処理
          console.error(e.message);
        }
    }



    //アカウント作成ボタンをクリックしたときにアカウントを作る処理をサーバーへ指示
    document.getElementById('createButton').onclick = function () {
        var userName = document.getElementById('userName_create').value;//ユーザー名を取得
        var password = document.getElementById('password_create').value;//パスワードを取得

        //ユーザー名、パスワードが入力されているか判定（未入力、空白の場合）
        if (!userName || !password || userName.match(/\s/) || password.match(/\s/)) {
            alert('ユーザー名またはパスワードが入力されていません！');
            return;
        }

        try {
            var requests = 'userName=' + userName + '&password=' + password + '&mode=create&';//リクエストの内容
            var xhr = new XMLHttpRequest();//XMLHttpRequestインスタンスを生成
            xhr.open('POST', url, true);//POSTメソッドでオープン
            xhr.responseType = 'json';//json形式でレスポンスを受信
            xhr.setRequestHeader( 'Content-Type', 'application/x-www-form-urlencoded' );//リクエストヘッダを指定
            xhr.send(requests);//リクエスト受信

            xhr.onreadystatechange = function () {//readyStateが変化したときに作動
                if(this.readyState === 4 && this.status === 200){//操作が完了し、成功
                    var res = this.response;//サーバーからのレスポンスを受け取る
                    if (res === true) {//アカウントを作成し、ログインできたとき
                        alert('アカウントを作成し、ログインしました！');
                        window.location.href = './map.php?userName=' + userName;//マップページへ移動
                    } else {
                        alert('入力されたユーザ名は登録されています！');
                    }
                }
            };
        } catch (e) {//例外処理
          console.error(e.message);
        }
    }



    //ゲストユーザーとしてログインするボタンをクリックしたときにログインする処理をサーバーへ指示
    document.getElementById('guestButton').onclick = function () {
        alert('ゲストユーザーとしてログインしました！');
        window.location.href = './map.php?userName=guest';//マップページへ移動
    }
}
