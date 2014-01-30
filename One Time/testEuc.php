<?php

require_once('../_includes/map.php');

$map = new Map();

	
		$attMap = array(1,1,1,0);
		
		$a = array(1,2,3,4);
		
		$b = array (1,1,1,1);
		
		echo $map->getEucDis($a,$b,$attMap);
		
	
