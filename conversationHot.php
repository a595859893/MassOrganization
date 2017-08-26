<?php
	require 'commonFunction.php';
	$mysqli=linkToSQL();
	$time = time();
	$rst = $mysqli->query("SELECT * FROM conversation AS a WHERE a.UID NOT IN (SELECT conID FROM conversationHot) AND type='conversation' ORDER BY good DESC");
	while($row = $rst->fetch_array(MYSQLI_ASSOC)){
		$id = $row["UID"];
		$rst = $mysqli->query("INSERT INTO conversationHot (conID,time) VALUES($id,$time)");
		break;
	}
?>
