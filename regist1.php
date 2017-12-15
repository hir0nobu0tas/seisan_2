<?php
// セッション開始
session_start();
if ($_GET['mode_regist1']=='select') {

	$mode = $_GET["mode_regist1"];
	$sel_item = 1;

} elseif ($_SESSION["mode_regist1"]!=NULL) {

	$mode = $_SESSION["mode_regist1"];

	if ($_SESSION["sel_item"]!=NULL) {
		$sel_item = $_SESSION["sel_item"];
	} else {
		$sel_item = 1;
	}

} else {

	$mode = $_GET["mode_regist1"];

}
?>

<!doctype html public "-//w3c//dtd html 4.01 Transitional//en">
<html>
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8">
	<title>生産管理システム</title>
	<link rel="stylesheet" type="text/css" href="main.css">
</head>

<body>

<!--
[生産管理システム]

 * テキストデータ 一括登録

  2007/04/

  2007/06/07
  PEAR::DB -> PEAR::MDB2 など

  2007/06/
  旧棚番、新棚番登録追加
  今後のマスターデータ及びラベルデータの生成用

  2007/07/03
  月単位のSMT部品使用数の集計処理のお手伝い処理？

  2007/07/12
  元々のマシンデータの一括登録処理へ戻す
  事前に荷姿を半角カナから英数字へ一括変換していたが変換による文字数の違いで
  数量データが読めない場合があった 事前に仮に英字に変換しておきシステムで
  読込む時に再変換を行う 半角カナ -> 英字 -> 全角カナ

  2007/07/26
  月単位のSMT部品使用数の集計処理 前回の残骸から次回も使える程度に復活

  2007/08/20
  1ステーションの部品登録数の最大値は40という事だったが実際には70近い物もある
  最大値を100とする

  2007/08/23
  現在のセットアップシートの手書き棚番を入力した更新用データの登録処理 実装開始

  2007/09/25
  棚番データの更新、追加処理 実装開始

  2007/10/02
  スタイルシートで背景変更

  2007/10/09
  リール管理番号データ登録 追加

  2007/11/09
  ストック部品確認データ登録は別メニューへ

  2008/03/13
  MW500出荷履歴管理は別プロジェクトへ分けたのでデータディレクトリ修正

  2008/03/24
  ディレクトリ修正

  2008/09/12
  登録項目の整理 現状では基本的に部品リール管理番号のみ登録している

  2009/02/06
  CBE品名のインポート処理追加(仮) 1回使用してコメントアウト
  (頻繁に使用する機能ではない データの更新時に使用)

  2009/02/09
  セットアップデータの更新処理 検討中

  2009/02/10
  セットアップデータの更新処理(ユニットデータの更新処理はコメントアウト)
  ユニットの登録時に[unit_id]の検索を行い登録済みであれば該当する他のデータを
  一旦削除([unit_id]は保持)後、再登録を行なう
  [unit_data]の基板ID、プログラム名等が更新された場合は別ユニットが追加された
  として通常通りの追加処理を行なう

  2009/03/06
  セットアップデータの更新ファイルによるアップデート(現場見直しによる棚番指定など)
  実装を途中で放置していたが復活

  2009/03/18
  アドバンス向け棚番テーブル[part_advance]追加とその登録処理(登録処理は1回のみ実施)
  棚番データが更新された場合は再登録

  2009/07/08
  アドバンス棚番テーブル[part_advance]のテーブル定義変更に対応

  2009/08/26
  生産時の注意事項 一括登録 追加中

  2010/07/27
  session_start()を先頭に移動

  2010/09/21
  dataディレクトリ修正

  2011/06/22
  SMT部品の使用履歴登録 実装開始

  2013/02/12
  HLC VerUpによるデータフォーマット変更に対応(プログラム名などの位置が変更)

  2015/10/26
  新棚番対応

  2016/04/14
  データ登録のファイル選択ボックスのズレ修正

  2017/07/13
  文字コードをUTF-8へ

-->

<?php
//---------------------------------------------------------------
// 初期設定
//---------------------------------------------------------------

