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
		$order = "SELECT openID from openID WHERE shaID=$openID";
		$rst = $mysqli->query($order);
		while($row = $rst->fetch_array(MYSQLI_ASSOC)){
			$openID = $row["openID"];
			break;
		}
	}
	if(!$openID) $this-?error("openID获取失败");
	return $openID;
}

function setOpenID($code,$state){
	$appid = "wx67a84c01324490ca";
	$appSecret = "5f734c9f9aae69d158461f66da95cd1e";
	if(empty($code)) $this-?error("授权失败");
	$token_url="https://api.weixin.qq.com/sns/oauth2/access_token?appid=$appid&secret=$appSecret&code=$code&grant_type=authorization_code";
	$token = json_decode(file_get_contents($token_url));
	if(isset($token->errcode)){
		return false;
		exit();
	}
	$exist = false;
	$rst = $mysqli->query($order);
	while($row = $rst->fetch_array(MYSQLI_ASSOC)){
		$exist = true;
		break;
	}
	
	$openID = $token["openid"];
	$shaID = sha1($openID);
	
	if(!$exist)
	{
		$mysqli = linkToSQL();
		$order = "INSERT INTO openID (shaID,openID) VALUES ($shaID,$openID)";
		$rst = $mysqli->query($order);
		if(!$rst)$this->error("openID储存失败，错误信息：".$mysqli->error);
			
	}
		
	setCookie("openID",$shaID,time()+36000);
}

function setError($code,$desc){
	$err = array();
	$err["code"] = $code;
	$err["desc"] = $desc;
	return $err;
}
?>