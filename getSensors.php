<?php
    require("database.php");
    
    // Get parameters from URL
    $center_lat = $_GET["lat"];
    $center_lng = $_GET["lng"];
    $radius = $_GET["radius"];
    
    // Start XML file, create parent node
    $dom = new DOMDocument("1.0");
    $node = $dom->createElement("sensors");
    $parnode = $dom->appendChild($node);
    
    // Opens a connection to a mySQL server
    $connection=mysql_connect (localhost, $username, $password);
    if (!$connection) {
        die("Not connected : " . mysql_error());
    }
    
    // Set the active mySQL database
    $db_selected = mysql_select_db($database, $connection);
    if (!$db_selected) {
        die ("Can\'t use db : " . mysql_error());
    }
    
    // Search the rows in the markers table
    $query = sprintf("SELECT token, ( 6371 * acos( cos( radians('%s') ) * cos( radians( lat ) ) * cos( radians( lng ) - radians('%s') ) + sin( radians('%s') ) * sin( radians( lat ) ) ) ) AS distance FROM assets HAVING distance < '%s' ORDER BY distance LIMIT 0,1",
                     mysql_real_escape_string($center_lat),
                     mysql_real_escape_string($center_lng),
                     mysql_real_escape_string($center_lat),
                     mysql_real_escape_string($radius));
    
    $result = mysql_query($query);
    
    $result = mysql_query($query);
    if (!$result) {
        die("Invalid query: " . mysql_error());
    }
    
    header("Content-type: text/xml");
    
    // Iterate through the rows, adding XML nodes for each
    while ($row = @mysql_fetch_assoc($result)){
        $node = $dom->createElement("sensor");
        $newnode = $parnode->appendChild($node);
        $newnode->setAttribute("token", $row['token']);
        $newnode->setAttribute("distance", $row['distance']);
    }
    
    echo $dom->saveXML();
    ?>