<?php
	require_once("genPbs.php");
	require_once("units.php");

if(!$projHome)exit("please set your project path!");
date_default_timezone_set("PRC");
function setSubProject($index){
global $projHome;
$pid=shell_exec("cd $projHome/$index;qsub lammps.pbs;");
echo "submit: $projHome/$index\n";
#sleep(1);
return intval($pid);
}
$loops=fopen("$projHome/qloops.txt","w");
$idx=0;
function write($cmd,$fileName){
	$file=fopen($fileName,"w");
	fprintf($file,$cmd."\n");
	fclose($file);
}
function submit(){
	global $projHome;
	global $projName;
	$argc= func_num_args();    #获取参数个数
       $argv = func_get_args();    #获取参数值
       if($argc>0)$cmd=$argv[0];
       else $cmd="";
	global $idx;
	global $loops;
	global $nodes;
	global $procs;
	global $species;
	global $method;
	global $units;
		global $queue;
	global $runTime;
		if(!$queue)$queue="q1.1";
	if(!$nodes)$nodes=1;
	if(!$procs)$procs=12;
	$para="";
		makeLoopFile($cmd,$idx);
	$pid=setSubProject($idx);
	$json_obj=array("id"=>$idx,"pid"=>$pid,"time"=>date('Y-m-d H:i:s'),"cmd"=>$cmd,"nodes"=>$nodes,"procs"=>$procs,"species"=>$species,"method"=>$method,"units"=>$units,"runTime"=>$runTime);
	//fprintf($loops,"{\"id\":\"%s\",\"pid\":\"%s\",\"time\":\"%s\",\"cmd\":\"%s\",\"status\":\"Q\"",$idx,$pid,date('Y-m-d H:i:s'),$cmd);
	if($argc>1){
		$pa=$argv[1];
		while($key=key($pa)){
				$json_obj[$key]=$pa[$key];
				$para=sprintf("$para,\"%s\":%s",$key,$pa[$key]);
				next($pa);
		}
	}
	fprintf($loops,"%s\n",json_encode($json_obj));
		write($cmd.";\n\$projHome=\"$projHome/$idx\";".$para,"$projHome/$idx/cmd.txt");
	$idx++;
}
function makeLoopFile($cmd,$idx){
	global $projHome;
	global $projName;
	global $species;
	global $units;
	global $method;
	global $queue;
		global $nodes;
	global $procs;
	shell_exec("mkdir -p $projHome/$idx;cd $projHome/$idx;mkdir -p minimize");
genPbs("$projHome/$idx","zy_$projName"."_$idx",$queue,$nodes,$procs);
	write("<?php\n$cmd;\n\$projHome=\"$projHome/$idx\";\n?>","$projHome/$idx/qloop.php");
	write("<?php\n\$species=\"$species\";\n\$units=\"$units\";\n\$method=\"$method\";\n?>","$projHome/$idx/species.php");
}
?>
