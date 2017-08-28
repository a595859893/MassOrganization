<?php
	require 'commonFunction.php';
	//session_start();
	
	if($_SERVER["REQUEST_METHOD"]=="POST"){
		$serverType = $_POST["serverType"];
		
		//$mysqli = linkToSQL();
		$line = array();
		/*
		if("login" == $serverType)
		{
			$account = $_POST["account"];
			$password = $_POST["password"];
			
			$rst = $mysqli->query("SELECT * FROM mass WHERE account='$account' LIMIT 1");
			if($rst){
				if($row = $rst->fetch_array(MYSQLI_ASSOC)){
					if($row["password"]==sha1($password)){
						$line = $row;
						$_SESSION["uid"] = $row["UID"];
					}else $line["error"] = setError(1,"密码错误！");
				}else $line["error"] = setError(2,"账号不存在！");
			}else $line["error"] = setError(0,"登陆时，数据库错误，提示：".$mysqli->error);
		}
		else if("logout" == $serverType){
			unset($_SESSION["uid"]);
		}
		else if("checkState" == $serverType)
		{
			if(isset($_SESSION["uid"]))
			{
				$uid = $_SESSION["uid"];
				$line["state"] = true;
				$rst = $mysqli->query("SELECT * FROM mass WHERE UID=$uid LIMIT 1");
				if($rst){
					if($row = $rst->fetch_array(MYSQLI_ASSOC)){
						$line["account"] = $row;
					}else $line["error"] = setError(3,"不存在的UID");
				}else $line["error"] = setError(0,"检查登录状态时，数据库错误，提示：".$mysqli->error);
			}else{
				$line["state"] = false;
			}
		}else if("openID" == $serverType){
			$account = $_POST["code"];
			$password = $_POST["state"];
			
			$appid = "wx67a84c01324490ca";
			$appSecret = "5f734c9f9aae69d158461f66da95cd1e";
			$token_url="https://api.weixin.qq.com/sns/oauth2/access_token?appid=$appid&secret=$appSecret&code=$code&grant_type=authorization_code";
			$token = json_decode(file_get_contents($token_url));
			if(!isset($token->errcode)){
				$openID = $token["openid"];
				$shaID = sha1($openID);
				$rst = $mysqli->query($order);
				if($rst){
					if(!$row = $rst->fetch_array(MYSQLI_ASSOC))
					{
						$mysqli = linkToSQL();
						$order = "INSERT INTO openID (shaID,openID) VALUES ($shaID,$openID)";
						$rst = $mysqli->query($order);
						if(!$rst) setError(0,"注册数据存在时，数据库错误，提示：".$mysqli->error);	
					}
					setCookie("openID",$shaID,time()+36000);
				}else $line["error"] = setError(0,"判断数据存在时，数据库错误，提示：".$mysqli->error);	
			}else setError(4,"token获取错误");
		}else{
			$line["error"] = setError(0,"不匹配的类型");
		}
		*/
		echo json_encode($line);
		//$mysqli->close();
	}
?>
