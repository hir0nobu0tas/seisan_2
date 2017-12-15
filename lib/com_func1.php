<?php
//-------------------------------------------------------------------
// com_func1.php
//
// 2007/06/07 PEAR::DB -> PEAR::MDB2 へ変更
//
// 2007/07/18
// DB接続処理修正(factory接続への変更とオプション指定追加)
//
// 2007/10/09
// はんだ種別[solder_no]追加
//
// 2010/07/01
// PHP 5.3 対応を一部コメントアウトでテスト中
//
// 2011/08/02
// function getdirtree() エラー対応修正
//-------------------------------------------------------------------

//-------------------------------------------------------------------
// db_connect
// DB接続(seisan_project)
//
// 引数   なし
// 戻り値 return = $mdb2
//-------------------------------------------------------------------
function db_connect() {

	$dsn = "pgsql://seisan:u6oc7rwn@localhost/seisan_project";
	$options = array(
    	'debug'            => 2,
    	'portability'      => MDB2_PORTABILITY_ALL,
		'use_transactions' => true,
    );

//	$mdb2 =& MDB2::connect($dsn);
	$mdb2 =& MDB2::factory($dsn, $options);
	if (PEAR::isError($mdb2)) {
    	die($mdb2->getMessage());
	}

	return($mdb2);

}

//-------------------------------------------------------------------
// db_connect_smt
// DB接続(smt_project) オーダー情報参照用
//
// 引数   なし
// 戻り値 return = $mdb2
//-------------------------------------------------------------------
function db_connect_smt() {

	$dsn = "pgsql://smt:kzlawb8n@localhost/smt_project";
	$options = array(
    	'debug'            => 2,
    	'portability'      => MDB2_PORTABILITY_ALL,
		'use_transactions' => true,
    );

//	$mdb2 =& MDB2::connect($dsn);
	$mdb2 =& MDB2::factory($dsn, $options);
	if (PEAR::isError($mdb2)) {
    	die($mdb2->getMessage());
	}

	return($mdb2);

}

//-------------------------------------------------------------------
// db_disconnect
// DB切断
//
// 引数   $db    = $mdb2
// 戻り値 なし
//-------------------------------------------------------------------
function db_disconnect($mdb2) {

	$mdb2->disconnect();

}

//-------------------------------------------------------------------
// err_chk (DBエラーの確認)
// DBエラーが発生した場合のエラー表示
//
// 引数   $res   = クエリの結果オブジェクト
// 戻り値          エラー有り -> エラーメッセージを表示してスクリプト停止
//                 エラー無し -> 引数をそのまま返す
//-------------------------------------------------------------------
function err_check($res) {

	if (PEAR::isError($res)) {
        die($res->getMessage());
    } else {
    	return ($res);
    }

}

//-------------------------------------------------------------------
// transaction_end (トランザクションブロックの終了処理)
// DBエラーが発生した場合のエラー表示
//
// 引数   $mdb2  = 接続したDBオブジェクト
//        $res   = 結果配列
// 戻り値          エラー有り->ロールバック
//                 エラー有り->コミット
//-------------------------------------------------------------------
function transaction_end($mdb2, $res) {

	if (PEAR::isError($res)) {
    	if ($mdb2->in_transaction) {
//    		die($res->getMessage());
    		$mdb2->rollback();
			return ('ROLLBACK');
    	}
	} else {
    	if ($mdb2->in_transaction) {
    		$mdb2->commit();
			return ('COMMIT');
    	}
	}

}

