<?php
//-------------------------------------------------------------------
// search1_inc3.php
// 棚番(新、旧)→部品名 検索
// $search_no = 3
//
// 2007/08/
//
// 2007/09/26 [part_smt]テーブル小変更対応
//
// 2007/12/21 新棚番と旧棚番の表示位置入替え(実質、新棚番では運用出来ていない)
//
// 2007/12/25 試験的に新品名、サブ品名、NEC品名をコメントアウト(峯Sより)
//            今後基本的に品名(資材伝票品名)とメーカー品名で管理する
//
// 2007/12/26 部品名からの検索と同様に編集機能追加
//
// 2008/06/24 現場からの要望でSMT部品データ[part_smt]に棚番備考[rem_rack]追加
//
// 2009/08/03 アドバンス専用棚[part_advance]に対応
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
// 2016/01/21 部品定数(p_constant)の表示追加
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
    print('[ ');
    print($search);
    print(' ] => ');
    print('[ ');
    print($parameter);
    print(' ]');
    print('</b></font></div>');
    print('</caption>');

    print('<tr bgcolor="#cccccc">');
    print('<th>新棚番</th>');
    print('<th>旧棚番</th>');
    print('<th>品名</th>');
    print('<th>はんだ</th>');
    print('<th>メーカー品名</th>');
    print('<th>定数</th>');
    print('<th>サイズ</th>');
    //	print('<th>高額品</th>');
    print('<th>備考(部品)</th>');
    print('<th>備考(棚番)</th>');
    print('</tr>');

    //
    while ($row = $res_query->fetchRow(MDB2_FETCHMODE_ASSOC)) {
        // 列ごとに色を変える
//		if ($bgcolor == '#dcdcdc') {
//			$bgcolor = '#f8f8ff';
//		} elseif ($bgcolor == '#f8f8ff') {
//			$bgcolor = '#dcdcdc';
//		}
//
//		$color_set = "<td bgcolor=\"" . $bgcolor . "\">";
//		$color_set = "<td bgcolor=#f8f8ff>";

        // 2017/02/21 買取部材はセル色(赤 tomato)へ変更
        if ($row['rem_rack']=='2017-02 部材[CR]' or $part_data['rem_rack']=='2017-02 部材[MW]') {
            $color_set = "<td bgcolor=#ff6347>";
        } else {
            // 2016/01/29 高額品のセル色(gold)へ変更
            switch ($row['exp_item']) {
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
        print($row['rack_no']);
        print('</td>');

        // 旧棚番
        print($color_set);
        if ($row['rack_old']==null) {
            print('<br>');
        } else {
            print($row['rack_old']);
        }
        print('</td>');

        // 品名
        print($color_set);
        print($row['product']);
        print('</td>');

        // はんだ
        print($color_set);
        print(solder_name($row['solder']));
        print('</td>');

        // メーカー品名
        print($color_set);
        if ($row['p_maker']==null) {
            print('<br>');
        } else {
            print($row['p_maker']);
        }
        print('</td>');

        // 部品定数
        print($color_set);
        if ($row['p_constant']==null) {
            print('<br>');
        } else {
            print($row['p_constant']);
        }
        print('</td>');

        // サイズ
        print($color_set);
        print(r_size_name($row['r_size']));
        print('</td>');

        // 高額品
/*		print($color_set);
		switch ($row['exp_item']) {
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
        if ($row['rem_part']==null) {
            print('<br>');
        } else {
            print($row['rem_part']);
        }
        print('</td>');

        // 備考(棚番)
        print($color_set);
        if ($row['rem_rack']==null) {
            print('<br>');
        } else {
            print($row['rem_rack']);
        }
        print('</td>');

        if ($permission=='admin' or $permission=='setup') {
            print($color_set);
            $btn1 = "<input type='button' name='edit' onClick=\"location.href='search1.php?mode_search1=edit&eid=";
            $btn1 = $btn1 . $row['smt_id'] . "'\" value='編集'></td>";
            print($btn1);

            print($color_set);
            $btn2 = "<input type='button' name='edit' onClick=\"location.href='search1.php?mode_search1=del&did=";
            $btn2 = $btn2 . $row['smt_id'] . "'\" value='削除'></td>";
            print($btn2);
        }

        print('</tr>');
    }
    print('</table>');
    print('<br>');


    print('<table border="1" align=center width="95%">');
    print('<caption>');
    print('<div align="center"><font size="4" color="#0066cc"><b>');
    print('[ 棚番検索( ');
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

    $res_query = $mdb2->query("SELECT * FROM part_smt WHERE rack_no like '$search_para' OR rack_old like '$search_para' ORDER BY smt_id");
    $res_query = err_check($res_query);

    while ($row = $res_query->fetchRow(MDB2_FETCHMODE_ASSOC)) {
        $part_data[0]  = $row['smt_id'];    // SMT部品ID
        $part_data[1]  = $row['rack_no'];   // 新棚番
        $part_data[2]  = $row['rack_old'];  // 旧棚番
        $part_data[3]  = $row['product'];   // 品名
        $part_data[4]  = $row['solder'];    // はんだ

        $stock_row = $mdb2->queryRow("SELECT * FROM stock_smt WHERE smt_id='$part_data[0]'");
        if (PEAR::isError($stock_row)) {
        } else {
            $stock_data[0]  = $stock_row[0];    // 在庫ID
            $stock_data[1]  = $stock_row[2];    // 部品単価
            $stock_data[2]  = $stock_row[3];    // 在庫数
            $stock_data[3]  = $stock_row[4];    // 更新日
            $stock_data[4]  = $stock_row[5];    // 備考
        }

        $color_set = "<td bgcolor=#f8f8ff>";

        print('<tr>');

        // 新棚番
        print($color_set);
        if ($part_data[1]==null) {
            print('<br>');
        } else {
            print($part_data[1]);
        }
        print('</td>');

        // 旧棚番
        print($color_set);
        if ($part_data[2]==null) {
            print('<br>');
        } else {
            print($part_data[2]);
        }
        print('</td>');

        // 品名
        print($color_set);
        print($part_data[3]);
        print('</td>');

        // 単価
/* 		print($color_set);
		if ($stock_data[1]==NULL) {
			print('<br>');
		} else {
			print($stock_data[1]);
		}
		print('</td>');
 */
        // 在庫数(マイナスの場合は背景色変更)
        if ($stock_data[2]<=0) {
            print('<td bgcolor=#ff99ff>');
        } else {
            print($color_set);
        }

        if ($stock_data[2]==null) {
            print('<br>');
        } else {
            print($stock_data[2]);
        }
        print('</td>');

        // 更新日
        print($color_set);
        if ($stock_data[3]==null) {
            print('<br>');
        } else {
            print($stock_data[3]);
        }
        print('</td>');

        // 備考(在庫)
        print($color_set);
        if ($stock_data[4]==null) {
            print('<br>');
        } else {
            print($stock_data[4]);
        }
        print('</td>');

        if ($permission=='admin' or $permission=='setup') {
            print($color_set);
            $btn3 = "<input type='button' name='edit' onClick=\"location.href='search1.php?mode_search1=edit_s&eid_s=";
            $btn3 = $btn3 . $stock_data[0] . "'\" value='編集'></td>";
            print($btn3);
        }

        print('</tr>');

        unset($part_data);
        unset($stock_data);
    }

    print('</table>');
    print('<br>');


    print('<table border="1" align=center width="95%">');
    print('<caption>');
    print('<div align="center"><font size="4" color="#0066cc"><b>');
    print('[ 棚番検索( ');
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

    //$sql_select = "SELECT * FROM reel_no";
    //$sql_where = " WHERE (product LIKE '$search_para' OR p_maker LIKE '$search_para') AND plan like '$plan2'";
    //$sql_order = " ORDER BY plan, operate_no, dash_no, eqp, product";

    $part_row = $mdb2->queryRow("SELECT * FROM part_smt WHERE rack_no LIKE '$search_para' OR rack_old LIKE '$search_para' ORDER BY smt_id");
    $product_reel = $part_row[2];

    // 2017/08/08 降順ソートへ修正
    // $res_query = $mdb2->query("SELECT * FROM reel_no WHERE product LIKE '$product_reel' OR p_maker LIKE '$product_reel' ORDER BY due_date, check_no, r_seq, r_total");
    $res_query = $mdb2->query("SELECT * FROM reel_no WHERE product LIKE '$product_reel' OR p_maker LIKE '$product_reel' ORDER BY due_date DESC, check_no DESC, r_seq, r_total");
    $res_query = err_check($res_query);

    while ($row = $res_query->fetchRow(MDB2_FETCHMODE_ASSOC)) {
        $check_no = $row['check_no'];
        $r_seq    = $row['r_seq'];
        $r_total  = $row['r_total'];
        $product  = $row['product'];
        $qty      = $row['qty'];
        $lot_no   = $row['lot_no'];
        $due_date = $row['due_date'];
        $exp_date = $row['exp_date'];
        $end_date = $row['end_date'];
        $rem_reel = $row['rem_reel'];
        $shit_no  = $row['shit_no'];

        // 列ごとに色を変える
        // 2017/08/08 修正
        if ($end_date!=null) {
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
        print(number_format($qty));
        print('</td>');

        // ロット
        print($color_set);
        print($lot_no);
        print('</td>');

        // 有効期限
        print($color_set);
        print($exp_date);
        print('</td>');

        // 使用完日
        print($color_set);
        if ($end_date==null) {
            print('<br>');
        } else {
            print($end_date);
        }
        print('</td>');

        // 備考
        print($color_set);
        if ($rem_reel==null) {
            print('<br>');
        } else {
            print($rem_reel);
        }
        print('</td>');

        print('</tr>');
    }
} elseif ($sel_rack=='1') {
    // アドバンス専用棚
    print('<table border="1" align=center width="95%">');
    print('<caption>');
    print('<div align="center"><font size="4" color="#0066cc"><b>');
    print('[ 棚番検索*アドバンス専用棚*( ');
    print($parameter);
    print(' ) ] => ');
    print('[ 部品名 ]');
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
    $bgcolor = '#dcdcdc';

    while ($row = $res_query->fetchRow(MDB2_FETCHMODE_ASSOC)) {
        $advance_id  = $row['advance_id'];
        $rack_no     = $row['rack_no'];
        $part_no     = $row['part_no'];
        $part_1      = $row['part_1'];
        $part_2      = $row['part_2'];
        $part_3      = $row['part_3'];
        $part_4      = $row['part_4'];
        $part_5      = $row['part_5'];
        $rem_advance = $row['rem_advance'];

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
        if ($rack_no==null) {
            print('<br>');
        } else {
            print($rack_no);
        }
        print('</td>');

        // 部品番号
        print($color_set);
        if ($part_no==null) {
            print('<br>');
        } else {
            print($part_no);
        }
        print('</td>');

        // 品名1
        print($color_set);
        if ($part_1==null) {
            print('<br>');
        } else {
            print($part_1);
        }
        print('</td>');

        // 品名2
        print($color_set);
        if ($part_2==null) {
            print('<br>');
        } else {
            print($part_2);
        }
        print('</td>');

        // 品名3
        print($color_set);
        if ($part_3==null) {
            print('<br>');
        } else {
            print($part_3);
        }
        print('</td>');

        // 品名4
        print($color_set);
        if ($part_4==null) {
            print('<br>');
        } else {
            print($part_4);
        }
        print('</td>');

        // 品名5
        print($color_set);
        if ($part_5==null) {
            print('<br>');
        } else {
            print($part5);
        }
        print('</td>');

        // 備考
        print($color_set);
        if ($rem_advance==null) {
            print('<br>');
        } else {
            print($rem_advance);
        }
        print('</td>');

//		print($color_set);
//		$btn1 = "<input type='button' name='edit' onClick=\"location.href='excel/excel_out2.php?id=";
//		$btn1 = $btn1 . $part_data[$i][0] . "'\" value='Excel出力'></td>";
//		print($btn1);

        if ($permission=='admin' or $permission=='setup') {
            print($color_set);
            $btn1 = "<input type='button' name='edit' onClick=\"location.href='search1.php?mode_search1=edit_a&eid_a=";
            $btn1 = $btn1 . $advance_id . "'\" value='編集'></td>";
            print($btn1);

            print($color_set);
            $btn2 = "<input type='button' name='edit' onClick=\"location.href='search1.php?mode_search1=del_a&did_a=";
            $btn2 = $btn2 . $advance_id . "'\" value='削除'></td>";
            print($btn2);
        }

        print('</tr>');
    }
    print('</table>');
    print('<br>');
}
