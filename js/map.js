    'use strict';

    var map;//マップクラスのインスタンスで利用
    var geocoder;//ジオコードクラスのインスタンスで利用
    var marker;//新しく作るマーカークラスのインスタンスで利用
    var xhr = [];//xmlhttprequestクラスのインスタンスで利用
    var infoWindow = [];//情報ウィンドウクラスのインスタンスで利用

    //使用するメッセージ
    const textSetMarker = 'マークしました!';
    const textRemoveMarker = 'マーカーを削除しました!';
    const textReadMarker = 'マーカーを読み込みました!';

    //map.phpから使用する属性IDを取得
    const userName = document.getElementById('userName').getAttribute('data-val');//ログイン中のユーザ名を取得
    const mapId = document.getElementById('map');//マップを表示するタグのIDを取得
    const markerNumId = document.getElementById('numberOfMarker');//マーカー数を表示するタグのIDを取得

    //ログイン中のユーザ名用のマーカー画像を読み込む
    const userSymbol = 'https://maps.google.com/mapfiles/ms/icons/red-dot.png';
    //フォロー中のユーザー用のマーカー画像を読み込む
    const followUserSymbol = 'https://maps.google.com/mapfiles/ms/icons/blue-dot.png';



    function initMap () {//google maps APIを読み込み終えたときに起動するコールバック関数
        var mapOptions = {//マップの初期位置、ズーム数を指定
            center: {
                lat: 35.681236,
                lng: 139.767125
            },
            zoom: 15
        }
        map  = new google.maps.Map(mapId, mapOptions);//マップ生成
        geocoder = new google.maps.Geocoder();//ジオコーダークラスのインスタンスを生成



        //ログインユーザーとフォローしているユーザーのマーカー設置(データベースから読み込む)
        window.onload = function () {
            readingMarker('readMarker');//ログインユーザーのマーカーを読み込む
            readingMarker('readFollowMarker');//ログインユーザーがフォローしているユーザーのマーカーを読み込む
            alert(textReadMarker);//読みこみ完了のアラート
        }



        //クリックした場所に移動,座標を表示
        map.addListener('click', function(e) {
            map.setCenter(e.latLng);
        });



        //マーカー設置(右クリック)する処理
        map.addListener('rightclick', function(e) {//マップを右クリックしたとき
            var marker = createUserMarker(e.latLng);//右クリックした位置にマーカーを作成
            infoWindowCreateAndOpen(marker, userName);//マーカーインスタンス、ユーザ名を渡して情報ウィンドウを作成

            var mode = 'setMarker';//マーカーを登録するモード
            phpRequest(mode, marker);//データーベースへマーカー情報を書き込む処理をサーバーへ指示
            removeMarker(marker);//マーカー削除
        });
    }



    //サーバーへモード別にリクエスト
    function phpRequest (mode, marker) {
        var url = './_ajax.php';//非同期処理で利用するURLを指定
        var requests = 'mode=' + mode + '&userName=' + userName;//リクエストする内容

        if (mode == 'setMarker' || mode == 'removeMarker') {//指定したmodeがsetMarkerまたはremoveMarkerのとき
            requests += '&lat=' + marker.position.lat() + '&lng=' + marker.position.lng();//リクエスト内容に緯度と経度を追加
        }

        try {
            xhr.mode = new XMLHttpRequest();//XMLHttpRequestインスタンスを生成
            xhr.mode.open('POST', url, true);//POSTメソッドでオープン

            xhr.responseType = 'json';//json形式でレスポンスを受信
            xhr.mode.setRequestHeader( 'Content-Type', 'application/x-www-form-urlencoded');//リクエストヘッダを指定

            switch (mode) {//modeによって非同期に行う処理を指定
                case 'setMarker'://マーカーをセットするモード
                    xhr.mode.send(requests);//リクエスト送信
                    phpResponse(xhr.mode, textSetMarker);//レスポンスが来た時の処理
                    markerNumId.textContent = parseInt(markerNumId.textContent) + 1;//マーカー数を1増やす
                    break;

                case 'removeMarker'://マーカーを取り除くモード
                    xhr.mode.send(requests);//リクエスト送信
                    phpResponse(xhr.mode, textRemoveMarker);//レスポンスが来た時の処理
                    markerNumId.textContent = parseInt(markerNumId.textContent) - 1;//マーカー数を1減らす
                    break;

                default:
                    xhr.mode.send(requests);//リクエスト送信
            }
        } catch (e) {
          console.error(e.message);//例外処理
        }
    }



    //phpからの応答が来たらアラートする
    function phpResponse (xhr, text) {
        xhr.onreadystatechange = function(){//readyStateが変化したときに作動
            try {
                if (this.readyState === 4 && this.status === 200) {//操作が完了し、成功
                    alert(text);//指定したテキストをアラート
                }
            } catch(e) {//例外処理
                console.error(e.message);
            }
        };
    }



    //データベースからマーカー情報を読み込む処理
    function readingMarker (mode) {
        phpRequest(mode);//modeを渡して非同期処理を行う

        xhr.mode.onreadystatechange = function () {//readyStateが変化したときに作動
            try {
                if (this.readyState === 4 && this.status === 200) {//操作が完了し、成功
                    var res = this.response;//レスポンス内容を受け取る
                    res = JSON.parse(res);//レスポンスをjson形式に変更
                    var readMarkers=[];//読み込んだマーカー用のインスタンスで利用

                    for (var i = 0; i < res.length; i++) {
                        readMarkers[i] = createUserMarker(new google.maps.LatLng(res[i]['lat'], res[i]['lng']));//緯度経度を渡してマーカーを作成
                        infoWindowCreateAndOpen(readMarkers[i], res[i]['username']);//マーカーインスタンスとユーザー名を渡して情報ウィンドウを作成

                        if (mode == 'readFollowMarker') {//modeがreadFollowMarkerのとき
                            readMarkers[i].setIcon(followUserSymbol);//フォロー中ユーザー用のマーカー画像をセット
                            readMarkers[i].setZIndex(2);//zIndexを指定
                        } else {
                            removeMarker(readMarkers[i]);//マーカーインスタンスを渡してマーカーを削除
                        }
                    }
                }
            } catch (e) {
                console.error(e.message);
            }
        }
    }



    //座標positionにマーカーを設置
    function createUserMarker (position) {
        var marker = new google.maps.Marker({
            position: position,
            map: map,
            icon: userSymbol,//ログインユーザー用のマーカー画像を指定
            zIndex: 3,//zIndexを指定
        });
        return marker;
    }



    //情報ウィンドウを作成し、ウィンドウを開く処理を追加
    function infoWindowCreateAndOpen (marker, res) {
        var infoWindow = new google.maps.InfoWindow({
            position: marker.getPosition(),
            content: 'ユーザー：' + res,//ウィンドウ内容を指定
            maxWidth: 120,//横幅最大値を指定
        });

        map.addListener('zoom_changed', function() {//マップのズーム数が変化したとき
            if (map.getZoom() < 11) {//ズーム数が11未満に縮小したとき
                infoWindow.close(map);//ウィンドウを閉じる
            }
        });

        marker.addListener('click', function() {//マーカーをクリックしたとき
            infoWindow.open(map);//ウィンドウを開く
        });
    }



    //マーカー削除する処理
    function removeMarker (marker) {
        marker.addListener('rightclick', function() {//マーカーを右クリックしたとき
            marker.setMap(null);//マーカーを初期化
            var mode = 'removeMarker';//マーカーを削除するモード
            phpRequest(mode, marker);//データーベースのマーカー情報を削除する処理をサーバーへ指示
        });
    }



    //マーカー設置(マーカーボタン)
    document.getElementById('marker').addEventListener('click', function (e) {//マークボタンをクリックしたとき
        try {
            geocoder.geocode({
                address: document.getElementById('address').value//ジオコーダーインスタンスのaddressプロパティに入力した住所または名前渡す
            }, function(results, status) {

                if (status !== 'OK') {//住所または名前が入力されていないとき
                    alert('住所または名前が入力されていません！');
                    return;
                }
                if (results[0]) {
                    var marker = createUserMarker(results[0].geometry.location);//入力した住所または名前の位置にマーカーを生成
                    infoWindowCreateAndOpen(marker, userName);//マーカーインスタンス、ユーザ名を渡して情報ウィンドウを作成

                    var mode = 'setMarker';//マーカーを登録するモード
                    phpRequest(mode, marker);//phpへリクエスト
                    removeMarker(marker);//マーカー削除
                } else {//入力された住所または名前が見つからないとき
                    alert('入力された住所または名前が見つかりませんでした！');
                    return;
                }
            });
        } catch (e) {
            console.error(e.message);
        }
    });



    //住所または名前を入力してマップに表示
    document.getElementById('search').addEventListener('click', function() {//探すボタンをクリックしたとき
        try {
            geocoder.geocode({
                address: document.getElementById('address').value//ジオコーダーインスタンスのaddressプロパティに入力した住所または名前渡す
            }, function(results, status) {
                if (status !== 'OK') {//住所または名前が入力されていないとき
                    alert('住所または名前が入力されていません！');
                    return;
                }

                if (results[0]) {
                    map.setCenter(results[0].geometry.location);//入力した住所または名前の位置にマーカーを生成
                    map.setZoom(20);//ズーム数20で拡大
                } else {//入力された住所または名前が見つからないとき
                    alert('入力された住所または名前が見つかりませんでした！');
                    return;
                }
            });
        } catch (e) {
            console.error(e.message);
        }
    });
