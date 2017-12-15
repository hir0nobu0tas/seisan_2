<?php

//-------------------------------------------------------------------
// CODE39用チェックデジット モジュラス43計算
//
// CODE39のチェックデジット計算(テクニカルのwebより)
// ------------------------------------------------------------------
// (3)モジュラス43
// モジュラス43はCODE39で用いられるチェックデジットの計算方法です。
// CODE39で有効な43種類のキャラクタにそれぞれ NumericNumberが割り当てら
// れていて、 数値に換算された後に計算します。
// CODE39で「*TEST*」のチェックデジットを求めてみます。
//
// 1. 第2表より、すべてのデータキャラクタを数値に変換します。
//   （スタート、ストップコード「＊」は除きます。）
//    TEST→29,14,28,29
//
// 2. 変換された数値の和を取ります。
//    29 ＋ 14 ＋ 28 ＋ 29 = 100
//
// 3. 2の結果を43で割り、余りを算出します。
//    100÷43 = 余り14
//
// 4. 3で算出した余りの14を、第2表でキャラクタに変換します。
//    14 -> E
//    チェックデジットはEで、データは「*TESTE*」となります。
//
// 第2表　モジュラス43　チェックデジット換算表
// 文字 	0 	1 	2 	3 	4 	5 	6 	7 	8 	9 	A 	B 	C 	D 	E
// 数値 	0 	1 	2 	3 	4 	5 	6 	7 	8 	9 	10 	11 	12 	13 	14
//
// 文字 	F 	G 	H 	I 	J 	K 	L 	M 	N 	O 	P 	Q 	R 	S 	T
// 数値 	15 	16 	17 	18 	19 	20 	21 	22 	23 	24 	25 	26 	27 	28 	29
//
// 文字 	U 	V 	W 	X 	Y 	Z 	- 	・ 	　 	$ 	/ 	+ 	%
// 数値 	30 	31 	32 	33 	34 	35 	36 	37 	38 	39 	40 	41 	42
// ------------------------------------------------------------------
//
// 2006/12/26
// テストOK
//
//-------------------------------------------------------------------

//-------------------------------------------------------------------
// Char2Code(文字 -> 数値 変換)
//
// 引数 $value    = 1文字
//
// 戻り値 return  = 数値
//-------------------------------------------------------------------
function Char2Code($value) {

	switch($value) {
	case '0':
		return('0');
		break;
	case '1':
		return('1');
		break;
	case '2':
		return('2');
		break;
	case '3':
		return('3');
		break;
	case '4':
		return('4');
		break;
	case '5':
		return('5');
		break;
	case '6':
		return('6');
		break;
	case '7':
		return('7');
		break;
	case '8':
		return('8');
		break;
	case '9':
		return('9');
		break;
	case 'A':
		return('10');
		break;
	case 'B':
		return('11');
		break;
	case 'C':
		return('12');
		break;
	case 'D':
		return('13');
		break;
	case 'E':
		return('14');
		break;
	case 'F':
		return('15');
		break;
	case 'G':
		return('16');
		break;
	case 'H':
		return('17');
		break;
	case 'I':
		return('18');
		break;
	case 'J':
		return('19');
		break;
	case 'K':
		return('20');
		break;
	case 'L':
		return('21');
		break;
	case 'M':
		return('22');
		break;
	case 'N':
		return('23');
		break;
	case 'O':
		return('24');
		break;
	case 'P':
		return('25');
		break;
	case 'Q':
		return('26');
		break;
	case 'R':
		return('27');
		break;
	case 'S':
		return('28');
		break;
	case 'T':
		return('29');
		break;
	case 'U':
		return('30');
		break;
	case 'V':
		return('31');
		break;
	case 'W':
		return('32');
		break;
	case 'X':
		return('33');
		break;
	case 'Y':
		return('34');
		break;
	case 'Z':
		return('35');
		break;
	case '-':
		return('36');
		break;
	case '.':
		return('37');
		break;
	case ' ':
		return('38');
		break;
	case '$':
		return('39');
		break;
	case '/':
		return('40');
		break;
	case '+':
		return('41');
		break;
	case '%':
		return('42');
		break;
	}

}

