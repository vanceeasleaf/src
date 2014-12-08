<?php
require_once("discript.php");
require_once("postMini.php");
$result=fopen($fileResult,"w");
fprintf($result,"method:$method\n");
if($method=="greenkubo"){
	$gk_result=shell_exec("tail -1 $fileKappa 2>err");
	list($step,$kx,$ky,$kz)=sscanf($gk_result,"%d%f%f%f");
	fprintf($result,"kx=%f ky=%f kz=%f\n",$kx,$ky,$kz);
	exit();
}
function getTempProfile($begin,$fileTempProfile,$fileTempAve){
	$file=fopen($fileTempProfile,"r");
	if(!$file)exit("fail to open $fileTempProfile\n");
	$n=0;
	for($i=0;$i<3;$i++)fgets($file);
	while($info=fscanf($file,"%d %d\n")){
		list($timestep,$natom)=$info;
		$m=0;
		for($i=0;$i<$natom;$i++){
			$line=fscanf($file,"  %d %f %f %f %f");
			list($Bin[$i],$Coord[$i],$Ncount[$i],$v_temp[$i],$jx[$i])=$line;
			if($Ncount[$i]<=0)continue;
			if($n==$begin){	
				$aveTemp[$m]=0;
				$avejx[$m]=0;
				$aveN[$m]=0;
				$aveC[$m]=0;
			}
			if($n>$begin){
				$aveTemp[$m]+=$v_temp[$i];
				$avejx[$m]+=$jx[$i];
				$aveN[$m]+=$Ncount[$i];
				$aveC[$m]=$Coord[$i];
			}
			$m++;
		}
		$n++;
	}
	$ntime=$n;
	for($bin=0;$bin<$m;$bin++){
		$aveTemp[$bin]/=$ntime-$begin-1;
		$avejx[$bin]/=$ntime-$begin-1;
		$aveN[$bin]/=$ntime-$begin-1;
		//$aveC[$bin]/=$ntime-$begin-1;
		//if($bin==6754)echo ($ntime-$begin-1)."\t".$aveC[$bin]."\n";
	}
	$ave=fopen($fileTempAve,"w");
	fprintf($ave,"id\tCoord\tCount\tTemp\tJx\n");
	for($i=0;$i<$m;$i++){
		fprintf($ave,"%d\t%f\t%f\t%f\t%f\n",$i+1,$aveC[$i],$aveN[$i],$aveTemp[$i],$avejx[$i]);
	}
	fclose($ave);
$home=dirname(__FILE__);
	$m=strrpos($fileTempAve,'/');
	$projHome=substr($fileTempAve,0,$m);
	shell_exec("cp $home/plot/tempAve.dis $projHome;cd $projHome;gnuplot tempAve.dis 2>>err;");
	return array($aveC,$aveN,$aveTemp,$avejx);
}

list($aveC,$aveN,$aveTemp,$avejx)=getTempProfile($begin,$fileTempProfile,$fileTempAve);
$flux_src=getFlux($method,$fileNvtWork,$begin,$timestep,$S,$fileSwap,$conti,$lz,$excRate,$swapEnergyRate);
function getFlux($method,$fileNvtWork,$begin,$timestep,$S,$fileSwap,$conti,$lz,$excRate,$swapEnergyRate){
	if($method=="nvt"){
	$nvtWork=fopen($fileNvtWork,"r");
	if(!$nvtWork)exit("fail to open $fileNvtWork\n");
	fscanf($nvtWork,"");
	fscanf($nvtWork,"");
	$co=0;
	while(list($f_step,$f_hot)=fscanf($nvtWork,"%d%f")){
		$step[$co]=$f_step;
	$hot[$co++]=$f_hot;
	}
	$st=$begin;
	$hotslope=abs($hot[$co-1]-$hot[$st-1])/($step[$co-1]-$step[$st-1]);
	$J=$hotslope/$timestep;

	$flux_src=$J/$S;
	fclose($nvtWork);
}
if($method=="muller"){
	$file=fopen($fileSwap,"r");
	fscanf($file,"");
	fscanf($file,"");
	$co=0;
	$sum=0;
	while(list($f_step,$heat_swap)=fscanf($file,"%d%f")){
			$step[$co]=$f_step;
	$hot[$co]=$heat_swap;
	$sum+=$heat_swap;
	$co++;
	}
	fclose($file);
		$st=$begin;
	$ave_heat_swap=abs($hot[$co-1]-$hot[$st-1])/($step[$co-1]-$step[$st-1]);
	
	if($conti)$ave_heat_swap=$sum*$lz/$co/$excRate;
	$J=$ave_heat_swap/($timestep);
	$flux_src=$J/(2*$S);
}
if($method=="inject"){
	$J=$swapEnergyRate;
	$flux_src=$J/(2*$S);
}
	return $flux_src;
}

