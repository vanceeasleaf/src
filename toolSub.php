<?php
require_once("genPbs.php");
require_once("genPbss.php");
require_once("units.php");
require_once("$srcHome/exec.php");
if(!$projHome)exit("please set your project path!");

/* ����ʱ��*/
date_default_timezone_set("PRC");

$loops=fopen("$projHome/qloops.txt","w");
$idx=0;

/**
 * ����ĳ������
 * @author zhouy
 * @input ��������
 * @output ������к�
 */
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

function write($cmd,$fileName){
	$file=fopen($fileName,"w");
	fprintf($file,$cmd."\n");
	fclose($file);
}

/**
 * ��ȡ����������qloops.php ���������棬����lammps.pbs�ȶ�����Ϣ����Ͷ����
 * @author zhouy
 */
function submit(){
	global $projHome;
	global $stage;
	if($stage!=1)return;
	global $projName;
	$argc= func_num_args();    #��ȡ��������
       $argv = func_get_args();    #��ȡ����ֵ
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
	
	/* �������������ļ�������submit���ι����Ľ���qloop.php*/
	makeLoopFile($cmd,$idx);
	if(!$universe)$pid=setSubProject($idx);
	
	/* project��ÿ��work����Ϣ��Ҫ��������������*/
	$json_obj=array(
		"id"=>$idx,
		"pid"=>$pid,
		"time"=>date('Y-m-d H:i:s'),
		"cmd"=>$cmd,
		"nodes"=>$nodes,
		"procs"=>$procs,
		"species"=>$species,
		"method"=>$method,
		"units"=>$units,
		"runTime"=>$runTime
	);
		
	/* ʹ��submit�Ĳ������ݵĲ���*/
	if($argc>1){
		$pa=$argv[1];
		while($key=key($pa)){
				$json_obj[$key]=$pa[$key];
				next($pa);
		}
	}
	
	/* ÿ��һ��json*/
	fprintf($loops,"%s\n",json_encode($json_obj));
	$idx++;
}

/**
 * ͳһsubmit������������ֻ�ṩ���ĵڶ����������Զ����ɵ�һ������
 * @author zhouy
 */
function submitq($pa=array()){
	$cmd="";
	foreach($pa as $key=>$val){
		$cmd.="\$".$key."=$val;";
	}
	submit($cmd,$pa);
}

/**
 * �����еĶ���˲��Ϊ���鲢���м���work
 * @author zhouy
 */
