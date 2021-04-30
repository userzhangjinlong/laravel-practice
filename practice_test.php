<?php
#输出未找到 查询所在位置所以第0个 所以是未找到
//$str = 'abc';
//$res = strpos($str,'a');
//if ($res){
//    echo '找到了';
//}
//else {
//    echo '未找到';
//}

/*$str1 = null;
$str2 = false;
echo $str1 == $str2 ? '相等' : '不相等'; //相等
$str3 = '';
$str4 = 0;
echo $str3 == $str4 ? '相等' : '不相等'; //相等
$str5 = 0;
$str6 = '0';
echo $str5===$str6 ? '相等' : '不相等'; //不相等*/

/*$a1 = null;
$a2 = false;
$a3 = 0;
$a4 = '';
$a5 = '0';
$a6 = 'null';
$a7 = array();
$a8 = array(array());
echo empty($a1) ? 'true' : 'false'; //true
echo empty($a2) ? 'true' : 'false'; //true
echo empty($a3) ? 'true' : 'false'; //true
echo empty($a4) ? 'true' : 'false'; //true
echo empty($a5) ? 'true' : 'false'; //false true
echo empty($a6) ? 'true' : 'false'; //false
echo empty($a7) ? 'true' : 'false'; //true
echo empty($a8) ? 'true' : 'false'; //false*/

//$test = 'aaaaaa';
//$abc = & $test;
//unset($test); //销毁变量
//echo $abc; //null aaaaaa

/*$count = 5;
function get_count(){
    static $count = 0; //静态变量重置内存中变量的值
    return $count++;
}
echo $count; //5
++$count;
echo get_count(); //0
echo get_count(); //1*/

//考察和array_merge的区别 数组相加会返回第一个遇到的值 array_merge则会将所有值合并到一起
/*$a = [0,1,2,3];
$b = [1,2,3,4,5];
$a+=$b;
var_export($a); //[0,1,2,3,4,5] */

//引用变量$v会导致数组记住最后一个值，所以下面循环的时候会导致最后一个值会成为倒数第二个值 如果变量名还是一样 内存地址就未变
/*$a = [1,2,3];
foreach ($a as &$v){

}
foreach ($a as $v){

}
var_export($a); //[1,2,3] [1,2,2]*/

//var_export(array_merge([], [[1,2,4],[4,5,6]]));

$a = 0.57;
echo intval(floatval($a) * 100);
