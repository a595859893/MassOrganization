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
	$openID = false;
	if(isset($_COOKIE["openID"])){
		$shaID = $_COOKIE["openID"];
		$mysqli = linkToSQL();
		$order = "SELECT openID from openID WHERE shaID=$shaID";
		$rst = $mysqli->query($order);
		while($row = $rst->fetch_array(MYSQLI_ASSOC)){
			$openID = $row["openID"];
			break;
		}
	}
	return $openID;
}

function setError($code,$desc){
	$err = array();
	$err["code"] = $code;
	$err["desc"] = $desc;
	return $err;
}
?>