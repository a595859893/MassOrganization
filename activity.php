<?php
	require 'commonFunction.php';

	if($_SERVER["REQUEST_METHOD"]=="POST"){
		$serverType = $_REQUEST["serverType"];
		$openID = getOpenID();
		$line=array();
		$mysqli = linkToSQL();
		
		if($serverType=="GetList"){
			$place 	= $_REQUEST["place"];
			$type 	= $_REQUEST["type"];
			$order 	= $_REQUEST["order"];

			$place = "'".preg_replace("/,/","','",$place)."','不限'";
			$type = "'".preg_replace("/,/","','",$type)."','不限'";
			$orderStr = "SELECT * FROM activity WHERE campus in(".$place.")AND type in(".$type.")";
			if($order=="最新")
				$orderStr.="ORDER BY time DESC";
			else if($order=="最热")//有待完善
				$orderStr.="ORDER BY mark DESC";
			
			$rst = $mysqli->query($orderStr);
			if($rst){
				$looptag = 0;
				while($row = $rst->fetch_array(MYSQLI_ASSOC)){
					$actID = $row["UID"];
					$openID = getOpenID();
					$exist = false;
					$rst2 = $mysqli->query("SELECT * from activityMark WHERE openID=$openID AND actID=$actID");
					if($rst2){
						while($row2 = $rst2->fetch_array(MYSQLI_ASSOC)){
							$exist = true;
							break;
						}
						$row["mark"] = $exist;
						$line[] = $row;
						$looptag++;
					}else{
						$line["error"] = "收藏获取错误，错误提示: ".$mysqli->error;
						$line["success"] = false;
						echo json_encode($line);
						$mysqli->close();
						exit();
					}
				}
				$line["success"] = true;
				$line["length"] = $looptag;
			}else{
				$line["error"] = "活动获取错误，错误提示: ".$mysqli->error;
				$line["success"] = false;
			}
			
		}
		else if($serverType=="Mark"){
			$actID = $_POST["actID"];

			$exist = false;
			$rst = $mysqli->query("SELECT * from activityMark WHERE openID=$openID AND actID=$actID");
			while($row = $rst->fetch_array(MYSQLI_ASSOC)){
				$exist = true;
				break;
			}
			
			if(!$exist){
				$rst2 = $mysqli->query("INSERT INTO activityMark (openID,actID) VALUES('$openID',$actID)");
				$line["mark"] = true;
				if($rst2){
					$mysqli->query("UPDATE activity SET mark=mark+1 WHERE UID=$actID");
					$line["success"] = true;
				}else{
					$line["error"] = "收藏错误，错误提示: ".$mysqli->error;
					$line["success"] = false;
				}
			}else{
				$rst2 = $mysqli->query("DELETE FROM activityMark WHERE openID=$openID AND actID=$actID");
				$line["mark"] = false;
				if($rst2){
					$mysqli->query("UPDATE activity SET mark=mark-1 WHERE UID=$actID");
					$line["success"] = true;
				}else{
					$line["error"] = "收藏删除错误，错误提示: ".$mysqli->error;
					$line["success"] = false;
				}
			}
		}
		else if($serverType=="Post"){
			$json = $_REQUEST["json"];
			$uid = $_REQUEST["UID"];
			
			if($rst = $mysqli->query("INSERT INTO actList (openID,json,actUID)VALUES('$openID','$json',$uid)")){
				$line["success"] = true;
			}else{
				$line["error"] = "表单发布错误，错误提示: ".$mysqli->error;
				$line["success"] = false;
			}
		}else if($serverType=="Billboard"){
			$num = $_REQUEST["num"];
			
			$line["success"] = true;
			$line["mass"] = array();
			$line["activity"] = array();
			
			$rst = $mysqli->query("SELECT * FROM mass as a WHERE $num>(SELECT count(*) FROM mass WHERE good>a.good) ORDER BY a.good DESC");
			if($rst){
				while($row = $rst->fetch_array(MYSQLI_ASSOC)){
					$line["mass"][]=$row;
				}
			}else{
				$line["success"] = false;
				$line["error"] = "排行榜获取错误，错误提示: ".$mysqli->error;
			}
			$rst = $mysqli->query("SELECT * FROM activity as a WHERE $num>(SELECT count(*) FROM mass WHERE mark>a.mark) ORDER BY a.mark DESC");
			if($rst){
				while($row = $rst->fetch_array(MYSQLI_ASSOC)){
					$line["activity"][]=$row;
				}
			}else{
				$line["success"] = false;
				$line["error"] = "排行榜获取错误，错误提示: ".$mysqli->error;
			}
		}
		
		echo json_encode($line);
		$mysqli->close();
	}
?>
