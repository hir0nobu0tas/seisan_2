<?php

// SMT M/C 生産計画システム 生産管理票(バーコード)サンプル
// 2007/01/09 H.Sato

// 表示対象データの作成
// 実業務アプリケーションではDBからの読み込みになる
//$data = array();
$plan = $_GET["plan"];
$operate_no = '1234-00';
$dash_no = '012345';
$order_no = '123-0123456789';
$product = 'ABCDEFG-01';
$eqp = 'ABC-0123456-001';

// MBFPDFの読み込みと拡張クラス定義
require_once("fpdf/fpdf.php");
require_once("fpdf/mbfpdf.php");
$EUC2SJIS=true;		// EUC-JP使用
include 'lib/modulus43.php';

class HeaderFooterExtended extends MBFPDF {
	function Header() { theHeader($this); }
//	function Footer() { theFooter($this); }
}


// ヘッダー用関数
function theHeader(&$pdf) {

	$pdf->SetFont('MS-Gothic', 'B', 12);
	$pdf->SetFillColor(200, 200, 255);
	$pdf->SetXY(20, 15);
	$pdf->Cell(100, 8, '生産管理票(基板単位)', 1, 0, "C", 1);
	$pdf->SetFillColor(153, 255, 204);
	$today = date("Y-m-d H:i");
	$pdf->Cell(60, 8, $today . ' 発行', 1, 0,"C", 1);
	$pdf->Ln();

}

// フッター用関数
function theFooter(&$pdf) {
	$pdf->SetFont('MS-Gothic', '', 10);
	$pdf->SetXY(60, -15);
	$pdf->Cell(100, 10, 'Page ' . $pdf->PageNo() . ' / {nb}', 0, 0,"C", 0);
}

// A4縦指定
$pdf=new HeaderFooterExtended('P','mm','A4');
$pdf->AddMBFont('MS-Gothic', 'SJIS');
$pdf->AddMBFont('CODE39', 'SJIS');
//$pdf->SetAuthor('生産管理システム');
$pdf->AddPage();

$pdf->SetFont('MS-Gothic', '', 12);
$x_pos = 20;
$y_pos = 30;

$pdf->SetXY($x_pos, $y_pos);
$pdf->Cell(30,7, '工番', 1, 0, "C", 0);
$pdf->Cell(50,7, $operate_no, 1, 0, "L", 0);
$pdf->Cell(30,7, 'ダッシュ', 1, 0, "C", 0);
$pdf->Cell(65,7, $dash_no, 1, 0, "L", 0);
$pdf->Ln();

$x_pos = $x_pos;
$y_pos = $y_pos + 7;
$pdf->SetXY($x_pos, $y_pos);
$pdf->Cell(30,7, 'オーダー', 1, 0, "C", 0);
$pdf->Cell(50,7, $order_no, 1, 0, "L", 0);
$pdf->Cell(30,7, '事区', 1, 0, "C", 0);
$pdf->Cell(65,7, '70 メディアグローバルリンクス', 1, 0, "L", 0);
$pdf->Ln();

$x_pos = $x_pos;
$y_pos = $y_pos + 7;
$pdf->SetXY($x_pos, $y_pos);
$pdf->Cell(30,7,'装置名', 1, 0, "C", 0);
$pdf->Cell(50,7,$eqp, 1, 0, "L", 0);
$pdf->Cell(30,7,'生産品名', 1, 0, "C", 0);
$pdf->Cell(65,7,$product, 1, 0, "L", 0);
$pdf->Ln();

// 構成表示
$pdf->SetFont('MS-Gothic', '', 10);
$x_pos = $x_pos;
$y_pos = $y_pos + 10;
$pdf->SetXY($x_pos, $y_pos);
$pdf->Cell(15,5,'構成', 1, 0, "C", 0);
$pdf->Cell(20,5,'ダッシュ', 1, 0, "C", 0);
$pdf->Cell(50,5,'品名', 1, 0, "C", 0);
$pdf->Cell(60,5,'品名コード', 1, 0, "C", 0);
$pdf->Cell(15,5,'手配数', 1, 0, "C", 0);
$pdf->Cell(15,5,'生産', 1, 0, "C", 0);
$pdf->Ln();

$x_pos = $x_pos;
$y_pos = $y_pos + 5;
$pdf->SetXY($x_pos, $y_pos);
$pdf->Cell(15,5,'Main', 1, 0, "C", 0);
$pdf->Cell(20,5,'01-0001', 1, 0, "C", 0);
$pdf->Cell(50,5,'ABCDEFG-00', 1, 0, "L", 0);
$pdf->Cell(60,5,'NT2-484-P0284-0A00', 1, 0, "L", 0);
$pdf->Cell(15,5,'10', 1, 0, "C", 0);
$pdf->Cell(15,5,'', 1, 0, "C", 0);
$pdf->Ln();

