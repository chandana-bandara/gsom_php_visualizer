<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<link rel="stylesheet" type="text/css" href="_css/map.css"/>
		<link rel="stylesheet" type="text/css" href="_css/jquery-ui-1.9.2.custom.min.css"/>
		<script type="text/Javascript" src="_js/jquery-1.6.1.min.js"></script>
		<script type="text/Javascript" src="_js/jquery-ui-1.9.2.custom.min.js"></script>
		<script type="text/Javascript" src="_js/jquery.layout-latest.js"></script>
		<script type="text/Javascript" src="_js/jquery.bt.min.js"></script>
		<script type="text/Javascript" src="_js/jquery.hoverIntent.minified.js"></script>
		<script type="text/Javascript" src="_js/excanvas.compiled.js"></script>
		

		
	</head>
	<body>
		<div id="nodeItemShowHover" class="ui-layout-south">Cluster Information:</div>
	<?php 
	
	require_once('_includes/functions.php');
	
	$mysql = mysql_connect("localhost","root","");
	mysql_select_db("msc_gsom_gal");
	
		//error_reporting(0);
	
	
	//set_time_limit (0);
