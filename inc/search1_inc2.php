<?php
//-------------------------------------------------------------------
// search1_inc2.php
// 部品名→棚番 検索
// $search_no = 2
//
// 2007/08
//
// 2007/09/26 [part_smt]テーブル小変更対応
//
// 2007/11/06 部品リールの使用完表示復活 ある程度使用完登録が進むまでは
//            参考用に使用完日がわかるようにする
//
// 2007/12/11 リールサイズと高額品を文字列から数値へ(型はStringのまま)
//
// 2007/12/21 新棚番と旧棚番の表示位置入替え(実質、新棚番では運用出来ていない)
//
// 2007/12/25 試験的に新品名、サブ品名、NEC品名をコメントアウト(峯Sより)
//            今後基本的に品名(資材伝票品名)とメーカー品名で管理する
//
// 2008/06/24 現場からの要望でSMT部品データ[part_smt]に棚番備考[rem_rack]追加
//
// 2008/06/30 リール払い出しコメント追加
//
// 2009/07/08 アドバンス専用棚[part_advance]に対応
//
// 2009/08/07 SMT準備[arrange]ユーザ追加対応(アドバンス新規品追加と部品検索の編集)
//
// 2009/08/08 テーブルの幅を95%で指定追加
//
// 2009/10/15 在庫数表示を追加(仮運用中)
//
// 2009/10/16 在庫数がマイナスの場合背景色を替える
//
// 2009/10/20 在庫数の編集機能追加
//
// 2009/12/02 在庫数のコメントを修正
//
// 2011/05/19 備考(棚番)の表示を復活
//
// 2015/10/06 表示フォーマット変更
//
// 2016/01/08 部品定数(p_constant)の表示追加
//
// 2016/02/01 高額品は検索→棚番のセル色をオレンジ(gold)へ変更
//
// 2016/05/24 リールの伝票番号の追加
//
// 2017/02/21 買取部材[CR]及び[MW]を備考(棚番)を一旦全てクリアして入力したので該当する場合はセル色を赤へ
//
// 2017/08/08 リール管理番号リストのソート順を降順にして残リールをわかりやすく
//
//-------------------------------------------------------------------

