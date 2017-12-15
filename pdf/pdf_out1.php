<?php
/*

 �����ײ襷���ƥ� ��������ѥС������� ����

 2008/03/


 */
//---------------------------------------------------------------
// �������
//---------------------------------------------------------------
// ��ͭ�ؿ�
include '../inc/parameter_inc.php';
include '../lib/com_func1.php';
include '../lib/modulus43.php';
include '../lib/pdf_func.php';

// PEAR�饤�֥��
require_once 'HTML/QuickForm.php';
require_once 'MDB2.php';

// MBFPDF�ȳ�ĥ���饹���
define('FPDF_FONTPATH','../lib/fpdf/font/');
require_once '../lib/fpdf/fpdf.php';
require_once '../lib/fpdf/mbfpdf.php';

// EUC-JP����
$EUC2SJIS = true;

//---------------------------------------------------------------

// ��������
$order_id = $_GET["order_id"];
$st_id    = $_GET["st_id"];

// DB��³(smt_project)
$mdb2_smt = db_connect_smt();

// [order_info]����Լ��� 
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

// [product_smt]����Լ���
$res_row = $mdb2_smt->queryRow("SELECT * FROM product_smt WHERE product_id='$product_id'");
if (PEAR::isError($res_row)) {
} elseif ($res_row[0]!=NULL) {
	$client_id = $res_row[1];
	$eqp       = $res_row[2];
	$product   = $res_row[3];
}
unset($res_row);

// [client]����Լ���
$res_row = $mdb2_smt->queryRow("SELECT * FROM client WHERE client_id='$client_id'");
if (PEAR::isError($res_row)) {
} elseif ($res_row[0]!=NULL) {
	$client = $res_row[1];
}
unset($res_row);

// DB����(smt_project)
db_disconnect($mdb2_smt);

// DB��³(seisan_project)
$mdb2 = db_connect();

// [station_data]����Լ���
$res_row = $mdb2->queryRow("SELECT * FROM station_data WHERE st_id='$st_id'");
if (PEAR::isError($res_row)) {
} elseif ($res_row[0]!=NULL) {
	$unit_id = $res_row[1];
	$p_name  = $res_row[3];
}
unset($res_row);

// [unit_data]����Լ���
$res_row = $mdb2->queryRow("SELECT * FROM unit_data WHERE unit_id='$unit_id'");
if (PEAR::isError($res_row)) {
} elseif ($res_row[0]!=NULL) {
	$file_name = $res_row[2];
	$board     = $res_row[3];
}
unset($res_row);

// [set_data]����Լ���
$set_i = 0;
$res_query = $mdb2->query("SELECT * FROM set_data WHERE st_id='$st_id'");
$res_query = err_check($res_query);
while($row = $res_query->fetchRow(MDB2_FETCHMODE_ASSOC)) {
	$set_id[$set_i] = $row['set_id'];
	$set_i++;
}

// ���åȿ��Υ������
$cnt_set = count($set_id) - 1;

// DB����(seisan_project)
db_disconnect($mdb2);

// �إå���&�եå�����ɽ��
class HeaderFooterExtended extends MBFPDF {
	function Header() {
		pdf_Header($this, '������� �ǡ�����Ͽ�� �С�������[��0��]');
	}
//	function Footer() {
//		pdf_Footer($this);
//	}
}

// A4�Ļ���
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

// ������������ �С�������
pdf_Order_Cell($pdf, $pdf_data);

// ���åȰ��� �С�������
for ($i=0; $i<=19; $i++) {
	if ($set_id[$i]!=NULL) {
		pdf_Set_Cell($pdf, $mdb2, $i, $set_id[$i]);
	}
}

// �����ƥ���Ͽ������󥻥� �С�������ɽ��
pdf_Entry_Cell($pdf);

// 20��ʾ����Ͽ�ǡ�����������ϥڡ����ɲ�
if ($cnt_set>=20) {

	// �ڡ����ɲ�
	$pdf->AddPage('Portrait');
	
	// ������������ �С�������
	pdf_Order_Cell($pdf, $pdf_data);
	
	// ���åȰ��� �С�������
	for ($i=0; $i<=19; $i++) {
		if ($set_id[$i + 20]!=NULL) {
			pdf_Set_Cell($pdf, $mdb2, $i, $set_id[$i + 20]);
		}
	}
	
	// �����ƥ���Ͽ������󥻥� �С�������ɽ��
	pdf_Entry_Cell($pdf);

}

$pdf->AliasNbPages();
$pdf->Output();


?>
