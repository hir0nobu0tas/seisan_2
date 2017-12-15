<?php
//-------------------------------------------------------------------
// regist1_inc4.php
// 部品リール管理番号登録
//
// 2007/10/09 登録処理
// 2007/10/11 登録不具合有り 登録データと処理 修正
// 2008/03/13 MW500出荷履歴管理を分けたのでデータディレクトリ修正
// 2008/03/22 Integer型への書込み時に型キャスト追加
// 2016/05/24 伝票番号追加([shit_no]カラム追加)
//
//-------------------------------------------------------------------

$csv_file = "../data/reel/" . $txt_file;

// 登録後の確認用
$set_cnt = 0;
unset($reel_no);

$fp = fopen($csv_file, "r");

// 部品リール管理[reel_no]へ格納
// 2016/05/24 管理番号($shit_no)をデータに追加 [shit_no]カラムを追加
while (($data=fgetcsv($fp, 10000, ","))!==FALSE) {

	// Trimによる前後スペース削除
	for ($i=0; $i<=13; $i++) {
		$data[$i] = Trim($data[$i]);
	}

	$due_date = $data[0];
	$shit_no  = $data[1];
	$check_no = $data[2];
	$r_seq    = (int)$data[3];
	$r_total  = (int)$data[4];
	$solder   = solder_no($data[5]);
	$product  = $data[6];
//	$p_maker  = $data[7];
//	$maker    = $data[8];
//	$qty      = (int)$data[9];
	$lot_no   = $data[10];
	$exp_date = $data[11];
	$end_date = $data[12];
	$rem_reel = $data[13];

	if ($r_seq==1) {
		$p_maker = $data[7];
		$maker   = $data[8];
		$qty     = (int)$data[9];

		$p_maker_tmp = $p_maker;
		$maker_tmp   = $maker;
		$qty_tmp     = $qty;
	} elseif ($r_seq!=1 and $r_total!=1) {
		$p_maker = $p_maker_tmp;
		$maker   = $maker_tmp;
		$qty     = $qty_tmp;
	}

	// 2016/05/24 伝票番号が入力されている場合には備考に追記
/* 	if ($shit_no<>"") {
		$rem_reel = "伝番 " . $shit_no . " " . $rem_reel;
	} else {
		$rem_reel = $rem_reel;
	}
 */
	$res = $mdb2->queryRow("SELECT * FROM reel_no WHERE check_no='$check_no'");
	if (PEAR::isError($res)) {
	} elseif ($res[0]==NULL) {

		// 未登録 -> 追加登録
		$res = $mdb2->query("INSERT INTO reel_no(due_date, check_no, r_seq, r_total, solder, product, p_maker, maker, qty, lot_no, exp_date, rem_reel, shit_no) VALUES(
						".$mdb2->quote($due_date, 'Date').",
						".$mdb2->quote($check_no, 'Text').",
						".$mdb2->quote($r_seq, 'Integer').",
						".$mdb2->quote($r_total, 'Integer').",
						".$mdb2->quote($solder, 'Text').",
						".$mdb2->quote($product, 'Text').",
						".$mdb2->quote($p_maker, 'Text').",
						".$mdb2->quote($maker, 'Text').",
						".$mdb2->quote($qty, 'Integer').",
						".$mdb2->quote($lot_no, 'Text').",
						".$mdb2->quote($exp_date, 'Date').",
						".$mdb2->quote($rem_reel, 'Text').",
						".$mdb2->quote($shit_no, 'Text').")");

	} else {

		// トランザクションブロック開始
		$res = $mdb2->beginTransaction();

		// 登録済み -> データ更新
		$res = $mdb2->exec("UPDATE reel_no SET due_date='$due_date' WHERE check_no='$check_no'");
		$res = $mdb2->exec("UPDATE reel_no SET r_seq='$r_seq' WHERE check_no='$check_no'");
		$res = $mdb2->exec("UPDATE reel_no SET r_total='$r_total' WHERE check_no='$check_no'");
		$res = $mdb2->exec("UPDATE reel_no SET solder='$solder' WHERE check_no='$check_no'");
		$res = $mdb2->exec("UPDATE reel_no SET product='$product' WHERE check_no='$check_no'");
		$res = $mdb2->exec("UPDATE reel_no SET p_maker='$p_maker' WHERE check_no='$check_no'");
		$res = $mdb2->exec("UPDATE reel_no SET maker='$maker' WHERE check_no='$check_no'");
		$res = $mdb2->exec("UPDATE reel_no SET qty='$qty' WHERE check_no='$check_no'");
		$res = $mdb2->exec("UPDATE reel_no SET lot_no='$lot_no' WHERE check_no='$check_no'");
		$res = $mdb2->exec("UPDATE reel_no SET exp_date='$exp_date' WHERE check_no='$check_no'");
		$res = $mdb2->exec("UPDATE reel_no SET rem_reel='$rem_reel' WHERE check_no='$check_no'");
		$res = $mdb2->exec("UPDATE reel_no SET shit_no='$shit_no' WHERE check_no='$check_no'");

		if ($end_date!=NULL) {
			$res = $mdb2->exec("UPDATE reel_no SET end_date='$end_date' WHERE check_no='$check_no'");
		}

		// トランザクションブロック終了
		$res = transaction_end($mdb2, $res);

	}

	$reel_no[$set_cnt] = $check_no;
	$set_cnt++;

}