if ($sel_rack=='0') {

	// 日東S品棚
	print('<table border="1" align=center width="95%">');
	print('<caption>');
	print('<div align="center"><font size="4" color="#0066cc"><b>');
	print('[ 部品検索( ');
	print($parameter);
	print(' ) ] => ');
	print('[ 棚番 ]');
	print('</b></font></div>');
	print('</caption>');

	print('<tr bgcolor="#cccccc">');
	print('<th>新棚番</th>');
	print('<th>旧棚番</th>');
	print('<th>品名</th>');
	print('<th>はんだ</th>');
	print('<th>メーカー品名</th>');
	// 2016-01-08追加
	print('<th>定数</th>');
	print('<th>サイズ</th>');
	//print('<th>高額品</th>');
	print('<th>備考(部品)</th>');
	print('<th>備考(棚番)</th>');
	print('</tr>');

	//
	if ($smt_id!=NULL) {

		$cnt_smt = count($smt_id) - 1;
//		$bgcolor = '#dcdcdc';

		for ($i=0; $i<=$cnt_smt; $i++) {

			$part_row = $mdb2->queryRow("SELECT * FROM part_smt WHERE smt_id='$smt_id[$i]'");
			if (PEAR::isError($part_row)) {
			} else {
				$part_data[$i][0]  = $part_row[0];	// SMT部品ID
				$part_data[$i][1]  = $part_row[1];	// 新棚番
				$part_data[$i][2]  = $part_row[10];	// 旧棚番
				$part_data[$i][3]  = $part_row[2];	// 品名
				$part_data[$i][4]  = $part_row[3];	// はんだ
				$part_data[$i][5]  = $part_row[4];	// 新品名
				$part_data[$i][6]  = $part_row[5];	// メーカー品名
				$part_data[$i][7]  = $part_row[6];	// サブ品名
				$part_data[$i][8]  = $part_row[7];	// NEC品名
				$part_data[$i][9]  = $part_row[8];	// リールサイズ
				$part_data[$i][10] = $part_row[9];	// 高額品
				$part_data[$i][11] = $part_row[1];	// 新棚番
				$part_data[$i][12] = $part_row[11];	// 備考
				$part_data[$i][13] = $part_row[12];	// 棚番備考
				$part_data[$i][14] = $part_row[13];	// 2016-01-08追加 部品定数
			}

			// 列ごとに色を変える
//			if ($bgcolor == '#dcdcdc') {
//				$bgcolor = '#f8f8ff';
//			} elseif ($bgcolor == '#f8f8ff') {
//				$bgcolor = '#dcdcdc';
//			}
//
//			$color_set = "<td bgcolor=\"" . $bgcolor . "\">";
			//$color_set = "<td bgcolor=#f8f8ff>";

            // 2017/02/21 買取部材はセル色(赤 tomato)へ変更
            if ($part_data[$i][13]=='2017-02 部材[CR]' or $part_data[$i][13]=='2017-02 部材[MW]') {
				$color_set = "<td bgcolor=#ff6347>";
            } else {
                // 2016/01/29 高額品のセル色(gold)へ変更
    			switch ($part_data[$i][10]) {
    				case 0:
    					$color_set = "<td bgcolor=#f8f8ff>";
    					break;
    				case 1:
    					$color_set = "<td bgcolor=#ffd700>";
    					break;
    			}
            }
			print('<tr>');

			// 新棚番
			print($color_set);
			if ($part_data[$i][1]==NULL) {
				print('<br>');
			} else {
				print($part_data[$i][1]);
			}
			print('</td>');

			// 旧棚番
			print($color_set);
			if ($part_data[$i][2]==NULL) {
				print('<br>');
			} else {
				print($part_data[$i][2]);
			}
			print('</td>');

			// 品名
			print($color_set);
			print($part_data[$i][3]);
			print('</td>');

			// はんだ
			print($color_set);
			print(solder_name($part_data[$i][4]));
			print('</td>');

			// メーカー品名
			print($color_set);
			if ($part_data[$i][6]==NULL) {
				print('<br>');
			} else {
				print($part_data[$i][6]);
			}
			print('</td>');

			// 部品定数
			print($color_set);
			if ($part_data[$i][14]==NULL) {
				print('<br>');
			} else {
				print($part_data[$i][14]);
			}
			print('</td>');

			// サイズ
			print($color_set);
			print(r_size_name($part_data[$i][9]));
			print('</td>');

			// 高額品
/* 			print($color_set);
			switch ($part_data[$i][10]) {
			case 0:
				print('<br>');
				break;
			case 1:
				print('高額');
				break;
			}
			print('</td>');
 */
			// 備考(部品)
			print($color_set);
			if ($part_data[$i][12]==NULL) {
				print('<br>');
			} else {
				print($part_data[$i][12]);
			}
			print('</td>');

			// 備考(棚番)
			print($color_set);
			if ($part_data[$i][13]==NULL) {
				print('<br>');
			} else {
				print($part_data[$i][13]);
			}
			print('</td>');

//			print($color_set);
//			$btn1 = "<input type='button' name='edit' onClick=\"location.href='excel/excel_out2.php?id=";
//			$btn1 = $btn1 . $part_data[$i][0] . "'\" value='Excel出力'></td>";
//			print($btn1);

			if ($permission=='admin' or $permission=='setup') {

				print($color_set);
				$btn1 = "<input type='button' name='edit' onClick=\"location.href='search1.php?mode_search1=edit&eid=";
				$btn1 = $btn1 . $part_data[$i][0] . "'\" value='編集'></td>";
				print($btn1);

				print($color_set);
				$btn2 = "<input type='button' name='edit' onClick=\"location.href='search1.php?mode_search1=del&did=";
				$btn2 = $btn2 . $part_data[$i][0] . "'\" value='削除'></td>";
				print($btn2);

			}

			print('</tr>');
		}
	}
	print('</table>');
	print('<br>');


	print('<table border="1" align=center width="95%">');
	print('<caption>');
	print('<div align="center"><font size="4" color="#0066cc"><b>');
	print('[ 部品検索( ');
	print($parameter);
	print(' ) ] => ');
	print('[ 在庫数(* 参考数量 *) ]');
	print('</b></font></div>');
	print('</caption>');

	print('<tr bgcolor="#cccccc">');
	print('<th>新棚番</th>');
	print('<th>旧棚番</th>');
	print('<th>品名</th>');
//	print('<th>単価</th>');
	print('<th>在庫数</th>');
	print('<th>更新日</th>');
	print('<th>備考(在庫)</th>');
	print('</tr>');

	//
	if ($smt_id!=NULL) {

		$cnt_smt = count($smt_id) - 1;

		for ($i=0; $i<=$cnt_smt; $i++) {

			$part_row = $mdb2->queryRow("SELECT * FROM part_smt WHERE smt_id='$smt_id[$i]'");
			if (PEAR::isError($part_row)) {
			} else {
				$part_data[$i][0]  = $part_row[0];	// SMT部品ID
				$part_data[$i][1]  = $part_row[1];	// 新棚番
				$part_data[$i][2]  = $part_row[10];	// 旧棚番
				$part_data[$i][3]  = $part_row[2];	// 品名
				$part_data[$i][4]  = $part_row[3];	// はんだ
			}

			$stock_row = $mdb2->queryRow("SELECT * FROM stock_smt WHERE smt_id='$smt_id[$i]'");
			if (PEAR::isError($stock_row)) {
			} else {
				$stock_data[$i][0]  = $stock_row[0];	// 在庫ID
				$stock_data[$i][1]  = $stock_row[2];	// 部品単価
				$stock_data[$i][2]  = $stock_row[3];	// 在庫数
				$stock_data[$i][3]  = $stock_row[4];	// 更新日
				$stock_data[$i][4]  = $stock_row[5];	// 備考
			}

			$color_set = "<td bgcolor=#f8f8ff>";

			print('<tr>');

			// 新棚番
			print($color_set);
			if ($part_data[$i][1]==NULL) {
				print('<br>');
			} else {
				print($part_data[$i][1]);
			}
			print('</td>');

			// 旧棚番
			print($color_set);
			if ($part_data[$i][2]==NULL) {
				print('<br>');
			} else {
				print($part_data[$i][2]);
			}
			print('</td>');

			// 品名
			print($color_set);
			print($part_data[$i][3]);
			print('</td>');

			// 単価
/* 			print($color_set);
			if ($stock_data[$i][1]==NULL) {
				print('<br>');
			} else {
				print($stock_data[$i][1]);
			}
			print('</td>');
 */
			// 在庫数(マイナスの場合には背景色変更)
			if ($stock_data[$i][2]<=0) {
				print('<td bgcolor=#ff99ff>');
			} else {
				print($color_set);
			}

			if ($stock_data[$i][2]==NULL) {
				print('<br>');
			} else {
				print($stock_data[$i][2]);
			}
			print('</td>');

			// 更新日
			print($color_set);
			if ($stock_data[$i][3]==NULL) {
				print('<br>');
			} else {
				print($stock_data[$i][3]);
			}
			print('</td>');

			// 備考(在庫)
			print($color_set);
			if ($stock_data[$i][4]==NULL) {
				print('<br>');
			} else {
				print($stock_data[$i][4]);
			}
			print('</td>');

			if ($permission=='admin' or $permission=='setup') {

				print($color_set);
				$btn3 = "<input type='button' name='edit' onClick=\"location.href='search1.php?mode_search1=edit_s&eid_s=";
				$btn3 = $btn3 . $stock_data[$i][0] . "'\" value='編集'></td>";
				print($btn3);

			}

			print('</tr>');

		}

		unset($part_data);
		unset($stock_data);

	}

	print('</table>');
	print('<br>');


	print('<table border="1" align=center width="95%"');
	print('<caption>');
	print('<div align="center"><font size="4" color="#0066cc"><b>');
	print('[ 部品検索( ');
	print($parameter);
	print(' ) ] => ');
	print('[ リール管理番号 ]');
	print('</b></font></div>');
	print('</caption>');

	print('<tr bgcolor="#cccccc">');
	print('<th>納入日</th>');
	print('<th>伝票番号</th>');
	print('<th>管理番号</th>');
	print('<th>品名</th>');
	print('<th>連</th>');
	print('<th>総</th>');
	print('<th>数量</th>');
	print('<th>ロット</th>');
	print('<th>使用期限</th>');
	print('<th>使用完</th>');
	print('<th>備考</th>');
	print('</tr>');

    // 2017/08/08 降順ソートへ修正
	// $res_query = $mdb2->query("SELECT * FROM reel_no WHERE product LIKE '$search_para' OR p_maker LIKE '$search_para' ORDER BY due_date, check_no, r_seq, r_total");
	$res_query = $mdb2->query("SELECT * FROM reel_no WHERE product LIKE '$search_para' OR p_maker LIKE '$search_para' ORDER BY due_date DESC, check_no DESC, r_seq, r_total");
	$res_query = err_check($res_query);

	while($row = $res_query->fetchRow(MDB2_FETCHMODE_ASSOC)) {

		$reel_id  = $row['reel_id'];
		$check_no = $row['check_no'];
		$r_seq    = $row['r_seq'];
		$r_total  = $row['r_total'];
		$product  = $row['product'];
//		if ($r_seq==1) {
//			$qty = $row['qty'];
//		} else {
//			$qty = 0;
//		}
		$qty      = $row['qty'];
		$lot_no   = $row['lot_no'];
		$due_date = $row['due_date'];
		$exp_date = $row['exp_date'];
		$end_date = $row['end_date'];
		$rem_reel = $row['rem_reel'];
		$shit_no  = $row['shit_no'];

		$manage_row = $mdb2->queryRow("SELECT * FROM reel_no WHERE reel_id='$reel_id'");
			if (PEAR::isError($reel_row)) {
			} elseif ($reel_row[0]!=NULL) {
				$check_no = $reel_row[2];
				$product  = $reel_row[6];
				$solder   = $reel_row[5];
				$r_seq    = $reel_row[3];
				$r_total  = $reel_row[4];
				$qty      = $reel_row[9];
			}
			unset($reel_row);

		// 列ごとに色を変える
		// 2017/08/08 修正
		if ($end_date!=NULL) {
			$bgcolor = '#ffcc99';
		} elseif ($bgcolor==null) {
			$bgcolor = '#f8f8ff';
		} elseif ($bgcolor=='#ffcc99') {
			$bgcolor = '#f8f8ff';
		} elseif ($bgcolor=='#dcdcdc') {
			$bgcolor = '#f8f8ff';
		} elseif ($bgcolor=='#f8f8ff') {
			$bgcolor = '#dcdcdc';
		}

		$color_set = "<td bgcolor=\"" . $bgcolor . "\">";

		print('<tr>');

		// 納入日
		print($color_set);
		print($due_date);
		print('</td>');

		// 伝票番号
		print($color_set);
		print($shit_no);
		print('</td>');

		// 管理番号
		print($color_set);
		print($check_no);
		print('</td>');

		// 品名
		print($color_set);
		print($product);
		print('</td>');

		// 連番
		print($color_set);
		print($r_seq);
		print('</td>');

		// 総数
		print($color_set);
		print($r_total);
		print('</td>');

		// 数量
		print($color_set);
		if ($qty==0) {
			print('<br>');
		} else {
			print(number_format($qty));
		}
		print('</td>');

		// ロット
		print($color_set);
		if ($lot_no==NULL) {
			print('<br>');
		} else {
			print($lot_no);
		}
		print('</td>');

		// 有効期限
		print($color_set);
		print($exp_date);
		print('</td>');

		// 使用完日
		print($color_set);
		if ($end_date==NULL) {
			print('<br>');
		} else {
			print($end_date);
		}
		print('</td>');

		// 備考
		print($color_set);
		if ($rem_reel==NULL) {
			print('<br>');
		} else {
			print($rem_reel);
		}
		print('</td>');

		print('</tr>');

	}
	print('</table>');


} elseif ($sel_rack=='1') {

	// アドバンス専用棚

	print('<table border="1" align=center width="95%">');
	print('<caption>');
	print('<div align="center"><font size="4" color="#0066cc"><b>');
	print('[ 部品検索*アドバンス専用棚*( ');
	print($parameter);
	print(' ) ] => ');
	print('[ 棚番 ]');
	print('</b></font></div>');
	print('</caption>');

	print('<tr bgcolor="#cccccc">');
	print('<th>棚番</th>');
	print('<th>部品番号</th>');
	print('<th>品名1</th>');
	print('<th>品名2</th>');
	print('<th>品名3</th>');
	print('<th>品名4</th>');
	print('<th>品名5</th>');
	print('<th>備考</th>');
	print('</tr>');

	//
	if ($smt_id!=NULL) {

		$cnt_smt = count($smt_id) - 1;
		$bgcolor = '#dcdcdc';

		for ($i=0; $i<=$cnt_smt; $i++) {

			$part_row = $mdb2->queryRow("SELECT * FROM part_advance WHERE advance_id='$smt_id[$i]'");
			if (PEAR::isError($part_row)) {
			} else {
				$part_data[$i][0]  = $part_row[0];	// SMT部品ID
				$part_data[$i][1]  = $part_row[1];	// 棚番
				$part_data[$i][2]  = $part_row[2];	// 部品番号
				$part_data[$i][3]  = $part_row[3];	// 品名1
				$part_data[$i][4]  = $part_row[4];	// 品名2
				$part_data[$i][5]  = $part_row[5];	// 品名3
				$part_data[$i][6]  = $part_row[6];	// 品名4
				$part_data[$i][7]  = $part_row[7];	// 品名5
				$part_data[$i][8]  = $part_row[8];	// 備考
			}

			// 列ごとに色を変える
			if ($bgcolor == '#dcdcdc') {
				$bgcolor = '#f8f8ff';
			} elseif ($bgcolor == '#f8f8ff') {
				$bgcolor = '#dcdcdc';
			}

			$color_set = "<td bgcolor=\"" . $bgcolor . "\">";

			print('<tr>');

			// 棚番
			print($color_set);
			if ($part_data[$i][1]==NULL) {
				print('<br>');
			} else {
				print($part_data[$i][1]);
			}
			print('</td>');

			// 部品番号
			print($color_set);
			if ($part_data[$i][2]==NULL) {
				print('<br>');
			} else {
				print($part_data[$i][2]);
			}
			print('</td>');

			// 品名1
			print($color_set);
			if ($part_data[$i][3]==NULL) {
				print('<br>');
			} else {
				print($part_data[$i][3]);
			}
			print('</td>');

			// 品名2
			print($color_set);
			if ($part_data[$i][4]==NULL) {
				print('<br>');
			} else {
				print($part_data[$i][4]);
			}
			print('</td>');

			// 品名3
			print($color_set);
			if ($part_data[$i][5]==NULL) {
				print('<br>');
			} else {
				print($part_data[$i][5]);
			}
			print('</td>');

			// 品名4
			print($color_set);
			if ($part_data[$i][6]==NULL) {
				print('<br>');
			} else {
				print($part_data[$i][6]);
			}
			print('</td>');

			// 品名5
			print($color_set);
			if ($part_data[$i][7]==NULL) {
				print('<br>');
			} else {
				print($part_data[$i][7]);
			}
			print('</td>');

			// 備考
			print($color_set);
			if ($part_data[$i][8]==NULL) {
				print('<br>');
			} else {
				print($part_data[$i][8]);
			}
			print('</td>');

//			print($color_set);
//			$btn1 = "<input type='button' name='edit' onClick=\"location.href='excel/excel_out2.php?id=";
//			$btn1 = $btn1 . $part_data[$i][0] . "'\" value='Excel出力'></td>";
//			print($btn1);

			if ($permission=='admin' or $permission=='setup') {

				print($color_set);
				$btn1 = "<input type='button' name='edit' onClick=\"location.href='search1.php?mode_search1=edit_a&eid_a=";
				$btn1 = $btn1 . $part_data[$i][0] . "'\" value='編集'></td>";
				print($btn1);

				print($color_set);
				$btn2 = "<input type='button' name='edit' onClick=\"location.href='search1.php?mode_search1=del_a&did_a=";
				$btn2 = $btn2 . $part_data[$i][0] . "'\" value='削除'></td>";
				print($btn2);

			}

			print('</tr>');
		}
	}
	print('</table>');
	print('<br>');


}

?>