// 共有関数
include_once 'inc/parameter_inc.php';
include_once 'lib/com_func1.php';
include_once 'lib/com_func2.php';

// PEARライブラリ
include_once 'HTML/QuickForm.php';
include_once 'MDB2.php';

$sel_regist = array(0=>"-Select-",
					1=>"ユニットデータ登録",
					2=>"数量データ登録",
					3=>"ユニットデータ更新",
					4=>"SMT部品リール管理番号 登録",
					5=>"SMTストック部品 在庫データ登録",
					6=>"棚番データ登録",
					7=>"SMT部品 入出庫履歴 登録",
					8=>"生産時の注意事項 登録");

//---------------------------------------------------------------

// セッション破棄
function unset_session() {
	unset($_SESSION["mode_regist1"]);
}


// セッション破棄
unset($_SESSION["mode_regist1"]);

switch($mode) {
case "select":

	print('<br>');

	// セッション破棄
	unset_session();

	$dir[0] = "../data/setup/unit";
	$dir[1] = "../data/setup/qty";
	$dir[2] = "../data/setup/update";
	$dir[3] = "../data/reel";
	$dir[4] = "../data/stock/qty";
	$dir[5] = "../data/rack";
	$dir[6] = "../data/record";
	$dir[7] = "../data/notes";

	// ファイル一覧の分解(ファイル名のみとフルパス名)
	for ($i=0; $i<=7; $i++) {

		// 指定ディレクトリのファイル一覧
		$file[$i] = getdirtree($dir[$i]);
//		$file[$i] = getdirlist($dir[$i], FALSE);
//		$file[$i] = findFiles($dir[$i]);

//print('<pre>');
//Var_Dump($file);
//print('</pre>');

		// ファイル数
		$cnt[$i] = count($file[$i]);

		if ($cnt[$i]!=NULL) {
			for ($k=0; $k<$cnt[$i]; $k++) {
				list($txt[$i][$k], $txt_full[$i][$k]) = each($file[$i]);
			}
		}
	}

	$form = new HTML_QuickForm("select", "POST");
	$form -> addElement("header", "title", "登録ファイル選択:");
	$form -> addElement("select", "sel_regist", "登録モード:", $sel_regist);
	$form -> addElement("static", "br", "");
	$form -> addElement("static", "br", "");
	$form -> addElement("static", "br", "");

	$form -> addElement("header", "title", "マシンデータ選択:");
	$form -> addElement("static", "br", "");
	$form -> addElement("static", "br", "");
	$form -> addElement("select", "txt_unit", "ユニットデータ:", $txt[0]);
	$form -> addElement("static", "br", "");
	$form -> addElement("static", "br", "");
	$form -> addElement("select", "txt_qty", "数量データ:", $txt[1]);
	$form -> addElement("static", "br", "");
	$form -> addElement("static", "br", "");
	$form -> addElement("select", "txt_update", "更新(ユニット):", $txt[2]);
	$form -> addElement("static", "br", "");
	$form -> addElement("static", "br", "");
	$form -> addElement("static", "br", "");

	$form -> addElement("header", "title", "リール管理番号データ選択:");
	$form -> addElement("static", "br", "");
	$form -> addElement("static", "br", "");
	$form -> addElement("select", "txt_reel_no", "管理番号データ:", $txt[3]);
	$form -> addElement("static", "br", "");
	$form -> addElement("static", "br", "");
	$form -> addElement("static", "br", "");

	$form -> addElement("header", "title", "SMT在庫データ選択:");
	$form -> addElement("static", "br", "");
	$form -> addElement("static", "br", "");
	$form -> addElement("select", "txt_stock", "在庫データ:", $txt[4]);
	$form -> addElement("static", "br", "");
	$form -> addElement("static", "br", "");
	$form -> addElement("static", "br", "");

	$form -> addElement("header", "title", "棚番データ選択:");
	$form -> addElement("static", "br", "");
	$form -> addElement("static", "br", "");
	$form -> addElement("select", "txt_rack", "棚番データ:", $txt[5]);
	$form -> addElement("static", "br", "");
	$form -> addElement("static", "br", "");
	$form -> addElement("static", "br", "");

	$form -> addElement("header", "title", "入出庫履歴データ選択:");
	$form -> addElement("static", "br", "");
	$form -> addElement("static", "br", "");
	$form -> addElement("select", "txt_record", "入出庫履歴データ:", $txt[6]);
	$form -> addElement("static", "br", "");
	$form -> addElement("static", "br", "");
	$form -> addElement("static", "br", "");

	$form -> addElement("header", "title", "生産時の注意事項データ選択:");
	$form -> addElement("static", "br", "");
	$form -> addElement("static", "br", "");
	$form -> addElement("select", "txt_notes", "生産時の注意事項:", $txt[7]);
	$form -> addElement("static", "br", "");
	$form -> addElement("static", "br", "");
	$form -> addElement("static", "br", "");

	// 初期値設定
//	$form -> setDefaults(array(	"sel_regist"=>array("sel_regist"=>$sel_item)));
	$form -> setDefaults(array(	"sel_regist"=>array("sel_regist"=>4)));

	$form -> addElement("static", "br", "");
	$form -> addElement("static", "br", "");
	$form -> addElement("submit", "send", "登録開始");
	$form->display();

	$_SESSION["mode_regist1"] = 'regist';
	$_SESSION["txt"] = $txt;
	$_SESSION["txt_full"] = $txt_full;

	break;


case "regist":

	// DB接続
	$mdb2 = db_connect();

	// 作業用一時テーブル作成
	$res = $mdb2->exec("CREATE TEMP TABLE data_tmp (
						tmp_id SERIAL,
						data0 TEXT,
						data1 TEXT,
						data2 TEXT,
						data3 TEXT,
						data4 TEXT,
						data5 TEXT,
						data6 TEXT,
						data7 TEXT,
						data8 TEXT,
						data9 TEXT,
						data10 TEXT,
						data11 TEXT,
						data12 TEXT,
						CONSTRAINT data_tmp_pkey PRIMARY KEY (tmp_id))");

	// ファイル名取得
	$sel_item = $_POST['sel_regist'];
	$_SESSION["sel_item"] = $sel_item;

	switch ($sel_item) {
	case 0:
		break;

	case 1:
		$txt_full = $_SESSION['txt_full'][0];
		$cnt_txt  = count($txt_full) - 1;
		$txt      = $_SESSION['txt'][0];
//		$txt_file = $txt[$_POST['txt_unit']];
		break;

	case 2:
		$txt_full = $_SESSION['txt_full'][1];
		$cnt_txt  = count($txt_full) - 1;
		$txt      = $_SESSION['txt'][1];
//		$txt_file = $txt[$_POST['txt_qty']];
		break;

	case 3:
		$txt_full = $_SESSION['txt_full'][2];
		$cnt_txt  = count($txt_full) - 1;
		$txt      = $_SESSION['txt'][2];
		break;

	case 4:
		$txt_full = $_SESSION['txt_full'][3];
		$cnt_txt  = count($txt_full) - 1;
		$txt      = $_SESSION['txt'][3];
		$txt_file = $txt[$_POST['txt_reel_no']];
		break;

	case 5:
		$txt_full = $_SESSION['txt_full'][4];
		$cnt_txt  = count($txt_full) - 1;
		$txt      = $_SESSION['txt'][4];
		$txt_file = $txt[$_POST['txt_stock']];
		break;

	case 6:
		$txt_full = $_SESSION['txt_full'][5];
		$cnt_txt  = count($txt_full) - 1;
		$txt      = $_SESSION['txt'][5];
		$txt_file = $txt[$_POST['txt_rack']];
		break;

	case 7:
		$txt_full = $_SESSION['txt_full'][6];
		$cnt_txt  = count($txt_full) - 1;
		$txt      = $_SESSION['txt'][6];
		$txt_file = $txt[$_POST['txt_record']];
		break;

	case 8:
		$txt_full = $_SESSION['txt_full'][7];
		$cnt_txt  = count($txt_full) - 1;
		$txt      = $_SESSION['txt'][7];
		$txt_file = $txt[$_POST['txt_notes']];
		break;
	}

//	// txt(shift-jis) -> (euc)へ変換
//  // Windowsでは正常に変換できないがLinuxではOKとの噂 紛らわしいので転送前に
//  // 文字コードの変換などは行ってから転送
//	$buf = mb_convert_encoding(file_get_contents($txt_file), "EUC", "SJIS");
//	$fp = tmpfile();
//	fwrite($fp, $buf);
//	rewind($fp);

	// ファイル名よりunit_index抽出
	// [-**]の書式で2桁取得後、DB側がINT4型なので最初が0の場合は削除して1桁とする
	// 2007/06/20 ファイル名取得 追加(基板IDが無い場合対応)
	if ($sel_item==1 OR $sel_item==2) {

		for ($i=0; $i<=$cnt_txt; $i++) {

//			$file_len = strlen($txt_full[$i]);
//			$unit_index[$i] = substr($txt_full[$i], $file_len - 6, 2);
//			$tmp_index = substr($unit_index[$i], 0, 1);
//
//			if ($tmp_index==0) {
//				$unit_index[$i] = substr($unit_index[$i], 1, 1);
//			}
//
//			$unit_file[$i] = substr($txt_full[$i], 18, $file_len - 22);

			$file_len       = strlen($txt[$i]);
			$unit_index[$i] = substr($txt[$i], $file_len - 6, 2);
			$tmp_index      = substr($unit_index[$i], 0, 1);

			if ($tmp_index==0) {
				$unit_index[$i] = substr($unit_index[$i], 1, 1);
			}

			$unit_file[$i] = substr($txt[$i], 5, $file_len - 9);
		}

	}

	// 変数開放
	unset($file_len);
	unset($tmp_index);

	if ($sel_item==1 OR $sel_item==2) {

		for ($cnt_loop=0; $cnt_loop<=$cnt_txt; $cnt_loop++) {

			// 行単位で配列に格納(行送りなどの為)
			$row = txt2array($txt_full[$cnt_loop]);

			// 行数取得(0から開始なので-1)
			$row_cnt = count($row) - 1;

			switch ($sel_item) {
			case 1:
				// ユニットデータ(TXT)データ抽出 → テーブル格納
				include 'inc/regist1_inc1.php';
				break;

			case 2:
				// 数量データ(TXT)データ抽出 → テーブル格納
				include 'inc/regist1_inc2.php';
				break;

			}

			// 登録ファイル表示
			print('<div align="left"><font size="3" color="#0066cc"><b>');
			print('＊[ ');
			print($txt[$cnt_loop]);
			print(' ] を登録しました');
			print('</b></div>');

			// 配列開放
			unset($row);

		}

	} elseif ($sel_item==3) {

		for ($cnt_loop=0; $cnt_loop<=$cnt_txt; $cnt_loop++) {

			// 行単位で配列に格納(行送りなどの為)
			$row = txt2array($txt_full[$cnt_loop]);

			// 行数取得(0から開始なので-1)
			$row_cnt = count($row) - 1;

			// 更新データ(CSV)データ抽出 → [set_data]テーブル更新
			include 'inc/regist1_inc3.php';

			// 登録ファイル表示
			print('<div align="left"><font size="3" color="#0066cc"><b>');
			print('＊[ ');
			print($txt[$cnt_loop]);
			print(' ] を登録しました');
			print('</b></div>');

			// 配列開放
			unset($row);

		}

	} elseif ($sel_item==4) {

		// リール管理番号データ登録
		include 'inc/regist1_inc4.php';

	} elseif ($sel_item==5) {

		// SMT部品在庫データ登録
		include 'inc/regist1_inc5.php';

	} elseif ($sel_item==6) {

		// 棚番データ登録
		include 'inc/regist1_inc6.php';

	} elseif ($sel_item==7) {

		// 入出庫履歴データ登録
		include 'inc/regist1_inc7.php';

	} elseif ($sel_item==8) {

		// 生産時の注意事項データ登録
		include 'inc/regist1_inc8.php';

	}

	// DB切断
	db_disconnect($mdb2);

	break;

}


?>
</body>
</html>
