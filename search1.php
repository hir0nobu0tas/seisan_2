<?php
// セッション開始
session_start();
if ($_GET['mode_search1']=='search') {

	unset($_SESSION["item"]);
	unset($_SESSION["parameter"]);
	unset($_SESSION["eid"]);
	unset($_SESSION["did"]);

	$mode = $_GET["mode_search1"];
	$permission = $_GET["permission"];

} elseif ($_SESSION["mode_search1"]!=NULL) {

	$mode_tmp = $_GET["mode_search1"];
	if ($mode_tmp!=NULL) {
		$mode = $mode_tmp;
	} else {
		$mode = $_SESSION["mode_search1"];
	}

	if ($_GET['permission']!=NULL) {
		$permission = $_GET["permission"];
	} else {
		$permission = $_SESSION["permission"];
	}

	// 編集するIDと削除するIDを別管理
	// 部品ID(日東S品) 編集
	if ($_SESSION["eid"]==NULL or $_GET["eid"]!=NULL) {
		$eid = $_GET["eid"];
		$_SESSION["eid"] = $eid;
	}

	// 部品ID(日東S品) 削除
	if ($_SESSION["did"]==NULL or $_GET["did"]!=NULL) {
		$did = $_GET["did"];
		$_SESSION["did"] = $did;
	}

	// 部品ID(アドバンス) 編集
	if ($_SESSION["eid_a"]==NULL or $_GET["eid_a"]!=NULL) {
		$eid_a = $_GET["eid_a"];
		$_SESSION["eid_a"] = $eid_a;
	}

	// 部品ID(アドバンス) 削除
	if ($_SESSION["did_a"]==NULL or $_GET["did_a"]!=NULL) {
		$did_a = $_GET["did_a"];
		$_SESSION["did_a"] = $did_a;
	}

	// 在庫ID 編集
	if ($_SESSION["eid_s"]==NULL or $_GET["eid_s"]!=NULL) {
		$eid_s = $_GET["eid_s"];
		$_SESSION["eid_s"] = $eid_s;
	}

} else {

	$mode = $_GET["mode_search1"];
	$permission = $_GET["permission"];

}
?>

<!doctype html public "-//w3c//dtd html 4.01 Transitional//en">
<html>
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8">
	<title>生産管理システム</title>
	<script type="text/javascript" src="lib/colorful.js"></script>
	<link rel="stylesheet" type="text/css" href="main.css">
</head>

<body>

<!--
[生産管理システム]

 * データ検索
  DBに登録されているデータの検索

  2007/04/11

  2007/07/18
  ファイル名 or 基板ID検索でブラウザへの一覧表示をコメントアウトして
  基板ID一覧から直接Excel出力へ変更中

  2007/09/10
  部品検索(棚番)で資材品名、メーカー品名等もLike検索するようにした

  2007/09/25
  登録済み部品一覧の出力 実装開始

  2007/09/26
  [part_smt]テーブル小変更対応

  2007/10/02
  スタイルシートで背景変更

  2007/12/21
  部品検索(棚番)で編集、削除機能を追加

  2008/01/17
  準備作業時にリール管理番号等のデータを収集する為の帳票出力 追加開始

  2008/03/18
  福島ストック品在庫数一覧 追加 この一覧に在庫数を入力してもらい初期データ
  とする

  2008/06/24
  現場からの要望でSMT部品データ[part_smt]に棚番備考[rem_rack]追加

  2008/07/04
  現場からの要望でリールサイズの表記を修正

  2009/02/12
  検索項目整理

  2009/03/11
  セットアップシートの棚番検索の「Like検索」を止めて「完全一致」で検索へ変更
  品名(棚番違い)による部品形状違いとのクレームがあったので「完全一致」とする

  2009/07/07
  部品検索(棚番)でLike検索を一時止める(棚番データ確認の為)

  2009/07/08
  部品検索(棚番)で完全一致と部分一致(Like検索)を切替え可能とした
  アドバンス専用棚も検索、編集対応 開始(まだ未完成)

  2009/07/13
  アドバンス専用棚 編集対応

  2009/08/07
  SMT準備[arrange]ユーザ追加対応
  (アドバンス新規品追加と部品検索の編集を許可する)

  2009/08/24
  基板名検索(登録データ)は使っていないのでコメントアウト
  代わりに「アドバンス専用棚 一覧」を追加
  セットアップデータ一覧を追加(予定)

  2009/10/15
  部品在庫数を仮表示

  2009/10/20
  部品在庫数の編集機能 実装

  2009/12/16
  セットアップデータがGrp共用データとGrp別のデータを組合せた構成にデータテーブル追加
  (特殊構成データ[sp_set_data])で対応
  追加登録した QPC30349C-01 と QPC30349D-01 の裏面データはGrp.Aと共用で QPC30349A,C,D-02
  として登録済み
  従来の検索処理では Grp.C で使用する表面と裏面のみを表示(使用部品一覧と共用部品の検索)
  する事が出来なかった → 使用部品一覧に他のGrpの部品が混じる事になる

  2009/12/17
  リール管理番号検索で検索出来無くなっていた! 修正済み ついでに表示フォーマットも修正
  検索項目の名称を変更(より判りやすくしたつもり)
  部品名(棚番)から使用基板の検索 検討中

  2009/12/22
  まずは比較的簡単に実装出来そうな部品名から使用基板の検索から

  2010/07/27
  session_start()を先頭に移動

  2011/06/14
  部品名 → セットアップシート 検索 追加

  2015/10/06
  新棚番で検索出来るように修正
  作業者は BS XXXXX X の中間5桁で検索するのでLike検索で処理
  旧棚番でも検索可能なはず

  2015/10/30
  新棚番対応 リールサイズ修正

  2016/01/08
  部品定数(p_constant)の編集追加

  2016/02/01
  高額品のセル色(オレンジ)設定

  2016/05/24
  リールの管理番号追加処理中

  2016/11/21
  ユニットデータを[unit_id]ソートから[u_index]ソートへ変更
  unit_idでは実装面がソートされない場合が有り、表1-裏1-裏2構成の時に実装面(u_index)でソートしてして
  いないと共用部品が正常に検索出来なかったので修正

  2016/11/30
  石川からの要望で高額品を@500以上から@200以上へ修正したのでコメントを修正

  2017/02/21
  買取部材[CR]及び[MW]を備考(棚番)を一旦全てクリアして入力したので該当する場合はセル色を赤へ

  2017/07/13
  文字コードをUTF-8へ

  2017/07/28
  CentOS 7(PHP 5.4.16)環境で動く用に修正中
  アドバンス棚 削除


-->

<?php
//---------------------------------------------------------------
// 初期設定
//---------------------------------------------------------------

// 共有関数
include 'inc/parameter_inc.php';
include 'lib/com_func1.php';
include 'lib/com_func2.php';
include 'lib/search1_func.php';

// PEARライブラリ
require_once 'HTML/QuickForm.php';
require_once 'MDB2.php';
//require_once 'Benchmark/Timer.php';	// 処理時間計測で使用

