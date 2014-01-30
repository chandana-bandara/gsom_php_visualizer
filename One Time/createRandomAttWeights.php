<?php

$con = mysql_connect("localhost","root","cbandara");
mysql_select_db("msc_gsom_galg");


$insQ = "INSERT INTO fitness (weights) VALUES ";

$insV = array();
for ($i=1; $i<=100; $i++){
	$rarr = array();
	for ($j=1; $j<=36; $j++){
		$rarr[]= rand(0,1);
	}
	$insV []= "('".implode(",",$rarr)."')";
}

$insV = array_unique($insV);


$insQ .= implode(",",$insV);
//echo $insQ;
mysql_query ($insQ);