// CSVファイルを閉じる
fclose($fp);

// 登録データ確認一覧表示
print('<div align="center"><font size="4" color="#0066cc"><b>');
print('部品リール管理番号 データ入力一覧 作成日：');
print(date("Y-m-d H:i"));
print('<br>');
print('</b></font></div>');

print('<table border="1" align=center>');

// 項目名
print('<tr bgcolor="#cccccc">');
print('<th>納入日</th>');
print('<th>伝票番号</th>');
print('<th>管理番号</th>');
print('<th>連</th>');
print('<th>総</th>');
print('<th>はんだ</th>');
print('<th>品名</th>');
print('<th>メーカー品名</th>');
print('<th>メーカー</th>');
print('<th>数量</th>');
print('<th>ロット</th>');
print('<th>使用期限</th>');
print('<th>使用完日</th>');
print('<th>備考</th>');

print('</tr>');

$set_cnt = count($reel_no) - 1;

for ($i=0; $i<=$set_cnt; $i++) {

	$res = $mdb2->queryRow("SELECT * FROM reel_no WHERE check_no='$reel_no[$i]'");
	if (PEAR::isError($res)) {
	} elseif ($res[0]!=NULL) {

		print('<tr>');

		print('<td>');
		print($res[1]);
		print('</td>');

		print('<td>');
		print($res[14]);
		print('</td>');

		print('<td>');
		print($res[2]);
		print('</td>');

		print('<td>');
		print($res[3]);
		print('</td>');

		print('<td>');
		print($res[4]);
		print('</td>');

		print('<td>');
		print(solder_name($res[5]));
		print('</td>');

		print('<td>');
		if ($res[6]!=NULL) {
			print($res[6]);
		} else {
			print('<br>');
		}
		print('</td>');

		print('<td>');
		if ($res[7]!=NULL) {
			print($res[7]);
		} else {
			print('<br>');
		}
		print('</td>');

		print('<td>');
		if ($res[8]!=NULL) {
			print($res[8]);
		} else {
			print('<br>');
		}
		print('</td>');

		print('<td>');
		if ($res[9]!=NULL) {
			print($res[9]);
		} else {
			print('<br>');
		}
		print('</td>');

		print('<td>');
		if ($res[10]!=NULL) {
			print($res[10]);
		} else {
			print('<br>');
		}
		print('</td>');

		print('<td>');
		if ($res[11]!=NULL) {
			print($res[11]);
		} else {
			print('<br>');
		}
		print('</td>');

		print('<td>');
		if ($res[12]!=NULL) {
			print($res[12]);
		} else {
			print('<br>');
		}
		print('</td>');

		print('<td>');
		if ($res[13]!=NULL) {
			print($res[13]);
		} else {
			print('<br>');
		}
		print('</td>');

		print('</tr>');

	}

}

print('</table></td>');

// 変数破棄
unset($reel_no);
unset($set_cnt);

?>
