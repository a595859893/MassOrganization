<?php
	header("Content-type: text/html; charset=utf-8");
	if($_SERVER["REQUEST_METHOD"]=="POST"){
		$mysqli = new mysqli('localhost','ipeuser','*ipE123','ipe_db','3306');
		$line=array();
		if($mysqli->connect_errno){
			$line["error"] = "服务器连接失败，错误提示：".$mysqli->error;
			$line["success"] = false;
		}else{
			switch($_POST["type"]){
				case("new"):
					$rst = $mysqli->query("SELECT * FROM activity as a WHERE 3>(SELECT count(*) FROM activity WHERE UID>a.UID) ORDER BY a.UID DESC");
			}
			
			if($rst){
				$looptag = 0;
				while($row = $rst->fetch_array(MYSQLI_ASSOC)){
					$line[] = $row;
					$looptag++;
				}
				$line["success"] = true;
				$line["length"] = $looptag;
			}else{
				$line["error"] = "活动获取错误，错误提示: ".$mysqli->error;
				$line["success"] = false;
			}
		}
		echo json_encode($line);
		$mysqli->close();
	}
?>
