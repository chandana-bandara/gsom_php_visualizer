<?php 
	require_once ('_includes/header.php');
	require_once ('_includes/gsom.php');

	
	$inFiles = glob("_files/*.in");
	
	$file = $inFiles[0];
		
	
	if (isset($_GET['file'])) {
		$file = '_files/'.$_GET['file'];
	}
	
	$threshold = 80;
	$radius = 3;
	$sp = 0.3;
	$lr = 0.25;
	$iterations = 3;
	$colorization = "GENERAL";
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
	
	$file = '_files/gsom_churn.in';
	
	for ($iterations=1; $iterations<=100; $iterations+=10)
		for ($sp=0.1; $sp<=1.0; $sp+=0.1)
			for ($lr=0.1; $lr<=1; $lr+=0.05)
				for ($rad=0; $rad<=5; $rad++){
					$gsom = new GSOM($sp, $radius, $lr, $iterations,$file);
					$gsom->setMapThreshold($threshold);
					$gsom->setMapColorization($colorization);
					$gsom->drawMap();
					$gsom->showMapInfo();
					$gsom->showClusterInfo();
				}
	
	require_once('_includes/footer.php');
