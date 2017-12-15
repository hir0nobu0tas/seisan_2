<?php
//-------------------------------------------------------------------
// excel_out_inc2.php
// Excel出力 セルフォーマット定義 2(セットアップシート)
//
// 2008/10/22
// 2008/10/31 現場要望で「罫線を細く」に疑似対応する為線色を変えてみた
// 2016/02/01 高額品のセル色追加
//
//-------------------------------------------------------------------

// セルフォーマット定義
// [表題]
$f0 =& $workbook->addFormat();
$f0->setFontFamily("MS UI Gothic");
$f0->setSize($font_size[1]);
$f0->setBold(1);
$f0->setAlign('center');
$f0->setAlign('vcenter');
$f0->setColor(0);
$f0->setFgColor(42);

// [基本データ]
$f1 =& $workbook->addFormat();
$f1->setFontFamily("MS UI Gothic");
$f1->setSize($font_size[1]);
$f1->setAlign('left');
$f1->setAlign('vcenter');
$f1->setBorder(1);
$f1->setColor(0);
$f1->setFgColor(31);

// [項目]
$f2 =& $workbook->addFormat();
$f2->setFontFamily("MS UI Gothic");
$f2->setSize($font_size[1]);
$f2->setAlign('center');
$f2->setAlign('vcenter');
$f2->setBorder(1);
$f2->setColor(0);
$f2->setFgColor(47);

// [データ 左詰]
$f3 =& $workbook->addFormat();
$f3->setFontFamily("MS UI Gothic");
$f3->setSize($font_size[1]);
$f3->setAlign ('left');
$f3->setAlign('vcenter');
$f3->setBorder (1);
$f3->setColor(0);
$f3->setFgColor(9);
$f3->setBorderColor(55);

// [データ 右詰]
$f4 =& $workbook->addFormat();
$f4->setFontFamily("MS UI Gothic");
$f4->setSize($font_size[1]);
$f4->setAlign ('right');
$f4->setAlign('vcenter');
$f4->setBorder (1);
$f4->setColor(0);
$f4->setFgColor(9);
$f4->setBorderColor(55);

// [データ 中央]
$f5 =& $workbook->addFormat();
$f5->setFontFamily("MS UI Gothic");
$f5->setSize($font_size[1]);
$f5->setAlign ('center');
$f5->setAlign('vcenter');
$f5->setBorder (1);
$f5->setColor(0);
$f5->setFgColor(9);
$f5->setBorderColor(55);

// [旧データ(照合OK) 左詰]
$f6 =& $workbook->addFormat();
$f6->setFontFamily("MS UI Gothic");
$f6->setSize($font_size[1]);
$f6->setAlign ('left');
$f6->setAlign('vcenter');
$f6->setBorder (1);
$f6->setColor(0);
$f6->setFgColor(26);

// [旧データ(照合OK) 中央]
$f7 =& $workbook->addFormat();
$f7->setFontFamily("MS UI Gothic");
$f7->setSize($font_size[1]);
$f7->setAlign ('center');
$f7->setAlign('vcenter');
$f7->setBorder (1);
$f7->setColor(0);
$f7->setFgColor(26);

// [旧データ(照合NG) 左詰]
$f8 =& $workbook->addFormat();
$f8->setFontFamily("MS UI Gothic");
$f8->setSize($font_size[1]);
$f8->setAlign ('left');
$f8->setAlign('vcenter');
$f8->setBorder (1);
$f8->setColor(0);
$f8->setFgColor(51);

// [旧データ(照合NG) 中央]
$f9 =& $workbook->addFormat();
$f9->setFontFamily("MS UI Gothic");
$f9->setSize($font_size[1]);
$f9->setAlign ('center');
$f9->setAlign('vcenter');
$f9->setBorder (1);
$f9->setColor(0);
$f9->setFgColor(51);

// [面情報]
$f10 =& $workbook->addFormat();
$f10->setFontFamily("MS UI Gothic");
$f10->setSize($font_size[3]);
$f10->setAlign ('center');
//$f10->setBorder (1);
$f10->setColor(0);
$f10->setFgColor(27);

// [選択棚番]
$f11 =& $workbook->addFormat();
$f11->setFontFamily("MS UI Gothic");
$f11->setSize($font_size[1]);
$f11->setAlign ('center');
//$f11->setBorder (1);
$f11->setColor(0);
$f11->setFgColor(27);

// [シート備考]
$f12 =& $workbook->addFormat();
$f12->setFontFamily("MS UI Gothic");
$f12->setSize($font_size[1]);
$f12->setBold(1);
$f12->setAlign ('center');
$f12->setAlign('vcenter');
//$f12->setBorder (1);
$f12->setColor(0);
$f12->setFgColor(51);

// [高額品]
$f13 =& $workbook->addFormat();
$f13->setFontFamily("MS UI Gothic");
$f13->setSize($font_size[1]);
$f13->setAlign ('left');
$f13->setAlign('vcenter');
$f13->setBorder (1);
$f13->setColor(0);
$f13->setFgColor(51);
$f13->setBorderColor(55);

?>
