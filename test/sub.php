<?php
$srcHome="/home/xggong/home1/zhouy/tcscripts/src";
$projHome=dirname(__FILE__);
$projName=basename($projHome);
if($stage==1){
	$units="metal";
	$species="C3N";
	$method="muller";
	for($lx=200;$lx<300;$lx*=2){
	submit("\$xlen=$lx * Unit('metal','l');",array("xlen"=>$lx* Unit('metal','l')));
	}
}
shell_exec("cp $projHome/sub.php $srcHome;");
require_once("$srcHome/submit.php");
?>
