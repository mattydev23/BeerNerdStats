<html>
<head>
	<title>St Augustines Stats</title>
</head>
<body>
	<?php
	
		try
		{
			//$html = file_get_html('http://www.staugustinesvancouver.com/');
			//echo $html
			
			//$test = file_get_contents("http://live-menu.staugustinesvancouver.com/taps.json?offset=0&amount=9999");
			
			//$json_a = json_decode($test, true);
			
			//echo json_a[1]
			
			//foreach ($json_a as $k => $v) {
			//	echo '<span style="color:', $v["colour"], '">',$v["brewer"], ' - ', $v["name"], ' (', $v["remaining"], '%)</span><br/>';
			//}

			<?php
			
			$con = mysql_connect("mattymc","fXb9*dc2Tkop","");
			if (!$con)
			{
				die('Could not connect: ' . mysql_error());
			}

			mysql_select_db("mattymc_beernerdstats", $con);

			$result = mysql_query("SELECT * FROM brew");

			while($row = mysql_fetch_array($result))
			{
				echo $row['name'] . " " . $row['general_style'];
				echo "<br />";
			}

			mysql_close($con);
			
			?>
			
		} 
		catch (Exception $e) 
		{
			echo 'Caught exception: ',  $e->getMessage(), "\n";
		}

	?>
	<br/>
	<br/>
	<span>FYI - We are not from St Augustines. We are just beer nerds playing with stats.</span>
</body>
</html>