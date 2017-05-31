<?php // mainPage.php
	include_once 'mainMenu.php';
	include_once 'searchTFL.php';
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Main Page</title>
		<script src="http://maps.google.com/maps/api/js?key=AIzaSyDxCfPMvex2gPy8ATVoSnA79_KpWP7diLs&sensor=false"
            type="text/javascript"></script>
		    <script type="text/javascript">
    //<![CDATA[
	// Ref google api documentation
    var customIcons = {
		bar: {
			icon: 'http://labs.google.com/ridefinder/images/mm_20_blue.png',
			shadow: 'http://labs.google.com/ridefinder/images/mm_20_shadow.png'
		},
		coffee: {
			icon: 'http://labs.google.com/ridefinder/images/mm_20_red.png',
			shadow: 'http://labs.google.com/ridefinder/images/mm_20_shadow.png'
		}
    };

    function loadMap() {
		var map = new google.maps.Map(document.getElementById("map"), {
			center: new google.maps.LatLng(51.534721, -0.104329),
			zoom: 12,
			});
		map.setMapTypeId(google.maps.MapTypeId.ROADMAP); 
		var infoWindow = new google.maps.InfoWindow;

		downloadUrl("XMLGenerator.php", function(data) {
			var xml = data.responseXML;
			var establishments = xml.documentElement.getElementsByTagName("poi");
			for (var i = 0; i < establishments.length; i++) {
				var poiId = establishments[i].getAttribute("poiId");
				var name = establishments[i].getAttribute("name");
				var pstreet = establishments[i].getAttribute("pstreet");
				var pcity = establishments[i].getAttribute("pcity");
				var ppostcode = establishments[i].getAttribute("ppostcode");
				var ptype = establishments[i].getAttribute("ptype");
				var point = new google.maps.LatLng(
				parseFloat(establishments[i].getAttribute("lat")),
				parseFloat(establishments[i].getAttribute("long")));
				var infoWindowText = "<b>" + name + "</b> <br/>" + pstreet + "<br/>" + pcity + " " + ppostcode + "<br/>[<a href='addComment.php?view=" + poiId + "'>Add Comment</a>]<br/>" + "[<a href='mainPage.php?view=" + poiId + "'>View All Comments</a>]<br/>";
				var icon = customIcons[ptype] || {};
				var marker = new google.maps.Marker({
					map: map,
					position: point,
					icon: icon.icon,
					shadow: icon.shadow
					});
				bindInfoWindow(marker, map, infoWindow, infoWindowText);
			}
		});
		
		google.maps.event.addListener(map, 'click', function(event) {
			setCoordinates(event.latLng);
		});	
    }
	function setCoordinates(coordinates) {
		document.getElementById('latCoordinate').value = coordinates.lat();
		document.getElementById('lonCoordinate').value = coordinates.lng();
	}
	
	function bindInfoWindow(marker, map, infoWindow, infoWindowText) {
		google.maps.event.addListener(marker, 'click', function() {
			infoWindow.setContent(infoWindowText);
			infoWindow.open(map, marker);
		});
    }

    function downloadUrl(url, callback) {
		var request = window.ActiveXObject ?
			new ActiveXObject('Microsoft.XMLHTTP') :
			new XMLHttpRequest;

		request.onreadystatechange = function() {
			if (request.readyState == 4) {
				request.onreadystatechange = doNothing;
				callback(request, request.status);
			}	
		};

		request.open('GET', url, true);
		request.send(null);
    }

	function doNothing() {}

    //]]>
  </script>
	</head>
	<body onload="loadMap()">
		<center><div id="map" style="width: 700px; height: 500px; border:solid black 1px;"></div>
		<form action="mainPage.php" method="post">
		<input type="text" id="searchNearPlaces" name="searchNearPlaces"/>
		<select id="selectArea" name="selectArea">
		<option value="5" selected>5km</option>
		<option value="10">10km</option>
		<option value="20">20km</option>
		<option value="30">30km</option>
		</select>
		<input type="submit" value="Search" name="searchNearEstablishment"/>
		<input type="hidden" name="latCoordinate" id="latCoordinate" value="" />
		<input type="hidden" name="lonCoordinate" id="lonCoordinate" value="" />
		<input type="submit" value="Search from selected point" name="searchFromSelectedPoint"/>
		</form><br/>
		<form action="mainPage.php" method="post">
		<input type="submit" value="Show All Comments" name="showAllCmnts"/>
		<input type="submit" value="Show All Establishments" name="showAllEstablishment"/>
		</form>
		<form action="addEstablishment.php" method="post">
		<input type="submit" value="Add Establishment" name="addEstablishment"/>
		</form></center>
	</body>		
	<?php 
		
		if (isset($_POST['searchFromSelectedPoint'])) {
		
			$lat = cleanInput($_POST['latCoordinate']);
			$lon = cleanInput($_POST['lonCoordinate']);
			$area = cleanInput($_POST['selectArea']);
			
			haversineSearch($lat, $lon, $area);
		
		}
		
		if (isset($_POST['searchNearEstablishment'])) {

			$establishmentAddress = cleanInput($_POST['searchNearPlaces']);
			$area = cleanInput($_POST['selectArea']);
			
			$establishmentAddress = trim ($establishmentAddress, " ");
			$establishmentAddress = str_replace ( ' ' , '+' , $establishmentAddress);

			$geocodeURL = "http://maps.googleapis.com/maps/api/geocode/json?address=" . $establishmentAddress . "&sensor=false";
			$contents = file_get_contents($geocodeURL);
			$jsonDecoded= json_decode($contents);
			$lat = $jsonDecoded->results[0]->geometry->location->lat;
			$lon = $jsonDecoded->results[0]->geometry->location->lng;
			$lat = cleanInput($lat);
			$lon = cleanInput($lon);
			
			haversineSearch($lat, $lon, $area);
	
		}
				
		if (isset($_GET['view'])) {
		
			$id = cleanInput($_GET['view']);
			
			$query  = "SELECT * FROM establishments WHERE poiId='$id'";
			
			$queryResult = mysql_query($query, $db_server) or die("Query failed: $query<br />" . mysql_error() . "<br /><br />");
			
			$readRow = mysql_fetch_row($queryResult);
			
			echo "<br />$readRow[1]<br />" . 
				     "Address: $readRow[2], $readRow[4]<br />$readRow[3]<br />"; 
			
			$query  = "SELECT * FROM comments WHERE poiId='$id' ORDER BY timedate DESC";
			
			$queryResult = mysql_query($query, $db_server) or die("Query failed: $query<br />" . mysql_error() . "<br /><br />");
			
			if (mysql_num_rows($queryResult) == 0) {
			
				echo "<br />There are no comments for this Establishment<br /><br />";
			
			}
			
			$numRows = mysql_num_rows($queryResult);
			
			for ($i = 0 ; $i < $numRows ; ++$i) {
			
				$readRow = mysql_fetch_row($queryResult);
				
				echo "<br />Comment about $readRow[2] (";
				echo date('M jS \'y g:i a', $readRow[1]) . ")<br />";
				echo "$readRow[3]<br />";
				echo "$readRow[4] <br />";
			}
		}
		
		if (isset($_GET['from']) && isset($_GET['to']) && isset($_GET['selectCmnt'])) {

			echo "<br /><form action='mainPage.php'>" . 
			 "Select comments from: <input type='date' name='from'>" . 
			 "to <input type='date' name='to'>" . 
			 "<input type='submit' value='Select Comments' name='selectCmnt' id='selectCmnt'/>" .
			 "</form>";
				 
			$yearFrom = substr(cleanInput($_GET['from']), 0,4);
			$monthFrom = substr(cleanInput($_GET['from']), 5,2);
			$dayFrom = substr(cleanInput($_GET['from']), 8,2);
				
			$yearTo = substr(cleanInput($_GET['to']), 0,4);
			$monthTo = substr(cleanInput($_GET['to']), 5,2);
			$dayTo = substr(cleanInput($_GET['to']), 8,2);
				
			$timeFrom = mktime(0,0,0,$monthFrom,$dayFrom,$yearFrom);
			$timeTo = mktime(0,0,0,$monthTo,$dayTo,$yearTo);
				
			if ($timeFrom < $timeTo) {
				
				$query  = "SELECT * FROM comments WHERE timedate>='$timeFrom' AND timedate<='$timeTo' ORDER BY timedate DESC";
					
				$queryResult = mysql_query($query, $db_server) or die("Query failed: $query<br />" . mysql_error() . "<br /><br />");
				
				$numRows = mysql_num_rows($queryResult);
					
				for ($i = 0 ; $i < $numRows ; ++$i) {
			
					$readRow = mysql_fetch_row($queryResult);
					
					echo "<br />Comment about $readRow[2] (";
					echo date('M jS \'y g:i a', $readRow[1]) . ")<br />";
					echo "$readRow[3]<br />";
					echo "$readRow[4] <br />";	
				}	
			}	
				else
					echo "The dates are incorrect.<br />";
		}
		
		if (isset($_POST['showAllCmnts'])) {
		
			echo "<br /><form action='mainPage.php'>" . 
				 "Select comments from: <input type='date' name='from'>" . 
				 "to <input type='date' name='to'>" . 
				 "<input type='submit' value='Select Comments' name='selectCmnt' id='selectCmnt'/>" .
				 "</form>";

			$query  = "SELECT * FROM comments ORDER BY timedate DESC";

			$queryResult = mysql_query($query, $db_server) or die("Query failed: $query<br />" . mysql_error() . "<br /><br />");
			
			$numRows = mysql_num_rows($queryResult);
			
			for ($i = 0 ; $i < $numRows ; ++$i) {
			
				$readRow = mysql_fetch_row($queryResult);
				
				echo "<br />Comment about $readRow[2] (";
				echo date('M jS \'y g:i a', $readRow[1]) . ")<br />";
				echo "$readRow[3]<br />";
				echo "$readRow[4] <br />";	
			}
		}
		
		if (isset($_GET['from']) && isset($_GET['to']) && isset($_GET['selectEstablishment'])) {

				echo "<br /><form action='mainPage.php'>" . 
				 "Select establishments from: <input type='date' name='from'>" . 
				 "to <input type='date' name='to'>" . 
				 "<input type='submit' value='Select Establishments' name='selectEstablishment' id='selectEstablishment'/>" .
				 "</form>";
				 
				$yearFrom = substr(cleanInput($_GET['from']), 0,4);
				$monthFrom = substr(cleanInput($_GET['from']), 5,2);
				$dayFrom = substr(cleanInput($_GET['from']), 8,2);
				
				$yearTo = substr(cleanInput($_GET['to']), 0,4);
				$monthTo = substr(cleanInput($_GET['to']), 5,2);
				$dayTo = substr(cleanInput($_GET['to']), 8,2);
				
				$timeFrom = mktime(0,0,0,$monthFrom,$dayFrom,$yearFrom);
				$timeTo = mktime(0,0,0,$monthTo,$dayTo,$yearTo);
				
				if ($timeFrom < $timeTo) {
				
					$query  = "SELECT * FROM establishments WHERE timedate>='$timeFrom' AND timedate<='$timeTo' ORDER BY timedate DESC";
					
					$queryResult = mysql_query($query, $db_server) or die("Query failed: $query<br />" . mysql_error() . "<br /><br />");
				
					$numRows = mysql_num_rows($queryResult);
					
					for ($i = 0 ; $i < $numRows ; ++$i) {
					
						$readRow = mysql_fetch_row($queryResult);
						
						echo "<br />$readRow[1]<br />" . 
							 "Address: $readRow[2], $readRow[4]<br />$readRow[3]<br />" . 
							 "[<a href='mainPage.php?view=$readRow[0]'>View All Comments</a>]"; 
						
						if ($sessionON == TRUE) {
													 
							echo "[<a href='addComment.php?view=$readRow[0]'>Add Comment</a>]<br />";
						}
						else
							echo "<br /><br />";	
					}	
				}	
				else
					echo "The dates are incorrect.<br />";
		}
		
		if (isset($_POST['showAllEstablishment'])) {
		
			echo "<br /><form action='mainPage.php'>" . 
				 "Select comments from: <input type='date' name='from'>" . 
				 "to <input type='date' name='to'>" . 
				 "<input type='submit' value='Select Establishments' name='selectEstablishment' id='selectEstablishment'/>" .
				 "</form>";

			$query  = "SELECT * FROM establishments ORDER BY timedate DESC";

			$queryResult = mysql_query($query, $db_server) or die("Query failed: $query<br />" . mysql_error() . "<br /><br />");
			
			$numRows = mysql_num_rows($queryResult);
			
			for ($i = 0 ; $i < $numRows ; ++$i) {
			
				$readRow = mysql_fetch_row($queryResult);
				
				echo "<br />$readRow[1]<br />" . 
				     "Address: $readRow[2], $readRow[4]<br />$readRow[3]<br />" . 
					 "[<a href='mainPage.php?view=$readRow[0]'>View All Comments</a>]"; 
				
				if ($sessionON == TRUE) {
											 
					echo "[<a href='addComment.php?view=$readRow[0]'>Add Comment</a>]<br />";
				}
				else
					echo "<br />";	
			}
		}
		
		function haversineSearch($latitude, $longitud, $area) {
		
			global $db_server;			
			global $sessionON;
					
			$query = sprintf("SELECT poiId, name, pstreet, ppostcode, pcity, lat, `long`, ( 3959 * acos( cos( radians('%s') ) * cos( radians( lat ) ) * cos( radians( `long` ) - radians('%s') ) + sin( radians('%s') ) * sin( radians( lat ) ) ) ) AS distance FROM establishments HAVING distance < '%s' ORDER BY distance LIMIT 0 , 20",
			$latitude, $longitud, $latitude, $area);

			$queryResult = mysql_query($query, $db_server) or die("Query failed: $query<br />" . mysql_error() . "<br /><br />");
			
			$numRows = mysql_num_rows($queryResult);
			
			for ($i = 0 ; $i < $numRows ; ++$i) {
					
				$readRow = mysql_fetch_row($queryResult);
						
				echo "<br />$readRow[1]<br />" . 
					 "Address: $readRow[2], $readRow[3]<br />$readRow[4]<br />" . 
					 "[<a href='mainPage.php?view=$readRow[0]'>View All Comments</a>]"; 
						
				if ($sessionON == TRUE) {
													 
					echo "[<a href='addComment.php?view=$readRow[0]'>Add Comment</a>]<br />";
				}
				else
					echo "<br /><br />";	
			}				
		}		
		mysql_close($db_server);
	?>
</html>