<?php
require_once('GoogleGeocoder.php');

$out = array();
$row = 1;
if (($handle = fopen("locations.csv", "r")) !== FALSE) {
	$headers = fgetcsv($handle, 1000, ",");
	$index_street = array_search("Street", $headers);
	$index_city = array_search("City", $headers);
	$index_postal_code = array_search("Zip", $headers);
	$index_province = array_search("State", $headers);
	while(($loc = fgetcsv($handle, 1000, ",")) !== FALSE) {
		$location = array(
		    'street' => $loc[$index_street],
		    'city'   => $loc[$index_city],
		    'postal_code' => $loc[$index_postal_code],
			'province'    => $loc[$index_province],
			'country'     => "us" 		
		);
		
		$gc = new GoogleGeocoder('ABQIAAAADF2STd2FFyIZbSoiWXIbaxR7PiuzwriKPLyzR6zyLjSn6oZVURSUPbbY1cObAiEF0-t2-A1LNN8x1w');
		$_location = $gc->geocodeLocation($location, FALSE);
		$lat = $_location['latitude'];
		$long = $_location['longitude'];
		echo $lat . ',' . $long . '\n';
	}
	fclose($handle);
}
?>
