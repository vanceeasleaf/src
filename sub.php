<?php
$srcHome="/home/xggong/home1/zhouy/tcscripts/src";
$projHome=dirname(__FILE__);
$projName=basename($projHome);
if($stage==1){
	$units="metal";
	$species="CC-small";
	$method="greenkubo";
	$nodes=1;
$procs=4;$queue="q1.1";
$runTime=5000000;
for($mass=9.1;$mass<15;$mass+=0.3){
submit("\$nvt=1;\$gstart=500000;\$runTime=$runTime;\$computeTc=1;\$seed=13251253;\$mass=$mass;\$cell=\"CN/0\";\$thick=1.44;\$langevin=0;\$hdeta=8*\$deta;\$usinglat=1;\$timestep=.5e-3;\n\$latx=1;\$laty=1;\$latz=1;");
}}
shell_exec("cp $projHome/sub.php $srcHome;");
require_once("$srcHome/submit.php");
?>
