<?php
//-------------------------------------------------------------------
// regist1_inc6.php
// 棚番データ登録
//
// 2007-09-25 実装
// 2007-10-12 登録と更新を一本化
// 2007-12-03 Excel版棚番マスタと整合させる為の処理追加(1回のみ使用)
// 2007-12-04 整合処理(多分1回のみ使用)
// 2007-12-05 更新処理修正(削除処理等追加)
// 2007-12-11 リールサイズと高額品を文字列から数値へ(型はStringのまま)
// 2007-12-26 表示順を変更
// 2008-03-13 MW500出荷履歴管理を分けたのでデータディレクトリ修正
// 2008-04-14 抽出→修正した[part_smt]のデータのそのまま再登録するように修正
// 2009-02-06 CBE品名のインポート用に修正
// 2009-03-18 アドバンス向け部品[part_advance]へのインポート用に修正
// 2009-07-08 アドバンス向け部品[part_advance]のテーブル定義変更対応
// 2010-02-16 アドバンス向け部品[part_advance]へデータ追加対応
//
// 2015-10-05 新棚番の更新処理作成開始
// 2015-10-14 登録処理修正
// 2015-10-29 新棚番対応
// 2015-11-05 CBE品名の登録処理を追加
//
//
//-------------------------------------------------------------------

$csv_file = "../data/rack/" . $txt_file;

// 更新後の確認用
$up_cnt = 0;

$fp = fopen($csv_file, "r");

