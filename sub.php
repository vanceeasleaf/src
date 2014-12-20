<?php
$srcHome="/home/xggong/home1/zhouy/tcscripts/src";
$projHome=dirname(__FILE__);
$projName=basename($projHome);

	$units="metal";
	$species="SiGe";
	$method="greenkubo";
	$nodes=1;
	$procs=4;$queue="q1.4";
	
	$runTime=10000000;
	if($stage==1){
	for($ratio=0.05;$ratio<=0.95;$ratio+=.05){
	submit("\$seed=13513;\$computeTc=1;\$ratio=$ratio;\$usinglat=1;\$hdeta=\$deta;\$latx=3;\$laty=3;\$latz=3;",array("ratio"=>$ratio));
	}
}
shell_exec("cp $projHome/sub.php $srcHome;");
require_once("$srcHome/submit.php");
?>
