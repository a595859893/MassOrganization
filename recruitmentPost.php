<?php
	if($_SERVER["REQUEST_METHOD"]=="POST"){
		$type 		= $_POST["type"];
		$title 		= $_POST["title"];
		$object 	= $_POST["object"];
		$end 		= $_POST["end"];
		$intro	 	= $_POST["introduction"];
		$link	 	= $_POST["link"];
		$UID		= $_POST["UID"];
		$time	 	= time();
		
		$line = array();
		$mysqli = new mysqli('localhost','ipeuser','*ipE123','ipe_db','3306');
		$mysqli->query("set character set 'utf8'");
		$mysqli->query("set names 'utf8'");
		if($mysqli->connect_errno){
			$line["success"] = false;
			$line["error"] = "连接服务器失败!".$mysqli->connect_errno;
		}else{
			$order = "INSERT INTO recruitment (type,title,object,end,intro,link,time,massUID) ";
			$order .="VALUES('$type','$title','$object','$end','$intro','$link',FROM_UNIXTIME($time),$UID)";

			$rst = $mysqli->query($order);
			
			if($rst){
				$line["success"] = true;
			}else{
				$line["success"] = false;
				$line["error"] = "招新发起错误，错误提示:(".$mysqli->error.")";
			}
		}
		echo json_encode($line);
		$mysqli->close();
	}
?>