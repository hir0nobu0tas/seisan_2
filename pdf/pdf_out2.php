<?php
/*

 SMT生産計画システム ポイントフロー 登録作業者一覧 出力

 2007/04/25

 2007/05/09
 [product] -> [product_smt]へ変更

 2007/05/22
 バーコードフォント[CODE39.ttf]を埋め込み化
 フォントメトリックファイル、フォント定義ファイル生成後、AddMBFont
 では無く AddFont でフォントを登録

 他にMSゴシックを使用しているが、クライアントがWindowsならデフォルトでフォント
 は入っているはずなのでバーコードフォントのみ埋め込みとする

 2007/07/05 [第2版]
 チェックデジット廃止
 チェックデジット無しの場合約300万文字に1文字の誤読というデータがあり、ほぼ誤読
 は無いと判断 他のバーコード(他の伝票など)と合わせてチェックデジットを廃止する
 (チェックデジット有りの場合は約1億4,900万文字に1文字の誤読)

 2007/07/09 [第3版]
 並行作業用の予備社員番号対応 社員番号の先頭を[A]とする


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

// MBFPDFの読み込みと拡張クラス定義
define('FPDF_FONTPATH','../lib/fpdf/font/');
require_once '../lib/fpdf/fpdf.php';
require_once '../lib/fpdf/mbfpdf.php';
$EUC2SJIS = true;	// EUC-JP使用

//---------------------------------------------------------------
	
// ヘッダー&フッターの表示
class HeaderFooterExtended extends MBFPDF {
	function Header() {
		pdf_Header($this, 'ポイントフロー工程 作業者一覧[第3版]');
	}
//	function Footer() {
//		pdf_Footer($this);
//	}
}

// A4縦指定
$pdf=new HeaderFooterExtended('P','mm','A4');
$pdf->AddMBFont('MS-Gothic', 'SJIS');
//$pdf->AddMBFont('CODE39', 'SJIS');
$pdf->AddFont('CODE39', '', 'CODE39.php');
$pdf->SetTitle('Worker of IMT');
$pdf->SetAutoPageBreak(false);	// 自動改ページ禁止
$pdf->AddPage();

$pdf->SetFont('MS-Gothic', '', 12);
$x_pos = 20;
$y_pos = 30;

// バーコード部表示

// DB接続
$mdb2 = db_connect();

$x_pos = 20; 
$y_pos = 30;

//$res_query = $mdb2->query('SELECT * FROM worker ORDER BY worker_id');
$res_query = $mdb2->query("SELECT * FROM worker WHERE rem_worker='IMT作業者' ORDER BY worker_id");
$res_query = err_check($res_query);

while($row = $res_query->fetchRow(MDB2_FETCHMODE_ASSOC)) {

	//
	pdf_Worker_Cell($pdf, $x_pos, $y_pos, $row['worker_no'], $row['worker_name']);

	if ($x_pos==20) {
		
		$x_pos = 115;
		$y_pos = $y_pos;
		
	} elseif ($x_pos==115) {
		
		$x_pos = 20;
		$y_pos = $y_pos + 30;
		
	}

	if ($y_pos>=280) {
		
		$pdf->AddPage();
		
		$x_pos = 20; 
		$y_pos = 30;
		
	}
	
}

$pdf->AddPage();
$x_pos = 20; 
$y_pos = 30;

$res_query = $mdb2->query("SELECT * FROM worker WHERE rem_worker='IMT作業 予備' ORDER BY worker_id");
$res_query = err_check($res_query);

while($row = $res_query->fetchRow(MDB2_FETCHMODE_ASSOC)) {

	//
	pdf_Worker_Cell($pdf, $x_pos, $y_pos, $row['worker_no'], $row['worker_name']);

	if ($x_pos==20) {
		
		$x_pos = 115;
		$y_pos = $y_pos;
		
	} elseif ($x_pos==115) {
		
		$x_pos = 20;
		$y_pos = $y_pos + 30;
		
	}

	if ($y_pos>=280) {
		
		$pdf->AddPage();
		
		$x_pos = 20; 
		$y_pos = 30;
		
	}
	
}

// DB切断
db_disconnect($mdb2);

$pdf->AliasNbPages();
$pdf->Output();

?>
