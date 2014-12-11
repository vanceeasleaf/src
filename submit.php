<?php
$initsub=1;
require_once("sub.php");
require_once("$srcHome/config.php");
require_once("$srcHome/funcs.php");
function pwrite($fp,$s){   
	printf("$s");
	fprintf($fp,"$s");
}
function tabjoin(){
	  $argc = func_num_args();    #获取参数个数
        $argv = func_get_args();    #获取参数值
        $s="";
        for($i=0;$i<$argc;$i++){
        	$s.=$argv[$i];
        	if($i<$argc-1)$s.="\t";
        }
	return $s;
}
if($argc==1){
	clean();
	require_once("$srcHome/toolSub.php");
	$stage=1;
	require("sub.php");
}
else if($argv[1]=="q"){
	$result=fopen("$projHome/result.txt","w");
        $obj=getObjs("$projHome/qloops.txt");
        $paras=getParas($obj);
         echo "id\tpercent\tstatus\tqueue\tprocs";
         fprintf($result,"id");
         for($j=0;$j<count($paras);$j++){
         	 pwrite($result,"\t".$paras[$j]);
         }
         pwrite($result,"\tkappa\ttotalE\tNatom\tE/N\tdisorder\tdisorderC\n");
        for($i=0;$i<count($obj);$i++){
        	$ob=$obj[$i];
        	        	$id=$ob["id"];
        	$pid=$ob["pid"];
        	$runTime=$ob["runTime"];
        	$lastline=shell_exec("cd $projHome/$id;tail -1 log.out 2>log");
        	$qstat=shell_exec("qstat $pid 2>&1|tail -1 ");
        	if(strpos($qstat,"Unknown Job Id")){
        		        	$time="complete";
        		   if(strpos($lastline,"builds")){
        	$status="C";
        	$percent="100%";
	           	}else{
	           		$status="E";
	           		$percent="1%";
	           	}
        	$queue="q0";
        	$nodes="0";
        	$procs="0";
        	}else{
        	list($null,$null,$null,$time,$status,$queue)=sscanf($qstat,"%s%s%s%s%s%s");
        	$info=shell_exec("qstat -f $pid 2>&1|grep nodes");

        list($null,$null,$info)=sscanf($info,"%s%s%s");
        $nnn=split(":ppn=",$info);
        $nodes=$nnn[0];
        $procs=$nnn[1];
        	        	list($step)=sscanf($lastline,"%d");
        	$percent=sprintf("%.1f%%",$step/$runTime*100);
        	}

        	echo tabjoin($id,$percent,$status,$queue,$nodes."x".$procs);
        	for($j=0;$j<count($paras);$j++){
        		$key=$paras[$j];
         	 printf("\t%s",$ob[$key]===""?"def":$ob[$key]);
         	 if($percent+0>.5){
         	    	 fprintf($result,"%s\t",$ob[$key]==""?"def":$ob[$key]);
         	 }
         	          }
         	          if($percent+0>0){
         	          	  $dir="$projHome/$id";
         	          	  $dir=preg_replace("/\//","\\\/",$dir);
         	          	  $sed="sed 's/projHome=.\+/projHome=\"".$dir."\";/g ' qloop.php>qloop.php1";
         	          	  if(file_exists("post.php"))$postfile= "../post.php";
         	          	  else $postfile="";
         	          	  passthru("cd $projHome/$id;$sed;cat qloop.php1 $postfile>qloop.php2;$php $srcHome/profile.php \"$projHome/$id/qloop.php2\" \"$projHome/$id/species.php\";");
         	          $kappaline=shell_exec("cd $projHome/$id;tail -1 result.txt;");
         	          $kappa=trim(substr($kappaline,strpos($kappaline,'=')+1));
         	          pwrite($result,"$kappa");
         	          $totalEline=shell_exec("cd $projHome/$id/minimize;tail -22 log.out| head -1;");
         	          list($null,$totalE)=sscanf($totalEline,"%d%f");
         	        pwrite($result,"\t$totalE");
         	                   	          $Natomline=shell_exec("cd $projHome/$id/minimize;head -5 log.out|tail -1 ;");
         	          list($Natom)=sscanf($Natomline,"%d");
         	          if($Natom){
         	        pwrite($result,"\t$Natom");
         	          $epn=$totalE/$Natom;
         	          
         	        pwrite($result,"\t$epn");
         	          }
         	           $disorderLine=shell_exec("cd $projHome/$id/minimize;mkdir disorder 2>err;cd disorder;cp $srcHome/in.disorder .;$APP_PATH<in.disorder 2>err 1>log;tail -1 disorder.txt  2>err;");
         	          list($null,$disorder)=sscanf($disorderLine,"%d%f");
         	          pwrite($result,"\t$disorder");
         	          $disorderLine=shell_exec("cd $projHome/$id/minimize;mkdir disorderC 2>err;cd disorderC;cp $srcHome/in.disorderC .;$APP_PATH<in.disorderC 2>err 1>log;tail -1 disorder.txt  2>err;");
         	          list($null,$disorderC)=sscanf($disorderLine,"%d%f");
         	          pwrite($result,"\t$disorderC");
         	          /*
         	             $nonequ=shell_exec("cd $projHome/$id/minimize;mkdir nonequ 2>err;cd nonequ;$php $srcHome/nonequ.php;");
         	          pwrite($result,"\t$nonequ");
         	          $nonequ3=shell_exec("cd $projHome/$id/minimize/nonequ ;$php $srcHome/nonequ3.php;");
         	          pwrite($result,"\t$nonequ3");
         	           $nonequ4=shell_exec("cd $projHome/$id/minimize/nonequ ;$php $srcHome/nonequ4.php;");
         	          pwrite($result,"\t$nonequ4");*/
         	          }
         	       pwrite($result,"\n");
       }
}else if($argv[1]=="clean"){clean();

}else if($argv[1]=="stop"){
	printf("Comfirm to stop all the simulation in this project?[y/n]");
$stdin = fopen('php://stdin', 'r');
list($s)=fscanf($stdin,"%s"); 
if($s!="y")exit("exit with no change.");
stop();

}
function stop(){
	global $projName;
		global $projHome;
		
		//容易kill掉同名工程程序

$tarname="zy_$projName"."_";
if(strlen($tarname)<10){
	shell_exec("qstat|grep $tarname>tmp");
	$file=fopen("$projHome/tmp","r");
	while(list($pid)=fscanf($file,"%s")){
		$pid=intval($pid);
		echo "qdel:$pid\n";
		shell_exec("qdel $pid 2>log");
	}
}else{
		shell_exec("qstat|grep xggong >tmp");
	$file=fopen("$projHome/tmp","r");
	while(list($pid)=fscanf($file,"%s")){
		$pid=intval($pid);
		$jobnameString=shell_exec("qstat -f $pid |grep Job_Name");
		list($null,$null,$jobname)=sscanf($jobnameString,"%s%s%s");
		if(strstr($jobname,$tarname)){
			echo "qdel:$pid\n";
			shell_exec("qdel $pid 2>log");
		}
	}
}

		/*
//复制文件夹后容易kill掉原来工程的程序
		$qloop=fopen("qloops.txt","r");
$n=0;			
while($json_string=fgets($qloop)){
        $obj[$n++]=json_decode($json_string,true);
        }
        for($i=0;$i<count($obj);$i++){
        	$ob=$obj[$i];
        	$pid=$ob["pid"];
	shell_exec("qdel $pid 2>log");
        }
        */
}
function getObjs($fileName){
		$qloop=fopen($fileName,"r");
		$n=0;
		while($json_string=fgets($qloop)){
        	$obj[$n++]=json_decode($json_string,true);
        }
        return 	$obj;
}
 function getParas($obj){
	$paras=array();
         for($i=0;$i<count($obj);$i++){
         	 $pa=$obj[$i];
         	 while($key=key($pa)){
         	 	 if($key=="id"||$key=="pid"||$key=="time"||$key=="cmd"||$key=="project"||$key=="nodes"||$key=="procs"||$key=="species"||$key=="units"||$key=="method"){next($pa);continue;}
         	 	 $flag=0;
         	 	 for($j=0;$j<count($paras);$j++){
         	 	 	 if($key==$paras[$j]){$flag=1;break;}
         	 	 }
         	 	 if($flag==0)$paras[count($paras)]=$key;
				next($pa);
		}
         }
         return $paras;
}
function clean(){
	global $projName;
	global $projHome;
	printf("Comfirm to clean all the files in this project?[y/n]");
	$stdin = fopen('php://stdin', 'r');
	list($s)=fscanf($stdin,"%s"); 
	if($s!="y")exit();
	stop();
		
	shell_exec("cd $projHome;ls >$projHome/tmp");
	$file=fopen("$projHome/tmp","r");
	while(list($ls)=fscanf($file,"%s")){
		if($ls=="sub.php"||$ls=="tmp"||$ls=="post.php"||$ls=="data")continue;
		echo "deleting:$ls\n";
		shell_exec("cd $projHome;rm -r $ls");
	}
	shell_exec("cd $projHome;rm tmp");
/*

        rm("log");
        //rm("qloops.txt","result.txt");
        */
}

?>
