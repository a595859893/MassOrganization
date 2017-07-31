<?php
	header("Content-type: text/html; charset=utf-8");
	if($_SERVER["REQUEST_METHOD"]=="POST"){
		$place 	= _POST["place"];
		$type 	= _POST["type"];
		$order 	= _POST["order"];
		
		$mysqli = new mysqli('localhost','ipeuser','*ipE123','ipe_db','3306');
		$line=array();
		if($mysqli->connect_errno){
			$line["error"] = "服务器连接失败，错误提示：".$mysqli->error;
			$line["success"] = false;
		}else{
			$place = "'".preg_replace("/,/","','",$place)."','不限'";
			$type = "'".preg_replace("/,/","','",$type)."','不限'";
			$orderStr = "SELECT * FROM activity WHERE campus in(".$place.")AND type in(".$type.")";
			if($order=="最新")
				$orderStr.="ORDER BY time DESC";
			else if($order=="最热")//有待完善
				$orderStr.="ORDER BY time";
			
			$rst = $mysqli->query($orderStr);
			
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