#if(!$upP)
$upP=3;
#calculate slope
function nvtSlope($aveC,$aveTemp,$aveN,$avejx,$upP){
	$m=count($aveC);
$downP=$upP;
$pt1=$downP;
$pt2=$m+1-$upP;
$pt1--;$pt2--;
$n=$pt2-$pt1+1;
$savejx=array_slice($avejx,$pt1,$n);
$saveN=array_slice($aveN,$pt1,$n);
$ave_jx=arr_ave(arr_abs($savejx));
$ave_N=arr_ave($saveN);
$J_bulk=$ave_jx*$ave_N;
$J_bulkc=arr_ave(arr_abs(arr_mul($saveN,$savejx)));
$slope=abs(slope($aveC,$aveTemp,$pt1,$pt2));
return array($slope,$J_bulk,$J_bulkc);
}
function mullerSlope($aveC,$aveTemp,$aveN,$avejx,$upP){
		$m=count($aveC);
$downP=$upP;
$cter=floor(($m+1)/2);
$pt11=$downP;$pt12=$cter-$upP;
$pt22=$m+1-$downP;$pt21=$cter+$upP;
$pt11--;$pt12--;$pt21--;$pt22--;
$slope1=slope($aveC,$aveTemp,$pt11,$pt12);
$n=$pt12-$pt11+1;
$savejx=array_slice($avejx,$pt11,$n);
$saveN=array_slice($aveN,$pt11,$n);
$ave_jx=arr_ave(arr_abs($savejx));
$ave_N=arr_ave($saveN);
$J_bulk1=$ave_jx*$ave_N;
$J_bulkc1=arr_ave(arr_abs(arr_mul($saveN,$savejx)));
$slope2=-slope($aveC,$aveTemp,$pt21,$pt22);
$n=$pt22-$pt21+1;
$savejx=array_slice($avejx,$pt21,$n);
$saveN=array_slice($aveN,$pt21,$n);
$ave_jx=arr_ave(arr_abs($savejx));
$ave_N=arr_ave($saveN);
$J_bulk2=$ave_jx*$ave_N;
$J_bulkc2=arr_ave(arr_abs(arr_mul($saveN,$savejx)));
$slope=($slope1+$slope2)/2;
$J_bulk=($J_bulk1+$J_bulk2)/2;
$J_bulkc=($J_bulkc1+$J_bulkc2)/2;
return array($slope,$J_bulk,$J_bulkc);
}
if($method=="nvt"){
list($slope,$J_bulk)=nvtSlope($aveC,$aveTemp,$aveN,$avejx,$upP);
$flux_bulk=$J_bulk/($deta*$S);
}
if($method=="muller"||$method=="inject"){
list($slope,$J_bulk)=mullerSlope($aveC,$aveTemp,$aveN,$avejx,$upP);
$flux_bulk=$J_bulk/($deta*$S);
}
$kappa_src=$flux_src/$slope*$tcfactor*$zfactor;
$kappa_bulk=$flux_bulk/$slope*$tcfactor*$zfactor;
fprintf($result,"kappa_src=%f\n",$kappa_src);
fclose($result);
$fileScan=fopen($fileScanResult,"w");
fprintf($fileScan,"method:$method\n");
$numS=0;
if($method=="muller"||$method=="inject"){
	$m=count($aveC);
	for($upP=1;$upP<$lx/4/$deta;$upP++){
		$downP=$upP;
		$cter=floor(($m+1)/2);
		$pt11=$downP;$pt12=$cter-$upP;
		$pt22=$m+1-$downP;$pt21=$cter+$upP;
		$pt11--;$pt12--;$pt21--;$pt22--;
		if($pt12-$pt11+1<8)break;
		$slope1=slope($aveC,$aveTemp,$pt11,$pt12);
		$n=$pt12-$pt11+1;
$savejx=array_slice($avejx,$pt11,$n);
$saveN=array_slice($aveN,$pt11,$n);
$ave_jx=arr_ave(arr_abs($savejx));
$ave_N=arr_ave($saveN);
$J_bulk1=$ave_jx*$ave_N;
$J_bulkc1=arr_ave(arr_abs(arr_mul($saveN,$savejx)));
		$slope2=-slope($aveC,$aveTemp,$pt21,$pt22);
		$n=$pt22-$pt21+1;
$savejx=array_slice($avejx,$pt21,$n);
$saveN=array_slice($aveN,$pt21,$n);
$ave_jx=arr_ave(arr_abs($savejx));
$ave_N=arr_ave($saveN);
$J_bulk2=$ave_jx*$ave_N;
$J_bulkc2=arr_ave(arr_abs(arr_mul($saveN,$savejx)));
		$slope=($slope1+$slope2)/2;
		$J_bulk=($J_bulk1+$J_bulk2)/2;
		$J_bulkc=($J_bulkc1+$J_bulkc2)/2;
				$J_bulks[$numS]=$J_bulk;
					$J_bulkcs[$numS]=$J_bulkc;
		$slopes[$numS++]=$slope;

	}
}
if($method=="nvt"){
		$m=count($aveC);
	for($upP=1;$upP<$lx/2/$deta;$upP++){
		$downP=$upP;
		$pt1=$downP;
		$pt2=$m+1-$upP;
		$pt1--;$pt2--;
		if($pt2-$pt1+1<8)break;
		$slope=abs(slope($aveC,$aveTemp,$pt1,$pt2));
		$savejx=array_slice($avejx,$pt1,$n);
$saveN=array_slice($aveN,$pt1,$n);
$ave_jx=arr_ave(arr_abs($savejx));
$ave_N=arr_ave($saveN);
$J_bulk=$ave_jx*$ave_N;
$J_bulkc=arr_ave(arr_abs(arr_mul($saveN,$savejx)));
				$J_bulks[$numS]=$J_bulk;
									$J_bulkcs[$numS]=$J_bulkc;
		$slopes[$numS++]=$slope;
	}
}
fprintf($fileScan,"upP\tkappa_src\tkappa_bulk\tkappa_bulkc\tflux_src\tflux_bulk\tflux_bulkc\tslope\n");
for($i=0;$i<$numS;$i++){
	$kappa_src=$flux_src/$slopes[$i]*$tcfactor*$zfactor;
	$flux_bulk=$J_bulks[$i]/($deta*$S);
	$flux_bulkc=$J_bulkcs[$i]/($deta*$S);
	$kappa_bulk=$flux_bulk/$slopes[$i]*$tcfactor*$zfactor;
	$kappa_bulkc=$flux_bulkc/$slopes[$i]*$tcfactor*$zfactor;
	fprintf($fileScan,"%d\t%f\t%f\t%f\t%f\t%f\t%f\t%f\n",$i+1,$kappa_src,$kappa_bulk,$kappa_bulkc,$flux_src,$flux_bulk,$flux_bulkc,$slopes[$i]);
}
fclose($fileScan);


