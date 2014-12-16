<?php
$srcHome="/home/xggong/home1/zhouy/tcscripts/src";
$projHome=dirname(__FILE__);
$projName=basename($projHome);

	$units="metal";
	$species="CC-small";
	$method="nvt";
	$nodes=1;
$procs=4;$queue="q1.1";
$uprocs=1;
$unodes=12;$uqueue="q1.2";
$universe=1;
$runTime=10000000;
if($stage==1){
for($mass=9;$mass<=15;$mass++){
for($i=0;$i<5;$i++){	
submit("\$seed=13515;\$mass=$mass;\$cell=\"C12N/$i\";\$thick=1.44;\$langevin=0;\$hdeta=8*\$deta;\$usinglat=1;\$timestep=.5e-3;\n\$latx=11;\$laty=1;\$latz=1;");
}
}
uexec();
}
shell_exec("cp $projHome/sub.php $srcHome;");
require_once("$srcHome/submit.php");
?>
