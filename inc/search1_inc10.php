<?php
//-------------------------------------------------------------------
// search1_inc10.php
// リール管理番号 検索
// $search_no = 10
//
// 2008/09/
// 2009/12/17 表示フォーマット修正 未入力で検索時のエラー対応
// 2010/05/31 備考の表示を修正
// 2016/03/10 使用完日の表示追加など
// 2016/05/24 リールの伝票番号の追加
//
//-------------------------------------------------------------------

print('<table border="1" align=center width="95%">');
print('<caption>');
print('<div align="center"><font size="4" color="#0066cc"><b>');
print('[ リール管理番号 ] => ');
print('[ ');
print($search_para);
print(' ]');
print('</b></font></div>');
print('</caption>');

print('<tr bgcolor="#cccccc">');
print('<th>納入日</th>');
print('<th>伝票番号</th>');
print('<th>管理番号</th>');
print('<th>品名</th>');
//print('<th>メーカー品名</th>');
print('<th>メーカー</th>');
print('<th>はんだ</th>');
print('<th>連番</th>');
print('<th>総数</th>');
print('<th>数量</th>');
print('<th>ロット</th>');
print('<th>使用期限</th>');
print('<th>使用完</th>');
print('<th>備考</th>');
print('</tr>');

if ($search_para!=NULL) {

	$res_query = $mdb2->query($search_sql);
	$res_query = err_check($res_query);

	$bgcolor = '#dcdcdc';
	while($row = $res_query->fetchRow(MDB2_FETCHMODE_ASSOC)) {

		$check_no = $row['check_no'];
		$r_seq    = $row['r_seq'];
		$r_total  = $row['r_total'];
		$due_date = $row['due_date'];
		$product  = $row['product'];
		$p_maker  = $row['p_maker'];
		$maker    = $row['maker'];
		$solder   = $row['solder'];
		$qty      = $row['qty'];
		$lot_no   = $row['lot_no'];
		$exp_date = $row['exp_date'];
		$end_date = $row['end_date'];
		$rem_reel = $row['rem_reel'];
		$shit_no  = $row['shit_no'];

		// 列ごとに色を変える(PBFはまた別の色で)
		if ($row['solder']==2) {
			$bgcolor = '#ffe4e1';
		} else {
			if ($bgcolor=='#ffe4e1') {
				$bgcolor = '#dcdcdc';
			} elseif ($bgcolor=='#dcdcdc') {
				$bgcolor = '#f8f8ff';
			} elseif ($bgcolor=='#f8f8ff') {
				$bgcolor = '#dcdcdc';
			}
		}

		$color_set = "<td bgcolor=\"" . $bgcolor . "\">";

		print('<tr>');

		print($color_set);
		print($due_date);
		print('</td>');

		print($color_set);
		print($shit_no);
		print('</td>');

		print($color_set);
		print($check_no);
		print('</td>');

		print($color_set);
		print($product);
		print('</td>');

		print($color_set);
		if ($maker!=NULL) {
			print($maker);
		} else {
			print('<br>');
		}
		print('</td>');

		print($color_set);
		print(solder_name($solder));
		print('</td>');

		print($color_set);
		print($r_seq);
		print('</td>');

		print($color_set);
		print($r_total);
		print('</td>');

		print($color_set);
		print($qty);
		print('</td>');

		print($color_set);
		if ($lot_no!=NULL) {
			print($lot_no);
		} else {
			print('<br>');
		}
		print('</td>');

		print($color_set);
		print($exp_date);
		print('</td>');

		// 2016/03/10追加
		print($color_set);
		print($end_date);
		print('</td>');

		print($color_set);
		if ($rem_reel!=NULL) {
			print($rem_reel);
		} else {
			print('<br>');
		}
		print('</td>');

//		if ($permission=='admin') {
//			print($color_set);
//			$btn1 = "<a href='./pdf/pdf_out1.php?order_id=";
//			$btn1 = $btn1 . $order_id ."&st_id=";
//			$btn1 = $btn1 . $st_id[$i];
//			$btn1 = $btn1 . "' target='result'><img src='../graphics/seisan_pdf.png' border='0'></a></td>";
//			print($btn1);
//		}

		print('</tr>');

	}

}

?>
