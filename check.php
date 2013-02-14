<html>
<head>
<title>St Augustines Stats</title>
</head>
<body>

<p>We need to add the "session" concept to catch rollovers</p>

<?php

require 'classes/sql.php';
require 'mail.php';

try
{

//$html = file_get_html('http://www.staugustinesvancouver.com/');
//echo $html

$test = file_get_contents("http://live-menu.staugustinesvancouver.com/taps.json?offset=0&amount=9999");

$json_a = json_decode($test, true);

//echo json_a[1]

foreach ($json_a as $k => $v) {

$con = GetDbCon();
if (!$con)
{
die('Could not connect: ' . mysql_error());
}

mysql_select_db("mattymc_beernerdstats", $con);

$filter = "CALL spStoreBrewData('%s', '%s', '%s', '%s', '%s', '%s', '%s', %d, %f, %f, '%s', '%s', %f);";

$filter = sprintf($filter, mysql_real_escape_string($v["brewer"]), mysql_real_escape_string($v["brewer_location"]), mysql_real_escape_string($v["name"]), mysql_real_escape_string($v["colour"]), mysql_real_escape_string($v["description"]), mysql_real_escape_string($v["general_style"]), mysql_real_escape_string($v["general_style_colour"]), mysql_real_escape_string($v["ibu"]), mysql_real_escape_string($v["alcohol_by_volume"]), mysql_real_escape_string($v["price"]), mysql_real_escape_string($v["specific_style"]), mysql_real_escape_string($v["specific_style_colour"]), mysql_real_escape_string($v["remaining"]));

echo $filter, '<br>';
$result = mysql_query($filter) or die(mysql_error());
$row = mysql_fetch_array($result);
$isNew = $row["is_new"];
$isNewBrew = $row["is_new_brew"];

echo 'is new: ' . $isNew . '<br>is new brew: ' . $isNewBrew . '<br><br>';

if ($isNew  == 'Y')
{
	if ($isNewBrew == 'Y')
		$subject = 'NEW BREW tapped at St Augustines: ' . $v["brewer"] . ' - ' . $v["name"];
	else
		$subject = 'Keg tapped at St Augustines: ' . $v["brewer"] . ' - ' . $v["name"];
	
	$body = 'Brewery: ' . $v["brewer"] . '<br>';
	$body = $body . '<span style="font-weight:bold">Brew</span>: ' . $v["name"] . '<br>';
	$body = $body . '<span style="font-weight:bold">Style</span>: ' . $v["specific_style"] . '<br>';
	$body = $body . '<span style="font-weight:bold">Colour</span>: <span style="background-color:' . $v["colour"] . ';color:' . $v["colour"] . '">||||||||</span><br>';
	$body = $body . '<span style="font-weight:bold">IBU</span>: ' . $v["ibu"] . '<br>';
	$body = $body . '<span style="font-weight:bold">ABV</span>: ' . $v["alcohol_by_volume"] . '<br>';
	$body = $body . '<span style="font-weight:bold">Description</span>: ' . $v["description"] . '<br>';
	$body = $body . '<span style="font-weight:bold">New Brew at St. Augustines?</span>: ' . ($isNewBrew == 'Y' ? "Yes" : "No") . '<br>';
	$body = $body . '<span style="font-weight:bold">BeerAdvocate</span>: <a href="http://beeradvocate.com/search?q=' . str_replace(' ', '+', $v["brewer"]) . '+' . str_replace(' ', '+', $v["name"]) . '+&qt=beer">here</a><br>';
	
	$body = $body . '<br>Check out this beers stats here: <a href="http://staugustinesstats.com/">http://staugustinesstats.com/</a><br>';
	
	$recipients = array("mattymc99@gmail.com", "alexwilson4@gmail.com", "iborobotosis@gmail.com");
	SendBeerMsg($recipients, $subject, $body);
}
mysql_close($con);

}



}
catch (Exception $e)
{
echo 'Caught exception: ', $e->getMessage(), "\n";
}

?>
<br/>
<br/>
<span>FYI - We are not from St Augustines. We are just beer nerds playing with stats.</span>
</body>
</html>