$x_pos = $x_pos;
$y_pos = $y_pos + 5;
$pdf->SetXY($x_pos, $y_pos);
$pdf->Cell(15,5,'Sub 1', 1, 0, "C", 0);
$pdf->Cell(20,5,'01-0001', 1, 0, "C", 0);
$pdf->Cell(50,5,'ABCDEFG-01', 1, 0, "L", 0);
$pdf->Cell(60,5,'NT2-484-P0284-0B01', 1, 0, "L", 0);
$pdf->Cell(15,5,'10', 1, 0, "C", 0);
$pdf->Cell(15,5,'*', 1, 0, "C", 0);
$pdf->Ln();

$x_pos = $x_pos;
$y_pos = $y_pos + 5;
$pdf->SetXY($x_pos, $y_pos);
$pdf->Cell(15,5,'Sub 2', 1, 0, "C", 0);
$pdf->Cell(20,5,'01-0001', 1, 0, "C", 0);
$pdf->Cell(50,5,'ABCDEFG-02', 1, 0, "L", 0);
$pdf->Cell(60,5,'NT2-484-P0284-0B02', 1, 0, "L", 0);
$pdf->Cell(15,5,'10', 1, 0, "C", 0);
$pdf->Cell(15,5,'', 1, 0, "C", 0);
$pdf->Ln();

$x_pos = $x_pos;
$y_pos = $y_pos + 5;
$pdf->SetXY($x_pos, $y_pos);
$pdf->Cell(15,5,'Sub 3', 1, 0, "C", 0);
$pdf->Cell(20,5,'01-0001', 1, 0, "C", 0);
$pdf->Cell(50,5,'ABCDEFG-03', 1, 0, "L", 0);
$pdf->Cell(60,5,'NT2-484-P0284-0B03', 1, 0, "L", 0);
$pdf->Cell(15,5,'10', 1, 0, "C", 0);
$pdf->Cell(15,5,'', 1, 0, "C", 0);
$pdf->Ln();

$x_pos = $x_pos;
$y_pos = $y_pos + 5;
$pdf->SetXY($x_pos, $y_pos);
$pdf->Cell(15,5,'Sub 4', 1, 0, "C", 0);
$pdf->Cell(20,5,'01-0001', 1, 0, "C", 0);
$pdf->Cell(50,5,'ABCDEFG-04', 1, 0, "L", 0);
$pdf->Cell(60,5,'NT2-484-P0284-0B04', 1, 0, "L", 0);
$pdf->Cell(15,5,'10', 1, 0, "C", 0);
$pdf->Cell(15,5,'', 1, 0, "C", 0);
$pdf->Ln();

$x_pos = $x_pos;
$y_pos = $y_pos + 5;
$pdf->SetXY($x_pos, $y_pos);
$pdf->Cell(15,5,'Sub 5', 1, 0, "C", 0);
$pdf->Cell(20,5,'01-0001', 1, 0, "C", 0);
$pdf->Cell(50,5,'ABCDEFG-05', 1, 0, "L", 0);
$pdf->Cell(60,5,'NT2-484-P0284-0B05', 1, 0, "L", 0);
$pdf->Cell(15,5,'10', 1, 0, "C", 0);
$pdf->Cell(15,5,'', 1, 0, "C", 0);
$pdf->Ln();


// バーコード部表示
$x_pos = $x_pos;
$y_pos = $y_pos + 10;
$pdf->SetXY($x_pos, $y_pos);
$disp0 = '0123456788';
$disp1 = Modulus43_Work($disp0);

$pdf->SetFont('MS-Gothic', '', 12);
$pdf->Cell(30,14,'管理ID', 1, 0, "C", 0);
$pdf->Cell(50,7, $disp0, 1, 0, "C", 0);

$pdf->SetFont('CODE39', '', 24);	// 2006/12/26 サイズ24で読み取りOK
$pdf->Cell(95,14, $disp1, 1, 0, "C", 0);

$pdf->SetFont('MS-Gothic', '', 12);
$x_pos = 50;
$y_pos = $y_pos + 7;
$pdf->SetXY($x_pos, $y_pos);
$pdf->Cell(50,7,$disp1, 1, 0, "C", 0);
$pdf->Ln();

$x_pos = 20;
$y_pos = $y_pos + 10;
$pdf->SetXY($x_pos, $y_pos);
$pdf->Cell(30,14,'数量', 1, 0, "C", 0);
$disp2 = '10';
$disp3 = Modulus43_Work($disp2);
$pdf->Cell(50,7, $disp2, 1, 0, "C", 0);

$pdf->SetFont('CODE39', '', 24);
$pdf->Cell(95,14, $disp3, 1, 0, "C", 0);

$pdf->SetFont('MS-Gothic', '', 12);
$x_pos = 50;
$y_pos = $y_pos + 7;
$pdf->SetXY($x_pos, $y_pos);
$pdf->Cell(50,7,$disp3, 1, 0, "C", 0);
$pdf->Ln();

$x_pos = 20;
$y_pos = $y_pos + 20;
//$pdf->SetXY($x_pos, $y_pos);
$pdf->Line($x_pos, $y_pos, $x_pos + 170, $y_pos);



$pdf->AliasNbPages();
$pdf->Output();

?>
