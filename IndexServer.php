<?php
	require 'commonFunction.php';
	
	
	if($_SERVER["REQUEST_METHOD"]=="POST"){
		$mysqli = linkToSQL();
		$serverType = $_POST["serverType"];
		switch($serverType){
			case("autoLogin"):
				$line = login(true,$mysqli);
				break;
			case("login"):
				$line = login(false,$mysqli);
				break;
			case("logout"):
				$line = logout($mysqli);
				break;
			case("acfForm"):
				$line = actForm($mysqli);
				break;
			case("recruitment"):
				$line = recruitment($mysqli);
				break;
			case("massConfig"):
				$line = massConfig($mysqli);
				break;
			case("getActivity"):
				$line = getActivity($mysqli);
				break;
			default:
				$line = array();
				$line["success"]=false;
				$line["error"]="错误：不匹配的类型";
		}
		echo json_encode($line);
		$mysqli->close();
	}
	
	function getActivity($mysqli){
		$openID = getOpenID();
		$actID = $_POST["actID"];
		
		$line["success"] = true;
		$line["mark"] = array();
		$line["list"] = array();
		$markNum = 0;
		$listNum = 0;
		
		$rst = $mysqli->query("SELECT * from activityMark WHERE openID=$openID");
		if($rst){
			while($row = $rst->fetch_array(MYSQLI_ASSOC)){
				$uid = $row["actID"];
				$rst2 = $mysqli->query("SELECT * FROM activity WHERE UID=$uid");
				while($row2 = $rst2->fetch_array(MYSQLI_ASSOC)){
					$markNum++;
					$line["mark"][] = $row2;
				}
			}
			$line["mark"]["length"]=$markNum;
		}else{
			$line["success"] = false;
			$line["error"] = "收藏获取错误，错误提示:".$mysqli->error;
		}
		
			
		$rst = $mysqli->query("SELECT * FROM actList WHERE openID=$openID");
		if($rst){
			while($row = $rst->fetch_array(MYSQLI_ASSOC)){
				
				$uid = $row["actUID"];
				$rst2 = $mysqli->query("SELECT * FROM activity WHERE UID=$uid");
				while($row2 = $rst2->fetch_array(MYSQLI_ASSOC)){
					$listNum++;
					$line["list"][] = $row2;
				}
				
			}
			$line["list"]["length"]=$listNum;
		}else{
			$line["success"] = false;
			$line["error"] = "报名获取错误，错误提示:".$mysqli->error;
		}
		
		return $line;
	}

	function recruitment($mysqli){
		$line = array();
		$type 		= $_POST["type"];
		$title 		= $_POST["title"];
		$object 	= $_POST["object"];
		$end 		= $_POST["end"];
		$intro	 	= $_POST["introduction"];
		$link	 	= $_POST["link"];
		$UID		= $_POST["UID"];
		$time	 	= time();
		
		$order = "INSERT INTO recruitment (type,title,object,end,intro,link,time,massUID) ";
		$order .="VALUES('$type','$title','$object','$end','$intro','$link',FROM_UNIXTIME($time),$UID)";

		$rst = $mysqli->query($order);
			
		if($rst){
			$line["success"] = true;
		}else{
			$line["success"] = false;
			$line["error"] = "招新发起错误，错误提示:(".$mysqli->error.")";
		}
		return $line;
	}
	
	function logout($mysqli){
		$line = array();
		setCookie("account",$account,time());
		setCookie("password",$password,time());
		setCookie("remember",$remember,time());
		$line["success"] = true;
		return $line;
	}
	
	
	function login($autologin,$mysqli){
		$line = array();
		$is_login_success = false;
		
		if($autologin){
			if(isset($_COOKIE["account"])){
				$account = $_COOKIE["account"];
				$password = $_COOKIE["password"];
				$remember = $_COOKIE["remember"];
			}else{
				$line["success"] = false;
				$line["error"] = "自动登陆失败，不存在cookies";
				return $line;
			}
			
		}else{
			$account = $_POST["account"];
			$password = $_POST["password"];
			$remember = $_POST["remember"];	
		}
		
		$rst = $mysqli->query("SELECT * FROM mass WHERE account='$account'");
		while($row = $rst->fetch_array(MYSQLI_ASSOC)){
			if($row["password"]==$password){
				$is_login_success = true;
				$line = $row;
			}
		}
		
		if($is_login_success){
			$line["success"] = true;
			$rememberTime = $remember? 720000:3600;
			if($remember){
				setCookie("account",$account,time()+$rememberTime);
				setCookie("password",$password,time()+$rememberTime);
				setCookie("remember",$remember,time()+$rememberTime);
			}
		}else{
			$line["success"] = false;
			setCookie("account","",time());
			setCookie("password","",time());
			setCookie("remember",false,time());
		}
		return $line;
	}
	
	function actForm($mysqli){
		$line = array();
		$name 		= $_POST["name"];
		$location 	= $_POST["location"];
		$holdtime 	= $_POST["time"];
		$organizer 	= $_POST["organizer"];
		$intro	 	= $_POST["introduction"];
		$type	 	= $_POST["type"];
		$json		= $_POST["json"];
		$campus		= $_POST["campus"];
		$logo		= $_POST["logo"];
		$UID		= $_POST["UID"];
		$time	 	= time();
		
		$order = "INSERT INTO activity (name,location,holdtime,time,organizer,introduction,type,campus,logo,json,organizerUID) ";
		$order .="VALUES('$name','$location','$holdtime',FROM_UNIXTIME($time),'$organizer','$intro','$type','$campus','$logo','$json',$UID)";

		$rst = $mysqli->query($order);
		if($rst){
			$line["success"] = true;
		}else{
			$line["success"] = false;
			$line["error"] = "活动发起错误，错误提示:".$mysqli->error;
		}
		return $line;
	}
	
	function massConfig($mysqli){
		$line = array();
		$name 	= $_POST["name"];
		$head 	= $_POST["head"];
		$logo 	= $_POST["logo"];
		$desc 	= $_POST["desc"];
		$UID	= $_POST["UID"];
		
		$order = "UPDATE mass SET name='$name',member='$head',logo='$logo',intro='$desc' WHERE UID=$UID";

		$rst = $mysqli->query($order);
		if($rst){
			$line["success"] = true;
		}else{
			$line["success"] = false;
			$line["error"] = "修改错误，错误提示:(".$mysqli->error.")";
		}
		return $line;
	}

?>