//-------------------------------------------------------------------
// Code2Char(数値 -> 文字 変換)
//
// 引数 $value    = 数値
//
// 戻り値 return  = 1文字
//
//-------------------------------------------------------------------
function Code2Char($value) {

	switch($value) {
	case '0':
		return('0');
		break;
	case '1':
		return('1');
		break;
	case '2':
		return('2');
		break;
	case '3':
		return('3');
		break;
	case '4':
		return('4');
		break;
	case '5':
		return('5');
		break;
	case '6':
		return('6');
		break;
	case '7':
		return('7');
		break;
	case '8':
		return('8');
		break;
	case '9':
		return('9');
		break;
	case '10':
		return('A');
		break;
	case '11':
		return('B');
		break;
	case '12':
		return('C');
		break;
	case '13':
		return('D');
		break;
	case '14':
		return('E');
		break;
	case '15':
		return('F');
		break;
	case '16':
		return('G');
		break;
	case '17':
		return('H');
		break;
	case '18':
		return('I');
		break;
	case '19':
		return('J');
		break;
	case '20':
		return('K');
		break;
	case '21':
		return('L');
		break;
	case '22':
		return('M');
		break;
	case '23':
		return('N');
		break;
	case '24':
		return('O');
		break;
	case '25':
		return('P');
		break;
	case '26':
		return('Q');
		break;
	case '27':
		return('R');
		break;
	case '28':
		return('S');
		break;
	case '29':
		return('T');
		break;
	case '30':
		return('U');
		break;
	case '31':
		return('V');
		break;
	case '32':
		return('W');
		break;
	case '33':
		return('X');
		break;
	case '34':
		return('Y');
		break;
	case '35':
		return('Z');
		break;
	case '36':
		return('-');
		break;
	case '37':
		return('.');
		break;
	case '38':
		return(' ');
		break;
	case '39':
		return('$');
		break;
	case '40':
		return('/');
		break;
	case '41':
		return('+');
		break;
	case '42':
		return('%');
		break;
	}

}

//-------------------------------------------------------------------
// Modulus43_Code(モジュラス43 計算 バーコード用)
//
// 引数 $value = 文字列
//
// 戻り値 return = '*' + 文字列 + チェックデジット(1文字) + '*'
//
// 2007/07/05
// チェックデジット付加を一旦キャンセル(他のバーコードと合わせる)
//
//-------------------------------------------------------------------
function Modulus43_Code($value) {

//	// チェックデジット区切り用'-'追加
//	$value = $value . '-';
	$len = strlen($value);

	for ($i=0; $i<$len; $i++) {
		$str[$i] = substr($value, $i, 1);
	}

	for ($i=0; $i<$len; $i++) {
		$code[$i] = Char2Code($str[$i]);
	}

	for ($i=0; $i<$len; $i++) {
		$sum = $sum + $code[$i];
	}

	$odd = $sum % 43;

	$digit = Code2Char($odd);

//	return('*' . $value . $digit . '*');
	return('*' . $value . '*');

}

//-------------------------------------------------------------------
// Modulus43_Disp(モジュラス43 計算 表示用)
//
// 引数 $value = 文字列
//
// 戻り値 return = '*' + 文字列 + '*'(チェックデジットは表示しない)
//-------------------------------------------------------------------
function Modulus43_Disp($value) {

	// 内容表示用に引数の両端に'*'を付けて返すのみ
//	$len = strlen($value);
//
//	for ($i=0; $i<$len; $i++) {
//		$str[$i] = substr($value, $i, 1);
//	}
//
//	for ($i=0; $i<$len; $i++) {
//		$code[$i] = Char2Code($str[$i]);
//	}
//
//	for ($i=0; $i<$len; $i++) {
//		$sum = $sum + $code[$i];
//	}
//
//	$odd = $sum % 43;
//
//	$digit = Code2Char($odd);

	return('*' . $value . '*');

}

?>
