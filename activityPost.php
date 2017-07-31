<?php
	if($_SERVER["REQUEST_METHOD"]=="POST"){
		$name 		= $_POST["name"];
		$location 	= $_POST["location"];
		$holdtime 	= $_POST["time"];
		$organizer 	= $_POST["organizer"];
		$intro	 	= $_POST["introduction"];
		$type	 	= $_POST["type"];
		$json		= $_POST["json"];
		$UID		= $_POST["UID"];
		$time	 	= time();
		
		$line = array();
		$mysqli = new mysqli('localhost','ipeuser','*ipE123','ipe_db','3306');
		if($mysqli->connect_errno){
			$line["success"] = false;
			$line["error"] = "连接服务器失败!".$mysqli->connect_errno;
		}else{
			$order = "INSERT INTO activity (name,location,holdtime,time,organizer,introduction,type,json,organizerUID) ";
			$order .="VALUES('$name','$location','$holdtime',FROM_UNIXTIME($time),'$organizer','$intro','$type','$json',$UID)";

			$rst = $mysqli->query($order);
			
			if($rst){
				$line["success"] = true;
			}else{
				$line["success"] = false;
				$line["error"] = "活动发起错误，错误提示:(".$mysqli->error.")";
			}
		}
		echo json_encode($line);
		$mysqli->close();
	}
?>
