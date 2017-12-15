<?php
//-------------------------------------------------------------------
// search1_inc4.php
// 基板名検索(登録データ)
// $search_no = 4
//
// 2007/07/
//
//-------------------------------------------------------------------

print('<table border="1" align=center>');
print('<caption>');
print('<div align="center"><font size="4" color="#0066cc"><b>');
print('[ ');
print($search);
print(' ] => ');
print('[ ');
print($parameter);
print(' ]');
print('</b></font></div>');
print('</caption>');

print('<tr bgcolor="#cccccc">');
print('<th>Unit_ID</th>');
print('<th>ファイル</th>');
print('<th>基板ID</th>');
print('<th>面</th>');
//print('<th>Index</th>');
print('<th>プログラム名</th>');
print('<th>ステーション名</th>');
print('</tr>');

//
if ($unit_id!=NULL) {

	$cnt_unit = count($unit_id) - 1;
	$bgcolor = '#dcdcdc';

	for ($i=0; $i<=$cnt_unit; $i++) {
		$unit_row = $mdb2->queryRow("SELECT * FROM unit_data WHERE unit_id='$unit_id[$i]'");
		$unit_data[$i][0] = $unit_row[0];
		$unit_data[$i][1] = $unit_row[1];
		$unit_data[$i][2] = $unit_row[2];
		$unit_data[$i][3] = $unit_row[3];
		$unit_data[$i][4] = $unit_row[4];

		// [station_data] 取得
		$rs_st = $mdb2->query("SELECT * FROM station_data WHERE unit_id='$unit_id[$i]'");
		$st_cnt = 0;
		while($row = $rs_st->fetchRow(MDB2_FETCHMODE_ASSOC)) {
			$st_data[$i][0][$st_cnt] = $row['st_id'];
			$st_data[$i][1][$st_cnt] = $row['st_index'];
			$st_data[$i][2][$st_cnt] = $row['p_name'];
			$st_data[$i][3][$st_cnt] = $row['s_name'];
			$st_cnt++;
		}

		// 列ごとに色を変える(PBFはまた別の色で)
		if ($bgcolor == '#dcdcdc') {
			$bgcolor = '#f8f8ff';
		} elseif ($bgcolor == '#f8f8ff') {
			$bgcolor = '#dcdcdc';
		}

		$color_set = "<td bgcolor=\"" . $bgcolor . "\">";

		print('<tr>');

		// ユニットID
		print($color_set);
		print($unit_data[$i][0]);
		print('</td>');

		// 基板名
		print($color_set);
		print($unit_data[$i][2]);
		print('</td>');

		// 製品名
		print($color_set);
		if ($unit_data[$i][3]==NULL) {
			print('<br>');
		} else {
			print($unit_data[$i][3]);
		}
		print('</td>');

		// 面
		print($color_set);
		switch ($unit_data[$i][1]) {
		case 1:
			print('表1');
			break;
		case 2:
			print('裏1');
			break;
		case 3:
			print('表2');
			break;
		case 4:
			print('裏2');
			break;
		}
		print('</td>');

//		// ステーションインデックス
//		print($color_set);
//		print($st_data[$i][1][0]);
//		print('</td>');

		// プログラム名
		print($color_set);
		print($st_data[$i][2][0]);
		print('</td>');

		// ステーション名
		print($color_set);
		print($st_data[$i][3][0]);
		print('</td>');

		print($color_set);
		$btn1 = "<input type='button' name='edit' onClick=\"location.href='excel/excel_out2.php?id=";
		$btn1 = $btn1 . $unit_data[$i][0] . "'\" value='Excel出力'></td>";
		print($btn1);

		print('</tr>');

	}

}




?>
