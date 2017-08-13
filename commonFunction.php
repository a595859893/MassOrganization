<?php
function linkToSQL(){
	$mysqli = new mysqli('localhost','root','SYSUcc123','ipe_db','3306');
	$mysqli->query("set character set 'utf8'");
	$mysqli->query("set names 'utf8'");
	if($mysqli->connect_errno){
		$line["success"] = false;
		$line["error"] = "连接服务器失败!".$mysqli->connect_errno;
		$mysqli->close();
		exit(json_encode($line));
		return false;
	}
	return $mysqli;
}

function getOpenID(){
	if(isset($_COOKIE["openID"])){
		$openID = $_COOKIE["openID"];
	}else{
		$exist = true;
		$mysqli = linkToSQL();
		while($exist){
			$exist = false;
			$openID = rand(100000,999999);
			$order = "SELECT openID from openID WHERE openID=$openID";
			$rst = $mysqli->query($order);
			while($row = $rst->fetch_array(MYSQLI_ASSOC)){
				$exist=true;
			}
		}
		$order = "INSERT INTO openID (openID) VALUES($openID)";
		$rst = $mysqli->query($order);
	}
	setCookie("openID",$openID,time()+9999999);
	return $openID;
}
?>