<?php
	require 'commonFunction.php';

	if($_SERVER["REQUEST_METHOD"]=="POST"){
		$serverType = $_POST["serverType"];
		$openID = getOpenID();
		$line = array();
		$mysqli = linkToSQL();
		
		if($serverType == "getList"){
			$rst = $mysqli->query("SELECT * FROM mass");
			
			if($rst){
				$line["success"] = true;
				$num = 0;
				while($row = $rst->fetch_array(MYSQLI_ASSOC)){
					$exist = false;
					$UID = $row["UID"];
					
					$rst2 = $mysqli->query("SELECT * from massGood WHERE openID=$openID AND massUID=$UID");
					while($row2 = $rst2->fetch_array(MYSQLI_ASSOC)){
						$exist = true;
						break;
					}
					$row["igood"] = $exist;
					
					$exist = false;
					$rst2 = $mysqli->query("SELECT * from massMark WHERE openID=$openID AND markUID=$UID");
					while($row2 = $rst2->fetch_array(MYSQLI_ASSOC)){
						$exist = true;
						break;
					}
					$row["imark"] = $exist;
					
					$line[] = $row;
					$num++;
				}
				$line["length"]=$num;
			}else{
				$line["success"] = false;
				$line["error"] = "评论发起错误，错误提示:(".$mysqli->error.")";
			}
		}
		else if($serverType=="good"){
			$massUID = $_POST["massUID"];

			$exist = false;
			$rst = $mysqli->query("SELECT * from massGood WHERE openID=$openID AND massUID=$massUID");
			while($row = $rst->fetch_array(MYSQLI_ASSOC)){
				$exist = true;
				break;
			}
			
			if(!$exist){
				$rst2 = $mysqli->query("INSERT INTO massGood (openID,massUID) VALUES('$openID',$massUID)");
				$mysqli->query("UPDATE mass SET good=good+1 WHERE UID=$massUID");
				$line["good"] = true;
				if($rst2)
					$line["success"] = true;
				else{
					$line["error"] = "点赞错误，错误提示: ".$mysqli->error;
					$line["success"] = false;
				}
			}else{
				$rst2 = $mysqli->query("DELETE FROM massGood WHERE openID=$openID AND massUID=$massUID");
				$mysqli->query("UPDATE mass SET good=good-1 WHERE UID=$massUID");
				$line["good"] = false;
				if($rst2)
					$line["success"] = true;
				else{
					$line["error"] = "点赞删除错误，错误提示: ".$mysqli->error;
					$line["success"] = false;
				}
			}
		}
		else if($serverType=="mark"){
			$markUID = $_POST["markUID"];

			$exist = false;
			$rst = $mysqli->query("SELECT * from massMark WHERE openID=$openID AND markUID=$markUID");
			while($row = $rst->fetch_array(MYSQLI_ASSOC)){
				$exist = true;
				break;
			}
			
			if(!$exist){
				$rst2 = $mysqli->query("INSERT INTO massMark (openID,markUID) VALUES('$openID',$markUID)");
				$mysqli->query("UPDATE mass SET number=number+1 WHERE UID=$markUID");
				$line["mark"] = true;
				if($rst2)
					$line["success"] = true;
				else{
					$line["error"] = "收藏错误，错误提示: ".$mysqli->error;
					$line["success"] = false;
				}
			}else{
				$rst2 = $mysqli->query("DELETE FROM massMark WHERE openID=$openID AND markUID=$markUID");
				$mysqli->query("UPDATE mass SET number=number-1 WHERE UID=$markUID");
				$line["mark"] = false;
				if($rst2)
					$line["success"] = true;
				else{
					$line["error"] = "收藏删除错误，错误提示: ".$mysqli->error;
					$line["success"] = false;
				}
			}
		}
		else if($serverType == "getMass"){
			$uid = $_POST["UID"];
			$rst = $mysqli->query("SELECT * FROM mass WHERE UID=$uid");
			
			if($rst){
				$line["success"] = true;
				while($row = $rst->fetch_array(MYSQLI_ASSOC)){
					$exist = 0;
					
					$rst2 = $mysqli->query("SELECT * FROM massGood WHERE openID=$openID AND massUID=$uid");
					while($row2 = $rst2->fetch_array(MYSQLI_ASSOC)){
						$exist++;
					}
					$row["igood"] = $exist;
					
					$exist = 0;
					$rst2 = $mysqli->query("SELECT * FROM massMark WHERE openID=$openID AND markUID=$uid");
					while($row2 = $rst2->fetch_array(MYSQLI_ASSOC)){
						$exist++;
					}
					$row["imark"] = $exist;
					$line["mass"] = $row;
				}
			}else{
				$line["success"] = false;
				$line["error"] = "评论发起错误，错误提示:(".$mysqli->error.")";
			}
		}
		
		echo json_encode($line);
		$mysqli->close();
	}
?>