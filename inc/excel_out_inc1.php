<?php
//-------------------------------------------------------------------
// excel_out_inc1.php
// Excel出力 セルフォーマット定義 1
//
// 2007/03/16
// 2007/09/11 右詰を数値(0.00)と定義
//-------------------------------------------------------------------

// セルフォーマット定義
// [表題]
$f0 =& $workbook->addFormat();
$f0->setFontFamily("MS UI Gothic");
$f0->setSize($font_size[0]);
$f0->setBold(1);
$f0->setAlign('center');
$f0->setAlign('vcenter');
$f0->setColor(0);
$f0->setFgColor(42);
//$f0->setMerge();

// [日付]
$f1 =& $workbook->addFormat();
$f1->setFontFamily("MS UI Gothic");
$f1->setSize($font_size[1]);
$f1->setBold(1);
$f1->setAlign('center');
$f1->setAlign('vcenter');
//$f1->setBorder(1);
$f1->setColor(0);
$f1->setFgColor(31);

// [項目]
$f2 =& $workbook->addFormat();
$f2->setFontFamily("MS UI Gothic");
$f2->setSize($font_size[2]);
$f2->setAlign('center');
$f2->setAlign('vcenter');
$f2->setBorder(1);
$f2->setColor(0);
$f2->setFgColor(47);

// [データ(共晶)左詰]
$f3 =& $workbook->addFormat();
$f3->setFontFamily("MS UI Gothic");
$f3->setSize($font_size[2]);
$f3->setAlign ('left');
$f3->setAlign('vcenter');
$f3->setBorder (1);
$f3->setColor(0);
$f3->setFgColor(9);

// [データ(共晶)右詰](数値 0.00)
$f4 =& $workbook->addFormat();
$f4->setFontFamily("MS UI Gothic");
$f4->setSize($font_size[2]);
$f4->setAlign ('right');
$f4->setAlign('vcenter');
$f4->setBorder (1);
$f4->setColor(0);
$f4->setFgColor(9);
$f4->setNumFormat('0.00');

// [データ(共晶)中央]
$f5 =& $workbook->addFormat();
$f5->setFontFamily("MS UI Gothic");
$f5->setSize($font_size[2]);
$f5->setAlign ('center');
$f5->setAlign('vcenter');
$f5->setBorder (1);
$f5->setColor(0);
$f5->setFgColor(9);

// [データ(PBF)左詰]
$f6 =& $workbook->addFormat();
$f6->setFontFamily("MS UI Gothic");
$f6->setSize($font_size[2]);
$f6->setAlign ('left');
$f6->setAlign('vcenter');
$f6->setBorder (1);
$f6->setColor(0);
$f6->setFgColor(43);

// [データ(PBF)右詰](数値 0.00)
$f7 =& $workbook->addFormat();
$f7->setFontFamily("MS UI Gothic");
$f7->setSize($font_size[2]);
$f7->setAlign ('right');
$f7->setAlign('vcenter');
$f7->setBorder (1);
$f7->setColor(0);
$f7->setFgColor(43);
$f7->setNumFormat('0.00');

// [データ(PBF)中央]
$f8 =& $workbook->addFormat();
$f8->setFontFamily("MS UI Gothic");
$f8->setSize($font_size[2]);
$f8->setAlign ('center');
$f8->setAlign('vcenter');
$f8->setBorder (1);
$f8->setColor(0);
$f8->setFgColor(43);

// [セパレータ](数値 0.00)
$f9 =& $workbook->addFormat();
$f9->setFontFamily("MS UI Gothic");
$f9->setSize($font_size[2]);
$f9->setAlign ('right');
$f9->setBorder (1);
$f9->setColor(0);
$f9->setFgColor(27);
$f9->setNumFormat('0.00');

?>
