<?php
//-------------------------------------------------------------------
// disp1_inc1.php
// 生産計画 使用部品集計 一覧表示
//
// 2009/08/28
//
//-------------------------------------------------------------------

print('<hr style="width: 100%; height: 2px;">');

print('<table border="1" align=center>');
print('<caption>');
print('<div align="center"><font size="4" color="#0066cc"><b>');
print('払い出しリール 管理 [');
print($process_disp);
print('] [');
print($mc_disp);
print('] 作成日：');
print(date("Y-m-d H:i"));
print('<br>');
print('<a href="excel/excel_out6.php?sel_process=');
print($sel_process);
print('&set_date=');
print($set_date);
print('" target="result"><img src="../graphics/seisan_excel_out.png" border="0"></a>');
print('</b></font></div>');
print('</caption>');

// 項目名
print('<tr bgcolor="#cccccc">');
print('<th>払い出し日</th>');
print('<th>返却日</th>');
print('<th>M/C担当</th>');
print('<th>管理番号</th>');
print('<th>品名</th>');
print('<th>はんだ</th>');
print('<th>棚番</th>');
print('<th>備考</th>');

if ($set_date!=NULL) {

	// SQL文 設定
	$list_sql = list_sql_set($sel_process, $set_date);

//print('<pre>');
//Var_Dump($list_sql);
//print('</pre>');

	$res_query = $mdb2->query($list_sql);
	$res_query = err_check($res_query);

	$reel_cnt = 0;

	while($row = $res_query->fetchRow(MDB2_FETCHMODE_ASSOC)) {

		$reel_id    = $row['reel_id'];
		$sel_mc     = $row['sel_mc'];
		$out_date   = $row['out_date'];
		$ret_date   = $row['ret_date'];
		$rem_manage = $row['rem_manage'];

		$mc_disp = mc_name($sel_mc);

		$reel_row = $mdb2->queryRow("SELECT * FROM reel_no WHERE reel_id='$reel_id'");
		if (PEAR::isError($reel_row)) {
		} elseif ($reel_row[0]!=NULL) {
			$check_no = $reel_row[2];
			$product  = $reel_row[6];
			$solder   = $reel_row[5];
	//		$r_seq    = $reel_row[3];
	//		$r_total  = $reel_row[4];
	//		$qty      = $reel_row[9];
		}
		unset($reel_row);

		$part_row = $mdb2->queryRow("SELECT * FROM part_smt WHERE product='$product'");
		if (PEAR::isError($part_row)) {
		} elseif ($part_row[0]!=NULL) {
			$rack_old = $part_row[10];
		}
		unset($part_row);

		print('<tr>');

		print('<td>');
		print($out_date);
		print('</td>');

		print('<td>');
		if ($ret_date!=NULL) {
			print($ret_date);
		} else {
			print('<br>');
		}
		print('</td>');

		print('<td>');
		print($mc_disp);
		print('</td>');

		print('<td>');
		print($check_no);
		print('</td>');

		print('<td>');
		print($product);
		print('</td>');

		print('<td>');
		print(solder_name($solder));
		print('</td>');

		print('<td>');
		if ($rack_old!=NULL) {
			print($rack_old);
		} else {
			print('<br>');
		}
		print('</td>');

		print('<td>');
		if ($rem_manage!=NULL) {
			print($rem_manage);
		} else {
			print('<br>');
		}
		print('</td>');

		print('</tr>');

		// リール数カウント
		$reel_cnt++;

	}

	print('<tr>');

	// 集計行表示
	for ($m=1; $m<=5; $m++) {
		print('<td bgcolor="#b0c4de"><br></td>');
	}

	print('<td bgcolor="#ccffcc">計</td>');

	print('<td bgcolor="#ffcc99">');
	print($reel_cnt);
	print('</td>');

	print('<td bgcolor="#b0c4de">巻</td>');

	print('</tr>');
}

print('</table></td>');
print('</form>');

?>