//-------------------------------------------------------------------
// pgArray2Array (オリジナル名はarray_from_pgarray)
// PostgreSQLの配列型をPHPの配列へ変換
// (set_value_to_multidim_arrayを中から呼び出している)
//
// Blog plotless 2004.08.07 PHP4 & Postgres
// http://plot.cocolog-nifty.com/plotless/2004/08/php4__postgres.html
// より
//
// 2007/03/01 正直処理の内容は良く判りません(~_~)
//
// 引数   $pgstr = PostgreSQLから読込んだ配列型書式文字列
// 戻り値 return = PHP配列
//-------------------------------------------------------------------
function pgArray2Array($pgstr, $base = 0) {

	$dim = 0;
	$max_dim = 0;
	$dim_index = array();
	$ret_array = array();
	$quot = false;
	$backslash = false;
	$tmpstr = "";

	for ($cp = 0; $cp < mb_strlen($pgstr); $cp++) {

		$c = mb_substr($pgstr, $cp, 1);

		if (!$backslash) {

			if (!$quot) {

				if ($c == '{') {
					$dim_index[++$dim] = $base;
					if ($dim > $max_dim) $max_dim = $dim;
					$backslash = false;
					continue;
				}

				if ($c == '}') {
					if ($dim == $max_dim)
					set_value_to_multidim_array($tmpstr, $ret_array, $dim_index, $dim);
					$tmpstr = "";
					$dim_index[$dim--] = $base;
					$backslash = false;
					continue;
				}

				if ($c == ',') {
					if ($dim == $max_dim)
					set_value_to_multidim_array($tmpstr, $ret_array, $dim_index, $dim);
					$tmpstr = "";
					$dim_index[$dim]++;
					$backslash = false;
					continue;
				}

			}

			if ($c == '"') {
				$quot = !$quot;
				$backslash = false;
				continue;
			}

			if ($c == '\\') {
				$backslash = true;
				continue;
			}
		}

		$tmpstr .= $c;
		$backslash = false;
	}

	return $ret_array;

}

function set_value_to_multidim_array($value, &$ar, $dim_index, $dim) {

	$d_index = array_shift($dim_index);

	if ($dim > 1) set_value_to_multidim_array($value, $ar[$d_index], $dim_index, $dim-1);
	else $ar[$d_index] = $value;

}

//-------------------------------------------------------------------
// php2pgarray
// PHP配列からPostgreSQLの配列型へ入力出来る書式へ変換
//
// 2007/03/01 オリジナルに作成
//
// 引数   $str   = PHP配列
// 戻り値 return = PostgreSQLの配列型書式文字列 '{****,****,****}'
//-------------------------------------------------------------------
function Array2pgArray($str) {

	// 配列の要素数
	$cnt = count($str) - 1;
	$s_str = "'{";
	$e_str = "}'";

	$out_str = $s_str;
	for ($i=0; $i<=$cnt; $i++) {
		if ($i==$cnt) {
			$out_str = $out_str . $str[$i];
		} else {
			$out_str = $out_str . $str[$i] . ',';
		}
	}

	$out_str = $out_str . $e_str;
	return($out_str);

}

//-------------------------------------------------------------------
// getdirtree
// 指定したディレクトリ以下のファイル一覧を取得
//
// PHPの基礎体力(ファイル操作)より
// http://www.sound-uz.jp/php/note/dirTree
//
// 引数   $path  = ディレクトリを示す文字列
// 戻り値 return = ファイル一覧を格納した配列
//
// ＊$dir は、スクリプトから見た相対パスを指定
//-------------------------------------------------------------------
function getdirtree($path) {

	// ディレクトリでなければ false を返す
	if (!is_dir($path)) {
		return false;
	}

	// 戻り値用の配列
	$dir = array();

	if ($handle = opendir($path)) {

		while (false!==($file = readdir($handle))) {

			// 自分自身と上位階層のディレクトリを除外
			if ('.'==$file || '..'==$file || 'old'==$file || 'pdf'==$file || 'regist'==$file) {
				continue;
			}

			if (is_dir($path.'/'.$file)) {

				// ディレクトリならば自分自身を呼び出し
				$dir[$file] = getdirtree($path.'/'.$file);

			} elseif (is_file($path.'/'.$file)) {

				// ファイルならばパスを格納
				$dir[$file] = $path.'/'.$file;

			}
		}

		closedir($handle);

	}

// print('<pre>');
// Var_Dump($dir);
// print('</pre>');

	return $dir;
}