function getJProfile($begin,$fileTempProfile,$fileTempAve){
	$file=fopen($fileTempProfile,"r");
	if(!$file)exit("fail to open $fileTempProfile\n");
	$n=0;
	while(fgets($file)){
		list($timestep)=fscanf($file,"%d");
		fgets($file);list($natom)=fscanf($file,"%d");
		for($i=0;$i<5;$i++)fgets($file);
		$m=0;
		for($i=0;$i<$natom;$i++){
			$line=fscanf($file,"  %d %f %f %f ");
			list($id[$i],$jx[$i],$jy[$i],$jz[$i])=$line;
			$pid[$id[$i]-1]=$i;
			if($n==$begin){	
				$avejx[$m]=0;
				$avejy[$m]=0;
				$avejz[$m]=0;
			}
			if($n>$begin){
				$avejx[$m]+=$jx[$i];
				$avejy[$m]+=$jy[$i];
				$avejz[$m]+=$jz[$i];
			}
			$m++;
		}
		$n++;
	}
	$ntime=$n;
	for($bin=0;$bin<$m;$bin++){
		$avejx[$bin]/=$ntime-$begin-1;
		$avejy[$bin]/=$ntime-$begin-1;
		$avejz[$bin]/=$ntime-$begin-1;
		//$aveC[$bin]/=$ntime-$begin-1;
		//if($bin==6754)echo ($ntime-$begin-1)."\t".$aveC[$bin]."\n";
	}
	$ave=fopen($fileTempAve,"w");
	fprintf($ave,"id\tjx\tjy\tjz\n");
	for($i=0;$i<$m;$i++){
		$id=	$pid[$i];
		fprintf($ave,"%d\t%f\t%f\t%f\n",$i+1,$avejx[$id],$avejy[$id],$avejz[$id]);
	}
	fclose($ave);
	//return array($aveC,$aveN,$aveTemp,$avejx);
}
getJProfile($begin,"jprofile.txt","avejpro.txt");

?>