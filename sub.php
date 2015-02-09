<?php
$srcHome="/home/xggong/home1/zhouy/tcscripts/src";
$projHome=dirname(__FILE__);
$projName=basename($projHome);
if($stage==1){
	$species="Ar-isotope";
	$method="muller";
	$nodes=1;
$procs=4;$queue="q1.4";
for($ratio=0;$ratio<=1;$ratio+=0.01){
submit("\$ratio=$ratio");
}
}
shell_exec("cp $projHome/sub.php $srcHome;");
require_once("$srcHome/submit.php");
?>
