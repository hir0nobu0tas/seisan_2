<!doctype html public "-//w3c//dtd html 4.01 Transitional//en">
<html>
<head>
	<title>生産管理システム</title>
	<meta http-equiv=content-type content="text/html; charset=utf-8">
	<meta http-equiv="content-script-type" content="text/javascript">
	<link rel="stylesheet" type="text/css" href="main.css">
</head>

<body>

<script type="text/javascript">
<!--
	function DblChg(FrNm1, Url1, FrNm2, Url2) {
		window.open(Url1, FrNm1);
		window.open(Url2, FrNm2);
	}
// -->
</script>

<!--
[生産管理システム]

 *ログイン処理
  ユーザIDとパスワードで各権限メニューの切替

  2007/02/13

  2007/09/29
  ログイン、ログアウトですぐに画面が切り替わるように修正
  (これで余分なクリックを省略)

  2007/10/02
  スタイルシートで背景変更

  2007/12/21
  [mw500] 追加

  2008/03/13
  MW500出荷履歴管理は別プロジェクトへ分けた

  2009/08/07
  SMT準備[arrange]ユーザ追加

  2017/07/13
  文字コードをUTF-8へ

-->

<?php

//---------------------------------------------------------------
// 初期設定
//---------------------------------------------------------------

// PEARライブラリ
require_once 'Auth/Auth.php';

function loginFunction($usr, $status)
{
    require_once 'loginform.php';
}

$params=array(
    "dsn"        =>"pgsql://seisan:u6oc7rwn@localhost/seisan_project",
    "table"      =>"auth",
    "usernamecol"=>"user_id",
    "passwordcol"=>"password",
    "db_fields"  =>"*",
    "cryptType"  =>"none");

//---------------------------------------------------------------

$myAuth=new Auth("DB", $params, "loginFunction");
$myAuth->start();

$mode = $_GET["mode"];

if ($mode=="login") {
    if ($myAuth->getAuth()) {
        $perm=$myAuth->getAuthData("permission");   // ユーザ毎の権限取得

//		print<<< EOF
//		<div align="center"><a href="menu.php?mode=$perm" target="menu">
//		<img src="../graphics/seisan_logo1.png" border="0"></a></div>
//EOF;

        switch ($perm) {
            case "developer":
                ?><script>
                DblChg('menu', 'menu.php?mode=developer', 'result', 'init_main.php');
                </script><?php
                break;

            case "admin":
                ?><script>
                DblChg('menu', 'menu.php?mode=admin', 'result', 'init_main.php');
                </script><?php
                break;

            case "user":
                ?><script>
                DblChg('menu', 'menu.php?mode=user', 'result', 'init_main.php');
                </script><?php
                break;

            case "guest":
                ?><script>
                DblChg('menu', 'menu.php?mode=guest', 'result', 'init_main.php');
                </script><?php
                break;

            case "setup":
                ?><script>
                DblChg('menu', 'menu.php?mode=setup', 'result', 'init_main.php');
                </script><?php
                break;
        }
    } else {
        exit(0);
    }
} elseif ($mode=="logout") {
//	print <<< EOF
//	<div align="center">
//	<a href="javascript:DblChg('menu','menu0.php','result','login.php?mode=login')">
//	<img src="../graphics/seisan_logout.png" border="0">
//	</a></div>
//EOF;
    ?><script>
        DblChg('menu', 'init_menu.php', 'result', 'login.php?mode=login');
    </script><?php

    $myAuth->logout();
} else {
    exit(0);
}
?>

</body>
</html>