// 配列設定
$sel_search = array(0=>"-Select-",
					1=>"データファイル名 → セットアップシート 検索",
					2=>"部品名 → 棚番 検索",
					3=>"棚番 → 部品名 検索",
//					4=>"基板名検索(登録データ)",
//					5=>"登録部品一覧",
//					6=>"補充部品在庫数一覧",
					7=>"福島ストック品 登録品一覧",
//					8=>"アドバンス棚 登録品一覧",
					9=>"セットアップデータ 登録一覧",
					10=>"リール管理番号 → 登録データ 検索",
					11=>"棚番 → セットアップシート 検索",
					12=>"部品名 → セットアップシート 検索");

$sel_sort = array(0=>"-Select-",
				  1=>"棚番->穴番号(Excel)",
				  2=>"棚番->部品名(Excel)",
				  3=>"穴番号(Excel)");

$sel_solder = array(0=>"不明",
					1=>"共晶",
					2=>"RoHS",
					3=>"混在");

$sel_size = array(0=>"中(250mm)",
				  1=>"小(180mm)",
				  2=>"大(330mm)",
				  3=>"特大(380mm)",
				  4=>"千代田専用",
				  5=>"防湿庫/乾燥庫");

$sel_exp = array(0=>"通常品",
				 1=>"高額品");

//---------------------------------------------------------------

// セッション破棄
function unset_session() {
	unset($_SESSION["permission"]);
	unset($_SESSION["mode_search1"]);
	unset($_SESSION["item"]);
	unset($_SESSION["parameter"]);
	unset($_SESSION["operate_no"]);
	unset($_SESSION["dash_no"]);
	unset($_SESSION["mf_qty"]);
	unset($_SESSION["eid"]);
	unset($_SESSION["did"]);
	unset($_SESSION["sel_match"]);
	unset($_SESSION["sel_rack"]);
}


