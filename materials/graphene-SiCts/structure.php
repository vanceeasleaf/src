<?php
//******
//a program to generate the graphene lattice;
//��ѡ��x�����y����ֱ�Ϊ���ֱ߽�����
//��ѡ��x���򣨴��������Ƿ���һЩ��ƽ��(��ƽ�У���x��Ϊarmchair�ߣ�����Ϊzigzag�ߣ�
//��x=zigzag,�Լ���aΪ��λ�����ξ���y��������Ϊ3��x��������Ϊsqrt��3��;�����ڸ�ԭ�ӵ�λ��Ϊ(0,0.5)(0,1.5)(0.5*sqrt(3),0)(0.5*sqrt(3),2);
//��x=armchair,�Լ���aΪ��λ�����ξ���y��������Ϊsqrt��3����x��������Ϊ3;�����ڸ�ԭ�ӵ�λ��Ϊ(0.5,0)(1.5,0)(0,0.5*sqrt(3))(2,0.5*sqrt(3));
//z�����������������
//����structure�ļ�,��ѡ������xyz�ļ�
//��ѡ������CN��graphne����CN��������ԭ�ӵ�typeΪC��N��N��C
//******

  		$home=dirname(__FILE__);	
require_once("$home/../../discript.php");
	$Ni="C";if($cn)	$Ni="N";
	  $atomCell=4;


    $xs=array();
    $ys=array();
    $zs=array(); 
     $types=array();
 $xc=array(0,0,0.5*sqrt(3),0.5*sqrt(3));
$yc=array(0.5,1.5,0,2);
$typec=array("C",$Ni,$Ni,"C");
$dx=sqrt(3);$dy=3;
if(!$xZigzag){
	 $yc=array(0,0,0.5*sqrt(3),0.5*sqrt(3));
$xc=array(0.5,1.5,0,2);
	$dx=3;$dy=sqrt(3);
}
	if($xlen&&$ylen){
		$lx=round($xlen/($dx*$bond));
		$ly=round($ylen/($dy*$bond));
	}
	    $Natom=$lx*$ly*$atomCell;
	    $far=50;
$xlo=0;$xhi=$lx*$dx;
$ylo=0;$yhi=$ly*$dy;
$zlo=-$far;$zhi=$far;
    $n=0;
    for( $j=0;$j<$ly;$j++)
		for( $i=0;$i<$lx;$i++){
			for( $k=0;$k<$atomCell;$k++){
				$types[$n]=$typec[$k];
				$xs[$n]=$xc[$k]+$dx*$i;
				$ys[$n]=$yc[$k]+$dy*$j;
			$n++;
			}
		}
	if(!$xp){
	if($xZigzag){    	$Natom+=$ly*2;
	for( $j=0;$j<$ly;$j++){
			for( $k=0;$k<2;$k++){
				$types[$n]=$typec[$k];
				$xs[$n]=$xc[$k]+$dx*$lx;
				$ys[$n]=$yc[$k]+$dy*$j;
			$n++;
	}
	}
	}

	$xlo=-$far;$xhi=$lx*$dx+$far;
}
if(!$yp){
	if(!$xZigzag){    $Natom+=$lx*2;
	for( $j=0;$j<$lx;$j++){
			for( $k=0;$k<2;$k++){
				$types[$n]=$typec[$k];
				$xs[$n]=$xc[$k]+$dx*$j;
				$ys[$n]=$yc[$k]+$dy*$ly;
			$n++;
	}
	}
	}
		
	$ylo=-$far;$yhi=$ly*$dy+$far;
}

for($i=0;$i<$Natom;$i++){
	$xs[$i]*=$bond;$ys[$i]*=$bond;$zs[$i]=.1*(random01()*2.0-1);
}
$xlo*=$bond;$xhi*=$bond;$ylo*=$bond;$yhi*=$bond;$zlo*=$bond;$zhi*=$bond;
$file=fopen("structure","w");
fprintf($file,"graphene\n");//ע��
fprintf($file,"%d atoms\n3  atom types\n",$Natom);
fprintf($file,"%f %f xlo xhi\n%f %f ylo yhi\n%f %f zlo zhi\n\nAtoms\n\n",$xlo,$xhi,$ylo,$yhi,$zlo,$zhi);
$ran=array();
for($n=0;$n<$Natom;$n++){
	$ran[$n]=random01()<$rmass;
	fprintf($file,"%d %d %f %f %f\n",$n+1,type($n,0),$xs[$n],$ys[$n],$zs[$n]);
}
fclose($file);

if($xyz)
{
	$file=fopen("structure.xyz","w");
fprintf($file,"%d\ncomment for vesta\n",$Natom);//ע��
for($n=0;$n<$Natom;$n++){
	fprintf($file,"%s %f %f %f\n",type($n,1),$xs[$n],$ys[$n],$zs[$n]);

}
fclose($file);
}
if($log){
	$file=fopen("structure.log","w");
	fprintf($file,"bond:C-%s , (B) = %f(A)\n",$Ni,$bond);
	fprintf($file,"length in lattice unit:%d(L) x %d(L)\n",$lx,$ly);
	fprintf($file,"length in bond length:%f(B) x %f(B)\n",$dx*$lx,$dy*$ly);
	fprintf($file,"length:%f(A) x %f(A)\n",$dx*$lx*$bond,$dy*$ly*$bond);
	fprintf($file,"(L)=lattice vector (in bond length):%f(B) x %f(B)\n",$dx,$dy);
	fprintf($file,"lattice vector :%f(A) x %f(A)\n",$dx*$bond,$dy*$bond);
	fprintf($file,"prefiodic :%s %s p\n",$xp?'p':'n',$yp?'p':'n');
	fprintf($file,"%f %f xlo xhi\n%f %f ylo yhi\n%f %f zlo zhi\n",$xlo,$xhi,$ylo,$yhi,$zlo,$zhi);
	if(!$yp){
		fprintf($file,"type:GNR\n");
	}else{fprintf($file,"type:graphene lattice\n");}
	if(!$xp){
		fprintf($file,"method:NVT\n");
	}else{fprintf($file,"method:Green-Kubo or Muller-Plathe\n");}
	if($xZigzag)
		fprintf($file,"%d-ZGNR\n",$ly*2);
	else{
		fprintf($file,"%d-AGNR\n",$ly*2+1);
	}
	
	fclose($file);
}
function type($n,$char){
	global $types,$ran;
	if($char){
		if($ran[$n])return "Si";
		else return $types[$n];
	}else{
			if($ran[$n])return 3;
			else return ($types[$n]!='C')+1;
	}
	
}
?>
