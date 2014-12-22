<?php
	require_once("genPbs.php");
	require_once("genPbss.php");
	require_once("units.php");
require_once("$srcHome/exec.php");
if(!$projHome)exit("please set your project path!");
date_default_timezone_set("PRC");
function setSubProject($index){
global $projHome;
global $single;
if($single)$pid=exec::background("sh $projHome/$index/run.sh");//$pid=exec("cd $projHome/$index;sh run.sh > /dev/null & echo $!");
else
$pid=shell_exec("cd $projHome/$index;qsub lammps.pbs;");
echo "submit: $pid\t$projHome/$index\n";
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
	global $universe;
		global $queue;
	global $runTime;
		if(!$queue)$queue="q1.1";
	if(!$nodes)$nodes=1;
	if(!$procs)$procs=12;
	$para="";
		makeLoopFile($cmd,$idx);
	if(!$universe)$pid=setSubProject($idx);
	$json_obj=array("id"=>$idx,"pid"=>$pid,"time"=>date('Y-m-d H:i:s'),"cmd"=>$cmd,"nodes"=>$nodes,"procs"=>$procs,"species"=>$species,"method"=>$method,"units"=>$units,"runTime"=>$runTime);
	//fprintf($loops,"{\"id\":\"%s\",\"pid\":\"%s\",\"time\":\"%s\",\"cmd\":\"%s\",\"status\":\"Q\"",$idx,$pid,date('Y-m-d H:i:s'),$cmd);
	if($argc>1){
		$pa=$argv[1];
		while($key=key($pa)){
				$json_obj[$key]=$pa[$key];
				//$para=sprintf("$para,\"%s\":%s",$key,$pa[$key]);
				next($pa);
		}
	}
	fprintf($loops,"%s\n",json_encode($json_obj));
		//write($cmd.";\n\$projHome=\"$projHome/$idx\";".$para,"$projHome/$idx/cmd.txt");
	$idx++;
}
function uexec(){
	global $universe;
	if($universe){
	global $projHome;
	$home=dirname(__FILE__);
	shell_exec("cd $projHome/pbs;ls *.pbs>tmp;");
	$fp=fopen("$projHome/pbs/tmp","r");
	$n=0;
	while(list($st[$n],$ed[$n])=fscanf($fp,"lammps%d-%d.pbs")){
		$n++;
	}
	//sort
	$len=$n;
	for($i=0;$i<$len;$i++){
		$idx[$i]=$i;
	}
	for($i=0;$i<$len;$i++)
		for($j=0;$j<$i;$j++){
			if($st[$i]<$st[$j]){
				swap($st[$i],$st[$j]);
				swap($ed[$i],$ed[$j]);
				swap($idx[$i],$idx[$j]);
			}
		}
	$fi=fopen("$projHome/pbs/info","w");
	for($i=0;$i<$len;$i++){
		$p=$i;
		$st1=$st[$p];
		$ed1=$ed[$p];
		echo "lammps${st1}-${ed1}.pbs\n";
		$lb="${st1}-${ed1}";
		$pid=shell_exec("cd $projHome/pbs;qsub lammps${st1}-${ed1}.pbs;");
		echo $pid;
		$pid=trim($pid);
		for($j=$st1;$j<=$ed1;$j++){
			$b=$j-$st1;
			fprintf($fi,"$j\t$pid\tlog.$lb.$b\tscreen.$lb.$b\n");
		}
	}	
	}
}	
function swap(&$a,&$b){
	$tmp=$b;$b=$a;$a=$tmp;
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
	global $uqueue;
	global $single;
		global $universe;
	if($universe){
		echo "prepared: $projHome/$idx\n";
		global $unodes;
		global $uprocs;
		$cores=$procs*$nodes;
		if(!$unodes)$unodes=20;
		if(!$uprocs)$uprocs=1;
		$ucores=$unodes*$uprocs;
		$len=floor($ucores/$cores);
		if(!$uqueue)$uqueue="q3.4";
		if($idx%$len==0)
		genPbss("$projHome","zy_$projName"."_",$uqueue,$unodes,$uprocs,$idx,$cores);
	}

	shell_exec("mkdir -p $projHome/$idx;cd $projHome/$idx;mkdir -p minimize");
		if($single){
		genSh("$projHome/$idx","zy_$projName"."_$idx",$procs);
	}
	if(!$universe&&!$single)genPbs("$projHome/$idx","zy_$projName"."_$idx",$queue,$nodes,$procs);
	write("<?php\n$cmd;\n\$projHome=\"$projHome/$idx\";\n?>","$projHome/$idx/qloop.php");
	write("<?php\n\$species=\"$species\";\n\$units=\"$units\";\n\$method=\"$method\";\n?>","$projHome/$idx/species.php");
}
?>
