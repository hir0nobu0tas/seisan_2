<?php
/*

 生産計画システム 準備作業用バーコード 出力

 2008/03/


 */
//---------------------------------------------------------------
// 初期設定
//---------------------------------------------------------------
// 共有関数
include '../inc/parameter_inc.php';
include '../lib/com_func1.php';
include '../lib/modulus43.php';
include '../lib/pdf_func.php';

// PEARライブラリ
require_once 'HTML/QuickForm.php';
require_once 'MDB2.php';

// MBFPDFと拡張クラス定義
define('FPDF_FONTPATH','../lib/fpdf/font/');
require_once '../lib/fpdf/fpdf.php';
require_once '../lib/fpdf/mbfpdf.php';

// EUC-JP使用
$EUC2SJIS = true;

//---------------------------------------------------------------

// 引数取得
$order_id = $_GET["order_id"];
$st_id    = $_GET["st_id"];

// DB接続(smt_project)
$mdb2_smt = db_connect_smt();

// [order_info]から行取得 
$res_row = $mdb2_smt->queryRow("SELECT * FROM order_info WHERE order_id='$order_id'");
if (PEAR::isError($res_row)) {
} elseif ($res_row[0]!=NULL) {
	$product_id = $res_row[1];
	$operate_no = $res_row[2];
	$dash_no    = $res_row[3];
	$qty10      = $res_row[4];
	$plan       = $res_row[9];
}
unset($res_row);

// [product_smt]から行取得
$res_row = $mdb2_smt->queryRow("SELECT * FROM product_smt WHERE product_id='$product_id'");
if (PEAR::isError($res_row)) {
} elseif ($res_row[0]!=NULL) {
	$client_id = $res_row[1];
	$eqp       = $res_row[2];
	$product   = $res_row[3];
}
unset($res_row);

// [client]から行取得
$res_row = $mdb2_smt->queryRow("SELECT * FROM client WHERE client_id='$client_id'");
if (PEAR::isError($res_row)) {
} elseif ($res_row[0]!=NULL) {
	$client = $res_row[1];
}
unset($res_row);

// DB切断(smt_project)
db_disconnect($mdb2_smt);

// DB接続(seisan_project)
$mdb2 = db_connect();

// [station_data]から行取得
$res_row = $mdb2->queryRow("SELECT * FROM station_data WHERE st_id='$st_id'");
if (PEAR::isError($res_row)) {
} elseif ($res_row[0]!=NULL) {
	$unit_id = $res_row[1];
	$p_name  = $res_row[3];
}
unset($res_row);

// [unit_data]から行取得
$res_row = $mdb2->queryRow("SELECT * FROM unit_data WHERE unit_id='$unit_id'");
if (PEAR::isError($res_row)) {
} elseif ($res_row[0]!=NULL) {
	$file_name = $res_row[2];
	$board     = $res_row[3];
}
unset($res_row);

// [set_data]から行取得
$set_i = 0;
$res_query = $mdb2->query("SELECT * FROM set_data WHERE st_id='$st_id'");
$res_query = err_check($res_query);
while($row = $res_query->fetchRow(MDB2_FETCHMODE_ASSOC)) {
	$set_id[$set_i] = $row['set_id'];
	$set_i++;
}

// セット数のカウント
$cnt_set = count($set_id) - 1;

// DB切断(seisan_project)
db_disconnect($mdb2);

// ヘッダー&フッターの表示
class HeaderFooterExtended extends MBFPDF {
	function Header() {
		pdf_Header($this, '準備作業 データ登録用 バーコード[第0版]');
	}
//	function Footer() {
//		pdf_Footer($this);
//	}
}

// A4縦指定
$pdf = new HeaderFooterExtended('P','mm','A4');
$pdf->AddMBFont('MS-Gothic', 'SJIS');
//$pdf->AddMBFont('CODE39', 'SJIS');
$pdf->AddFont('CODE39', '', 'CODE39.php');
$pdf->SetTitle('Process of M/C SetUp');
$pdf->AddPage('Portrait');
$pdf->SetFont('MS-Gothic', '', 12);

//$order_id = '1234567890';
//$operate_no = '1234-00';
//$dash_no = '000000';
//$qty10 = '10';
//$product = 'H264(P0674,0675,0680)';
//
//$unit_id = '0123456789';
//$board = 'H264(P0674,0675,0680)';
//$p_name = 'P0674A-1.H41';

$pdf_data = array(0=>$order_id,
				  1=>$operate_no,
				  2=>$dash_no,
				  3=>$qty10,
				  4=>$product,
				  5=>$unit_id,
				  6=>$board,
				  7=>$p_name);

// オーダー情報 バーコード
pdf_Order_Cell($pdf, $pdf_data);

// セット位置 バーコード
for ($i=0; $i<=19; $i++) {
	if ($set_id[$i]!=NULL) {
		pdf_Set_Cell($pdf, $mdb2, $i, $set_id[$i]);
	}
}

// システム登録・キャンセル バーコード表示
pdf_Entry_Cell($pdf);

// 20種以上の登録データがある場合はページ追加
if ($cnt_set>=20) {

	// ページ追加
	$pdf->AddPage('Portrait');
	
	// オーダー情報 バーコード
	pdf_Order_Cell($pdf, $pdf_data);
	
	// セット位置 バーコード
	for ($i=0; $i<=19; $i++) {
		if ($set_id[$i + 20]!=NULL) {
			pdf_Set_Cell($pdf, $mdb2, $i, $set_id[$i + 20]);
		}
	}
	
	// システム登録・キャンセル バーコード表示
	pdf_Entry_Cell($pdf);

}

$pdf->AliasNbPages();
$pdf->Output();


?>
