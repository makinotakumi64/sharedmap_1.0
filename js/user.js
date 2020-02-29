{
    'use strict';

    const userName = document.getElementById('userName').getAttribute('data-val');//ログインユーザーの名前
    const url = './_ajax.php';//非同期処理で利用するURLを指定
    const searchResult = document.getElementById('searchResult');//検索結果を表示するliタグを参照
    const removeButton = document.querySelectorAll('button[name="removeButton"]');//フォロー解除ボタンを参照
    const numberOfFollow = document.getElementById('numberOfFollow');
    const followerList = document.querySelectorAll('li[id="userList"]');
    const followerListNumberOfFollower = document.querySelectorAll('span[id="numberOfFollower"]');
    var numberOfFollower;
    searchResult.style.display = 'none';//検索結果を非表示にする


    //検索ボタンをクリックしたときにユーザーが存在するか検索する処理をサーバーへ指示
    document.getElementById('search').onclick = function() {
        var searchName = document.getElementById('searchName').value;//入力されたユーザー名を参照
        if (!searchName || searchName.match(/\s/)) {//未入力、空白の場合
            alert('ユーザー名が入力されていません！');
            return;
        }

        try {
            var requests = 'searchName=' + searchName + '&mode=search&userName=' + userName;//サーバーへリクエストする内容
            var xhr = new XMLHttpRequest();//XMLHttpRequestインスタンスを生成
            xhr.open('POST', url, true);//POSTメソッドでオープン
            xhr.responseType = 'json';//json形式でレスポンスを受信
            xhr.setRequestHeader( 'Content-Type', 'application/x-www-form-urlencoded' );//リクエストヘッダを指定
            xhr.send(requests);//リクエスト送信

            xhr.onreadystatechange = function() {//readyStateが変化したときに作動
                if (this.readyState === 4 && this.status === 200) {//操作が完了し、成功
                    var res = this.response;//サーバーからのレスポンスを受け取る
                    if (res.result == false) {//ユーザーが見つからなかったとき
                        alert(searchName+'は見つかりませんでした。');
                        return;
                    }

                    //参照したliタグを表示し、子要素へ値を渡す
                    searchResult.style.display = '';
                    searchResult.children[1].textContent = res['searchName'];
                    searchResult.children[3].textContent = 'マーカー: ' + res['marker']+ '';
                    searchResult.children[4].textContent = 'フォロー: ' + res['followUser'];
                    searchResult.children[5].textContent = 'フォロワー: ' + res['followerUser'];
                    numberOfFollower = res['followerUser'];

                    if (searchName !== userName) {//入力したユーザーとログインユーザーが違うとき
                        searchResult.children[2].style.display = '';//フォローボタンを表示
                        follow(res['searchName']);//フォローする処理へ
                    } else {//入力したユーザーがログインユーザー同じとき
                        searchResult.children[2].style.display = 'none';//フォローボタンを非表示
                    }
                }
            }
        } catch(e) {
            console.error(e.message);//問題が発生したときにメッセージを表示
        }
    }


    //フォローする処理をサーバーへ指示
    function follow (searchName) {
        document.getElementById('followButton').onclick = function() {//フォローボタンが押されたとき
            try {
                var requests = 'mode=follow&userName=' + userName + '&searchName=' + searchName;//サーバーへリクエストする内容
                var xhr = new XMLHttpRequest();//XMLHttpRequestインスタンスを生成
                xhr.open('POST', url, true);//POSTメソッドでオープン
                xhr.responseType = 'json';//json形式でレスポンスを受信
                xhr.setRequestHeader( 'Content-Type', 'application/x-www-form-urlencoded' );//リクエストヘッダの指定
                xhr.send(requests);//リクエスト送信

                xhr.onreadystatechange = function () {//readyStateが変化したときに作動
                    if (this.readyState === 4 && this.status === 200) {//操作が完了し、成功
                        var res = this.response;//サーバーからのレスポンスを受け取る
                        if (res == true) {//フォローする処理が成功したとき
                            alert(searchName + 'をフォローしました！');
                            addFollowList(res);//フォローしたユーザーをフォローリストへ追加
                        } else {
                            alert(searchName + 'はすでにフォローしています！');//すでにフォローしていたとき
                        }
                    }
                };
            } catch(e) {
                console.error(e.message);//問題が発生したときにメッセージを表示
            }
        }
    }


    //初めから存在するフォロー解除ボタンにフォロー解除処理動作を紐づけ
    for (var i = 0; i < removeButton.length; i++) {
        removeButton[i].addEventListener('click', {
            followUserName: removeButton[i].parentNode.children[1].textContent,//フォロー解除ボタンを押されたユーザ名を渡す
            removebtn: removeButton[i],//押したフォロー解除ボタンを渡す
            handleEvent: removeButtonClick,//handleEventを紐づけ
        }, false);
    }


    //フォローした際にフォローリストへ追加する処理
    function addFollowList () {
        const addList = document.getElementById('addList');//追加するdivタグを参照
        const copy =  searchResult.cloneNode(true);//検索結果の内容をコピー
        copy.children[5].textContent = 'フォロワー: ' + (numberOfFollower + 1);//フォロワーを1増やす
        copy.children[2].textContent = 'フォロー解除';//ボタンの内容を変更

        copy.children[2].addEventListener('click', {//フォロー解除ボタンにフォロー解除処理動作を紐づけ
            followUserName: copy.children[2].parentNode.children[1].textContent,//フォロー解除ボタンを押されたユーザ名を渡す
            removebtn: copy.children[2],//押したフォロー解除ボタンを渡す
            handleEvent: removeButtonClick,//handleEventを紐づけ
        }, false);

        followerListAddFollower(copy.children[1].textContent);//フォローした人がフォロワーかどうか判定
        addList.appendChild(copy);//divタグへ追加
        numberOfFollow.textContent = parseInt(numberOfFollow.textContent) + 1;
        searchResult.style.display = 'none';//検索結果を非表示
    }


    //フォローした人がフォロワーリストにいたときにフォロワーを増やす処理
    function followerListAddFollower(searchName) {
        for (var i = 0; i < followerList.length; i++) {
            if (followerList[i].children[1].textContent === searchName) {//フォローした人がフォロワーだったとき
                followerListNumberOfFollower[i].textContent = parseInt(followerListNumberOfFollower[i].textContent) + 1;//フォロー数を1増やす
            }
        }
    }


    //フォロー解除した人がフォロワーリストにいたときにフォロワーを減らす処理
    function followerListRemoveFollower(followUserName) {
        for (var i = 0; i < followerList.length; i++) {
            if (followerList[i].children[1].textContent === followUserName) {//フォローした人がフォロワーだったとき
                followerListNumberOfFollower[i].textContent = parseInt(followerListNumberOfFollower[i].textContent) - 1;//フォロー数を1増やす
            }
        }
    }


    //フォロー解除し表示を消す処理
    function removeButtonClick (event) {
        if (!this.followUserName || this.followUserName.match(/\s/)) {//フォロー解除するユーザ名が設定されているか判定
            alert('ユーザー名が設定されていません！ #removeFollow' );
            return;
        }
        removeFollow(this.followUserName);//フォロー解除する処理をサーバーへ指示
        this.removebtn.parentNode.remove();//表示を消す
        numberOfFollow.textContent = parseInt(numberOfFollow.textContent) - 1;
        followerListRemoveFollower(this.followUserName);//フォロー解除した人がフォロワーかどうか判定
    }


    //フォロー解除する処理をサーバーへ指示
    function removeFollow (followUserName) {
        try {
            var requests = 'mode=removeFollow&userName=' + userName + '&followUserName=' + followUserName;//サーバーへリクエストする内容
            var xhr = new XMLHttpRequest();//XMLHttpRequestインスタンスを生成
            xhr.open('POST', url, true);//POSTメソッドでオープン
            xhr.responseType = 'json';//json形式でレスポンスを受信
            xhr.setRequestHeader( 'Content-Type', 'application/x-www-form-urlencoded' );//リクエストヘッダの指定
            xhr.send(requests);//リクエスト送信

            xhr.onreadystatechange = function () {//readyStateが変化したときに作動
                if (this.readyState === 4 && this.status === 200) {//操作が完了し、成功
                    var res = this.response;//サーバーからのレスポンスを受け取る
                    if (res == true) {//フォロー解除する処理が成功したとき
                        alert('フォロー解除しました！');
                    } else {
                        alert('フォロー解除できませんでした！');
                    }
                }
            };
        } catch(e) {
            console.error(e.message);
        }
    }
}
