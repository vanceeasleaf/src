<?php
/** 
 * һЩ���ߺ���
 * @author zhouy
 */

/* ����0-1֮��������������0������1*/
function random01()
{
	return mt_rand() / mt_getrandmax();
}

/* ɾ����ǰĿ¼�µ�һ�������ļ����ļ���*/
function rm($file){
	$paramNum = func_num_args();    
	$params = func_get_args();    
	for($i=0;$i<$paramNum;$i++){
			$path=trim(shell_exec("pwd"));
		echo "deleting:$path/$params[$i]\n";
		shell_exec("if [ -e ".$params[$i]." ]; then rm -r ".$params[$i]."; fi");
	}
}

/* ���һ�������ƽ��ֵ*/
function arr_ave($array){
	$n=count($array);
	$sum=0;
	for($i=0;$i<$n;$i++){
	$sum+=$array[$i];
	}
	return $sum/$n;
}

/* ��������һ������ľ���ֵ*/
function arr_abs($array){
	$out=array();
	for($i=0;$i<count($array);$i++){
	$out[$i]=abs($array[$i]);
	}
	return $out;
}

/* �����ڻ�*/
function arr_mul($array,$b){
	$out=array();
	for($i=0;$i<count($array);$i++){
	$out[$i]=$array[$i]*$b[$i];
	}
	return $out;
}
?>