function uexec(){
	global $universe;
	if(!$universe)return;
	global $projHome;
	$home=dirname(__FILE__);
	shell_exec("cd $projHome/pbs;ls *.pbs>tmp;");
	$fp=fopen("$projHome/pbs/tmp","r");
	$n=0;
	while(list($st[$n],$ed[$n])=fscanf($fp,"lammps%d-%d.pbs")){
		$n++;
	}
	
	/* sort*/
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

/* exchange*/
function swap(&$a,&$b){
	$tmp=$b;$b=$a;$a=$tmp;
}

/**
 * ����ĳ��work�Ĳ����б�
 * @author zhouy
 */
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
	
	/**
	 * universe ,single ,normal�Ǽ��ֲ�ͬ�Ŀ��Ʒ�ʽ ,Ӧ����һ���ӿ��������ǡ�project�ƹܸ߲��߼�������֪����������ô����ģ���workֻ���Լ�������ͽ������
	 * ����ϸ�ڲ�����Ȥ��
	 * ��ôϵͳ����߼��߼�Ϊ��
	 * �û���Ҫִ��һЩwork���ֱ�ָ�����������ͣ�ϣ����һ��ָ��ϵͳ������ͳһ������Щwork��Ҳ����ѡ���Եص������м�����
	 * ���Ȳ������� work��ǰ����work��ִ�У�work�ĺ���work����ֹ��Ŀǰ����Ҫѡ���Ե��ȡ�
	 * ���Ա���Ϊһ���๦�ܿ��䣬�û�������Ÿ��ֱ��ɣ����������ˢ�ͣ�ǰ�������決��ִ�У�����ģ����������ȻҲ������ʱ��ֹ��ͬʱ��һЩ�������������ơ�
	 * �������ں�ˢ����һ����һ����ˢ�����ô�ˢ��һ��һ�е�ˢ��
	 * ��projectʵ����Userinterface�ӿڡ�projectӵ��һ��controller�������ھ������л��е�ִ�з�ʽ���û���ѡ����
	 * ���ڿ�����һ�����󣺺決�����в鿴���ɵĽ��ȡ����ֱ���һ�������ˣ�����ô���ȶ������ٱ䣬��100%���á�ÿ�����ɿ�������ʱ�䲻ͬ���ʿ�����μ������ֽ��ȡ�
	 * �����ʵķ�ʽ���ɣ������ʱ��ɣ�������20������Ľ����Ƕ��١������ӱ��ɵ�API����ʵ���Ͽ�����ô֪�����ڼ����ˣ���ֻ�ܼ��ȣ����ڼ��������ɵ����ջ��й�ϵ��
	 * ���Ӧ��ֱ���ʱ��ɣ���Ľ����Ƕ��١�
	 * ���ķ����������б��ɿ��������µƾ����ˣ������ɿ�������һ�ݴ˴κ決�ı��ɵı��档��Ȼһ�е���Ϣ��Ӧ�����ʱ��ɵõ��ģ�������Щ��ϢӦ���ʣ�����ѿ��Ʊ���
	 * �決�����и��Ĺ����������˰ɣ��Լ����ɺ決�ú�Ĳ�������������ӡ����������
	 * �������Ļ��Ҫ���������Ϣ��
	 * �決��Ĳ����������ЩҪ�����û�������Ϊ�˽���Ŀɱ��ԣ�����Ҫ����Щ�������ÿ�����ɶ�Ӧ�þ��У�Ҫ��Ȼ���ǲ��ܷ���һ�𿾡�
	 * ��������Ҫ�ʱ�������Щ����ǿ����ṩ�ġ�
	 */
	if($universe){
		printf("prepared: $projHome/$idx\n");
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
	
	/* ͨ��submit���ݵĲ���*/
	write("<?php\n$cmd;\n\$projHome=\"$projHome/$idx\";\n?>","$projHome/$idx/qloop.php");
	
	/* ͨ��sub.php���ݵĲ���*/
	write("<?php\n\$species=\"$species\";\n\$units=\"$units\";\n\$method=\"$method\";\n?>","$projHome/$idx/species.php");
	
	/**
	 * ����ϵͳ��ͳһ���ۣ�
	 * ��ǰϵͳ�д����������ִ��Σ� sub.php �ı�������->����ϵͳ����$method)��submit�д���-> ִ��ϵͳ(��submit("\$runTime=1000000");)��
	 * sub.php��������->ִ��ϵͳ��species.php) ��submit�ĵڶ�������->json
	 * sub.php+submit->����ϵͳ��queryĿǰʹ�õķ����ǣ���submit���̵Ĵ����ظ�һ�飬������3�ִ��Σ���������Ҫʹ��application�еĲ���ʱ������Ϊ��
	 * ��runTime����ֻ��Ҫ��ִ��ϵͳ������Ϊ�˼�����Ⱦ���Ҫ��sub.php��ָ����->json���Ҳ��ܿ�submit�ĵڶ������Σ���Ϊ����û���Ҫʹ��Ĭ��ֵ�Ļ��޷���ȡ��
	 * submit�����״��ο���ͳһ�� submitq,������Ȼ�޷���ȡĬ��ֵ������ ����ϵͳϣ��֪��ִ��ϵͳ�Ĳ����ǲ����ܵġ�
	 * sub.php�򹤳�ϵͳ���ε�Ŀ����$nodes��pbs��صĲ�����
	 * ��submit�ṩ�Ĳ���ִ���ں��棬���Ǳ���Ҫspecies.php
	 */
	 
	 /**
	  * ��������Ҫ���ò���ϵͳ��ʵ��shengbte,����Ҫ���Ӽ��ּ����ȵ��ʵķ��������Ӽ�����Ҫ�������������
	  */
}
?>
