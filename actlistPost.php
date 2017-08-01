<?php
	if($_SERVER["REQUEST_METHOD"]=="POST"){
		$json = $_REQUEST["json"];
		$uid = $_REQUEST["UID"];
		
		$line = array();
		$mysqli = new mysqli("localhost",'ipeuser','*ipE123','ipe_db','3306');
		$mysqli->query("set character set 'utf8'");
		$mysqli->query("set names 'utf8'");
		if($mysqli->connect_errno){
			$line["error"] = "服务器连接失败，错误提示：".$mysqli->error;
			$line["success"] = false;
		}else{
			if($rst = $mysqli->query("INSERT INTO actList (json,massUID)VALUES('$json',$uid)")){
				$line["success"] = true;
			}else{
				$line["error"] = "表单发布错误，错误提示: ".$mysqli->error;
				$line["success"] = false;
			}
		}
		
		echo json_encode($line);
		$mysqli->close();
	}
?>