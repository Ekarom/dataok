<?php
include "cfg/conn.php";

if (!empty($_SERVER['HTTP_CLIENT_IP']))   
  {
    $ip = $_SERVER['HTTP_CLIENT_IP'];
  }
//whether ip is from proxy
elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))  
  {
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
  }
//whether ip is from remote address
else
  {
    $ip = $_SERVER['REMOTE_ADDR'];
  }

$browser = $_SERVER ['HTTP_USER_AGENT'];

$log = date("Y-m-d H:i:s");

if(!isset($_COOKIE['skradm'], $_COOKIE['_junk']))
{
header("Location:index.php");

//$userc = $_COOKIE['username'];
//$passc =  $_COOKIE['password'];
//$skloginc = $_COOKIE['sklogin'];
//$scr = $_COOKIE['scr'];


//$cklog1 = mysqli_num_rows(mysqli_query($conn,"select * from usera WHERE username ='$userc' and password ='$passc'"));	

//$cek = mysqli_fetch_array($cklog1);



    

//if($cklog1 == 0){

//header("Location: ./");
//	}
	
}

if(!isset($_COOKIE['skradm'], $_COOKIE['scr']))
{
header("Location: ./");
}

if(isset($_COOKIE['skradm'], $_COOKIE['scr']))
{
//header("Location: ./");


$usc = $_COOKIE['skradm'];
$tpl = $_COOKIE['tapel'];
$st = $_COOKIE['smt'];
$scr = $_COOKIE['scr'];

$usc = $_COOKIE['username'];
$tpl = $_COOKIE['tapel'];
$st = $_COOKIE['smt'];
$scr = $_COOKIE['scr'];

//if(md5($usc, "cbt106b154") !== $scr)
	//{
//header("Location: ban.php");
	//}
}
if (!empty($_SERVER['HTTP_CLIENT_IP']))   
  {
    $ip_address = $_SERVER['HTTP_CLIENT_IP'];
  }
//whether ip is from proxy
elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))  
  {
    $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
  }
//whether ip is from remote address
else
  {
    $ip = $_SERVER['REMOTE_ADDR'];
  }

date_default_timezone_set("Asia/Jakarta");
$log = date("Y-m-d H:i:s");

$tahun =  date("Y");
$tahunb =  date("Y",strtotime("+1 year"));
$tahunm =  date("Y",strtotime("-3 year"));
$tahunm8 =  date("Y",strtotime("-2 year"));
$tahunm7 =  date("Y",strtotime("-1 year"));
$tapels =  "$tahunm7/$tahun";
$tapelb = "$tahun/$tahunb";
/*
$gettapel = mysqli_query($conn,"select * from usera");
$smt = mysqli_fetch_array($gettapel);
$semester = $smt['tapel'];
if($semester !== $tapelb)
{
$semester = "2";
}
else
{
$semester = "1";
}
*/
$userc = $_COOKIE['skradm'];

$getuser = mysqli_query($conn,"select * from usera where username='$userc'");
$test = mysqli_fetch_array($getuser);
$level = $test['level'];


$log4 = mysqli_query($conn,"select COUNT(user) as n1 from usera_log where user='$userc' order by waktu desc");
		$log5 = mysqli_fetch_array($log4);

$log1 = mysqli_query($conn,"select * from usera_log where user='$userc' order by waktu desc limit 25");
		//$log3 = mysqli_fetch_array($log1);
		
?>
