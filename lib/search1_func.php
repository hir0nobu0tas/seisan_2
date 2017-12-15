<?php
//-----------------------------------------------------------------------------
//[生産管理システム]
//
// *データ検索[serch1.php]用外部関数
//
//  2007/12/10
//  2009/12/14
//
//-----------------------------------------------------------------------------

//---------------------------------------------------------------
// 初期設定
//---------------------------------------------------------------

function serch_disp() {

	// 2007/07/18 このブロック廃止(ブラウザへ一覧表示はスキップして直接Excel出力)
	$unit_id    = $_SESSION['id'];
	$operate_no = $_SESSION['operate_no'];
	$dash_no    = $_SESSION['dash_no'];
	$mf_qty     = $_SESSION['mf_qty'];
	$sort_sel   = $_SESSION['sort'];

	// DB接続
	$mdb2 = db_connect();

	if ($unit_id!=NULL) {

		$unit_row = $mdb2->queryRow("SELECT * FROM unit_data WHERE unit_id='$unit_id'");
		if (PEAR::isError($unit_row)) {
		} else {
			$u_index = $unit_row[1];
			$file_name = $unit_row[2];
			$board = $unit_row[3];
			$refix_date = $unit_row[5];
		}
		unset($unit_row);

		// 登録データ確認一覧表示
		print('<div align="center"><font size="4" color="#0066cc"><b>');
		print('ユニットデータ一覧 [');
		print($board);
		print('] 作成日：');
		print(date("Y-m-d H:i"));
		print('<br>');
		print('<a href="excel/excel_out1.php?unit_id=');
		print($unit_id);
		print('&file_name=');
		print($file_name);
		print('&board=');
		print($board);
		print('&index=');
		print($u_index);
		print('&operate_no=');
		print($operate_no);
		print('&dash_no=');
		print($dash_no);
		print('&mf_qty=');
		print($mf_qty);
		print('&refix_date=');
		print($refix_date);
		print('&sort_sel=');
		print($sort_sel);
		print('" target="result"><img src="../graphics/seisan_excel_out.png" border="0"></a>');
		print('</b></font></div>');

		print('<table border="2" align=center>');

		// 工番情報
		print('<tr bgcolor="#cccccc">');
		print('<th>工番</th>');
		print('<th>ダッシュ</th>');
		print('<th>数量</th>');
		print('<th>更新日</th>');
		print('</tr>');

		print('<tr>');

		print('<td>');
		print($operate_no);
		print('</td>');

		print('<td>');
		print($dash_no);
		print('</td>');

		print('<td>');
		print($mf_qty);
		print('</td>');

		print('<td>');
		print($refix_date);
		print('</td>');

		print('</tr>');
		print('</table></td>');

		print('<table border="1" align=center>');

		// 項目名
		print('<tr bgcolor="#cccccc">');
		print('<th>St</th>');
		print('<th>プログラム名</th>');
		print('<th>ステーション名</th>');
		print('<th>Set</th>');
		print('<th>穴番号</th>');
		print('<th>部品名</th>');
		print('<th>荷姿</th>');
		print('<th>数量</th>');
		print('<th>リール数</th>');
		print('<th>棚番</th>');
		print('<th>はんだ</th>');
		print('<th>旧棚番</th>');
		print('</tr>');

		$sql_select = "SELECT * FROM set_data T3 JOIN station_data T2 ON(T3.st_id=T2.st_id) JOIN unit_data T1 ON(T2.unit_id=T1.unit_id)";
//		$sql_where = " WHERE board='$board' AND u_index='$u_index'";
		$sql_where = " WHERE file_name='$file_name' AND u_index='$u_index'";
		$sql_order = " ORDER BY set_id";
		$search_sql = $sql_select . $sql_where . $sql_order;
		$res = $mdb2->query($search_sql);
		$res = err_check($res);

		$cnt = 0;
		while($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {

			// 部品データ検索
			$part[$cnt] = $row['part'];

			$qty_row = $mdb2->queryRow("SELECT * FROM qty_data WHERE unit_id='$unit_id' AND q_part='$part[$cnt]'");
			if (PEAR::isError($qty_row)) {
			} else {
				$q_part = $qty_row[5];
				$qty = $qty_row[6];
				$reel = $qty_row[7];
			}
			unset($qty_row);

			// 重複品名の処理
			$key = array_search($q_part, $part);
			if ($key!=$cnt) {
				$qty = '--';
				$reel = '--';
			}
			$cnt++;

			print('<tr>');

			print('<td>');
			print($row['st_index']);
			print('</td>');

			print('<td>');
			print($row['p_name']);
			print('</td>');

			print('<td>');
			print($row['s_name']);
			print('</td>');

			print('<td>');
			print($row['set_index']);
			print('</td>');

			print('<td>');
			print($row['hole_no']);
			print('</td>');

			print('<td>');
			print($row['part']);
			print('</td>');

			print('<td>');
			print($row['packing']);
			print('</td>');

			print('<td>');
			print($qty * $mf_qty);
			print('</td>');

			print('<td>');
			print($reel);
			print('</td>');

			// 棚番検索(%を付けてLike検索)
			$part_tmp =  '%' . $row['part'] . '%';
			$sql[1] = "SELECT * FROM smt_part";
			$sql[2] = " WHERE product like '$part_tmp'";
			$sql[3] = " OR p_res like '$part_tmp'";
			$sql[4] = " OR p_new like '$part_tmp'";
			$sql[5] = " OR p_maker like '$part_tmp'";
			$sql[6] = " OR p_nec like '$part_tmp'";
			$sql[0] = $sql[1] . $sql[2] . $sql[3] . $sql[4] . $sql[5] . $sql[5];
//			$rs_part = $mdb2->query($sql[0]);
//
//			$i = 0;
//			while($row = $rs_part->fetchRow(MDB2_FETCHMODE_ASSOC)) {
//				$solder[$i] = $row['solder'];
//				$rack_no[$i] = $row['rack_no'];
//				$rack_old[$i] = $row['rack_old'];
//				$i++;
//			}
			$part_row = $mdb2->queryRow($sql[0]);
			if (PEAR::isError($part_row)) {
			} else {
				$rack_no = $part_row[1];
				$solder = $part_row[3];
				$rack_old = $part_row[10];
			}
			unset($part_row);
			unset($sql);

			print('<td>');
			if ($rack_no==NULL) {
				print('<br>');
			} else {
				print($rack_no);
			}
			print('</td>');

			print('<td>');
			if ($solder==NULL) {
				print('<br>');
			} else {
				print(solder_name($solder));
			}
			print('</td>');

			print('<td>');
			if ($rack_old==NULL) {
				print('<br>');
			} else {
				print($rack_old);
			}
			print('</td>');

			unset($rack_no);
			unset($solder);
			unset($rack_old);

		}

		print('</tr>');
		print('</table></td>');

	}

	// DB切断
	db_disconnect($mdb2);

	$_SESSION["mode_search1"]='search';

}


?>
