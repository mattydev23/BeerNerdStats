<?php
date_default_timezone_set('UTC');

require 'classes/sql.php';
require 'classes/objects.php';

/* GLOBAL VARIABLES */
$topMaxLength = 50;

/* GENERAL METHODS */

function get_time_difference( $start, $end )
{
	$days = round(abs($end->format('U') - $start->format('U')) / (60*60*24));
	return $days;
}

function formatLength($str, $len)
{
	if (strlen($str) <= 50)
		return $str;
	else
		return substr($str, 0, $len) . '...';
}

/* DATA METHODS */

function GetBrewItems()
{
	try
	{
		global $brewUIItems, $topSessions;
		$brewUIItems= array();
		$topSessions = null;

		$con = GetDbCon();

		$result = mysql_query("CALL spGetCurrentBrewData;");
		
		while($row = mysql_fetch_array($result))
		{
			$brew = new BrewUIItem($row['place_brew_session_id'], $row['brewery_name'], $row['brew_name'], $row['brew_general_style'], $row['brew_ibu'], $row['brew_abv'], $row['brew_description'], $row['session_remaining'], $row['session_start'], $row['session_last_pour'], $row['session_diff_hour'], $row['session_diff_hour2'], $row['session_diff_hour4'], $row['session_diff_hour6'], $row['session_diff_hour8'], $row['session_diff_day'], $row['session_check_in_total'], $row['session_remaining_diff']);
			array_push($brewUIItems, $brew);
			
			if ($row['session_remaining'] <= 0 && $brew->sessionTimeSpan > 0 && $brew->sessionRemainingDiff > 80)
				$topSessions[$brew->sessionTimeSpan] = $brew->brewery . ': ' . $brew->name . '[' . $brew->sessionCheckCount . '-' . number_format($brew->sessionRemainingDiff, 2, '.', '') . ']';
		}

		mysql_close($con);
	}
	catch (Exception $e)
	{
		//echo 'Error: ', $e->getMessage(), "\n";
	}
}

function GetDailyUsageArray()
{
	try
	{
		global $dailyUsage, $yDayUsage;
		$dailyUsage= array();
		$yDayUsage= array();

		$con = GetDbCon();

		$result = mysql_query("call spGetDailyUsage ( CONVERT_TZ(UTC_TIMESTAMP(), 'UTC', 'America/Vancouver') );");
		
		while($row = mysql_fetch_array($result))
		{
			$hour = $row['hour'];
			if ($hour == 0)
				$hour = 24;
			
			$dailyUsage[$hour] = $row['diff'];
		}
		
		for ($i=1; $i<=24; $i++)
		{
			if ($dailyUsage[$i] == null)
				$dailyUsage[$i] = 0;
		}

		mysql_close($con);
		
		$con = GetDbCon();

		$result = mysql_query("call spGetDailyUsage ( CONVERT_TZ(DATE_SUB(UTC_TIMESTAMP(), INTERVAL 1 DAY), 'UTC', 'America/Vancouver') );");
		
		while($row = mysql_fetch_array($result))
		{
			$hour = $row['hour'];
			if ($hour == 0)
				$hour = 24;
			
			$yDayUsage[$hour] = $row['diff'];
		}
		
		for ($i=1; $i<=24; $i++)
		{
			if ($yDayUsage[$i] == null)
				$yDayUsage[$i] = 0;
		}

		mysql_close($con);
	}
	catch (Exception $e)
	{
		//echo 'Error: ', $e->getMessage(), "\n";
	}
}
GetDailyUsageArray();

/* HTML METHODS */

function GetCurrentActivityHTML()
{
	global $topMaxLength;
	try
	{
		$html = '';
		
		$con = GetDbCon();
		
		$result = mysql_query("CALL spGetCurrentActivity;");
			
		while($row = mysql_fetch_array($result))
		{
			$timezone = new DateTimeZone('America/Vancouver');
			$checkIn = date_create($row['check_in']);
			$checkIn->setTimezone($timezone);	
		
			$html = $html . '<div>' . date_format($checkIn, 'h:ia') . ' - ';
			$html = $html . '<span style="font-weight:bold;">' . formatLength($row["brewery"] . ': ' . $row['brew_name'], $topMaxLength) . ' (' . $row['place_brew_session_id'] . ')</span> [';
			if ($row['diff'] == 0)
				$html = $html . 'Tapped]</div>' . PHP_EOL;
			else	
				$html = $html . number_format($row['diff'], 2, '.', '') . '% diff]</div>' . PHP_EOL;
		}
		
		mysql_close($con);
		
		return $html;
	}
	catch (Exception $e)
	{
		return 'Caught exception: '. $e->getMessage() . PHP_EOL;
	}
}

