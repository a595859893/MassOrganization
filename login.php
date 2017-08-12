<?php
	header("Content-type: text/html; charset=utf-8");
	$is_login_success = false;
	if($_SERVER["REQUEST_METHOD"]=="POST"){
		if($_POST["type"]=="login"){
			$account = $_POST["account"];
			$password = $_POST["password"];
			$remember = $_POST["remember"];
		}else{
			setCookie("account","",time()-720000);
			setCookie("password","",time()-720000);
			setCookie("remember",false,time()-720000);
			$userInfo = array();
			$userInfo["success"] = true;
			echo json_encode($userInfo);
			exit();
		}
	}else if(isset($_COOKIE["account"])){
		$account = $_COOKIE["account"];
		$password = $_COOKIE["password"];
		$remember = $_COOKIE["remember"];
	}
	
	$mysqli = new mysqli('localhost','root','SYSUcc123','ipe_db','3306');
	$mysqli->query("set character set 'utf8'");
	$mysqli->query("set names 'utf8'");
	if($mysqli->connect_errno){
		printf("连接服务器失败!");
		exit();
	}

	$rst = $mysqli->query("SELECT * FROM login WHERE account='$account'");
	while($row = $rst->fetch_array(MYSQLI_ASSOC)){
		if($row["password"]==$password){
			$is_login_success = true;
			$userInfo = $row;
		}
	}
	
	if($is_login_success){
		$userInfo["success"] = true;
		if($remember){
			setCookie("account",$account,time()+720000);
			setCookie("password",$password,time()+720000);
			setCookie("remember",$remember,time()+720000);
		}
	}else{
		$userInfo["success"] = false;
		setCookie("account","",time()-720000);
		setCookie("password","",time()-720000);
		setCookie("remember",false,time()-720000);
	}
	
	echo json_encode($userInfo);
	$rst->free();
	$mysqli->close();
?>