//-------------------------------------------------------------------
// StoHMS (ss -> hh:mm:ss)
// 2006/07/07 修正(結果は同じ？)
//
// 引数   $value = ss
// 戻り値 return = hh:mm:ss
//-------------------------------------------------------------------
function StoHMS($value) {

	//$s = $value%60;
	//$m1 = floor($value/60);
	//$m2 = $m1%60;
	//$h = floor($m1/60);

	$h = floor($value / 3600);
	$m = floor(($value - ($h * 3600)) / 60);
	$s = ($value - ($h * 3600)) % 60;

	if (strlen($s)==1) {
		$s = '0' . $s;
	}

	if (strlen($m)==1) {
		$m = '0' . $m;
	}

	if (strlen($h)==1) {
		$h = '0' . $h;
	}

	return($h . ':' . $m . ':' . $s);

}


//-------------------------------------------------------------------
// HMStoS (hh:mm:ss -> ss)
//
// 引数   $value = hh:mm:ss
// 戻り値 return = ss
//-------------------------------------------------------------------
function HMStoS($value) {
	$h = substr($value, 0, 2);
	$m = substr($value, 3, 2);
	$s = substr($value, 6, 2);

	return(($h * 60 * 60) + ($m * 60) + $s);
}


//-------------------------------------------------------------------
// solder_name
//
// 引数   $solder = はんだ種別
// 戻り値 return  = はんだ種類(文字列)
//-------------------------------------------------------------------
function solder_name($solder) {

	switch($solder) {
	case 0:
		return('不明');
		break;
	case 1:
		return('共晶');
		break;
	case 2:
		return('RoHS');
		break;
	case 3:
		return('混在');
		break;
	}

}

//-------------------------------------------------------------------
// solder_no
//
// 引数   $solder = はんだ種別
// 戻り値 return  = はんだ種類(番号)
//-------------------------------------------------------------------
function solder_no($solder) {

	switch($solder) {
	case '':
		return('0');
		break;
	case '不明':
		return('0');
		break;
	case '共晶':
		return('1');
		break;
	case 'RoHS':
		return('2');
		break;
	case 'PBF':
		return('2');
		break;
	case '混在':
		return('3');
		break;
	}

}

//-------------------------------------------------------------------
// ass_side_name
//
// 引数   $ass_side = ASS面
// 戻り値 return    = ASS面
//-------------------------------------------------------------------
function ass_side_name($ass_side) {

	switch($ass_side) {
	case 1:
		return('裏');
		break;
	case 2:
		return('表');
		break;
	}

}

//-------------------------------------------------------------------
// mc_no(ブラウザ表示用)
//
// 引数   $mc    = M/C種別
// 戻り値 return = M/C No
//-------------------------------------------------------------------
function mc_no($mc) {

	switch($mc) {
	case 0:
		return('未定');
		break;
	case 1:
		return('JUKI[1]');
		break;
	case 2:
		return('JUKI[2]');
		break;
	case 3:
		return('九松');
		break;
	case 4:
		return('手付け');
		break;
	case 9:
		return('外注');
		break;
	}

}