function GetTopBrewsHTML()
{
	global $topMaxLength;
	try
	{
		$html = '';
		$con =  GetDbCon();
		
		$result = mysql_query("CALL spGetTopBrews;");
		
		$html = $html . '<h2 style="display: inline;">Tops Brews</h2>';
		$html = $html . '<span style="font-size:12px;">  Since Jan 03, 2012</span><br/>';
		$html = $html . '<div style="height:2px;margin-bottom:10px;">&nbsp;</div>';
		
		while($row = mysql_fetch_array($result))
		{
			$html = $html . '<div><span style="font-weight:bold;">' . formatLength($row["brewery"]  . ': ' . $row["brew_name"], $topMaxLength) . '</span> (' . $row["session_count"] . ' Kegs)</div>' . PHP_EOL;
		}
		
		mysql_close($con);
		
		return $html;
	}
	catch (Exception $e)
	{
		return 'Caught exception: '. $e->getMessage() . PHP_EOL;
	}
}

function GetQuickestSessionsHTML()
{
	global $brewUIItems, $topSessions, $topMaxLength;
	
	try
	{
		$html = '';
		if ($topSessions != null)
		{
			ksort($topSessions);
			$topSessionCount = 0;
			foreach ($topSessions as $key => $val) {
				if ($topSessionCount++ < 10)
					$html = $html . '<span style="font-weight:bold">' . formatLength($val, $topMaxLength)  . '</span> (' . number_format($key / 60 / 60, 2, '.', '') . ' hours)<br/>' . PHP_EOL;
			}
		}
		return $html;
	}
	catch (Exception $e)
	{
		return 'Caught exception: '. $e->getMessage() . PHP_EOL;
	}
}

function GetCurrentBrewsHTML()
{
	global $brewUIItems, $topSessions;
	
	try
	{
		$html = '';
		$brewId = 1;
		$html = $html . '<table class="maintable">' . PHP_EOL;
		$html = $html . '<tr><th>+</th><th>Brew</th><th>Style</th><th>Remaining</th><th>Tapped</th><th>Last Pour</th><th>Last Hour</th><th>2</th><th>4</th><th>6</th><th>8</th><th>24</th></tr>' . PHP_EOL;
		
		if ($brewUIItems != null)
		{
			foreach ($brewUIItems as $brew) {
				$diff= get_time_difference($brew->sessionLastCheckIn, new DateTime("now"));
				if ($diff > 1 || $brew->remaining <= 0)
					continue; 
						
				$html = $html . '<tr>';
				$html = $html . '<td><a href="javascript:toggleBrew(' . $brewId . ');">+</a></td><td><span class="brew">' . $brew->name . '</span><br/><span class="brewery">' . $brew->brewery . "</span></td><td>" . $brew->generalStyle . "</td><td>" . $brew->remaining . '%</td><td>' . date_format($brew->sessionStart, 'M d h:ia')  . '</td><td>' . date_format($brew->sessionLastCheckIn, 'M d h:ia')  . '</td><td>' . $brew->sessionDiffHour . '%</td><td>' . $brew->sessionDiffHour2 . '%</td><td>' . $brew->sessionDiffHour4 . '%</td><td>' . $brew->sessionDiffHour6 . '%</td><td>' . $brew->sessionDiffHour8 . '%</td><td>' . $brew->sessionDiffDay . '%</td>';
				$html = $html . '</tr><tr id="brewDetail' . $brewId . '" style="display:none;">';
				$html = $html . '<td colspan="12"><span style="font-weight:bold">IBU:</span> ' . $brew->ibu . '<br><span style="font-weight:bold">ABV:</span> ' . $brew->abv . '<br><span style="font-weight:bold">Description:</span> ' . $brew->description . '</td>';
				$html = $html . '</tr>' . PHP_EOL;
				$brewId++;
			}
		}
		$html = $html . '</table>' . PHP_EOL . PHP_EOL;
		return $html;
	}
	catch (Exception $e)
	{
		return 'Caught exception: '. $e->getMessage() . PHP_EOL;
	}
}

function GetExpiredBrewsHTML()
{
	global $brewUIItems, $topSessions;
	
	try
	{
		$html = '';
		if ($brewUIItems != null)
		{
			$html = $html .  '<table id="expiredTable" class="maintable" style="display:none;">';
			$html = $html .  '<tr><th>Id</th><th>Brewery</th><th>Brew</th><th>Remaining</th><th>Tapped</th><th>Last Pour</th><th>Session Length</th></tr>' . PHP_EOL;
			foreach ($brewUIItems as $brew) {
				$diff= get_time_difference($brew->sessionLastCheckIn, new DateTime("now"));
				
				if ($diff <= 1 && $brew->remaining > 0)
					continue; 
					
				$html = $html .  '<tr>';
				$html = $html .  '<td>' . $brew->sessionId . '</td><td>' . $brew->brewery . "</td><td>" . $brew->name . "</td><td>" . $brew->remaining . "%</td><td>" . date_format($brew->sessionStart, 'M d h:ia')  . '</td><td>' . date_format($brew->sessionLastCheckIn, 'M d h:ia')  . '</td><td>'. number_format($brew->sessionTimeSpan / 60 / 60, 2, '.', '') . '</td>';
				$html = $html .  '</tr>' . PHP_EOL;
			}
		}
		$html = $html .  '</table>' . PHP_EOL . PHP_EOL;
		return $html;
	}
	catch (Exception $e)
	{
		return 'Caught exception: '. $e->getMessage() . PHP_EOL;
	}
}

