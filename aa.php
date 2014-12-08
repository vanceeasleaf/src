<?
$path=trim(shell_exec("pwd"))."/input.php";
echo $path."\n";
$m=strrpos($path,'/');
echo $m."\n";
$dir=substr($path,0,$m);
echo $dir."\n";
?>
