<!doctype html public "-//w3c//dtd html 4.01 Transitional//en">
<html>
<head>
	<meta http-equiv="content-type" content="text/html; charset=euc-jp">
	<title>SMT生産管理システム</title>
	<script type="text/javascript" src="lib/colorful.js"></script>
	<link rel="stylesheet" type="text/css" href="main.css">
</head>

<body>

<!--
[SMT生産管理システム]

 *生産ラインスケジュール
  日付及び号機で検索 -> 該当する生産計画をテーブル表示
  -> [実完] SMT実装完了,[修完] SMT修正完了 登録

  2006/06/20

  2006/07/19
  残数処理は[order_info]と[planning]で同じとして基本的にSMT実装完の数量を
  オーダーの数量から引いていく事にする

  2006/09/12
  管理者、作業者、閲覧者を$permissionで区別し共用スクリプトとする

  2006/09/14
  生産ライン編集にて「SMT修正完」登録後、登録解除で生産数が元に戻らない
  -> 修正

  2006/09/15
  分割ASSで残数の辻褄が合わなくなっていた事を修正

  2006/09/25
  生産順が裏->表がデフォルトなので1=表、2=裏 => 1=裏、2=表へ変更

  2006/09/26
  SMT修正メニュー追加
  実装完登録が有り、修正完登録が無い工番をリストアップ 修正完登録専用

  2006/10/18
  SMT修正でSMT実装完時間を優先してソート表示へ変更
  (基本的に実装が完了した順に修正？)

  2006/10/26
  SMT修正完登録時に欠品等のコメント追加対応中
  (簡易送り状に欠品状況を記載したい 斎藤S要望)
  [order_info]の最後にTEXT配列で格納しようとしたが断念(扱いが難しい？)
  イマイチな処理だが専用のテーブルを作成して対応

  2006/11/06
  登録画面からの実績数が無効になっていた -> 修正
  生産計画数を実績数で更新していたのをコメントアウト

  2006/11/09
  M/C種別と号機を統合して品名基礎データに格納 [planning][machine]は参照しない
                          DB   表示
  未定                 -> 0 -> 0
  JUKI(1号機 ボンド有) -> 1 -> 1B
  JUKI(1号機)          -> 2 -> 1
  JUKI(2号機)          -> 3 -> 2
  九松(3号機)          -> 4 -> 3
  手付け               -> 5 -> 4
  外向                 -> 9 -> 9

  2006/11/10
  号機指定表示NG -> 修正

  2006/11/14
  [planning]に集合ASS[mf_set]を追加

  2006/11/16
  [order_info]に備考[rem_ass](欠品有りでも実装など実装時の指示)を追加
  大日程編集の個別編集で入力して生産ライン編集の一覧表示に表示

  共晶、PBFで表面/裏面の背景色固定(秀明S要望)

  2006/11/17
  SMT修正一覧表示変更(現場要望)
  両面     -> 表のみ表示
  片面(表) -> 表
  片面(裏) -> 裏

  両面でも表面分だけ修正登録を行う事になるが[planning]の更新は？
  [order_id]で検索してupdateだと分割ASS分までupdateされてしまう
  とりあえず[planning]更新は従来のまま 送り状作成では重複チェックでオーダー
  単位の表示としているので送り状から漏れる事は無い
  現状では両面の裏がSMT修正登録されないまま残る事になる

  2006/12/01
  手付け品も号機指定無し一覧で表示する -> 修正

  2006/12/11
  欠品有りでSMT修正完登録を「SMT修正(欠品取付)」で一覧表示と取付登録機能追加
  (SMT修正日時は欠品取付時に更新)

  2006/12/12
  SMT修正一覧にも「備考」表示追加

  2006/12/18
  一時テーブルへのデータ格納時にNULLチェック追加
  (ログにエラーとして残ってしまうのを防止)
  一覧表示部を外部ファイル化

  2006/12/19
  メニューから呼出された時はセッション情報のクリア追加
  (他項目から偏移した時の最初のエラーを無くす)

  2006/12/21
  メニューからのpermission取得が抜けていた
  -> 修正

  2006/12/28
  欠品有りのまま出荷対応
  「欠品出荷」を追加して「欠品取付」と同等の処理を行う

  2007/01/30
  装置名 -> 基板名表示へ
  号機 -> M/C種別(機能種別)
  追加    号機(実装号機)
  M/C種別、号機 分離対応

  (新)            DB
  [pdoduct]
  未定          = 0
  JUKI 1        = 1
  JUKI 1,2      = 2
  九松          = 3
  JUKI 1/九松   = 4
  JUKI 1,2/九松 = 5
  手付け        = 6
  外向          = 9

  [planning]
  未定          = 0
  JUKI 1        = 1
  JUKI 2        = 2
  九松          = 3
  手付け        = 4
  外向          = 9

  2007/02/05
  タクトタイム係数[$mod_tact]追加
  現在のタクトタイムからの生産予定と実際の作業時間の差が大きいので暫定処理として
  タクトタイムに1.4の係数を掛けて生産計画を出力する
  公表する処理ではなくあまり変更しないはずなのでソースに直接記述

  2007/02/08
  SMT開始ボタン追加 開始時間の記録と表示のみ

  2007/02/09
  SMT修正待ち一覧には基本的に表面(片面で裏は裏面)を表示して修正完登録を計画IDで
  行っていたが、裏面の修正登録がされないためオーダーIDで更新する
  分割ASSはダッシュに-01等を付けて別登録としているのでオーダーIDで更新しても
  分割分に影響は無い

  2007/02/26
  ASS時の1p部品照合として[product]に[ck_time]追加(初期値は1800(30分)とする)
  集計時間に反映

  2007/02/27
  実装確認は集合ASSの場合代表1p(集合ASS指定品は加算しない)

  2007/03/16
  欠品取付け一覧をExcel出力 簡易送り状のように欠品品名が判るようにする
  欠品一覧で従来は最初の1項目で欠品の有り無しを確認していたが10項目まで確認するように
  修正

  修正系の一覧から面表示削除(多分必要ない？)

  2007/04/19
  PEAR::DB -> PEAR::MDB2へ変更(DBは開発終了で後継がMDB2？)
  トランザクション処理追加とエラー発生時の処理見直し

  2007/04/23
  運用されていないようなのでスケジュールのバー表示コメントアウト

  2007/05/09
  [product] -> [product_smt]へ変更(ポイントフロー管理対応の為)

  2007/06/14
  PF工程での作業状況確認の為、[order_info][status]処理追加
  [order_info]でそのオーダーの作業状況を管理する

   NULL = 未登録
   0    = SMT実装 前(オーダー登録時に設定)
   1    = SMT実装 完(SMT実装完時に設定)
   2    = SMT修正 完(SMT修正完時に設定)
   3    = PF工程  完(SMT修正完一覧から管理者が個別に完了登録を行う)

  2007/10/02
  生産スケジュールのバーグラフは生産順がまともに設定されず、現状では
  表示させる意味がないので一時的にコメントアウトする
  もし生産順をある程度まともに入力出来るようになったら復活？

  2007/11/21
  開始/完了登録時に日時を変更しようとしてミスしている？ようなので、入力
  フィールドを分けて入力ミスをしにくくなるように入力画面を修正
  また検証ルールを入れて入力時のミスを弾くように修正
  入力書式のコメント追加
  残数処理で最悪でもマイナスの数量になったり、生産実績がオーダー数量を超え
  ないように暫定処置 残数処理は今後見直しが必要

  2007/12/11
  一覧表示のソート条件に[product]も追加

  2007/12/20
  号機別表示にした時に品名毎にソートされない ソート条件修正(機種を追加)

  2008/01/16
  エラー対策でInt型のテーブルに書く前に型キャスト(int)を行うように修正

  2008/01/18
  修正ミス！ 修正完登録が出来なくなっていた再修正

  2008/02/13
  PostgreSQL 8.3対応
  SMT修正(欠品取付け)でSQL文のWHERE句書式修正 text型に対してinteger型で
  検索しようとしていた エラーが出たり出なかったりする？

  2008/08/28
  備考[remarks]からテーブル定義を修正した[remarks2]へ 処理の修正
  インデックス管理によりカラム数を削減(32→6へ)

  2008/08/29
  スケジュールのバー表示処理を外部ファイル化