//-------------------------------------------------------------------
// client_name
//
// 引数   $client_id = 客先ID
// 戻り値 return     = 客先名
//-------------------------------------------------------------------
function client_name($client_id) {

	switch($client_id) {
	case 0:
		return('すべて');
		break;
	case 1:
		return('NTWX(自社)');
		break;
	case 2:
		return('NTWX(Bネット)');
		break;
	case 3:
		return('NTWX(国内海外)');
		break;
	case 9:
		return('Bネット');
		break;
	case 12:
		return('NECAT');
		break;
	case 13:
		return('IPNW4');
		break;
	case 14:
		return('PNW(NCOS)');
		break;
	case 31:
		return('モバイルワイヤレス');
		break;
	case 36:
		return('NEWIN(NG品)');
		break;
	case 37:
		return('NECファクトリー');
		break;
	case 44:
		return('NEWIN');
		break;
	case 45:
		return('NEC東北');
		break;
	case 51:
		return('NECマグナス(NTEM)');
		break;
	case 52:
		return('NECマグナス(Nメ)');
		break;
	case 54:
		return('NECシステム建設');
		break;
	case 55:
		return('ネットワークセンサ');
		break;
	case 56:
		return('NEEC横浜');
		break;
	case 58:
		return('NEEC府中');
		break;
	case 59:
		return('NEEC玉川');
		break;
	case 61:
		return('本社');
		break;
	case 70:
//		return('メディアグローバルリンクス');
		return('M G L');
		break;
	case 72:
		return('三共電子');
		break;
	case 73:
		return('千代田商事');
		break;
	case 75:
		return('大同信号');
		break;
	case 76:
//		return('NTTエレクトロニクス');
		return('N E L');
		break;
	case 77:
		return('東芝');
		break;
	case 78:
		return('芙蓉ビデオ');
		break;
	case 79:
		return('城南電気');
		break;
	case 80:
		return('羽黒電子');
		break;
	case 82:
		return('井上電気');
		break;
	case 83:
		return('アキュウテック');
		break;
	case 84:
		return('スカイウェア');
		break;
	case 85:
		return('ユニパーツ');
		break;
	case 86:
		return('TEAC');
		break;
	case 87:
		return('凌和電子');
		break;
	case 88:
		return('ソーワ');
		break;
	case 89:
		return('加賀電子');
		break;
	case 91:
		return('レグラス');
		break;
	case 99:
		return('その他');
		break;
	}

}

//-------------------------------------------------------------------
// pack_name
//
// 引数   $pack  = 荷姿
// 戻り値 return = 荷姿名
//-------------------------------------------------------------------
function pack_name($pack) {

	switch($pack) {
	case 'tape':
		return('テープ');
		break;
	case 'stick':
		return('スティック');
		break;
	case 'tray':
		return('トレイ');
		break;
	case '':
		return('その他');
		break;
	}

}

//-------------------------------------------------------------------
// part_list
//
// 引数   $mdb2    = 接続したDBオブジェクト
//        $unit_id = unit_id
// 戻り値 return   = 部品リスト配列
//-------------------------------------------------------------------
function part_list($mdb2, $unit_id) {

	// [station_data] 取得
	$res_query = $mdb2->query("SELECT * FROM station_data WHERE unit_id='$unit_id'");
	$res_query = err_check($res_query);

	$st_cnt = 0;
	while($row = $res_query->fetchRow(MDB2_FETCHMODE_ASSOC)) {
		$st_id[$st_cnt] = $row['st_id'];
		$st_cnt++;
	}

	$cnt_st = count($st_id) - 1;

	$set_cnt = 0;
	for ($st_tmp=0; $st_tmp<=$cnt_st; $st_tmp++) {

		// [set_data] 取得
		$res_query = $mdb2->query("SELECT * FROM set_data WHERE st_id='$st_id[$st_tmp]'");
		$res_query = err_check($res_query);

		while($row = $res_query->fetchRow(MDB2_FETCHMODE_ASSOC)) {
			$set_part[0][$set_cnt] = $row['set_id'];
			$set_part[1][$set_cnt] = $row['part'];

			$set_cnt++;
		}

	}

	return($set_part);

}


//-------------------------------------------------------------------
// part_list
//
// 引数   $mdb2    = 接続したDBオブジェクト
//        $unit_id = unit_id
// 戻り値 return   = 部品リスト配列
//-------------------------------------------------------------------
function shar_chk($mdb2, $unit_id) {


}


?>
