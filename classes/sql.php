<?php

function GetDbCon()
{
	$con = mysql_connect("localhost", "user","password");
	if (!$con)
	{
		die('Could not connect: ' . mysql_error());
	}
	mysql_select_db("dbname", $con);
	return $con;
}

?>