// SMT部品データ[part_smt]へ格納
while (($data=fgetcsv($fp, 10000, ","))!==FALSE) {

	// Trimによる前後スペース削除
	for ($i=0; $i<=7; $i++) {
		$data[$i] = Trim($data[$i]);
	}

	$rack_old  = $data[0];
	$rack_no   = $data[1];
	$product   = $data[2];
	$p_nec     = $data[3];
	$solder    = $data[4];

	if ($data[5]=='中') {
		$reel_size = '0';
	} elseif ($data[5]=='小') {
		$reel_size = '1';
	} elseif ($data[5]=='大') {
		$reel_size = '2';
	} elseif ($data[5]=='特大') {
		$reel_size = '3';
	} elseif ($data[5]=='千代田') {
		$reel_size = '4';
	} elseif ($data[5]=='防湿庫' or $data[4]=='乾燥庫') {
		$reel_size = '5';
	} else {
		$reel_size = '0';
	}

	$rem_part  = $data[6];
	$rem_rack  = $data[7];

	// 旧棚番、品名で検索
	// 2015/10/16 新棚番の登録処理で登録漏れがあったので仮に旧棚番だけで検索へ変更
	//$res = $mdb2->queryRow("SELECT * FROM part_smt WHERE product='$product' AND rack_old='$rack_old'");
	$res = $mdb2->queryRow("SELECT * FROM part_smt WHERE rack_old='$rack_old'");
	if (PEAR::isError($res)) {
	} elseif ($res[0]!=NULL) {

		$smt_id = $res[0];

//print('<pre>');
//var_dump($smt_id);
//print('</pre>');

		// トランザクションブロック開始
		$res = $mdb2->beginTransaction();

		// 棚番有り(登録済み) -> データ更新
		if ($rack_no!=NULL) {
			$res = $mdb2->exec("UPDATE part_smt SET rack_no='$rack_no' WHERE smt_id='$smt_id'");
		}

		if ($p_nec!=NULL) {
			$res = $mdb2->exec("UPDATE part_smt SET p_nec='$p_nec' WHERE smt_id='$smt_id'");
		}

		if ($reel_size!=NULL) {
			$res = $mdb2->exec("UPDATE part_smt SET r_size='$reel_size' WHERE smt_id='$smt_id'");
		}

		if ($rem_rack!=NULL) {
			$res = $mdb2->exec("UPDATE part_smt SET rem_rack='$rem_rack' WHERE smt_id='$smt_id'");
		}

		// トランザクションブロック終了
		$res = transaction_end($mdb2, $res);

		$rack_old  = '';
		$rack_no   = '';
		$product   = '';
		$p_nec     = '';
		$solder    = '';
		$reel_size = '';
		$rem_part  = '';
		$rem_rack  = '';

	 }

	 // 2007/10/04 新棚番は仮番の場合があるので一時的に旧棚番で処理
	 //	$up_no[$up_cnt] = $rack_no;
	 //	$up_no[$up_cnt] = $rack_old;
	 $up_no[$up_cnt] = $smt_id;
	 $up_cnt++;


/*
	// Trimによる前後スペース削除
	for ($i=0; $i<=10; $i++) {
		$data[$i] = Trim($data[$i]);
	}

	$rack_no  = $data[0];
	$product  = $data[1];
	$solder   = $data[2];
	$p_new    = $data[3];
	$p_maker  = $data[4];
	$p_sub    = $data[5];
	$p_nec    = $data[6];
	$r_size   = $data[7];
	$exp_item = $data[8];
	$rack_old = $data[9];
	$rem_part = $data[10];

	// 品名、はんだ、旧棚番で検索
	$res = $mdb2->queryRow("SELECT * FROM part_smt WHERE product='$product' AND solder='$solder' AND rack_old='$rack_old'");
	if (PEAR::isError($res)) {
	} elseif ($res[0]==NULL) {

		// 2007/12/05 新棚番の入力がない場合の仮処理
		if ($rack_no==NULL) {
			if ($solder==1) {
				$rack_no = 'BESXXXXXN';
			} elseif ($solder==2) {
				$rack_no = 'BESXXXXXR';
			} elseif ($solder==0 or $solder==3) {
				$rack_no = 'BESXXXXX';
			}
		}

		// 2007/12/11 リールサイズと高額品を文字列から数値へ
		if ($r_size=='大') {
			$r_size = 2;
		} elseif ($r_size=='小') {
			$r_size = 1;
		} else {
			$r_size = 0;
		}

		if ($exp_item=='高額' or $exp_item=='高額品') {
			$exp_item = 1;
		} else {
			$exp_item = 0;
		}

		// 棚番無し(未登録) -> 追加登録
		if ($product!=NULL) {
			$res = $mdb2->query("INSERT INTO part_smt(rack_no, product, solder, p_new, p_maker, p_sub, p_nec, r_size, exp_item, rack_old, rem_part) VALUES(
							".$mdb2->quote($rack_no, 'Text').",
							".$mdb2->quote($product, 'Text').",
							".$mdb2->quote($solder, 'Text').",
							".$mdb2->quote($p_new, 'Text').",
							".$mdb2->quote($p_maker, 'Text').",
							".$mdb2->quote($p_sub, 'Text').",
							".$mdb2->quote($p_nec, 'Text').",
							".$mdb2->quote($r_size, 'Text').",
							".$mdb2->quote($exp_item, 'Text').",
							".$mdb2->quote($rack_old, 'Text').",
							".$mdb2->quote($rem_part, 'Text').")");

			// 登録した[smt_id]の取得
			$res = $mdb2->queryRow("SELECT * FROM part_smt WHERE product='$product' AND solder='$solder' AND rack_old='$rack_old'");
			if (PEAR::isError($res)) {
			} elseif ($res[0]!=NULL) {
				$smt_id = $res[0];
			}

		}

	} else {

		$smt_id = $res[0];

		// 2007/12/05 備考が[Delete]なら登録削除 それ以外はデータ更新
		if ($rem_part=='Delete') {

			$res = $mdb2->exec("DELETE FROM part_smt WHERE smt_id='$smt_id'");

		} else {

			// トランザクションブロック開始
			$res = $mdb2->beginTransaction();

			// 2007/12/11 リールサイズと高額品を文字列から数値へ
			if ($r_size=='大') {
				$r_size = 2;
			} elseif ($r_size=='小') {
				$r_size = 1;
			} else {
				$r_size = 0;
			}

			if ($exp_item=='高額' or $exp_item=='高額品') {
				$exp_item = 1;
			} else {
				$exp_item = 0;
			}

			// 棚番有り(登録済み) -> データ更新
			if ($rack_no!=NULL) {
				$res = $mdb2->exec("UPDATE part_smt SET rack_no='$rack_no' WHERE smt_id='$smt_id'");
			}

			$res = $mdb2->exec("UPDATE part_smt SET p_new='$p_new' WHERE smt_id='$smt_id'");
			$res = $mdb2->exec("UPDATE part_smt SET p_maker='$p_maker' WHERE smt_id='$smt_id'");
			$res = $mdb2->exec("UPDATE part_smt SET p_sub='$p_sub' WHERE smt_id='$smt_id'");
			$res = $mdb2->exec("UPDATE part_smt SET p_nec='$p_nec' WHERE smt_id='$smt_id'");

			if ($r_size!=NULL) {
				$res = $mdb2->exec("UPDATE part_smt SET r_size='$r_size' WHERE smt_id='$smt_id'");
			}

			if ($exp_item!=NULL) {
				$res = $mdb2->exec("UPDATE part_smt SET exp_item='$exp_item' WHERE smt_id='$smt_id'");
			}

			if ($rem_part!=NULL) {
				$res = $mdb2->exec("UPDATE part_smt SET rem_part='$rem_part' WHERE smt_id='$smt_id'");
			}

			// トランザクションブロック終了
			$res = transaction_end($mdb2, $res);

		}

	}

	// 2007/10/04 新棚番は仮番の場合があるので一時的に旧棚番で処理
//	$up_no[$up_cnt] = $rack_no;
//	$up_no[$up_cnt] = $rack_old;
	$up_no[$up_cnt] = $smt_id;
	$up_cnt++;

*/


/*
	// Trimによる前後スペース削除
	for ($i=0; $i<=11; $i++) {
		$data[$i] = Trim($data[$i]);
	}

	$smt_id   = $data[0];
	$rack_no  = $data[1];
	$product  = $data[2];
	$solder   = $data[3];
	$p_new    = $data[4];
	$p_maker  = $data[5];
	$p_sub    = $data[6];
	$p_nec    = $data[7];
	$r_size   = $data[8];
	$exp_item = $data[9];
	$rack_old = $data[10];
	$rem_part = $data[11];


	// トランザクションブロック開始
	$res = $mdb2->beginTransaction();

	$res = $mdb2->exec("UPDATE part_smt SET exp_item='$exp_item' WHERE rack_old='$rack_old'");
	$res = $mdb2->exec("UPDATE part_smt SET rem_part='$rem_part' WHERE rack_old='$rack_old'");

	// トランザクションブロック終了
	$res = transaction_end($mdb2, $res);
*/

/*
	// CBE品名 登録処理
	// Trimによる前後スペース削除
	for ($i=0; $i<=1; $i++) {
		$data[$i] = Trim($data[$i]);
	}

	$p_nec   = $data[0];
	$product = $data[1];

	$res = $mdb2->exec("UPDATE part_smt SET p_nec='$p_nec' WHERE product='$product'");
*/

/*
	// アドバンス向け部品 棚番登録処理
	// 2009-07-08 品名追加(5種まで登録) Trimによる前後スペース削除
	// 2010-02-16 部品番号検索による追加登録処理追加(登録有り→そのまま、登録無し→追加登録)
	for ($i=0; $i<=7; $i++) {
		$data[$i] = Trim($data[$i]);
	}

	$rack_no     = $data[0];
	$part_no     = $data[1];
	$part_1      = $data[2];
	$part_2      = $data[3];
	$part_3      = $data[4];
	$part_4      = $data[5];
	$part_5      = $data[6];
	$rem_advance = $data[7];

	$res = $mdb2->queryRow("SELECT * FROM part_advance WHERE part_no='$part_no'");
	if (PEAR::isError($res)) {
	} else {
		$advance_id = $res[0];
	}
*/

//print('<pre>');
//var_dump($advance_id);
//print('</pre>');

/*
	if ($advance_id==NULL) {

		$rem_advance = date("Y-m-d") . " 追加 " . $rem_advance;

		$res = $mdb2->query("INSERT INTO part_advance(rack_no, part_no, part_1, part_2, part_3, part_4, part_5, rem_advance) VALUES(
								".$mdb2->quote($rack_no, 'Text').",
								".$mdb2->quote($part_no, 'Text').",
								".$mdb2->quote($part_1, 'Text').",
								".$mdb2->quote($part_2, 'Text').",
								".$mdb2->quote($part_3, 'Text').",
								".$mdb2->quote($part_4, 'Text').",
								".$mdb2->quote($part_5, 'Text').",
								".$mdb2->quote($rem_advance, 'Text').")");
	}
*/

}