-->

<?php
//---------------------------------------------------------------
// 初期設定
//---------------------------------------------------------------

// 共有関数
include 'inc/parameter_inc.php';
include 'lib/com_func1.php';
include 'lib/html_func.php';
include 'lib/time_func1.php';

// PEARライブラリ
require_once 'HTML/QuickForm.php';
require_once 'MDB2.php';

// 配列設定
$sel_mc1 = array(0=>"-Select-", 1=>"JUKI[1号機]", 2=>"JUKI[2号機]", 3=>"九松[3号機]",
				4=>"手付け", 5=>"SMT修正(通常)", 6=>"SMT修正(欠品取付)");

$sel_rem = array(0=>"-Select-", 1=>"欠品", 2=>"その他", 3=>"欠品取付", 4=>"欠品出荷");

//---------------------------------------------------------------
// SMT修正用一時テーブルへの登録処理
function fix_tmp_reg($mdb2, $data) {

	// トランザクションブロック開始
	$res = $mdb2->beginTransaction();

	$res = $mdb2->exec("INSERT INTO fix_tmp(plan_id) VALUES(".$mdb2->quote($data[0]).")");
	$res = $mdb2->exec("UPDATE fix_tmp SET operate_no='$data[1]' WHERE plan_id='$data[0]'");

	if ($data[2]!=NULL) {
		$res = $mdb2->exec("UPDATE fix_tmp SET dash_no='$data[2]' WHERE plan_id='$data[0]'");
	}

	$res = $mdb2->exec("UPDATE fix_tmp SET product='$data[3]' WHERE plan_id='$data[0]'");
	$res = $mdb2->exec("UPDATE fix_tmp SET board='$data[4]' WHERE plan_id='$data[0]'");

	if ($data[5]!=NULL) {
		$res = $mdb2->exec("UPDATE fix_tmp SET solder='$data[5]' WHERE plan_id='$data[0]'");
	}

	if ($data[6]!=NULL) {
		$res = $mdb2->exec("UPDATE fix_tmp SET ass_side='$data[6]' WHERE plan_id='$data[0]'");
	}

	if ($data[7]!=NULL) {
		$res = $mdb2->exec("UPDATE fix_tmp SET machine='$data[7]' WHERE plan_id='$data[0]'");
	}

	if ($data[8]!=NULL) {
		$res = $mdb2->exec("UPDATE fix_tmp SET qty20='$data[8]' WHERE plan_id='$data[0]'");
	}

	if ($data[9]!=NULL) {
		$res = $mdb2->exec("UPDATE fix_tmp SET qty30='$data[9]' WHERE plan_id='$data[0]'");
	}

	if ($data[10]!=NULL) {
		$res = $mdb2->exec("UPDATE fix_tmp SET plan_fix='$data[10]' WHERE plan_id='$data[0]'");
	}

	if ($data[11]!=NULL) {
		$res = $mdb2->exec("UPDATE fix_tmp SET smt_tmp='$data[11]' WHERE plan_id='$data[0]'");
	}

	if ($data[12]!=NULL) {
		$res = $mdb2->exec("UPDATE fix_tmp SET rem_ass='$data[12]' WHERE plan_id='$data[0]'");
	}

	// トランザクションブロック終了
	$res = transaction_end($mdb2, $res);

}

// セッション破棄
function unset_session() {
	unset($_SESSION["mode_schedule1"]);
	unset($_SESSION["id"]);
	unset($_SESSION["mfd"]);
	unset($_SESSION["machine"]);
	unset($_SESSION["status"]);
	unset($_SESSION["permission"]);
}

