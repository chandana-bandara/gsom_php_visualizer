<?php 

	error_reporting("E_ALL");
	require_once ('_includes/header.php');
	require_once ('_includes/gsom.php');

	$inFiles = glob("_files/*.in");
	
	$file = $inFiles[0];
	
	//$file = "_files/gsom_churn.in";
	
	if (isset($_GET['file'])) {
		$file = '_files/'.$_GET['file'];
	}
	//
	$file = "_files/gsom_ani.in";
	
	$threshold = 80;
	$radius = 3;
	$sp = 0.1;
	$lr = 0.65;
	$iterations = 10;
	$colorization = "GENERAL"; /* CHURN / GENERAL */
	$attMap = array(); /*Keep this as blank. You can assign the attributes below when initiating GSOM object */
	
	foreach ($_POST as &$p){
		$p = addslashes($p);
	}
	
	if (isset($_POST['inputFile'])) $file = '_files/'.$_POST['inputFile'];
	if (isset($_POST['sp']) && is_numeric($_POST['sp'])) $sp = $_POST['sp'];
	if (isset($_POST['lr']) && is_numeric($_POST['lr'])) $lr = $_POST['lr'];
	if (isset($_POST['it']) && is_numeric($_POST['it'])) $iterations = $_POST['it'];
	if (isset($_POST['radius']) && is_numeric($_POST['radius'])) $radius = $_POST['radius'];
	if (isset($_POST['threshold']) && is_numeric($_POST['threshold'])) $threshold = $_POST['threshold'];
	if (isset($_POST['colorization'])) $colorization = $_POST['colorization'];
	
	//$startTime =microtime(true);
	
	try{
		
		
		//$attMap = str_split("100010000110100000011100011110010110");
		//$attMap  = explode(",","1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1");
		
		//$attMap  = explode(",","1,1,1,1,1,1");
		
		$gsom = new GSOM($sp, $radius, $lr, $iterations,$file,false,false,null,$attMap);
	
	//$cost = microtime(true) - $startTime;
	
	//echo "Time ".$cost* 1000;
	
	$gsom->setMapThreshold($threshold);
	$gsom->setMapColorization($colorization);
	
	$gsom->drawMap();
	$gsom->showMapInfo();
	$gsom->showClusterInfo();
	
	
} catch (Exception $e){
	echo $e->getMessage();
}
	echo "Fitness ".$gsom->getFitness();

require_once('_includes/footer.php');