// CSVファイルを閉じる
fclose($fp);

/*
// 登録データ確認一覧表示
print('<div align="center"><font size="4" color="#0066cc"><b>');
print('棚番データ入力一覧 作成日：');
print(date("Y-m-d H:i:s"));
print('<br>');
print('</b></font></div>');

print('<table border="1" align=center>');

// 項目名
print('<tr bgcolor="#cccccc">');
print('<th>棚番ID</th>');
print('<th>現棚番</th>');
print('<th>品名</th>');
print('<th>はんだ</th>');
print('<th>新品名</th>');
print('<th>メーカー品名</th>');
print('<th>サブ品名</th>');
print('<th>NEC品名</th>');
print('<th>リールサイズ</th>');
print('<th>高額品</th>');
print('<th>新棚番</th>');
print('<th>備考</th>');
print('</tr>');

$up_cnt = count($up_no) - 1;

// 2007/12/05 20件以上の場合、最初の20件のみ表示
if ($up_cnt>20) {
	$up_cnt = 20;
}

for ($i=0; $i<=$up_cnt; $i++) {

	// 2007/10/04 新棚番は仮番の場合があるので一時的に旧棚番で処理
//	$res = $mdb2->queryRow("SELECT * FROM part_smt WHERE rack_no='$up_no[$i]'");
//	$res = $mdb2->queryRow("SELECT * FROM part_smt WHERE rack_old='$up_no[$i]'");
	$res = $mdb2->queryRow("SELECT * FROM part_smt WHERE smt_id='$up_no[$i]'");
	if (PEAR::isError($res)) {
	} elseif ($res[0]!=NULL) {

		print('<tr>');

		print('<td>');
		print($res[0]);
		print('</td>');

		print('<td>');
		print($res[10]);
		print('</td>');

		print('<td>');
		print($res[2]);
		print('</td>');

		print('<td>');
		if ($res[3]!=NULL) {
			print($res[3]);
		} else {
			print('<br>');
		}
		print('</td>');

		print('<td>');
		if ($res[4]!=NULL) {
			print($res[4]);
		} else {
			print('<br>');
		}
		print('</td>');

		print('<td>');
		if ($res[5]!=NULL) {
			print($res[5]);
		} else {
			print('<br>');
		}
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
		switch ($res[8]) {
		case 0:
			print('<br>');
			break;
		case 1:
			print('小');
			break;
		case 2:
			print('大');
			break;
		}
		print('</td>');

		print('<td>');
		switch ($res[9]) {
		case 0:
			print('<br>');
			break;
		case 1:
			print('高額');
			break;
		}
		print('</td>');

		print('<td>');
		print($res[1]);
		print('</td>');

		print('<td>');
		if ($res[11]!=NULL) {
			print($res[11]);
		} else {
			print('<br>');
		}
		print('</td>');

		print('</tr>');

	}

}

print('</table></td>');

// 変数破棄
unset($up_no);
unset($up_cnt);

*/
?>
