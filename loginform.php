<!doctype html public "-//w3c//dtd html 4.01 Transitional//en">
<html>
<head>
	<title>生産管理システム</title>
	<meta http-equiv=content-type content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="main.css">
</head>

<body>

<div align="center">
	<img src="../graphics/seisan_login.png">
<br>

<!--
[生産管理システム]

 *ログイン処理(入力部分)

  2007/01/19
  2007/10/02 スタイルシートで背景変更
  2008/01/19 FS品管理のマニュアルを置いた 最初日本語のファイル名にしたらダウンロードNG
             とりあえず英数字のファイル名に変更 urlencodeで文字コード変換してやればOK？
  2011/01/05 修正
  2015/11/17 Logo画像修正

  2017/07/13 文字コードをUTF-8へ


-->

<?php

switch($status) {
case AUTH_IDLED :

case AUTH_EXPIRED :
	$err="ログイン期限が切れています。再ログインしてください";
	break;
case AUTH_WRONG_LOGIN :
	$err="ユーザID／パスワードが間違っています。";
	break;
default:
	$err = "";
	break;
}

?>
<!--
<form method="post" action="<?php print($_SERVER['PHP_SELF']) ?>">
-->

<form method="post" action="login.php?mode=login">
	<table border="0">
	<tr>
		<th align="right">User ID：</th>
		<td><input type="text" name="username" size="15" maxlength="20" /></td>
	</tr>
	<tr>
		<th align="right">Password：</th>
		<td><input type="password" name="password" size="15" maxlength="15" /></td>
	</tr>
	<tr>
		<td colspan="2">
			<input type="submit" value="ログイン" />
		</td>
	</tr>
	</table>

	<font color="Red"><b><?php print($err); ?></b></font>
	<!--
	<br>
	<font color="Orange"><b>＊ブラウザ設定 [表示]->[文字のサイズ]->[小] をお勧めします</b></font>
	-->
</form>
</div>

<br>
<br>

<table width="700" height="10" border="2" frame="border" align="center">
<tr>
	<td><span style="color:#000080;font-size:small;">
	手順書など
	</span></td>
</tr>
</table>

<table width="700" height="10" border="2" frame="border" align="center">
<tr align="center">
	<td><span style="color:#000080;font-size:small;">
	内容
	</span></td>
	<td><span style="color:#000080;font-size:small;">
	右クリックで「対象をファイルに保存」をお勧めします
	</span></td>
</tr>
<tr>
	<td><span style="color:#000080;font-size:small;">
	生産管理システム SMT福島ストック品管理 手順書(PDF) 2008/01/11版
	</span></td>
	<td><span style="color:#000000;font-size:small;">
	<a href="../distribution/Seisan_Project_Manual_FS_080111.pdf">Seisan_Project_Manual_FS_080111.pdf(0.6MB)</a>
	</span></td>
</tr>
</table>

<br>
<br>
<br>

<div align="center">
	<img src="../graphics/centos_logo.png">
	<img src="../graphics/apache_logo.png">
	<img src="../graphics/postgresql_logo.png">
	<img src="../graphics/php_logo.png">
</div>

</body>
</html>
