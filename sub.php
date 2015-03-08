<?php
$srcHome="/home/xggong/home1/zhouy/tcscripts/src";
$projHome=dirname(__FILE__);
$projName=basename($projHome);
if($stage==1){
	$species="Ar-isotope-2D";
	$method="muller";
	$nodes=1;$units="metal";
$m2=2;
$m1=1;
$procs=4;$queue="q1.1";
for($ratio=0;$ratio<=1;$ratio+=0.02){
submit("\$runTime=3000000;\$ratio=$ratio;\$m2=$m2;");
}
}
shell_exec("cp $projHome/sub.php $srcHome;");
require_once("$srcHome/submit.php");
?>
