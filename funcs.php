<?php
function random01()
{
    return mt_rand() / mt_getrandmax();
}
	function rm($file){
				$paramNum = func_num_args();    
$params = func_get_args();    
for($i=0;$i<$paramNum;$i++){
		$path=trim(shell_exec("pwd"));
	echo "deleting:$path/$params[$i]\n";
	shell_exec("if [ -e ".$params[$i]." ]; then rm -r ".$params[$i]."; fi");
}
}
function arr_ave($array){
	$n=count($array);
$sum=0;
for($i=0;$i<$n;$i++){
$sum+=$array[$i];
}
return $sum/$n;
}
function arr_abs($array){
$out=array();
for($i=0;$i<count($array);$i++){
$out[$i]=abs($array[$i]);
}
return $out;
}
function arr_mul($array,$b){
$out=array();
for($i=0;$i<count($array);$i++){
$out[$i]=$array[$i]*$b[$i];
}
return $out;
}
?>