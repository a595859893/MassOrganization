<?php
	require 'commonFunction.php';
	session_start();
	
	if($_SERVER["REQUEST_METHOD"]=="POST"){
		$serverType = $_POST["serverType"];
		
		$mysqli = linkToSQL();
		//$openID = getOpenID();
		$line["error"] = setError(0,"错误");
		$line = array();
		$time = time();
		/*
		if("acfForm" == $serverType){
			$name 		= $_POST["name"];
			$location 	= $_POST["location"];
			$holdtime 	= $_POST["time"];
			$organizer 	= $_POST["organizer"];
			$intro	 	= $_POST["introduction"];
			$type	 	= $_POST["type"];
			$json		= $_POST["json"];
			$campus		= $_POST["campus"];
			$logo		= $_POST["logo"];
			$url		= $_POST["url"];
			$UID		= $_POST["UID"];
			
			$order = "INSERT INTO activity (name,location,holdtime,time,organizer,introduction,type,campus,logo,url,json,organizerUID) ";
			$order .="VALUES('$name','$location','$holdtime',FROM_UNIXTIME($time),'$organizer','$intro','$type','$campus','$logo','$url','$json',$UID)";

			$rst = $mysqli->query($order);
			if($rst){
				$mass_title = '新活动'.$name.'发布了!';
				$mass_content = '我们发布了新的活动，快来看呀！';
				
				$order = "INSERT INTO massDiary (title,content,time,logo,massUID) ";
				$order .="VALUES('$mass_title','$mass_content',FROM_UNIXTIME($time),'$logo',$UID)";

				$rst = $mysqli->query($order);
			}else $line["error"] = setError(0,"活动发起时，数据库错误，提示：".$mysqli->error);
		}else if("recruitment" == $serverType)
		{
			$type 		= $_POST["type"];
			$title 		= $_POST["title"];
			$object 	= $_POST["object"];
			$end 		= $_POST["end"];
			$intro	 	= $_POST["introduction"];
			$link	 	= $_POST["link"];
			$UID		= $_SESSION["uid"];
			
			$order = "INSERT INTO recruitment (type,title,object,end,intro,link,time,massUID) ";
			$order .="VALUES('$type','$title','$object','$end','$intro','$link',FROM_UNIXTIME($time),$UID)";

			$rst = $mysqli->query($order);
				
			if(!$rst)$line["error"] = setError(0,"招新发起时，数据库错误，提示：".$mysqli->error);
		}else if("massConfig" ==$serverType)
		{
			$type 	= $_POST["type"];
			$name 	= $_POST["name"];
			$head 	= $_POST["head"];
			$logo 	= $_POST["logo"];
			$desc 	= $_POST["desc"];
			$QRcode = $_POST["QRcode"];
			$UID	= $_SESSION["uid"];
			
			$order = "UPDATE mass SET name='$name',member='$head',logo='$logo',intro='$desc',type='$type',QRcode='$QRcode' WHERE UID=$UID";

			$rst = $mysqli->query($order);
			if(!$rst)$line["error"] = setError(0,"社团信息修改时，数据库错误，提示：".$mysqli->error);
		}else if("massDiary" == $serverType)
		{
			$title		= $_POST["title"];
			$content	= $_POST["content"];
			$logo		= $_POST["logo"];
			$img		= $_POST["img"];
			$url		= $_POST["url"];
			$UID		= $_SESSION["uid"];
			$order = "INSERT INTO massDiary (title,content,time,logo,img,url,massUID) ";
			$order .="VALUES('$title','$content',FROM_UNIXTIME($time),'$logo','$img','$url',$UID)";
			$line = array();
			$rst = $mysqli->query($order);
			if(!$rst)$line["error"] = setError(0,"社团日志发布时，数据库错误，提示：".$mysqli->error);
		}else if("getActivity")
		{
			$line["error"] = setError(0,"OpenID".$openID);
			
			$line["mark"] = array();
			$line["list"] = array();
			
			$markNum = 0;
			$listNum = 0;
			
			$rst = $mysqli->query("SELECT * from activityMark WHERE openID=$openID");
			if($rst){
				while($row = $rst->fetch_array(MYSQLI_ASSOC)){
					$uid = $row["actID"];
					$rst2 = $mysqli->query("SELECT * FROM activity WHERE UID=$uid LIMIT 1");
					if($rst2){
						if($row2 = $rst2->fetch_array(MYSQLI_ASSOC)){
							$markNum++;
							$line["mark"][] = $row2;
						}else $line["error"] = setError(1,"不存在的活动收藏");
					}else $line["error"] = setError(0,"收藏进一步获取时，数据库错误，提示：".$mysqli->error);
				}
				$line["mark"]["length"]=$markNum;
			}else $line["error"] = setError(0,"收藏获取时，数据库错误，提示：".$mysqli->error);
			
				
			$rst = $mysqli->query("SELECT * FROM actList WHERE openID=$openID");
			if($rst){
				while($row = $rst->fetch_array(MYSQLI_ASSOC)){
					$uid = $row["actUID"];
					$rst2 = $mysqli->query("SELECT * FROM activity WHERE UID=$uid LIMIT 1");
					if($row2 = $rst2->fetch_array(MYSQLI_ASSOC)){
						$listNum++;
						$line["list"][] = $row2;
					}else $line["error"] = setError(1,"不存在的活动报名");
				}
				$line["list"]["length"]=$listNum;
			}else $line["error"] = setError(0,"报名获取时，数据库错误，提示：".$mysqli->error);
			
		}else if("getActList" ==$serverType)
		{
			$actUID = $_POST["actUID"];
		
			$rst = $mysqli->query("SELECT * FROM actList WHERE actUID=$actUID");
			if($rst){
				while($row = $rst->fetch_array(MYSQLI_ASSOC)){
					$line[] = $row;
				}
			}else $line["error"] = setError(0,"报名列表获取时，数据库错误，提示：".$mysqli->error);
		}else if("destroyAct")
		{
			$actUID = $_POST["actUID"];
			$line = array();
			$line["success"]=true;
			$rst = $mysqli->query("DELETE FROM actList WHERE actUID=$actUID");
			if(!$rst) $line["error"] = setError(0,"删除报名列表时，数据库错误，提示：".$mysqli->error);
			$rst = $mysqli->query("DELETE FROM activityMark WHERE actID=$actUID");
			if(!$rst) $line["error"] = setError(0,"删除收藏列表时，数据库错误，提示：".$mysqli->error);
			$rst = $mysqli->query("DELETE FROM activity WHERE UID=$actUID");
			if(!$rst) $line["error"] = setError(0,"删除活动时，数据库错误，提示：".$mysqli->error);
		}else if("addDate")
		{
			$actName	= $_POST["actName"];
			$actStart	= $_POST["actStart"];
			$actEnd		= $_POST["actEnd"];
			
			$order = "INSERT INTO dateRemind (openID,start,end,name) ";
			$order .="VALUES('$openID','$actStart','$actEnd','$actName')";
			$rst = $mysqli->query($order);
			if(!$rst) $line["error"] = setError(0,"添加日程时，数据库错误，提示：".$mysqli->error);
		}else{
			$line["error"] = setError(0,"不匹配的类型");
		}
		*/
		echo json_encode($line);
		$mysqli->close();	
	}
?>