//セッション開始
session_start();
if ($_GET['mode_schedule1']=='search') {

	unset($_SESSION["id"]);
	unset($_SESSION["mfd"]);
	unset($_SESSION["machine"]);
	unset($_SESSION["status"]);
	unset($_SESSION["permission"]);
	$mode = $_GET["mode_schedule1"];
	$permission = $_GET["permission"];

} elseif ($_SESSION["mode_schedule1"]!=NULL) {

	$mode = $_SESSION["mode_schedule1"];
	$permission = $_SESSION["permission"];

	$eid = $_GET["id"];
	$_SESSION["eid"] = $eid;

	if ($_SESSION["mode_schedule1"]=='edit') {
		$status = $_GET["status"];
		$_SESSION["status"] = $status;
	}

} else {

	$mode = $_GET["mode_schedule1"];
	$permission = $_GET["permission"];

}

switch($mode) {
case "search":

	print('<br>');

	$form = new HTML_QuickForm("search", "POST");
	$form -> addElement("header", "title", "生産ライン 検索:");
	$form -> addElement("date", "mfd", "生産日:",
			array("language"=>"ja","minYear"=>2000,"maxYear"=>2030,"format"=>"Ymd"));
	$form -> addElement("select", "mc1", "号機:", $sel_mc1);

	// 今日の取得
	$yyyy = date("Y");
	$mm = date("m");
	$dd = date("d");

	// デフォルトは当日
	$form -> setDefaults(array("mfd"=>array("Y"=>$yyyy, "m"=>$mm, "d"=>$dd)));

	$form -> addElement("submit", "send", "検索");

	if ($form -> validate()) {
		$form -> process("showForm",FALSE);
	} else {
		$form -> setRequiredNote("<font color='Red'>*</font> 必須項目です");
		$form -> setJsWarnings("以下の項目でエラーが発生しました",
			"エラー項目を修正して、再度［データベース登録］ボタンをクリックしてください");
		$form->display();
	}

//	print('<div align="left"><font size="3" color="#0066cc"><b>');
//	print('＊SMT M/C実装は号機: -> 「担当号機」を選択してください');
//	print('<br>');
//	print('＊SMT 手付け実装は号機: -> 「手付け」を選択してください');
//	print('<br>');
//	print('＊SMT 修正は号機: -> 「SMT修正(通常)」を選択してください');
//	print('</b></div>');

	$_SESSION["mode_schedule1"] = 'disp';
	$_SESSION["permission"] = $permission;

	break;


case "disp":

	// DB接続
	$mdb2 = db_connect();

//	// 一時テーブル作成
	$res = $mdb2->exec("CREATE TEMP TABLE fix_tmp (
					tmp_id SERIAL,
					plan_id TEXT,
					operate_no TEXT,
					dash_no TEXT,
					product TEXT,
					board TEXT,
					solder TEXT,
					ass_side TEXT,
					machine TEXT,
					qty20 TEXT,
					qty30 TEXT,
					plan_fix DATE,
					smt_tmp TIMESTAMP,
					rem_ass TEXT,
					CONSTRAINT fix_tmp_pkey PRIMARY KEY (tmp_id))");


	// $_SESSION["id"]にデータ有り -> 2回目の表示(データの更新も行う)
	if ($_SESSION["id"]!=NULL) {

		$plan_id = $_SESSION["id"];
		$status  = $_SESSION["status"];

		$qty30   = (int)Trim($_POST['qty30']);

		// 2006/11/06 修正 登録画面からの実績数が無効になっていた
		$plan_row = row_plan($mdb2, $plan_id);
			$order_id = $plan_row[1];
			$ass_side = $plan_row[2];
			$qty20 = $plan_row[3];	// 生産数(計画)
			$qty21 = $plan_row[4];	// 残数

		$order_row = row_order($mdb2, $order_id);
			$qty10 = $order_row[4];	// 数量
			$qty11 = $order_row[5];	// 残数(表)
			$qty12 = $order_row[6];	// 残数(裏)

		if ($status=='st') {

			// SMT開始登録
			$date_start = Trim($_POST['date_start']);
			$time_start = Trim($_POST['time_start']);
			$smt_start  = $date_start . " " . $time_start;
			$mc_start   = (int)$_POST['mc_start']["mc_start"];

			// [planning] 更新
			if ($mc_start==1) {
				$res = $mdb2->exec("UPDATE planning SET smt_start='$smt_start' WHERE plan_id='$plan_id'");
			} elseif ($mc_start==0) {
				$res = $mdb2->exec("UPDATE planning SET smt_start=NULL WHERE plan_id='$plan_id'");
			}

		} elseif ($status=='mc') {

			// SMT終了登録
			$date_mc = Trim($_POST['date_mc']);
			$time_mc = Trim($_POST['time_mc']);
			$smt_mc  = $date_mc . " " . $time_mc;
			$mc_comp = (int)$_POST['mc_comp']["mc_comp"];

			// [planning] 更新
			// 2006/09/15 生産数 = [登録]実績数と残数処理修正
			if ($mc_comp==1) {

				// 登録追加
				if ($ass_side==2) {
					$qty_tmp = $qty11 - $qty30;
				} elseif ($ass_side==1) {
					$qty_tmp = $qty12 - $qty30;
				}

				// 2007/11/21 最悪でも残数がマイナスにならないように仮対応(暫定処置)
				if ($qty_tmp<0) {
					$qty_tmp = 0;
				}

				// トランザクションブロック開始
				$res = $mdb2->beginTransaction();

				$res = $mdb2->exec("UPDATE planning SET qty30='$qty30' WHERE plan_id='$plan_id'");
				$res = $mdb2->exec("UPDATE planning SET smt_mc='$smt_mc' WHERE plan_id='$plan_id'");

				// 2006/11/06 修正(生産計画数は更新しない)
//				$res = $mdb2->exec("UPDATE planning SET qty20='$qty30' WHERE plan_id='$plan_id'");
				$res = $mdb2->exec("UPDATE planning SET qty21='$qty_tmp' WHERE plan_id='$plan_id'");

				if ($ass_side==2) {
					$res = $mdb2->exec("UPDATE order_info SET qty11='$qty_tmp' WHERE order_id='$order_id'");
				} elseif ($ass_side==1) {
					$res = $mdb2->exec("UPDATE order_info SET qty12='$qty_tmp' WHERE order_id='$order_id'");
				}

				// 2007/06/14 追加
				$res = $mdb2->exec("UPDATE order_info SET status=1 WHERE order_id='$order_id'");

				// トランザクションブロック終了
				$res = transaction_end($mdb2, $res);

			} elseif ($mc_comp==0) {

				// 登録削除
				if ($ass_side==2) {
					$qty_tmp = $qty11 + $qty30;
				} elseif ($ass_side==1) {
					$qty_tmp = $qty12 + $qty30;
				}

				// 2007/11/21 最悪でも残数が生産数を超えないように仮対応(暫定処置)
				if ($qty20<$qty_tmp) {
					$qty_tmp = $qty20;
				}

				// トランザクションブロック開始
   				$res = $mdb2->beginTransaction();

				$res = $mdb2->exec("UPDATE planning SET qty30=0 WHERE plan_id='$plan_id'");
				$res = $mdb2->exec("UPDATE planning SET smt_mc=NULL WHERE plan_id='$plan_id'");

				// 2006/09/14 qty20処理追加
				// 2007/11/21 修正(生産計画数は更新しない)
//				$res = $mdb2->exec("UPDATE planning SET qty20='$qty_tmp' WHERE plan_id='$plan_id'");
				$res = $mdb2->exec("UPDATE planning SET qty21='$qty_tmp' WHERE plan_id='$plan_id'");

				if ($ass_side==2) {
					$res = $mdb2->exec("UPDATE order_info SET qty11='$qty_tmp' WHERE order_id='$order_id'");
				} elseif ($ass_side==1) {
					$res = $mdb2->exec("UPDATE order_info SET qty12='$qty_tmp' WHERE order_id='$order_id'");
				}

				// 2007/06/14 追加
				$res = $mdb2->exec("UPDATE order_info SET status=0 WHERE order_id='$order_id'");

				// トランザクションブロック終了
				$res = transaction_end($mdb2, $res);

			}

		} elseif ($status=='fix') {

			$date_fix = $_POST['date_fix'];
			$time_fix = $_POST['time_fix'];
			$smt_fix  = $date_fix . " " . $time_fix;
			$fix_comp = $_POST['fix_comp']["fix_comp"];
			$rem_set  = $_POST['remarks'];

			$plan_row = row_plan($mdb2, $plan_id);
				$order_id = $plan_row[1];

			// [order_info] 更新
			if ($rem_set!=NULL) {

				/*
				// オーダーIDで検索してレコードが無ければ追加登録、有れば更新
				$res = $mdb2->queryRow("SELECT * FROM remarks WHERE order_id='$order_id'");
				if (PEAR::isError($res)) {
				} else {
					$order_tmp = $res[0];
				}

				if ($order_tmp==NULL) {

					$res = $mdb2->exec("INSERT INTO remarks(order_id, rem00, rem01, rem02, rem10, rem11, rem12, rem20, rem21, rem22, rem30, rem31, rem32, rem40, rem41, rem42, rem50, rem51, rem52, rem60, rem61, rem62, rem70, rem71, rem72, rem80, rem81, rem82, rem90, rem91, rem92) VALUES(
								".$mdb2->quote($order_id, 'Integer').",
								".$mdb2->quote($rem_set[0][0], 'Text').",
								".$mdb2->quote($rem_set[0][1], 'Text').",
								".$mdb2->quote($rem_set[0][2], 'Text').",
								".$mdb2->quote($rem_set[1][0], 'Text').",
								".$mdb2->quote($rem_set[1][1], 'Text').",
								".$mdb2->quote($rem_set[1][2], 'Text').",
								".$mdb2->quote($rem_set[2][0], 'Text').",
								".$mdb2->quote($rem_set[2][1], 'Text').",
								".$mdb2->quote($rem_set[2][2], 'Text').",
								".$mdb2->quote($rem_set[3][0], 'Text').",
								".$mdb2->quote($rem_set[3][1], 'Text').",
								".$mdb2->quote($rem_set[3][2], 'Text').",
								".$mdb2->quote($rem_set[4][0], 'Text').",
								".$mdb2->quote($rem_set[4][1], 'Text').",
								".$mdb2->quote($rem_set[4][2], 'Text').",
								".$mdb2->quote($rem_set[5][0], 'Text').",
								".$mdb2->quote($rem_set[5][1], 'Text').",
								".$mdb2->quote($rem_set[5][2], 'Text').",
								".$mdb2->quote($rem_set[6][0], 'Text').",
								".$mdb2->quote($rem_set[6][1], 'Text').",
								".$mdb2->quote($rem_set[6][2], 'Text').",
								".$mdb2->quote($rem_set[7][0], 'Text').",
								".$mdb2->quote($rem_set[7][1], 'Text').",
								".$mdb2->quote($rem_set[7][2], 'Text').",
								".$mdb2->quote($rem_set[8][0], 'Text').",
								".$mdb2->quote($rem_set[8][1], 'Text').",
								".$mdb2->quote($rem_set[8][2], 'Text').",
								".$mdb2->quote($rem_set[9][0], 'Text').",
								".$mdb2->quote($rem_set[9][1], 'Text').",
								".$mdb2->quote($rem_set[9][2], 'Text').")");

				} else {

					for ($i=0; $i<=9; $i++) {

						$item[0] = 'rem' . $i . '0';
						$item[1] = 'rem' . $i . '1';
						$item[2] = 'rem' . $i . '2';
						$rem_tmp[0] = $rem_set[$i][0];
						$rem_tmp[1] = $rem_set[$i][1];
						$rem_tmp[2] = $rem_set[$i][2];

						// トランザクションブロック開始
						$res = $mdb2->beginTransaction();

						$res = $mdb2->exec("UPDATE remarks SET $item[0]='$rem_tmp[0]' WHERE order_id='$order_id'");
						$res = $mdb2->exec("UPDATE remarks SET $item[1]='$rem_tmp[1]' WHERE order_id='$order_id'");
						$res = $mdb2->exec("UPDATE remarks SET $item[2]='$rem_tmp[2]' WHERE order_id='$order_id'");

						// トランザクションブロック終了
						$res = transaction_end($mdb2, $res);

					}

				}
				*/

				// オーダーIDで検索してレコードが無ければ追加登録、有れば更新(2008-08-18 修正)
				$res = $mdb2->queryRow("SELECT * FROM remarks2 WHERE order_id='$order_id'");
				if (PEAR::isError($res)) {
					unset($order_tmp);
				} else {
					$order_tmp = $res[0];
				}

				if ($order_tmp==NULL) {

					for ($i=0; $i<=9; $i++) {
						$res = $mdb2->exec("INSERT INTO remarks2(order_id, rem_index, rem_item, rem_content, rem_qty) VALUES(
											".$mdb2->quote($order_id, 'Integer').",
											".$mdb2->quote($i, 'Integer').",
											".$mdb2->quote($rem_set[$i][0], 'Text').",
											".$mdb2->quote($rem_set[$i][1], 'Text').",
											".$mdb2->quote($rem_set[$i][2], 'Text').")");
					}

				} else {

					// トランザクションブロック開始
					$res = $mdb2->beginTransaction();

					for ($i=0; $i<=9; $i++) {
						$rem_item    = $rem_set[$i][0];
						$rem_content = $rem_set[$i][1];
						$rem_qty     = $rem_set[$i][2];

						$res = $mdb2->exec("UPDATE remarks2 SET rem_item='$rem_item' WHERE rem_index='$i' AND order_id='$order_id'");
						$res = $mdb2->exec("UPDATE remarks2 SET rem_content='$rem_content' WHERE rem_index='$i' AND order_id='$order_id'");
						$res = $mdb2->exec("UPDATE remarks2 SET rem_qty='$rem_qty' WHERE rem_index='$i' AND order_id='$order_id'");
					}

					// トランザクションブロック終了
					$res = transaction_end($mdb2, $res);

				}

			}

			// [planning] 更新
			// 2007/02/09 裏面もSMT修正完とする為オーダーIDで更新

			// トランザクションブロック開始
			$res = $mdb2->beginTransaction();

			if ($fix_comp==1) {

//				$res = $mdb2->exec("UPDATE planning SET qty30='$qty30' WHERE plan_id='$plan_id'");
//				$res = $mdb2->exec("UPDATE planning SET smt_fix='$smt_fix' WHERE plan_id='$plan_id'");
				$res = $mdb2->exec("UPDATE planning SET smt_fix='$smt_fix' WHERE order_id='$order_id'");

				// 2007/06/14 追加
				$res = $mdb2->exec("UPDATE order_info SET status=2 WHERE order_id='$order_id'");

			} elseif ($fix_comp==0) {

//				$res = $mdb2->exec("UPDATE planning SET qty30=NULL WHERE plan_id='$plan_id'");
//				$res = $mdb2->exec("UPDATE planning SET smt_fix=NULL WHERE plan_id='$plan_id'");
				$res = $mdb2->exec("UPDATE planning SET smt_fix=NULL WHERE order_id='$order_id'");

				// 2007/06/14 追加
				$res = $mdb2->exec("UPDATE order_info SET status=1 WHERE order_id='$order_id'");

			}

			// トランザクションブロック終了
			$res = transaction_end($mdb2, $res);

		}
	}

	if ($_SESSION["mfd"]!=NULL) {
		$mfd = $_SESSION["mfd"];
		//$machine = $_SESSION["machine"];
		$mc1 = $_SESSION["mc1"];
	} else {
		// 検索する条件の取得
		$yyyy = (int)$_POST['mfd']["Y"];
		$mm   = (int)$_POST['mfd']["m"];
		$dd   = (int)$_POST['mfd']["d"];

		// UNIXタイムスタンプへ変換 指定日&翌日
		$mfd_tmp = mktime(0, 0, 0, $mm, $dd, $yyyy);

		// UNIXタイムスタンプ(秒) -> 通常の日付表示
		$mfd = date('Y-m-d', $mfd_tmp);

		//$machine = $_POST['machine'];
		$mc1 = $_POST['mc1'];
	}

	if ($mc1!=5 and $mc1!=6) {

		// 2008-08-29 スケジュールのバー表示(コメントアウト)
//		include 'inc/schedule1_graph_inc.php';

	}

	// 検索条件によるSQL作成
	$sql_select = "SELECT * FROM planning T3 JOIN order_info T2 ON(T3.order_id=T2.order_id) JOIN product_smt T1 ON(T2.product_id=T1.product_id)";

	if ($mc1==0 or $mc1==NULL) {
		$sql_where = " WHERE mfd='$mfd' AND machine IN (0, 1, 2, 3, 4)";
		// 2007/12/11 ソート条件追加
//		$sql_order = " ORDER BY machine, mf_order, operate_no, dash_no, ass_side";
		$sql_order = " ORDER BY machine, mf_order, operate_no, dash_no, product, ass_side";
	} elseif ($mc1==5) {
		$sql_where = " WHERE smt_mc IS NOT NULL AND smt_fix IS NULL";
		$sql_order = " ORDER BY smt_mc, operate_no, dash_no, ass_side";
	} elseif ($mc1==6) {
		// 2006-12-07 欠品有りでSMT修正完品対応
		// 2007-03-16 5項目まで欠品状況を確認
		// 2008-02-13 WHERE句の書式修正 text型に対してinteger型で検索しようとしていた
		// 2008-08-28 [remarks2]対応
//		$sql_select_tmp = " LEFT OUTER JOIN remarks T4 ON(T4.order_id=T2.order_id)";
//		$sql_select = $sql_select . $sql_select_tmp;
//		$sql_where = " WHERE rem00='1' or rem10='1' or rem20='1' or rem30='1' or rem40='1' or rem50='1' or rem60='1' or rem70='1' or rem80='1' or rem90='1'";
//		$sql_order = " ORDER BY operate_no, dash_no";

		$sql_select_tmp = " LEFT OUTER JOIN remarks2 T4 ON(T4.order_id=T2.order_id)";
		$sql_select = $sql_select . $sql_select_tmp;
//		$sql_where = " WHERE (rem_index BETWEEN '0' AND '9') AND rem_item='1'";
		$sql_where = " WHERE rem_item='1'";
		$sql_order = " ORDER BY plan_id, smt_fix, operate_no, dash_no, side";

	} else {
		// 2007/12/20 ソート条件修正(機種を追加)
		$sql_where = " WHERE mfd='$mfd' AND machine='$mc1'";
		$sql_order = " ORDER BY machine, mf_order, operate_no, dash_no, product, ass_side";
	}

	$search_sql = $sql_select . $sql_where . $sql_order;

	// [planning],[order_info],[product_smt]を結合して検索条件で検索
	$res_query = $mdb2->query($search_sql);
	$res_query = err_check($res_query);

	if ($mc1!=5 and $mc1!=6) {

		// 1号機-3号機、手付け品一覧表示
		include 'inc/schedule1_inc1.php';

	} elseif ($mc1==5) {

		// SMT修正(通常)一覧表示
		include 'inc/schedule1_inc2.php';

	} elseif ($mc1==6) {

		// SMT修正(欠品取付)一覧表示
		include 'inc/schedule1_inc3.php';

	}

	// DB切断
	db_disconnect($mdb2);

	break;


case "edit":

	$plan_id = $_SESSION["eid"];
	$status  = $_SESSION["status"];

	// DB接続
	$mdb2 = db_connect();

	if ($plan_id!=NULL) {

		// [planning]から行取得
		$plan_row = row_plan($mdb2, $plan_id);
			$order_id = $plan_row[1];
			$ass_side = $plan_row[2];
			$qty20    = $plan_row[3];
			$qty21    = $plan_row[4];
			$mfd      = $plan_row[5];
//			$machine  = $plan_row[6];
			$mf_order = $plan_row[7];
			$priority = $plan_row[8];
			$qty30    = $plan_row[13];

			// 年月日の分解
			list($yyyy1, $mm1, $dd1) = split('[/.-]', $mfd);

			if ($ass_side==2) {
				$ass_str = '表面';
			} elseif ($ass_side==1) {
				$ass_str = '裏面';
			}

		// [order_info]から行取得
		$order_row = row_order($mdb2, $order_id);
			$product_id = $order_row[1];
			$operate_no = $order_row[2];
			$dash_no    = $order_row[3];
			$qty10      = $order_row[4];
			$ass        = $order_row[10];

		// [product_smt]から行取得
		$product_row = row_product_smt($mdb2, $product_id);
			$eqp     = $product_row[2];
			$product = $product_row[3];
			$mc      = $product_row[4];
			$solder  = $product_row[6];

		// [remarks]から行取得(2008-08-18 修正)
//		$rem_row = row_rem($mdb2, $order_id);
//			for ($i=0; $i<=9; $i++) {
//				$rem_set[$i][0] = $rem_row[2+($i*3)];
//				$rem_set[$i][1] = $rem_row[3+($i*3)];
//				$rem_set[$i][2] = $rem_row[4+($i*3)];
//			}
		$rem_set = row_rem2($mdb2, $order_id);

		if ($qty30!=NULL) {
			$qty_tmp = $qty30;
		} else {
			$qty_tmp = $qty20;
		}

		if ($status=='st') {

			$form = new HTML_QuickForm("edit", "POST");
			$form -> addElement("header", "title", "生産実績(SMT実装開始)登録:");

			$ope =& HTML_QuickForm::createElement('text', '', null, array('size'=>10, 'maxlength'=>10));
			$dash =& HTML_QuickForm::createElement('text', '', null, array('size'=>10, 'maxlength'=>10));
			$form -> addGroup(array($ope, $dash), 'operate_no', '工番:', ' ダッシュ:');

			$form -> addElement("text", "eqp", "装置名:", array('size'=>40, 'maxlength'=>100));
			$form -> addElement("text", "product", "品名:", array('size'=>40, 'maxlength'=>100));

			$form -> addElement("text", "qty10", "数量:", array('size'=>10, 'maxlength'=>4));
			$form -> addElement("text", "ass_side", "面:", array('size'=>10));
			$form -> addElement("text", "qty30", "生産数:", array('size'=>10, 'maxlength'=>4));
			$form -> addElement("text", "date_start", "生産開始(日):", array('size'=>15));
			$form -> addElement("text", "time_start", "生産開始(時間):", array('size'=>15));
			$mc_start[] = $form -> createElement("radio", "mc_start", NULL, "実装開始登録", 1);
			$mc_start[] = $form -> createElement("radio", "mc_start", NULL, "実装開始削除", 0);
			$form -> addGroup($mc_start, "mc_start", "データ登録:");

			// 基礎データの設定
			$form -> setDefaults(array("operate_no[0]" =>$operate_no,
										"operate_no[1]"=>$dash_no,
										"eqp"          =>$eqp,
										"product"      =>$product,
										"qty10"        =>$qty10,
										"ass_side"     =>$ass_str,
										"qty30"        =>$qty_tmp,
										"mc_start"     =>array("mc_start"=>1),
										"date_start"   =>date("Y-m-d"), time(),
										"time_start"   =>date("H:i:s"), time()));

		} elseif ($status=='mc') {

			$form = new HTML_QuickForm("edit", "POST");
			$form -> addElement("header", "title", "生産実績(SMT実装完)登録:");

			$ope =& HTML_QuickForm::createElement('text', '', null, array('size'=>10, 'maxlength'=>10));
			$dash =& HTML_QuickForm::createElement('text', '', null, array('size'=>10, 'maxlength'=>10));
			$form -> addGroup(array($ope, $dash), 'operate_no', '工番:', ' ダッシュ:');

			$form -> addElement("text", "eqp", "装置名:", array('size'=>40, 'maxlength'=>100));
			$form -> addElement("text", "product", "品名:", array('size'=>40, 'maxlength'=>100));

			$form -> addElement("text", "qty10", "数量:", array('size'=>10, 'maxlength'=>4));
			$form -> addElement("text", "ass_side", "面:", array('size'=>10));
			$form -> addElement("text", "qty30", "生産数:", array('size'=>10, 'maxlength'=>4));
			$form -> addElement("text", "date_mc", "実装完(日):", array('size'=>15));
			$form -> addElement("text", "time_mc", "実装完(時間):", array('size'=>15));
			$mc_comp[] = $form -> createElement("radio", "mc_comp", NULL, "実装完登録", 1);
			$mc_comp[] = $form -> createElement("radio", "mc_comp", NULL, "実装完削除", 0);
			$form -> addGroup($mc_comp, "mc_comp", "データ登録:");

			// 基礎データの設定
			$form -> setDefaults(array("operate_no[0]" =>$operate_no,
										"operate_no[1]"=>$dash_no,
										"eqp"          =>$eqp,
										"product"      =>$product,
										"qty10"        =>$qty10,
										"ass_side"     =>$ass_str,
										"qty30"        =>$qty_tmp,
										"mc_comp"      =>array("mc_comp"=>1),
										"date_mc"      =>date("Y-m-d"), time(),
										"time_mc"      =>date("H:i:s"), time()));

		} elseif ($status=='fix') {

			$form = new HTML_QuickForm("edit", "POST");
			$form -> addElement("header", "title", "生産実績(SMT修正)登録:");

			$ope =& HTML_QuickForm::createElement('text', '', null, array('size'=>10, 'maxlength'=>10));
			$dash =& HTML_QuickForm::createElement('text', '', null, array('size'=>10, 'maxlength'=>10));
			$form -> addGroup(array($ope, $dash), 'operate_no', '工番:', ' ダッシュ:');

			$form -> addElement("text", "eqp", "装置名:", array('size'=>40, 'maxlength'=>100));
			$form -> addElement("text", "product", "品名:", array('size'=>40, 'maxlength'=>100));

			$form -> addElement("text", "qty10", "数量:", array('size'=>10, 'maxlength'=>4));
			$form -> addElement("text", "ass_side", "面:", array('size'=>10));
			$form -> addElement("text", "qty30", "生産数:", array('size'=>10, 'maxlength'=>4));
			$form -> addElement("text", "date_fix", "修正完(日):", array('size'=>15));
			$form -> addElement("text", "time_fix", "修正完(時間):", array('size'=>15));

			// 配列でうまく処理できず
//			$rem[0] =& HTML_QuickForm::createElement('select', '', null, $sel_rem);
//			$rem[1] =& HTML_QuickForm::createElement('text', '', null, array('size' => 50, 'maxlength' => 128));
//			$rem[2] =& HTML_QuickForm::createElement('text', '', null, array('size' => 5, 'maxlength' => 5));
//			$form -> addGroup(array($rem[0], $rem[1], $rem[2]),'remarks[0]', '備考1：', '：', '：');
//			$form -> addGroup(array($rem[0], $rem[1], $rem[2]),'remarks[1]', '備考2：', '：', '：');
//			$form -> addGroup(array($rem[0], $rem[1], $rem[2]),'remarks[2]', '備考3：', '：', '：');
//			$form -> addGroup(array($rem[0], $rem[1], $rem[2]),'remarks[3]', '備考4：', '：', '：');
//			$form -> addGroup(array($rem[0], $rem[1], $rem[2]),'remarks[4]', '備考5：', '：', '：');
//			$form -> addGroup(array($rem[0], $rem[1], $rem[2]),'remarks[5]', '備考6：', '：', '：');
//			$form -> addGroup(array($rem[0], $rem[1], $rem[2]),'remarks[6]', '備考7：', '：', '：');
//			$form -> addGroup(array($rem[0], $rem[1], $rem[2]),'remarks[7]', '備考8：', '：', '：');
//			$form -> addGroup(array($rem[0], $rem[1], $rem[2]),'remarks[8]', '備考9：', '：', '：');
//			$form -> addGroup(array($rem[0], $rem[1], $rem[2]),'remarks[9]', '備考10：', '：', '：');

			// 間抜けな処理だがとりあえず
			$rem[0] =& HTML_QuickForm::createElement('select', '', null, $sel_rem);
			$rem[1] =& HTML_QuickForm::createElement('text', '', null, array('size'=>50, 'maxlength'=>128));
			$rem[2] =& HTML_QuickForm::createElement('text', '', null, array('size'=>5, 'maxlength'=>5));
			$form -> addGroup(array($rem[0], $rem[1], $rem[2]),'remarks[0]', '備考1：', '：', '：');

			$rem[3] =& HTML_QuickForm::createElement('select', '', null, $sel_rem);
			$rem[4] =& HTML_QuickForm::createElement('text', '', null, array('size'=>50, 'maxlength'=>128));
			$rem[5] =& HTML_QuickForm::createElement('text', '', null, array('size'=>5, 'maxlength'=>5));
			$form -> addGroup(array($rem[3], $rem[4], $rem[5]),'remarks[1]', '備考2：', '：', '：');

			$rem[6] =& HTML_QuickForm::createElement('select', '', null, $sel_rem);
			$rem[7] =& HTML_QuickForm::createElement('text', '', null, array('size'=>50, 'maxlength'=>128));
			$rem[8] =& HTML_QuickForm::createElement('text', '', null, array('size'=>5, 'maxlength'=>5));
			$form -> addGroup(array($rem[6], $rem[7], $rem[8]),'remarks[2]', '備考3：', '：', '：');

			$rem[9] =& HTML_QuickForm::createElement('select', '', null, $sel_rem);
			$rem[10] =& HTML_QuickForm::createElement('text', '', null, array('size'=>50, 'maxlength'=>128));
			$rem[11] =& HTML_QuickForm::createElement('text', '', null, array('size'=>5, 'maxlength'=>5));
			$form -> addGroup(array($rem[9], $rem[10], $rem[11]),'remarks[3]', '備考4：', '：', '：');

			$rem[12] =& HTML_QuickForm::createElement('select', '', null, $sel_rem);
			$rem[13] =& HTML_QuickForm::createElement('text', '', null, array('size'=>50, 'maxlength'=>128));
			$rem[14] =& HTML_QuickForm::createElement('text', '', null, array('size'=>5, 'maxlength'=>5));
			$form -> addGroup(array($rem[12], $rem[13], $rem[14]),'remarks[4]', '備考5：', '：', '：');

			$rem[15] =& HTML_QuickForm::createElement('select', '', null, $sel_rem);
			$rem[16] =& HTML_QuickForm::createElement('text', '', null, array('size'=>50, 'maxlength'=>128));
			$rem[17] =& HTML_QuickForm::createElement('text', '', null, array('size'=>5, 'maxlength'=>5));
			$form -> addGroup(array($rem[15], $rem[16], $rem[17]),'remarks[5]', '備考6：', '：', '：');

			$rem[18] =& HTML_QuickForm::createElement('select', '', null, $sel_rem);
			$rem[19] =& HTML_QuickForm::createElement('text', '', null, array('size'=>50, 'maxlength'=>128));
			$rem[20] =& HTML_QuickForm::createElement('text', '', null, array('size'=>5, 'maxlength'=>5));
			$form -> addGroup(array($rem[18], $rem[19], $rem[20]),'remarks[6]', '備考7：', '：', '：');

			$rem[21] =& HTML_QuickForm::createElement('select', '', null, $sel_rem);
			$rem[22] =& HTML_QuickForm::createElement('text', '', null, array('size'=>50, 'maxlength'=>128));
			$rem[23] =& HTML_QuickForm::createElement('text', '', null, array('size'=>5, 'maxlength'=>5));
			$form -> addGroup(array($rem[21], $rem[22], $rem[23]),'remarks[7]', '備考8：', '：', '：');

			$rem[24] =& HTML_QuickForm::createElement('select', '', null, $sel_rem);
			$rem[25] =& HTML_QuickForm::createElement('text', '', null, array('size'=>50, 'maxlength'=>128));
			$rem[26] =& HTML_QuickForm::createElement('text', '', null, array('size'=>5, 'maxlength'=>5));
			$form -> addGroup(array($rem[24], $rem[25], $rem[26]),'remarks[8]', '備考9：', '：', '：');

			$rem[27] =& HTML_QuickForm::createElement('select', '', null, $sel_rem);
			$rem[28] =& HTML_QuickForm::createElement('text', '', null, array('size'=>50, 'maxlength'=>128));
			$rem[29] =& HTML_QuickForm::createElement('text', '', null, array('size'=>5, 'maxlength'=>5));
			$form -> addGroup(array($rem[27], $rem[28], $rem[29]),'remarks[9]', '備考10：', '：', '：');

			$fix_comp[] = $form -> createElement("radio", "fix_comp", NULL, "修正完登録", 1);
			$fix_comp[] = $form -> createElement("radio", "fix_comp", NULL, "修正完削除", 0);
			$form -> addGroup($fix_comp, "fix_comp", "データ登録:");

			// 基礎データの設定
			// 2006/10/26 [order_info]から備考を読んで表示
			$form -> setDefaults(array("operate_no[0]" =>$operate_no,
										"operate_no[1]"=>$dash_no,
										"eqp"          =>$eqp,
										"product"      =>$product,
										"qty10"        =>$qty10,
										"ass_side"     =>$ass_str,
										"qty30"        =>$qty_tmp,
										"remarks"      =>$rem_set,
										"fix_comp"     =>array("fix_comp"=>1),
										"date_fix"     =>date("Y-m-d"), time(),
										"time_fix"     =>date("H:i:s"), time()));

		}

		$form -> addElement("submit", "send", "データベース登録");

		// 2007/11/21 検証ルールの追加
//		$form -> addRule("qty10", "数量を入力して下さい。", "required", "", "client");
//		$form -> addRule("qty10", "数量が数字ではありません", "numeric", "", "client");
		$form -> addRule("qty30", "生産数を入力して下さい。", "required", "", "client");
		$form -> addRule("qty30", "生産数が数字ではありません", "numeric", "", "client");

		HTML_QuickForm::registerRule("set_date", "regex", "/[0-9]{4}\-[0-9]{2}\-[0-9]{2}/");
		HTML_QuickForm::registerRule("set_time", "regex", "/[0-9]{2}\:[0-9]{2}\:[0-9]{2}/");

		$form->addRule("date_start", "生産開始(日)の書式が違います", "set_date", NULL, "client");
		$form->addRule("time_start", "生産開始(時間)の書式が違います", "set_time", NULL, "client");

		$form->addRule("date_mc", "実装完(日)の書式が違います", "set_date", NULL, "client");
		$form->addRule("time_mc", "実装完(時間)の書式が違います", "set_time", NULL, "client");

		$form->addRule("date_fix", "修正完(日)の書式が違います", "set_date", NULL, "client");
		$form->addRule("time_fix", "修正完(時間)の書式が違います", "set_time", NULL, "client");

		if ($form -> validate()) {
			$form -> process("showForm",FALSE);
		} else {
			$form -> setRequiredNote("<font color='Red'>*</font> 必須項目です");
			$form -> setJsWarnings("以下の項目でエラーが発生しました",
				"エラー項目を修正して、再度［データベース登録］ボタンをクリックしてください");
			$form->display();
		}

		// 2007/11/21 書式コメント追加
		print('<table border=1 width=600 align=left>');
		print('<caption>');
		print('<div align="center"><font size="3" color="#0066cc"><b>');
		print('【 入力書式（すべて半角の数字と記号[-と:]です）】');
		print('</b></font></div>');
		print('</caption>');
		print('<tr align=center bgcolor="#cccccc">');
		print('<th><br></th>');
		print('<th>書式</th>');
		print('<th>入力例</th>');
		print('<th>備考</th>');
		print('</tr>');
		print('<tr align=left>');
		print('<td>数量</td>');
		print('<td>"0" から "9999"</td>');
		print('<td>10</td>');
		print('<td>SMT実装完時の生産数が生産実績数量です</td>');
		print('</tr>');
		print('<tr align=left>');
		print('<td>日付</td>');
		print('<td>YYYY-MM-DD</td>');
		print('<td>2007-11-11</td>');
		print('<td><br></td>');
		print('</tr>');
		print('<tr align=left>');
		print('<td>時間</td>');
		print('<td>24時間制で HH:MM:SS</td>');
		print('<td>13:00:00</td>');
		print('<td><br></td>');
		print('</tr>');
 		print('</table>');

 		$_SESSION["id"]             = $plan_id;
		$_SESSION["mfd"]            = $mfd;
//		$_SESSION["machine"]        = $machine;
		$_SESSION["status"]         = $status;
		$_SESSION["mode_schedule1"] = "disp";

	} else {

		// セッション破棄
		unset_session();

	}

	// DB切断
	db_disconnect($mdb2);

	break;

}

?>

</body>
</html>