switch($mode) {
case "search":

	// DB接続
	$mdb2 = db_connect();

	// $_SESSION["eid']にデータ有り -> 2回目の表示(データ更新)
	if ($_SESSION["eid"]!=NULL) {

		$smt_id[0]  = mb_convert_kana(Trim($_POST['smt_id']), "KVa");
		$rack_no    = mb_convert_kana(Trim($_POST['rack_no']), "KVa");
		$product    = mb_convert_kana(Trim($_POST['product']), "KVa");
		$solder     = $_POST['solder']["solder"];
		$p_new      = mb_convert_kana(Trim($_POST['p_new']), "KVa");
		$p_maker    = mb_convert_kana(Trim($_POST['p_maker']), "KVa");
		$p_sub      = mb_convert_kana(Trim($_POST['p_sub']), "KVa");
		$p_nec      = mb_convert_kana(Trim($_POST['p_nec']), "KVa");
		$r_size     = $_POST['r_size']["r_size"];
		$exp_item   = $_POST['exp_item']["exp_item"];
		$rack_old   = mb_convert_kana(Trim($_POST['rack_old']), "KVa");
		$rem_part   = mb_convert_kana(Trim($_POST['rem_part']), "KVa");
		$rem_rack   = mb_convert_kana(Trim($_POST['rem_rack']), "KVa");
		// 2016-01-08追加
		$p_constant = mb_convert_kana(Trim($_POST['p_constant']), "KVa");

		// トランザクションブロック開始
		$res = $mdb2->beginTransaction();

		// 2007/12/25 新棚番と品名は更新対象外とする
		// 2008/05/21 編集可能とする
		$res = $mdb2->exec("UPDATE part_smt SET rack_no='$rack_no' WHERE smt_id='$smt_id[0]'");
		$res = $mdb2->exec("UPDATE part_smt SET product='$product' WHERE smt_id='$smt_id[0]'");
		$res = $mdb2->exec("UPDATE part_smt SET solder='$solder' WHERE smt_id='$smt_id[0]'");
		$res = $mdb2->exec("UPDATE part_smt SET p_new='$p_new' WHERE smt_id='$smt_id[0]'");
		$res = $mdb2->exec("UPDATE part_smt SET p_maker='$p_maker' WHERE smt_id='$smt_id[0]'");
		$res = $mdb2->exec("UPDATE part_smt SET p_sub='$p_sub' WHERE smt_id='$smt_id[0]'");
		$res = $mdb2->exec("UPDATE part_smt SET p_nec='$p_nec' WHERE smt_id='$smt_id[0]'");
		$res = $mdb2->exec("UPDATE part_smt SET r_size='$r_size' WHERE smt_id='$smt_id[0]'");
		$res = $mdb2->exec("UPDATE part_smt SET exp_item='$exp_item' WHERE smt_id='$smt_id[0]'");
		$res = $mdb2->exec("UPDATE part_smt SET rack_old='$rack_old' WHERE smt_id='$smt_id[0]'");
		$res = $mdb2->exec("UPDATE part_smt SET rem_part='$rem_part' WHERE smt_id='$smt_id[0]'");
		$res = $mdb2->exec("UPDATE part_smt SET rem_rack='$rem_rack' WHERE smt_id='$smt_id[0]'");
		// 2016-01-08追加
		$res = $mdb2->exec("UPDATE part_smt SET p_constant='$p_constant' WHERE smt_id='$smt_id[0]'");

		// トランザクションブロック終了
		$res = transaction_end($mdb2, $res);

		// 更新後、ID破棄
		unset($_SESSION["eid"]);

	}

	// SMT部品データ削除？
	if ($_SESSION["did"]!=NULL) {

		$part_del = $_POST['part_del']["part_del"];
		if ($part_del==1) {

			$smt_id[0] = $_SESSION["did"];

			// [part_smt]から行削除
			$res = $mdb2->exec("DELETE FROM part_smt WHERE smt_id='$smt_id[0]'");

		}

		// 削除後、ID破棄
		unset($_SESSION["did"]);

	}

	// 2009/07/13 アドバンス専用棚の編集データ反映
	if ($_SESSION["eid_a"]!=NULL) {

		$advance_id[0] = mb_convert_kana(Trim($_POST['advance_id']), "KVa");
		$rack_no       = mb_convert_kana(Trim($_POST['rack_no']), "KVa");
		$part_no       = mb_convert_kana(Trim($_POST['part_no']), "KVa");
		$part_1        = mb_convert_kana(Trim($_POST['part_1']), "KVa");
		$part_2        = mb_convert_kana(Trim($_POST['part_2']), "KVa");
		$part_3        = mb_convert_kana(Trim($_POST['part_3']), "KVa");
		$part_4        = mb_convert_kana(Trim($_POST['part_4']), "KVa");
		$part_5        = mb_convert_kana(Trim($_POST['part_5']), "KVa");
		$rem_advance   = mb_convert_kana(Trim($_POST['rem_advance']), "KVa");

		// トランザクションブロック開始
		$res = $mdb2->beginTransaction();

		$res = $mdb2->exec("UPDATE part_advance SET rack_no='$rack_no' WHERE advance_id='$advance_id[0]'");
		$res = $mdb2->exec("UPDATE part_advance SET part_no='$part_no' WHERE advance_id='$advance_id[0]'");
		$res = $mdb2->exec("UPDATE part_advance SET part_1='$part_1' WHERE advance_id='$advance_id[0]'");
		$res = $mdb2->exec("UPDATE part_advance SET part_2='$part_2' WHERE advance_id='$advance_id[0]'");
		$res = $mdb2->exec("UPDATE part_advance SET part_3='$part_3' WHERE advance_id='$advance_id[0]'");
		$res = $mdb2->exec("UPDATE part_advance SET part_4='$part_4' WHERE advance_id='$advance_id[0]'");
		$res = $mdb2->exec("UPDATE part_advance SET part_5='$part_5' WHERE advance_id='$advance_id[0]'");
		$res = $mdb2->exec("UPDATE part_advance SET rem_advance='$rem_advance' WHERE advance_id='$advance_id[0]'");

		// トランザクションブロック終了
		$res = transaction_end($mdb2, $res);

		// 更新後、ID破棄
		unset($_SESSION["eid_a"]);

	}

	// アドバンス専用棚データ削除？
	if ($_SESSION["did_a"]!=NULL) {

		$part_del = $_POST['part_del']["part_del"];
		if ($part_del==1) {

			$advance_id[0] = $_SESSION["did_a"];

			// [part_smt]から行削除
			$res = $mdb2->exec("DELETE FROM part_advance WHERE advance_id='$advance_id[0]'");

		}

		// 削除後、ID破棄
		unset($_SESSION["did_a"]);

	}

	// 部品在庫データ反映
	if ($_SESSION["eid_s"]!=NULL) {

		$stock_id  = mb_convert_kana(Trim($_POST['stock_id']), "KVa");
		$rack_old  = mb_convert_kana(Trim($_POST['rack_old']), "KVa");
		$product   = mb_convert_kana(Trim($_POST['product']), "KVa");
		$qty_stock = mb_convert_kana(Trim($_POST['qty_stock']), "KVa");
		$up_date_y = mb_convert_kana(Trim($_POST['up_date']["Y"]), "KVa");
		$up_date_m = mb_convert_kana(Trim($_POST['up_date']["m"]), "KVa");
		$up_date_d = mb_convert_kana(Trim($_POST['up_date']["d"]), "KVa");
		$up_date   = $up_date_y . '-' . $up_date_m . '-' .  $up_date_d;
		$rem_stock = mb_convert_kana(Trim($_POST['rem_stock']), "KVa");

		// トランザクションブロック開始
		$res = $mdb2->beginTransaction();

		$res = $mdb2->exec("UPDATE stock_smt SET qty_stock='$qty_stock' WHERE stock_id='$stock_id'");
		$res = $mdb2->exec("UPDATE stock_smt SET up_date='$up_date' WHERE stock_id='$stock_id'");
		$res = $mdb2->exec("UPDATE stock_smt SET rem_stock='$rem_stock' WHERE stock_id='$stock_id'");

		// トランザクションブロック終了
		$res = transaction_end($mdb2, $res);

		// 更新後、ID破棄
		unset($_SESSION["eid_s"]);

	}

	// 検索条件の取得
	if ($_POST['search']!=NULL) {

		$search_no = $_POST['search'];
		$_SESSION["item"] = $search_no;

		$sort_sel = $_POST['sort'];
		$_SESSION["sort"] = $sort_sel;

		$sel_match = $_POST['sel_match']["sel_match"];
		$_SESSION["sel_match"] = $sel_match;

		$sel_rack = $_POST['sel_rack']["sel_rack"];
		$_SESSION["sel_rack"] = $sel_rack;

	} else {

		$search_no = $_SESSION['item'];
		$sort_sel  = $_SESSION['sort'];
		$sel_match = $_SESSION["sel_match"];
		$sel_rack  = $_SESSION["sel_rack"];

	}

	// 半角カナ->全角カナ 全角英数->半角英数変換
	if ($_POST['parameter']!=NULL) {
		$parameter = mb_convert_kana(Trim($_POST['parameter']), "KVa");
		$_SESSION["parameter"] = $parameter;
	} else {
		$parameter = $_SESSION['parameter'];
	}

	if ($_POST['operate_no']!=NULL) {
		$operate_no = Trim($_POST['operate_no']["0"]);
		$dash_no = Trim($_POST['operate_no']["1"]);
		$_SESSION['operate_no'] = $operate_no;
		$_SESSION['dash_no'] = $dash_no;
	} else {
		$operate_no = $_SESSION['operate_no'];
		$dash_no = $_SESSION['dash_no'];
	}

	if ($_POST['mf_qty']!=NULL) {
		$mf_qty = Trim($_POST['mf_qty']);
		$_SESSION["mf_qty"] = $mf_qty;
	} else {
		$mf_qty = $_SESSION['mf_qty'];
	}

	$form =& new HTML_QuickForm("search", "POST");
	$form -> addElement("header", "title", "生産管理 データベース検索:");
	$form -> addElement("select", "search", "検索種別:", $sel_search);

	$rack_sel[] = $form -> createElement("radio", "sel_rack", NULL, "日東S品", 0);
	$rack_sel[] = $form -> createElement("radio", "sel_rack", NULL, "その他", 1);
	$form -> addGroup($rack_sel, "sel_rack", "棚選択:");

	$match_sel[] = $form -> createElement("radio", "sel_match", NULL, "完全一致", 0);
	$match_sel[] = $form -> createElement("radio", "sel_match", NULL, "部分一致", 1);
	$form -> addGroup($match_sel, "sel_match", "部品検索(棚番):");

	$form -> addElement("text", "parameter", "パラメータ:", array('size'=>40, 'maxlength'=>60));
	$form -> addElement("static", "br", "");
	$form -> addElement("static", "br", "");

	$form -> addElement("header", "title", "セットアップシート関連 パラメータ:");
	$ope  =& HTML_QuickForm::createElement('text', '', null, array('size'=>10, 'maxlength'=>10));
	$dash =& HTML_QuickForm::createElement('text', '', null, array('size'=>10, 'maxlength'=>10));
	$form -> addGroup(array($ope, $dash), 'operate_no', '工番:', ' ダッシュ:');
	$form -> addElement("text", "mf_qty", "数量:", array('size'=>10, 'maxlength'=>10));
//	$form -> addElement("date", "plan", "生産予定日:",
//						array("language"=>"ja","minYear"=>2000,"maxYear"=>2030,"format"=>"Ymd"));
	$form -> addElement("select", "sort", "ソート種別:", $sel_sort);
	$form -> addElement("submit", "send", "検索");

	// 検索条件設定
	if ($permission=='developer' or $permission=='admin') {

		if ($search_no!=NULL) {
			$form -> setDefaults(array("search"=>$search_no));
			$form -> setDefaults(array("sort"=>$sort_sel));
		} else {
			$form -> setDefaults(array("search"=>'2'));
			$form -> setDefaults(array("sort"=>'3'));
		}

		if ($parameter!=NULL) {
			$form -> setDefaults(array("parameter"=>$parameter));
		}

		$form -> setDefaults(array("sel_rack"=>array("sel_rack"=>0)));
		$form -> setDefaults(array("sel_match"=>array("sel_match"=>0)));

	} elseif ($permission=='user' or $permission=='guest' or $permission=='setup') {

		if ($search_no!=NULL) {
			$form -> setDefaults(array("search"=>$search_no));
			$form -> setDefaults(array("sort"=>$sort_sel));
		} else {
			$form -> setDefaults(array("search"=>'2'));
			$form -> setDefaults(array("sort"=>'3'));
		}

		if ($parameter!=NULL) {
			$form -> setDefaults(array("parameter"=>$parameter));
		}

		$form -> setDefaults(array("sel_rack"=>array("sel_rack"=>0)));
		$form -> setDefaults(array("sel_match"=>array("sel_match"=>1)));

	}

	$form -> display();

	// 2007/09/19 コメント追加
//	print('<div align="left"><font size="3" color="#ff6347"><b>');
	print('<div align="left"><font size="3" color="#000000"><b>');
//	print('＊検索のコツ！ % を頭に付けると中間文字列も検索出来ます');
//	print('<br>');
//	print('(例) %AAAA で検索 -> BBBAAAACCC の品名がヒットします');

//	print('＊2010-10-14 棚番、部品データを2010-10-12版と差替え');
//	print('<br>');
	print('(！注意！生産以外で部品を使用する場合は、在庫数管理の為必ず連絡お願いいたします)');
	print('<br>');
	print('(＊高額品(単価200円以上)は部品検索=>棚番のセル色をオレンジにしています)');
	print('<br>');
	print('(＊買取部材[CR]及び[MW]は部品検索=>棚番のセル色を赤にしています)');
	print('</b></div>');

/* 	print('<div align="left"><font size="3" color="#000000"><b>');
	print('(！注意！新棚番は2015年10月末より運用予定です)');
	print('</b></div>');
 */

	// 取得データ確認
	// 現在すべて同じだが検索メニューに適応した正規表現でフィルタリング(予定)
	// 2015/10/06 新棚番の検索に対応
	if ($parameter!=NULL) {
		if ($search_no==1) {
			if (preg_match("/[^*]{1,25}$/", $parameter)) {
				$search_para = '%' . $parameter . '%';  // [%]を付けてlike検索
			}
		} elseif ($search_no==2) {
			if (preg_match("/[^*]{1,25}$/", $parameter)) {
				if ($sel_match==0) {
					$search_para = $parameter;  			// 完全一致検索
				} elseif ($sel_match==1) {
					$search_para = $parameter . '%';  		// 部分一致検索
				}
			}
		} elseif ($search_no==3) {
			if (preg_match("/[^*]{1,25}$/", $parameter)) {
				$search_para = '%' . $parameter . '%';  // [%]を付けてlike検索
			}
		} elseif ($search_no==4) {
			if (preg_match("/[^*]{1,25}$/", $parameter)) {
				$search_para = '%' . $parameter . '%';  // [%]を付けてlike検索
			}
		} elseif ($search_no==9) {
			if (preg_match("/[^*]{1,25}$/", $parameter)) {
				$search_para = $parameter;  			//
			}
		} elseif ($search_no==10) {
			if (preg_match("/[^*]{1,25}$/", $parameter)) {
				$search_para = $parameter;  			//
			}
		} elseif ($search_no==11) {
			if (preg_match("/[^*]{1,25}$/", $parameter)) {
				//$search_para = $parameter;  			//
				$search_para = '%' . $parameter . '%';  // [%]を付けてlike検索
			}
		} elseif ($search_no==12) {
			if (preg_match("/[^*]{1,25}$/", $parameter)) {
				$search_para = $parameter;  			//
			}
		}
	}

	//
	switch($search_no) {
	case 0:
		$serach = '未定';
		break;

	case 1:
		$search = 'データファイル名 → セットアップシート 検索';
		if ($parameter!=NULL) {

			// 2009/12/16
			// セットアップデータがGrp共用データとGrp別のデータを組合せた構成に対応

			// [unit_id] 検索(特殊構成)
			$sp_set_row = $mdb2->queryRow("SELECT * FROM sp_set_data WHERE file_name='$parameter'");
			if (PEAR::isError($sp_set_row)) {
			} else {

				for ($i=0; $i<=3; $i++) {

					if ($sp_set_row[$i + 2] != 0) {
						$unit_id[$i] = $sp_set_row[$i + 2];
					} else {
						break;
					}

				}

			}

			// [unit_id] 検索(通常) *特殊構成のデータが無かった場合
			if (count($unit_id) == 0) {

				// [unit_id] 検索
                // 2016/11/21 unit_idソートからu_indexソートへ変更
                // $res_query = $mdb2->query("SELECT * FROM unit_data WHERE file_name LIKE '$search_para'  ORDER BY unit_id");
				$res_query = $mdb2->query("SELECT * FROM unit_data WHERE file_name LIKE '$search_para'  ORDER BY u_index");
				$res_query = err_check($res_query);

				$i = 0;
				while($row = $res_query->fetchRow(MDB2_FETCHMODE_ASSOC)) {
					$unit_id[$i] = $row['unit_id'];
					$i++;
				}

			}

		}
		break;

	case 2:
		$search = '部品名 → 棚番 検索';
		if ($parameter!=NULL) {

			if ($sel_rack==0) {

				// [smt_id] 検索
				$sql[1] = "SELECT * FROM part_smt";

				if ($sel_match==0) {

					$sql[2] = " WHERE product='$search_para'";
					$sql[3] = " OR p_new='$search_para'";
					$sql[4] = " OR p_maker='$search_para'";
					$sql[5] = " OR p_sub='$search_para'";
					$sql[6] = " OR p_nec='$search_para'";
					$sql[7] = " ORDER BY smt_id";

				} elseif ($sel_match==1) {

					$sql[2] = " WHERE product LIKE '$search_para'";
					$sql[3] = " OR p_new LIKE '$search_para'";
					$sql[4] = " OR p_maker LIKE '$search_para'";
					$sql[5] = " OR p_sub LIKE '$search_para'";
					$sql[6] = " OR p_nec LIKE '$search_para'";
					$sql[7] = " ORDER BY smt_id";

				}

				$sql[0] = $sql[1] . $sql[2] . $sql[3] . $sql[4] . $sql[5] . $sql[6] . $sql[7];
				$res_query = $mdb2->query($sql[0]);
				$res_query = err_check($res_query);
				unset($sql);

				$i = 0;
				while($row = $res_query->fetchRow(MDB2_FETCHMODE_ASSOC)) {
					$smt_id[$i] = $row['smt_id'];
					$i++;
				}

			} elseif ($sel_rack==1) {

				// [advance_id] 検索
				$sql[1] = "SELECT * FROM part_advance";

				if ($sel_match==0) {

					$sql[2] = " WHERE part_1='$search_para'";
					$sql[3] = " OR part_2='$search_para'";
					$sql[4] = " OR part_3='$search_para'";
					$sql[5] = " OR part_4='$search_para'";
					$sql[6] = " OR part_5='$search_para'";
					$sql[7] = " ORDER BY advance_id";

				} elseif ($sel_match==1) {

					$sql[2] = " WHERE part_1 LIKE '$search_para'";
					$sql[3] = " OR part_2 LIKE '$search_para'";
					$sql[4] = " OR part_3 LIKE '$search_para'";
					$sql[5] = " OR part_4 LIKE '$search_para'";
					$sql[6] = " OR part_5 LIKE '$search_para'";
					$sql[7] = " ORDER BY advance_id";

				}

				$sql[0] = $sql[1] . $sql[2] . $sql[3] . $sql[4] . $sql[5] . $sql[6] . $sql[7];
				$res_query = $mdb2->query($sql[0]);
				$res_query = err_check($res_query);
				unset($sql);

				$i = 0;
				while($row = $res_query->fetchRow(MDB2_FETCHMODE_ASSOC)) {
					$smt_id[$i] = $row['advance_id'];
					$i++;
				}

			}

		}
		break;

	case 3:
		$search = '棚番 → 部品名 検索';
		if ($parameter!=NULL) {

			if ($sel_rack==0) {

				// 日東S品 [rack_no] 検索
				$res_query = $mdb2->query("SELECT * FROM part_smt WHERE rack_no like '$search_para' OR rack_old like '$search_para' ORDER BY smt_id");
				$res_query = err_check($res_query);

			} elseif ($sel_rack==1) {

				// アドバンス [rack_no] 検索
				$res_query = $mdb2->query("SELECT * FROM part_advance WHERE rack_no like '$search_para' ORDER BY advance_id");
				$res_query = err_check($res_query);

			}

		}
		break;

	case 4:
		$search = '基板名 → 登録データ 検索';
		if ($parameter!=NULL) {

			// [unit_id] 検索
			$res_query = $mdb2->query("SELECT * FROM unit_data WHERE board LIKE '$search_para' OR file_name LIKE '$search_para'  ORDER BY unit_id");
			$res_query = err_check($res_query);

			$i = 0;
			while($row = $res_query->fetchRow(MDB2_FETCHMODE_ASSOC)) {
				$unit_id[$i] = $row['unit_id'];
				$i++;
			}

		}
		break;

	case 5:
		$search = '登録部品一覧';
//		if ($parameter!=NULL) {
//			$sql_select = "SELECT * FROM order_info o JOIN product p ON(o.product_id=p.product_id)";
//			$sql_where = " WHERE (eqp like '$search_para' OR product like '$search_para') AND plan like '$plan2'";
//			$sql_order = " ORDER BY plan, operate_no, dash_no, eqp, product";
//			$search_sql = $sql_select . $sql_where . $sql_order;
//		}
		break;

	case 6:
		$search = '補充品 在庫数一覧';
//		if ($parameter!=NULL) {
//			$sql_select = "SELECT * FROM order_info o JOIN product p ON(o.product_id=p.product_id)";
//			$sql_where = " WHERE (eqp like '$search_para' OR product like '$search_para') AND plan like '$plan2'";
//			$sql_order = " ORDER BY plan, operate_no, dash_no, eqp, product";
//			$search_sql = $sql_select . $sql_where . $sql_order;
//		}
		break;

	case 7:
		$search = 'FS品 在庫数一覧';
//		if ($parameter!=NULL) {
//			$sql_select = "SELECT * FROM order_info o JOIN product p ON(o.product_id=p.product_id)";
//			$sql_where = " WHERE (eqp like '$search_para' OR product like '$search_para') AND plan like '$plan2'";
//			$sql_order = " ORDER BY plan, operate_no, dash_no, eqp, product";
//			$search_sql = $sql_select . $sql_where . $sql_order;
//		}
		break;

	case 8:
		$search = 'アドバンス棚 登録一覧';
//		if ($parameter!=NULL) {
//			$search_sql = "SELECT * FROM reel_no WHERE check_no='$search_para' ORDER BY reel_id";
//		}
		break;

	case 9:
		$search = 'セットアップデータ 登録一覧';
//		if ($parameter!=NULL) {
//			$search_sql = "SELECT * FROM reel_no WHERE check_no='$search_para' ORDER BY reel_id";
//		}
		break;

	case 10:
		$search = 'リール管理番号 → 登録データ 検索';
		if ($parameter!=NULL) {
			$search_sql = "SELECT * FROM reel_no WHERE check_no='$search_para' ORDER BY reel_id";
		}
		break;

	case 11:
		$search = '棚番 → セットアップシート 検索';
		if ($parameter!=NULL) {
			$search_sql = "SELECT * FROM unit_data WHERE unit_id IN(SELECT unit_id FROM station_data WHERE st_id IN(SELECT st_id FROM set_data WHERE rack_data='$parameter')) ORDER BY unit_id";
		}
		break;

	case 12:
		$search = '部品名 → セットアップシート 検索';
		if ($parameter!=NULL) {
			$search_sql = "SELECT * FROM unit_data WHERE unit_id IN(SELECT unit_id FROM station_data WHERE st_id IN(SELECT st_id FROM set_data WHERE part='$parameter')) ORDER BY unit_id";
		}
		break;

	}

	print('<hr style="width: 100%; height: 2px;">');

	switch ($search_no) {
	case 1:
		// データファイル名 → セットアップシート 検索
		include 'inc/search1_inc1.php';
		break;
	case 2:
		// 部品名 → 棚番 検索
		include 'inc/search1_inc2.php';
		break;
	case 3:
		// 棚番(新、旧) → 部品名 検索
		include 'inc/search1_inc3.php';
		break;
	case 4:
		// 基板名検索(登録データ)
//		include 'inc/search1_inc4.php';
		break;
	case 5:
		// 登録部品一覧
//		include 'inc/search1_inc5.php';
		break;
	case 6:
		// 補充品 在庫数一覧
//		include 'inc/search1_inc6.php';
		break;
	case 7:
		// 福島ストック品 登録一覧
		include 'inc/search1_inc7.php';
		break;
	case 8:
		// アドバンス棚 登録一覧
		include 'inc/search1_inc8.php';
		break;
	case 9:
		// セットアップデータ 登録一覧
		include 'inc/search1_inc9.php';
		break;
	case 10:
		// リール管理番号 検索
		include 'inc/search1_inc10.php';
		break;
	case 11:
		// 棚番 → 使用基板 検索
		include 'inc/search1_inc11.php';
		break;
	case 12:
		// 部品名 → 使用基板 検索
		include 'inc/search1_inc12.php';
		break;
	}

	print('</table>');
	print('</form>');

	// 配列開放
	// 2007/02/28 includeしている表示部で使用
	unset($t1);
	unset($t2);
	unset($t3);
	unset($t4);

	// セッション保存
	$_SESSION["mode_search1"] = 'search';
	$_SESSION["permission"] = $permission;

	// DB切断
	db_disconnect($mdb2);

	break;


case "search_2":

	// 2008/03/04
	// 1品名に最大4ユニット(表2、裏2)
	// 1ユニットに最大5ステーション
	// 1ステーションに最大40種の部品が搭載(1ユニットに最大200種)
	// 現在のサンプルシート1枚に最大20種なので最大2枚の出力

	$operate_no = mb_convert_kana(Trim($_POST['operate_no']), "KVa");

	print('<br>');
	$form =& new HTML_QuickForm("search", "POST");
	$form -> addElement("header", "title", "準備作業用 バーコードシート検索:");
	$form -> addElement("text", "operate_no", "検索工番:", array('size'=>15, 'maxlength'=>10));
//	$form -> addElement("date", "plan", "生産予定日:",
//						array("language"=>"ja","minYear"=>2000,"maxYear"=>2030,"format"=>"Ymd"));
	$form -> addElement("submit", "send", "検索");
	$form -> display();

	// セパレータ
	print('<hr style="width: 100%; height: 2px;">');

	// DB接続
	$mdb2_smt = db_connect_smt();

	// 工番検索
	$res_query = $mdb2_smt->query("SELECT * FROM product_smt T1 JOIN order_info T2 ON(T1.product_id=T2.product_id) WHERE operate_no='$operate_no' ORDER BY order_id");
	$res_query = err_check($res_query);

	// DB切断
	db_disconnect($mdb2_smt);

	// 工番検索一覧
	include 'inc/search1_inc7.php';

	// セッション保存
	$_SESSION["mode_search1"] = 'search_2';
	$_SESSION["permission"] = $permission;

	break;


case "disp":

	$res = serch_disp();

	break;


case "edit":

	$smt_id = $_SESSION["eid"];

	// DB接続
	$mdb2 = db_connect();

	if ($smt_id!=NULL) {

		// SMT部品データ取得
		$res_row = $mdb2->queryRow("SELECT * FROM part_smt WHERE smt_id='$smt_id'");
		if (PEAR::isError($res_row)) {
		} else {
			$rack_no    = $res_row[1];
			$product    = $res_row[2];
			$solder     = $res_row[3];
			$p_new      = $res_row[4];
			$p_maker    = $res_row[5];
			$p_sub      = $res_row[6];
			$p_nec      = $res_row[7];
			$r_size     = $res_row[8];
			$exp_item   = $res_row[9];
			$rack_old   = $res_row[10];
			$rem_part   = $res_row[11];
			$rem_rack   = $res_row[12];
			// 2016-01-08追加
			$p_constant = $res_row[13];
		}

		//
		$form = new HTML_QuickForm("edit", "POST");
		$form -> addElement("header", "title", "SMT部品データ修正:");

		$form -> addElement("text", "smt_id", "SMT ID:", array('size'=>15, 'maxlength'=>20));
		$form -> addElement("text", "rack_no", "棚番:", array('size'=>15, 'maxlength'=>20));
		$form -> addElement("text", "product", "品名:", array('size'=>30, 'maxlength'=>50));

		$solder_set[] = $form -> createElement("radio", "solder", NULL, "不明", 0);
		$solder_set[] = $form -> createElement("radio", "solder", NULL, "共晶", 1);
		$solder_set[] = $form -> createElement("radio", "solder", NULL, "RoHS", 2);
		$form -> addGroup($solder_set, "solder", "はんだ:");

		$form -> addElement("text", "p_new", "新品名:", array('size'=>30, 'maxlength'=>50));
		$form -> addElement("text", "p_maker", "メーカー品名:", array('size'=>30, 'maxlength'=>50));
		$form -> addElement("text", "p_sub", "サブ品名:", array('size'=>30, 'maxlength'=>50));
		$form -> addElement("text", "p_nec", "NEC品名:", array('size'=>30, 'maxlength'=>50));

		// 2016-01-08追加
		$form -> addElement("text", "p_constant", "部品定数:", array('size'=>30, 'maxlength'=>30));

		$size_set[] = $form -> createElement("radio", "r_size", NULL, "小(180mm)", 1);
		$size_set[] = $form -> createElement("radio", "r_size", NULL, "中(250mm)", 0);
		$size_set[] = $form -> createElement("radio", "r_size", NULL, "大(330mm)", 2);
		$size_set[] = $form -> createElement("radio", "r_size", NULL, "特大(380mm)", 3);
		$size_set[] = $form -> createElement("radio", "r_size", NULL, "千代田専用", 4);
		$size_set[] = $form -> createElement("radio", "r_size", NULL, "防湿庫/乾燥庫", 5);
		$form -> addGroup($size_set, "r_size", "リールサイズ:");

		$exp_set[] = $form -> createElement("radio", "exp_item", NULL, "通常", 0);
		$exp_set[] = $form -> createElement("radio", "exp_item", NULL, "高額品", 1);
		$form -> addGroup($exp_set, "exp_item", "高額品:");

		$form -> addElement("text", "rack_old", "旧棚番:", array('size'=>15, 'maxlength'=>20));
		$form -> addElement("text", "rem_part", "備考(部品):", array('size'=>60, 'maxlength'=>100));
		$form -> addElement("text", "rem_rack", "備考(棚番):", array('size'=>60, 'maxlength'=>100));
		$form -> addElement("static", "br", "");
		$form -> addElement("static", "br", "");

		// 部品データの設定
		// 2016-01-08 部品定数(p_constant)追加
		$form -> setDefaults(array("smt_id"     =>$smt_id,
									"rack_no"   =>$rack_no,
									"product"   =>$product,
									"solder"    =>array("solder"=>$solder),
									"p_new"     =>$p_new,
									"p_maker"   =>$p_maker,
									"p_sub"     =>$p_sub,
									"p_nec"     =>$p_nec,
									"p_constant"=>$p_constant,
									"r_size"    =>array("r_size"=>$r_size),
									"exp_item"  =>array("exp_item"=>$exp_item),
									"rack_old"  =>$rack_old,
									"rem_part"  =>$rem_part,
									"rem_rack"  =>$rem_rack));

		$form -> addElement("submit", "send", "データベース更新");

		// 入力確認
		$form -> setRequiredNote("<font color='Red'>*</font> 必須項目です");
		$form -> addRule("rack_old", "旧棚番を入力して下さい", "required", "", "client");
		$form -> addRule("product", "品名を入力して下さい", "required", "", "client");
		$form -> addRule("rack_no", "新棚番を入力してください", "required", "", "client");
//		HTML_QuickForm::registerRule("set_no", "regex", "/[a-zA-Z0-9]{9}/");
//		$form -> addRule("rack_no", '棚番(新)の書式が違います', "set_no", "", "client");
//		$form -> addRule("operate_no", "工番を入力して下さい", "required", "", "client");

		if ($form -> validate()) {
			$form -> process("showForm",FALSE);
		} else {
			$form -> setJsWarnings("以下の項目でエラーが発生しました",
					"エラー項目を修正して、再度［データベース更新］ボタンをクリックしてください");
			$form->display();
		}

//		$_SESSION["eid"] = $smt_id;
		$_SESSION["mode_search1"] = 'search';

	}

	// DB切断
	db_disconnect($mdb2);

	break;


case "del":

	$smt_id = $_SESSION["did"];

	// DB接続
	$mdb2 = db_connect();

	if ($smt_id!=NULL) {

		// SMT部品データ取得
		$res_row = $mdb2->queryRow("SELECT * FROM part_smt WHERE smt_id='$smt_id'");
		if (PEAR::isError($res_row)) {
		} else {
			$rack_no  = $res_row[1];
			$product  = $res_row[2];
			$solder   = $res_row[3];
			$p_new    = $res_row[4];
			$p_maker  = $res_row[5];
			$p_sub    = $res_row[6];
			$p_nec    = $res_row[7];
			$r_size   = $res_row[8];
			$exp_item = $res_row[9];
			$rack_old = $res_row[10];
			$rem_part = $res_row[11];
			$rem_rack = $res_row[12];
			// 2016-01-08追加
			$p_constant = $res_row[13];
		}

		//
		$form = new HTML_QuickForm("del", "POST");
		$form -> addElement("header", "title", "削除するSMT部品データ:");

		$form -> addElement("text", "smt_id", "SMT ID:", array('size'=>15, 'maxlength'=>20));
		$form -> addElement("text", "rack_no", "棚番:", array('size'=>15, 'maxlength'=>20));
		$form -> addElement("text", "product", "品名:", array('size'=>30, 'maxlength'=>50));

		$solder_set[] = $form -> createElement("radio", "solder", NULL, "不明", 0);
		$solder_set[] = $form -> createElement("radio", "solder", NULL, "共晶", 1);
		$solder_set[] = $form -> createElement("radio", "solder", NULL, "RoHS", 2);
		$form -> addGroup($solder_set, "solder", "はんだ:");

		$form -> addElement("text", "p_new", "新品名:", array('size'=>30, 'maxlength'=>50));
		$form -> addElement("text", "p_maker", "メーカー品名:", array('size'=>30, 'maxlength'=>50));
		$form -> addElement("text", "p_sub", "サブ品名:", array('size'=>30, 'maxlength'=>50));
		$form -> addElement("text", "p_nec", "NEC品名:", array('size'=>30, 'maxlength'=>50));

		// 2016-01-08追加
		$form -> addElement("text", "p_constant", "部品定数:", array('size'=>30, 'maxlength'=>30));

		$size_set[] = $form -> createElement("radio", "r_size", NULL, "小(180mm)", 1);
		$size_set[] = $form -> createElement("radio", "r_size", NULL, "中(250mm)", 0);
		$size_set[] = $form -> createElement("radio", "r_size", NULL, "大(330mm)", 2);
		$size_set[] = $form -> createElement("radio", "r_size", NULL, "特大(380mm)", 3);
		$size_set[] = $form -> createElement("radio", "r_size", NULL, "千代田専用", 4);
		$size_set[] = $form -> createElement("radio", "r_size", NULL, "防湿庫/乾燥庫", 5);
		$form -> addGroup($size_set, "r_size", "リールサイズ:");

		$exp_set[] = $form -> createElement("radio", "exp_item", NULL, "通常", 0);
		$exp_set[] = $form -> createElement("radio", "exp_item", NULL, "高額品", 1);
		$form -> addGroup($exp_set, "exp_item", "高額品:");

		$form -> addElement("text", "rack_old", "旧棚番:", array('size'=>15, 'maxlength'=>20));
		$form -> addElement("text", "rem_part", "備考(部品):", array('size'=>60, 'maxlength'=>100));
		$form -> addElement("text", "rem_rack", "備考(棚番):", array('size'=>60, 'maxlength'=>100));
		$form -> addElement("static", "br", "");
		$form -> addElement("static", "br", "");

		$part_del[] = $form -> createElement("radio", "part_del", NULL, "部品データ削除", 1);
		$part_del[] = $form -> createElement("radio", "part_del", NULL, "削除キャンセル", 0);
		$form -> addGroup($part_del, "part_del", "データ削除？:");

		// 部品データの設定
		// 2016-01-08 部品定数(p_constant)追加
		$form -> setDefaults(array("smt_id"     =>$smt_id,
									"rack_no"   =>$rack_no,
									"product"   =>$product,
									"solder"    =>array("solder"=>$solder),
									"p_new"     =>$p_new,
									"p_maker"   =>$p_maker,
									"p_sub"     =>$p_sub,
									"p_nec"     =>$p_nec,
									"p_constant"=>$p_constant,
									"r_size"    =>array("r_size"=>$r_size),
									"exp_item"  =>array("exp_item"=>$exp_item),
									"rack_old"  =>$rack_old,
									"rem_part"  =>$rem_part,
									"rem_rack"  =>$rem_rack,
									"part_del"=>array("part_del"=>1)));

		$form -> addElement("submit", "send", "データベース更新");

		// 入力確認
//		HTML_QuickForm::registerRule("set_no", "regex", "/[a-zA-Z0-9]{9}/");
//		$form -> addRule("rack_no", '棚番(新)の書式が違います', "set_no", "", "client");
//		$form -> addRule("operate_no", "工番を入力して下さい", "required", "", "client");
//		$form -> setRequiredNote("<font color='Red'>*</font> 必須項目です");
//		$form -> freeze();
		$form->display();

//		$_SESSION["eid"] = $smt_id;
		$_SESSION["mode_search1"] = 'search';

	}

	// DB切断
	db_disconnect($mdb2);

	break;


case "edit_a":

	$advance_id = $_SESSION["eid_a"];

	// DB接続
	$mdb2 = db_connect();

	if ($advance_id!=NULL) {

		// アドバンス部品データ取得
		$res_row = $mdb2->queryRow("SELECT * FROM part_advance WHERE advance_id='$advance_id'");
		if (PEAR::isError($res_row)) {
		} else {
			$rack_no     = $res_row[1];
			$part_no     = $res_row[2];
			$part_1      = $res_row[3];
			$part_2      = $res_row[4];
			$part_3      = $res_row[5];
			$part_4      = $res_row[6];
			$part_5      = $res_row[7];
			$rem_advance = $res_row[8];
		}

		//
		$form = new HTML_QuickForm("edit", "POST");
		$form -> addElement("header", "title", "アドバンス専用棚 部品データ修正:");

		$form -> addElement("text", "advance_id", "ADVANCE ID:", array('size'=>15, 'maxlength'=>20));
		$form -> addElement("text", "rack_no", "棚番:", array('size'=>15, 'maxlength'=>20));
		$form -> addElement("text", "part_no", "部品番号:", array('size'=>15, 'maxlength'=>20));
		$form -> addElement("text", "part_1", "品名 1:", array('size'=>30, 'maxlength'=>50));
		$form -> addElement("text", "part_2", "品名 1:", array('size'=>30, 'maxlength'=>50));
		$form -> addElement("text", "part_3", "品名 1:", array('size'=>30, 'maxlength'=>50));
		$form -> addElement("text", "part_4", "品名 1:", array('size'=>30, 'maxlength'=>50));
		$form -> addElement("text", "part_5", "品名 1:", array('size'=>30, 'maxlength'=>50));
		$form -> addElement("text", "rem_advance", "備考:", array('size'=>60, 'maxlength'=>100));
		$form -> addElement("static", "br", "");
		$form -> addElement("static", "br", "");

		// 部品データの設定
		$form -> setDefaults(array("advance_id"  =>$advance_id,
									"rack_no"    =>$rack_no,
									"part_no"    =>$part_no,
									"part_1"     =>$part_1,
									"part_2"     =>$part_2,
									"part_3"     =>$part_3,
									"part_4"     =>$part_4,
									"part_5"     =>$part_5,
									"rem_advance"=>$rem_advance));

		$form -> addElement("submit", "send", "データベース更新");

		// 入力確認
		$form -> setRequiredNote("<font color='Red'>*</font> 必須項目です");
		$form -> addRule("rack_no", "棚番を入力して下さい", "required", "", "client");
//		HTML_QuickForm::registerRule("set_no", "regex", "/[a-zA-Z0-9]{9}/");
//		$form -> addRule("rack_no", '棚番(新)の書式が違います', "set_no", "", "client");
//		$form -> addRule("operate_no", "工番を入力して下さい", "required", "", "client");

		if ($form -> validate()) {
			$form -> process("showForm",FALSE);
		} else {
			$form -> setJsWarnings("以下の項目でエラーが発生しました",
					"エラー項目を修正して、再度［データベース更新］ボタンをクリックしてください");
			$form->display();
		}

//		$_SESSION["eid"] = $smt_id;
		$_SESSION["mode_search1"] = 'search';

	}

	// DB切断
	db_disconnect($mdb2);

	break;


case "del_a":

	$advance_id = $_SESSION["did_a"];

	// DB接続
	$mdb2 = db_connect();

	if ($advance_id!=NULL) {

		// アドバンス部品データ取得
		$res_row = $mdb2->queryRow("SELECT * FROM part_advance WHERE advance_id='$advance_id'");
		if (PEAR::isError($res_row)) {
		} else {
			$rack_no     = $res_row[1];
			$part_no     = $res_row[2];
			$part_1      = $res_row[3];
			$part_2      = $res_row[4];
			$part_3      = $res_row[5];
			$part_4      = $res_row[6];
			$part_5      = $res_row[7];
			$rem_advance = $res_row[8];
		}

		//
		$form = new HTML_QuickForm("del", "POST");
		$form -> addElement("header", "title", "アドバンス専用棚 部品データ削除:");

		$form -> addElement("text", "advance_id", "ADVANCE ID:", array('size'=>15, 'maxlength'=>20));
		$form -> addElement("text", "rack_no", "棚番:", array('size'=>15, 'maxlength'=>20));
		$form -> addElement("text", "part_no", "部品番号:", array('size'=>15, 'maxlength'=>20));
		$form -> addElement("text", "part_1", "品名 1:", array('size'=>30, 'maxlength'=>50));
		$form -> addElement("text", "part_2", "品名 1:", array('size'=>30, 'maxlength'=>50));
		$form -> addElement("text", "part_3", "品名 1:", array('size'=>30, 'maxlength'=>50));
		$form -> addElement("text", "part_4", "品名 1:", array('size'=>30, 'maxlength'=>50));
		$form -> addElement("text", "part_5", "品名 1:", array('size'=>30, 'maxlength'=>50));
		$form -> addElement("text", "rem_advance", "備考:", array('size'=>60, 'maxlength'=>100));
		$form -> addElement("static", "br", "");
		$form -> addElement("static", "br", "");

		$part_del[] = $form -> createElement("radio", "part_del", NULL, "部品データ削除", 1);
		$part_del[] = $form -> createElement("radio", "part_del", NULL, "削除キャンセル", 0);
		$form -> addGroup($part_del, "part_del", "データ削除？:");

		// 部品データの設定
		$form -> setDefaults(array("advance_id"  =>$advance_id,
									"rack_no"    =>$rack_no,
									"part_no"    =>$part_no,
									"part_1"     =>$part_1,
									"part_2"     =>$part_2,
									"part_3"     =>$part_3,
									"part_4"     =>$part_4,
									"part_5"     =>$part_5,
									"rem_advance"=>$rem_advance,
									"part_del"=>array("part_del"=>1)));

		$form -> addElement("submit", "send", "データベース更新");

		// 入力確認
//		HTML_QuickForm::registerRule("set_no", "regex", "/[a-zA-Z0-9]{9}/");
//		$form -> addRule("rack_no", '棚番(新)の書式が違います', "set_no", "", "client");
//		$form -> addRule("operate_no", "工番を入力して下さい", "required", "", "client");
//		$form -> setRequiredNote("<font color='Red'>*</font> 必須項目です");
//		$form -> freeze();
		$form->display();

//		$_SESSION["eid"] = $smt_id;
		$_SESSION["mode_search1"] = 'search';

	}

	// DB切断
	db_disconnect($mdb2);

	break;


case "edit_s":

	$stock_id = $_SESSION["eid_s"];

	// DB接続
	$mdb2 = db_connect();

	if ($stock_id!=NULL) {

		// SMT在庫データ取得
		$res_row = $mdb2->queryRow("SELECT * FROM stock_smt WHERE stock_id='$stock_id'");
		if (PEAR::isError($res_row)) {
		} else {
			$smt_id    = $res_row[1];
			$unit_cost = $res_row[2];
			$qty_stock = $res_row[3];
			$up_date   = $res_row[4];
			$rem_stock = $res_row[5];
		}

		// SMT部品データ取得
		$res_row = $mdb2->queryRow("SELECT * FROM part_smt WHERE smt_id='$smt_id'");
		if (PEAR::isError($res_row)) {
		} else {
			$rack_no  = $res_row[1];
			$product  = $res_row[2];
			$solder   = $res_row[3];
			$p_new    = $res_row[4];
			$p_maker  = $res_row[5];
			$p_sub    = $res_row[6];
			$p_nec    = $res_row[7];
			$r_size   = $res_row[8];
			$exp_item = $res_row[9];
			$rack_old = $res_row[10];
			$rem_part = $res_row[11];
			$rem_rack = $res_row[12];
			// 2016-01-08追加
			$p_constant = $res_row[13];
		}

		//
		$form = new HTML_QuickForm("edit", "POST");
		$form -> addElement("static", "br", "");
		$form -> addElement("header", "title", "SMT部品在庫データ編集:");

//		$form -> addElement("text", "smt_id", "SMT ID:", array('size'=>15, 'maxlength'=>20));
		$form -> addElement("text", "stock_id", "在庫ID:", array('size'=>15, 'maxlength'=>20));
		$form -> addElement("text", "rack_old", "現棚番:", array('size'=>15, 'maxlength'=>20));
		$form -> addElement("text", "product", "品名:", array('size'=>30, 'maxlength'=>60));

		$form -> addElement("text", "qty_stock", "在庫数:", array('size'=>15, 'maxlength'=>20));

		$form -> addElement("date", "up_date", "更新日:",
					array("language"=>"ja","minYear"=>2000,"maxYear"=>2030,"format"=>"Ymd"));

		$form -> addElement("text", "rem_stock", "備考(在庫):", array('size'=>30, 'maxlength'=>60));
		$form -> addElement("static", "br", "");

		// 部品データの設定
		$form -> setDefaults(array("stock_id"  =>$stock_id,
									"rack_old" =>$rack_old,
									"product"  =>$product,
									"qty_stock"=>$qty_stock,
									"up_date"  =>array("Y"=>date("Y"), "m"=>date("m"), "d"=>date("d")),
									"rem_stock"=>$rem_stock));

		$form -> addElement("submit", "send", "データベース更新");

		// 入力確認
//		$form -> setRequiredNote("<font color='Red'>*</font> 必須項目です");
//		$form -> addRule("rack_old", "現棚番を入力して下さい", "required", "", "client");
//		$form -> addRule("product", "品名を入力して下さい", "required", "", "client");
		$form -> addRule("qty_stock", "数量を入力して下さい", "numeric", "", "client");

		if ($form -> validate()) {
			$form -> process("showForm",FALSE);
		} else {
			$form -> setJsWarnings("以下の項目でエラーが発生しました",
					"エラー項目を修正して、再度［データベース更新］ボタンをクリックしてください");
			$form->display();
		}

//		$_SESSION["eid"] = $smt_id;
		$_SESSION["mode_search1"] = 'search';

	}

	// DB切断
	db_disconnect($mdb2);

	break;

}

?>

</body>
</html>