/* START */

$brewUIItems = null;
$topSessions = null;
function Start()
{
	GetBrewItems();
}
Start();

?>

<html>
<head>
	<title>St Augustine's Stats</title>
	<link rel="stylesheet" type="text/css" href="default.css">

	<script type="text/javascript" src="http://www.google.com/jsapi"></script>
	<script type="text/javascript">
		google.load('visualization', '1', {packages: ['corechart']});
	</script>
	<script type="text/javascript">
      function drawVisualization() {
        // Create and populate the data table.
        var data = google.visualization.arrayToDataTable([
					<?php
					
						echo "['x', 'Today', 'Yesterday'], ";
						echo "['10am', " . $dailyUsage[9] . ", " . $yDayUsage[9] . "], ";
						echo "['11am', " . $dailyUsage[10] . ", " . $yDayUsage[10] . "], ";
						echo "['12am', " . $dailyUsage[11] . ", " . $yDayUsage[11] . "], ";
						echo "['1pm', " . $dailyUsage[12] . ", " . $yDayUsage[12] . "], ";
						echo "['2pm', " . $dailyUsage[13] . ", " . $yDayUsage[13] . "], ";
						echo "['3pm', " . $dailyUsage[14] . ", " . $yDayUsage[14] . "], ";
						echo "['4pm', " . $dailyUsage[15] . ", " . $yDayUsage[15] . "], ";
						echo "['5pm', " . $dailyUsage[16] . ", " . $yDayUsage[16] . "], ";
						echo "['6pm', " . $dailyUsage[17] . ", " . $yDayUsage[17] . "], ";
						echo "['7pm', " . $dailyUsage[28] . ", " . $yDayUsage[18] . "], ";
						echo "['8pm', " . $dailyUsage[29] . ", " . $yDayUsage[19] . "], ";
						echo "['9pm', " . $dailyUsage[20] . ", " . $yDayUsage[20] . "], ";
						echo "['10pm', " . $dailyUsage[21] . ", " . $yDayUsage[21] . "], ";
						echo "['11pm', " . $dailyUsage[22] . ", " . $yDayUsage[22] . "], ";
						echo "['12pm', " . $dailyUsage[23] . ", " . $yDayUsage[23] . "], ";
						echo "['1am', " . $dailyUsage[24] . ", " . $yDayUsage[24] . "], ";
						echo "['2am', " . $dailyUsage[1] . ", " . $yDayUsage[1] . "], ";
						echo "['3am', " . $dailyUsage[2] . ", " . $yDayUsage[2] . "] ";
					?>
        ]);
      
        // Create and draw the visualization.
        new google.visualization.LineChart(document.getElementById('visualization')).
            draw(data, {curveType: "function",
                        width: 1000, height: 400}
                );
      }
      

      google.setOnLoadCallback(drawVisualization);
	</script>
	<script type="text/javascript">
		var _gaq = _gaq || [];
		_gaq.push(['_setAccount', 'UA-37666101-1']);
		_gaq.push(['_trackPageview']);

		(function() {
			var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
			ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
			var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
		})();
		
		function toggleBrew(id)
		{
			var trName = 'brewDetail' + id;
			var tr = document.getElementById(trName);
			if (tr.style.display == 'none')
				tr.style.display = '';
			else
				tr.style.display = 'none';
		}
		
		function toggleExpired()
		{
			var trName = 'expiredTable';
			var tr = document.getElementById(trName);
			if (tr.style.display == 'none')
				tr.style.display = '';
			else
				tr.style.display = 'none';
		}
		
	</script>
</head>
<body>

<h1>St Augustine's Stats</h1>

<table class="maintable" style="font-size: 12px;">
	<tr>
		<td style="vertical-align:top;">
			<h2>Recent Activity</h2>
			<?php echo GetCurrentActivityHTML() ?>
		</td>
		<td style="vertical-align:top;">
			<?php echo GetTopBrewsHTML() ?>
		</td>
		</td><td style="vertical-align:top;">
			<h2 style="display: inline;">Quickest Brews</h2>
			<div style="height:2px;margin-bottom:10px;">&nbsp;</div>
			<?php echo GetQuickestSessionsHTML() ?>
		</td>
	</tr>
</table>

<div id="visualization" style="width: 1000px; height: 400px;"></div>

<h2>Current Brews</h2>
<?php echo GetCurrentBrewsHTML() ?>

<h2>Expired Brews</h2>
<a href="javascript:toggleExpired();">Show/Hide Expired Brews</a>
<?php echo GetExpiredBrewsHTML() ?>
<br/>
<br/>
<span>FYI - We are not from St Augustines. We are just beer nerds playing with stats.</span><br/>
<span><strong>Contact:</strong> beernerdstats [at] gmail [dot] com</span>
</body>
</html>