<?php 
	require_once ('_includes/header_analyze.php');
	require_once ('_includes/gsom.php');

	
	$inFiles = glob("_files/*.in");
	
	$file = $inFiles[0];
		
	if (isset($_GET['file'])) {
		$file = '_files/'.$_GET['file'];
	}
	
	$threshold = 75;
	$radius = 3;
	$sp = 0.3;
	$lr = 0.25;
	$iterations = 3;
	$colorization = "CHURN";
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
	
	
	$cacheFiles = glob("_cache/_files-gsom_churn-in*.garc");
	
	$goodAttFile = fopen("_results/good_maps_wrt_fitness.out","w");
	
	
	echo "<br/> File count ".count($cacheFiles)."</br>";
	
	$skip = 0;
	foreach ($cacheFiles as $c){
		
		//if ($skip++ > 50) continue;
		$params = getParamsFromFileName($c);
		echo (implode(",",$params)."<br/>");
		$file = $c;
		echo "setting file $file to gsom construct<br/>";
		echo "{$params['sp']}, {$params['radius']}, {$params['lr']}, {$params['iterations']},$file";
		$gsom = new GSOM($params['sp'], $params['radius'], $params['lr'], $params['iterations'],$file, true);
		
		$gsom->setMapThreshold($threshold);
		$gsom->setMapColorization($colorization);
		$reportArr = $gsom->drawMap($analyze=false, $goodAttFile);
		
		$fitness = $gsom->getFitness();
		echo "got fitness ".$fitness;
		//$goodness = $reportArr['blue']+$reportArr['red'];
		
		fwrite($goodAttFile,"$c ,".$fitness."\n");
		
		//fwrite($goodAttFile, implode(",",$reportArr)."\n");
		
		
		
		
		//$gsom->showMapInfo();
		//$gsom->showClusterInfo();		
	}
	
	fclose($goodAttFile);
	require_once('_includes/footer_analyze.php');
