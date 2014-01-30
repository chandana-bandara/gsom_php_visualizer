<?php
require_once ('map.php');
require_once ('node.php');

class GSOM{
	private $spread;
	private $radius;
	private $lrate;
	private $iterations;
	private $map;
	private $gt;
	private $dimensions;
	private $trainingFile;
	private $mapSource; /* Whether values are calculated or taken from the cache */
	private $attMap; /* Created to store the currently using att Map for GA*/
	function __construct($spread, $radius, $lrate, $iterations, $infile,$analyze=false,$selectedFields=false, $fields=null, $attMap=array()){
		
		
		
		$this->setSpread($spread);
		$this->setRadius($radius);
		$this->setLrate($lrate);
		$this->setIterations($iterations);
		$this->setTrainingFile($infile);
		
		$this->setDimensionsFromFile($infile);
		//$this->setDimensions(6);
		
		$this->setGT($this->getDimensions(), $this->getSpread());		
		
		if (count($attMap)==0)  {
			 
			 
			 for ($i=0; $i<$this->getDimensions(); $i++){
				$atMap[]=1;
			 }
			 
			 $this->initMap(); 
		} else { 
			
			$this->setAttMap($attMap);
			
			/* Map initiation is done on the thiis->train method */}
		
		if (!($this->loadCacheMap($this->getTrainingFile(), $this->getSpread(), $this->getIterations(),$this->getRadius(), $this->getLrate(), $analyze, $attMap))){
			$this->train($attMap);
			echo "did not get the cache";
		} else {
			echo "got the cache";
		}
	}
	
	function setAttMap($aM=array()){
		$this->attMap = $aM;
	}
	
	function getAttMap(){
		return $this->attMap;
	}
	
	function getFitness(){
		return $this->map->getFitness();
	}
	
	function getGoodness(){
		
	}
	
	private function loadCacheMap($fileName="",$sp="",$iterations="",$radius="",$lr="",$analyze=false,$attMap=array()){
		
		if (!$analyze) $fileName = $this->getArchiveFileName($fileName,$sp,$iterations,$radius,$lr,$attMap);
		
		echo "<br/>checking cache file <br/>$fileName<br/>";
		if (file_exists($fileName)){
			
			$if = fopen($fileName,"r");
				$params = unserialize(fgets($if));
				$map = unserialize(fgets($if));
				$this->setMap($map);
				$this->setSpread($params['sp']);
				$this->setIterations($params['iterations']);
				$this->setRadius($params['radius']);
				$this->setLrate($params['lr']);
				$this->setAttMap($params['attMap']);
			fclose($if);
			
			return true;
		} else {
			return false;
		}
	}
	
	private function getArchiveFileName($fileName="",$sp="",$iterations="",$radius="",$lr="",$attMap=array()){
		
		if (count($attMap)>0){
			$fileName = $fileName.'_'.$sp . '_' . $iterations.'_'.$radius.'_'.$lr.'_'.implode("",$attMap);
		} else {
			$fileName = $fileName.'_'.$sp . '_' . $iterations.'_'.$radius.'_'.$lr;
		}
		
		
		$fileName = str_replace("/","-",$fileName);
		$fileName = str_replace(".","-",$fileName).".garc";
		$fileName = "_cache/".$fileName;
		
		return $fileName;
	}
	
	//function __destruct(){
	//	$this->dumpVars();
	//}
	
	public function dumpVars(){
		
		//return 0; /* for get fitness only - coz no need to cache again */
		
		$sp = $this->getSpread();
		$iterations = $this->getIterations();
		$radius = $this->getRadius();
		$lr = $this->getLrate();
		$file = $this->getTrainingFile();
		$attMap = $this->getAttMap();
		$fileName = $this->getArchiveFileName($file,$sp,$iterations,$radius,$lr,$attMap);
		
		$params['sp'] = $sp;
		$params['iterations'] = $iterations;
		$params['radius'] = $radius;
		$params['lr'] = $lr;
		$params['file'] = $file;
		$params['attMap'] = $attMap;
		
		if (file_exists($fileName)){
			unlink($fileName);
		} 
		
		echo "saving file<br/>$fileName<br/>";
		$of = fopen($fileName,'w');
		$map = $this->getMap();
		
		fwrite ($of, serialize($params)."\n");
		fwrite ($of,  serialize($map));
		fclose ($of);
		
		echo "writing done";
	}
	
	private function getWeightForNewNode($x,$y){
		$weight = array();
		$case ='';
		
	//	show("Getting weight for the node at $x, $y<br/>");
		
//		show($this->map,true);
	
		
		/* Case a */
			
		if (!$this->map->checkMapAvailability($x-1,$y) && !$this->map->checkMapAvailability($x-2,$y)){
			$nodeLeft1 = $this->map->getNodeAtLocation($x-1,$y);	
			$nodeLeft2 = $this->map->getNodeAtLocation($x-2,$y);
			$case ='a';
			
			//show ("got1  left1 = ".$nodeLeft1->getWeights(),true);
			//show ("got1 left2 = ".$nodeLeft2->getWeights(),true);
		}elseif (!$this->map->checkMapAvailability($x,$y-1) && !$this->map->checkMapAvailability($x,$y-2)){
			$nodeLeft1 = $this->map->getNodeAtLocation($x,$y-1);	
			$nodeLeft2 = $this->map->getNodeAtLocation($x,$y-2);
			$case ='a';
			//show ("got2 left1 = ".$nodeLeft1->getWeights(),true);
			//show ("got2 left2 = ".$nodeLeft2->getWeights(),true);
			
		}elseif(!$this->map->checkMapAvailability($x+1,$y) && !$this->map->checkMapAvailability($x+2,$y)){
			$nodeLeft1 = $this->map->getNodeAtLocation($x+1,$y);	
			$nodeLeft2 = $this->map->getNodeAtLocation($x+2,$y);
			$case ='a';
			//show ("got3 left1 = ".$nodeLeft1->getWeights(),true);
			//show ("got3 left2 = ".$nodeLeft2->getWeights(),true);
		}elseif(!$this->map->checkMapAvailability($x,$y+1) && !$this->map->checkMapAvailability($x,$y+2)){
			$nodeLeft1 = $this->map->getNodeAtLocation($x,$y+1);	
			$nodeLeft2 = $this->map->getNodeAtLocation($x,$y+2);
			$case ='a';
			//show ("got4 left1 = ".$nodeLeft1->getWeights(),true);
			//show ("got4 left2 = ".$nodeLeft2->getWeights(),true);
		} 
						
		/* End of case a */
		
		/* Case b */
		if ($case=='')
			if (!$this->map->checkMapAvailability($x-1,$y) && !$this->map->checkMapAvailability($x+1,$y)){
				$nodeLeft = $this->map->getNodeAtLocation($x-1,$y);
				$nodeRight = $this->map->getNodeAtLocation($x+1,$y);	
				$case = 'b';
			}elseif(!$this->map->checkMapAvailability($x,$y-1) && !$this->map->checkMapAvailability($x,$y+1)){
				$nodeLeft = $this->map->getNodeAtLocation($x,$y-1);
				$nodeRight = $this->map->getNodeAtLocation($x,$y+2);	
				$case ='b';
			}
		/* End of case b */
		
		/* Case c */
		if ($case=='')
			if (!$this->map->checkMapAvailability($x-1,$y) && $this->map->checkMapAvailability($x-2,$y) && $this->map->checkMapAvailability($x-1,$y+1) && !$this->map->checkMapAvailability($x-1,$y-1)){
				$nodeLeft = $this->map->getNodeAtLocation($x-1,$y);
				$nodeLeftTop = $this->map->getNodeAtLocation($x-1,$y-1);
				$case = 'c';
				show ("c1");
			} elseif (!$this->map->checkMapAvailability($x-1,$y) && $this->map->checkMapAvailability($x-2,$y) && $this->map->checkMapAvailability($x-1,$y-1) && !$this->map->checkMapAvailability($x-1,$y+1)){
				$nodeLeft = $this->map->getNodeAtLocation($x-1,$y);
				$nodeLeftTop = $this->map->getNodeAtLocation($x-1,$y+1);
				$case = 'c';
				show ("c2");
			} elseif (!$this->map->checkMapAvailability($x,$y-1) && $this->map->checkMapAvailability($x-1,$y-1) && $this->map->checkMapAvailability($x,$y-2) && !$this->map->checkMapAvailability($x+1,$y-1)){
				$nodeLeft = $this->map->getNodeAtLocation($x,$y-1);
				$nodeLeftTop = $this->map->getNodeAtLocation($x+1,$y-1);
				$case = 'c';
				show ("c3");
			} elseif (!$this->map->checkMapAvailability($x,$y-1) && $this->map->checkMapAvailability($x,$y-2) && $this->map->checkMapAvailability($x+1,$y-1) && !$this->map->checkMapAvailability($x-1,$y-1)){
				$nodeLeft = $this->map->getNodeAtLocation($x,$y-1);
				$nodeLeftTop = $this->map->getNodeAtLocation($x-1,$y-1);
				$case = 'c';
				show ("c4");
			} elseif (!$this->map->checkMapAvailability($x+1,$y) && $this->map->checkMapAvailability($x+1,$y-1) && $this->map->checkMapAvailability($x+2,$y) && !$this->map->checkMapAvailability($x+1,$y+1)){
				$nodeLeft = $this->map->getNodeAtLocation($x+1,$y);
				$nodeLeftTop = $this->map->getNodeAtLocation($x+1,$y+1);
				$case = 'c';
				show ("c5");
			} elseif (!$this->map->checkMapAvailability($x+1,$y) && $this->map->checkMapAvailability($x+1,$y+1) && $this->map->checkMapAvailability($x+2,$y) && !$this->map->checkMapAvailability($x+1,$y-1)){
				$nodeLeft = $this->map->getNodeAtLocation($x+1,$y);
				$nodeLeftTop = $this->map->getNodeAtLocation($x+1,$y-1);
				$case = 'c';
				show ("c6");
			} elseif (!$this->map->checkMapAvailability($x,$y+1) && $this->map->checkMapAvailability($x+1,$y+1) && $this->map->checkMapAvailability($x,$y+2) && !$this->map->checkMapAvailability($x-1,$y+1)){
				$nodeLeft = $this->map->getNodeAtLocation($x,$y+1);
				$nodeLeftTop = $this->map->getNodeAtLocation($x-1,$y+1);
				$case = 'c';
				show ("c7");
			} elseif (!$this->map->checkMapAvailability($x,$y+1) && $this->map->checkMapAvailability($x-1,$y+1) && $this->map->checkMapAvailability($x,$y+2) && !$this->map->checkMapAvailability($x+1,$y+1)){
				$nodeLeft = $this->map->getNodeAtLocation($x,$y+1);
				$nodeLeftTop = $this->map->getNodeAtLocation($x+1,$y+1);
				$case = 'c';
				show ("c8");
			}
		/* End of case c */
		
		
		/* Case d */
		if ($case=='')
			if (!$this->map->checkMapAvailability($x-1,$y) && $this->map->checkMapAvailability($x-1,$y-1) && $this->map->checkMapAvailability($x-1,$y+1) && $this->map->checkMapAvailability($x-2,$y)){
				$nodeLeft = $this->map->getNodeAtLocation($x-1,$y);
				$case = 'd';
			}elseif (!$this->map->checkMapAvailability($x,$y-1) && $this->map->checkMapAvailability($x-1,$y-1) && $this->map->checkMapAvailability($x+1,$y-1) && $this->map->checkMapAvailability($x,$y-2)){
				$nodeLeft = $this->map->getNodeAtLocation($x-1,$y);
				$case = 'd';
			}elseif (!$this->map->checkMapAvailability($x+1,$y) && $this->map->checkMapAvailability($x+1,$y-1) && $this->map->checkMapAvailability($x+1,$y+1) && $this->map->checkMapAvailability($x+2,$y)){
				$nodeLeft = $this->map->getNodeAtLocation($x-1,$y);
				$case = 'd';
			}elseif (!$this->map->checkMapAvailability($x,$y+1) && $this->map->checkMapAvailability($x+1,$y+1) && $this->map->checkMapAvailability($x-1,$y+1) && $this->map->checkMapAvailability($x,$y+2)){
				$nodeLeft = $this->map->getNodeAtLocation($x-1,$y);
				$case = 'd';
			}
			 
		/* End of case d */
		
		show ("Case ".$case);
		if ($case=='a'){
			if ($this->map->getRootSquardSumFromVector($nodeLeft1->getWeights()) >= $this->map->getRootSquardSumFromVector($nodeLeft2->getWeights())){
				//show("first");
				//show($nodeLeft1->getWeights(),true);
				//show ($nodeLeft2->getWeights(),true);
				$weight = 
					$this->map->subtractWeightVectors($nodeLeft1->getWeights(), 
						$this->map->subtractWeightVectors(
							$nodeLeft2->getWeights(),
							$nodeLeft1->getWeights()
						)
					);
					
					//show ("got weight ".$weight,true);
			} else if ($this->map->getRootSquardSumFromVector($nodeLeft2->getWeights()) > $this->map->getRootSquardSumFromVector($nodeLeft1->getWeights())){
				//show("second");
				$weight = 
					$this->map->addWeightVectors($nodeLeft1->getWeights(), 
						$this->map->subtractWeightVectors(
							$nodeLeft2->getWeights(),
							$nodeLeft1->getWeights()
						)
					);
			}
		} elseif ($case=='b'){
				$weight = $this->map->averageWeightVectors($nodeLeft->getWeights(), $nodeRight->getWeights());
		}elseif($case=='c'){
			if ($this->map->getRootSquardSumFromVector($nodeLeft->getWeights()) >= $this->map->getRootSquardSumFromVector($nodeLeftTop->getWeights())){
				$weight = 
					$this->map->subtractWeightVectors($nodeLeft->getWeights(), 
						$this->map->subtractWeightVectors($nodeLeftTop->getWeights(),$nodeLeft->getWeights())
					);
			} else {
				$weight = 
					$this->map->addWeightVectors($nodeLeft->getWeights(),
						$this->map->subtractWeightVectors($nodeLeft->getWeights(),$nodeLeftTop->getWeights())
					);
			}
		} elseif ($case=='d'){
			/* To be implemented - below is not align with the algo*/
				$weight = $nodeLeft->getWeights();
		}else {
			throw new Exception('No CASE for get weights for grow');
		}
		
		return $weight;
	}
	
	private function grow($x,$y){
		show ("Growing node $x,$y and the current map is");
		show ($this->map->getMap(),true);
		$d = $this->getDimensions();
		$grewProperly = false;
		
		if (!$this->map->checkMapAvailability($x,$y)){
			$growNodes =0;
			
			if ($this->map->checkMapAvailability($x-1,$y)){
				$growNodes++;
				$addingNode = new Node($d, $this->getWeightForNewNode($x-1,$y));
				$this->map->setMapNode($x-1,$y,$addingNode);
				$grewProperly = true;
			}
			
			if ($this->map->checkMapAvailability($x,$y-1)){
				
				$growNodes++;
				$addingNode = new Node($d, $this->getWeightForNewNode($x,$y-1));
				$this->map->setMapNode($x,$y-1,$addingNode);
				$grewProperly = true;
			}
			
			if ($this->map->checkMapAvailability($x+1,$y)){
				
				$growNodes++;
				$addingNode = new Node($d, $this->getWeightForNewNode($x+1,$y));
				$this->map->setMapNode($x+1,$y,$addingNode);
				$grewProperly = true;
			}
			
			if ($this->map->checkMapAvailability($x,$y+1)){
				
				$growNodes++;
				$addingNode = new Node($d, $this->getWeightForNewNode($x,$y+1));
				$this->map->setMapNode($x,$y+1,$addingNode);
				$grewProperly = true;
			}
			
			show ("Node $x,$y grew and the current map is");
			show ($this->map->getMap(),true);
		} else {
			throw new Exception("Can't grow an empty node");
			die("Can't grow an empty node");
		}
	}
	
	//$this->adjustWeightRecursively($values,$adjustedMap,$adjustQueue,$adjustDist,$rad, $affecttingLr);
	private function adjustWeightRecursively($inputWeightsVector, &$adjustedMap,&$adjustQueue,&$adjustDist,$radius,$lr){
		
		
		$test =0;
		
		do {
			$tookLoc = array_shift($adjustQueue);
			$tookLocArr = explode(",",$tookLoc);
			
			$x = $tookLocArr[0];
			$y = $tookLocArr[1];
			$tookDist = array_shift($adjustDist);
		
			
			
			if (!$this->map->checkMapAvailability($x,$y) && !isset($adjustedMap[$x][$y])){
				$adjustedMap[$x][$y] = true;
				if ($tookDist==0) {
					$affectedLR =  $lr;
				} else {
				//	$affectedLR = $lr;
					$affectedLR = $lr/(($tookDist+1));
				}
				$currentWeights = $this->map->getNodeAtLocation($x,$y)->getWeights();
				
				$newWeights = array();
				
				foreach ($currentWeights as $k=>$c){
					$newWeights[$k] = $c +  $affectedLR * ($inputWeightsVector[$k] - $c);
				}
				
				$this->map->setNodeWeight($x,$y,$newWeights);
				
				if ($tookDist < $radius) {
					$newDist = ++$tookDist;
					
					if (!in_array($x.','.$y-1,$adjustQueue) && !$this->map->checkMapAvailability($x,$y-1) && !isset($adjustedMap[$x][$y-1])){
						array_push($adjustQueue,$x.','.($y-1));
						array_push($adjustDist,$newDist);
						
					//	echo "<br/> Adjusting weight of $x,$y-1";
					}
					
					if (!in_array(array(($x+1).','.($y-1)),$adjustQueue) && !$this->map->checkMapAvailability($x+1,$y-1) && !isset($adjustedMap[$x+1][$y-1])){
						array_push($adjustQueue,($x+1).','.($y-1));
						array_push($adjustDist,$newDist);
						//echo "<br/> Adjusting weight of $x+1,$y-1";
					}
					
					if (!in_array(array(($x+1).','.$y),$adjustQueue) && !$this->map->checkMapAvailability($x+1, $y) && !isset($adjustedMap[$x+1][$y])){
						array_push($adjustQueue,($x+1).','.($y));
						array_push($adjustDist,$newDist);
						//echo "<br/> Adjusting weight of $x+1,$y";
					}
					
					if (!in_array(array(($x+1).','.$y+1),$adjustQueue) && !$this->map->checkMapAvailability($x+1, $y+1) && !isset($adjustedMap[$x+1][$y+1])){
						array_push($adjustQueue,($x+1).','.($y+1));
						array_push($adjustDist,$newDist);
						//echo "<br/> Adjusting weight of $x+1,$y+1";
					}
					
					if (!in_array(array(($x).','.$y+1),$adjustQueue) && !$this->map->checkMapAvailability($x, $y+1) && !isset($adjustedMap[$x][$y+1])){
						array_push($adjustQueue,($x).','.($y+1));
						array_push($adjustDist,$newDist);
						//echo "<br/> Adjusting weight of $x,$y+1";
					}
					
					if (!in_array(array(($x-1).','.$y+1),$adjustQueue) && !$this->map->checkMapAvailability($x-1, $y+1) && !isset($adjustedMap[$x-1][$y+1])){
						array_push($adjustQueue,($x-1).','.($y+1));
						array_push($adjustDist,$newDist);
						//echo "<br/> Adjusting weight of $x-1,$y+1";
				//		show ("6Adding ".($x-1).','.($y+1)." to the queue with dist $newDist");
					}
					
					if (!in_array(array(($x-1).','.$y),$adjustQueue) && !$this->map->checkMapAvailability($x-1, $y) &&!isset($adjustedMap[$x-1][$y])){
						array_push($adjustQueue,($x-1).','.($y));
						array_push($adjustDist,$newDist);
						//echo "<br/> Adjusting weight of $x-1,$y";
				//		show ("7Adding ".($x-1).','.($y)." to the queue with dist $newDist");
					}
					
					if (!in_array(array(($x-1).','.($y-1)),$adjustQueue) && !$this->map->checkMapAvailability($x-1, $y-1) && !isset($adjustedMap[$x-1][$y-1])){
						array_push($adjustQueue,($x-1).','.($y-1));
						array_push($adjustDist,$newDist);
						//echo "<br/> Adjusting weight of $x-1,$y-1";
				//		show ("8Adding ".($x-1).','.($y-1)." to the queue with dist $newDist");
					}
				}
			} else {
				//show ("Skipped $x,$y due to unavailability or is alrady done!");
			}
		} while(count($adjustQueue)>0);	
		
		return true;
	}
	
	function train($attMap=array()){
		$infile = $this->getTrainingFile();
		
		if (count($attMap)>0){
			$attMapOneCount = 0;
			foreach ($attMap as $a){
				$attMapOneCount += ($a==1) ? 1 :0;
			}
			
			$this->setDimensions($attMapOneCount);
			$this->initMap();
		}else {
			// Map inititation is done on the constructor
		}
		
		$dimensions = $this->getDimensions();
		
		
		$inf = fopen($infile,"r");
		$firstLine = fgets($inf);
		
		$k=0; /* delete this var */
		
		//$manupulateRecordsTrain = 100;
		//$manupulateRecordsAssign = 100;
		
		//echo "<br/>Started at ".date("H:i:s");
		$input = array();
		
		while (!feof($inf)){
			$line =  explode("\t",fgets($inf));
			$item = $line[0];
			$values = array_slice($line,1);
			if (!count($values)>0) continue;
			$tmpValues = array();
			if ((count($attMap)>0) && (count($attMap)==count($values))){
				
				
				foreach ($attMap as $k=>$v){
					if ($v==1){
						$tmpValues[]= $values[$k];
					}
				}
				
				$input[$item] = $tmpValues;
			} else {
				$input[$item] = $values;	
			}
				
		}
		fclose($inf);
		show ("Iterations = ".$this->getIterations());
		
		$initRadius = $this->getRadius();
		$totalIterations = $this->getIterations();
			
		
		for ($it=0; $it<$totalIterations; $it++){
			
			foreach ($input as $item=>$values){
				
				show ("Taking input ".implode(' , ',$values));
					
					$initLR = $this->getLrate();
				//for ($rad=$initRadius; $rad>=0; $rad--){
					$rad = ceil($initRadius * ( 1 - ( $it / $totalIterations ))) ;
					//$rad =3;
					$affecttingLr = ($initLR * ($rad) )/$initRadius;
					$bmu = $this->map->getBMU($values);
					$bmuNode = $this->map->getNodeAtLocation($bmu['x'], $bmu['y']);
					$errVector = $this->map->calcErrorVector($bmu['x'], $bmu['y'],$values);
					
					$error =$this->map->getRootSquardSumFromVector($errVector);
					if (abs($error) > 0) { 
						$bmuNode->incTotalError(abs($error));
					}
					
					
					if ($bmuNode->getTotalError() > $this->getGT()){
						$this->grow($bmu['x'], $bmu['y']);
						$bmuNode->setTotalError($bmuNode->getTotalError()*6/10);
					}
					
					$adjustedMap = array(); /* A map that will keep track of neighbours whose weights are adjusted */
					$adjustQueue = array();
					$adjustDist = array();
					
					array_push($adjustQueue,$bmu['x'].','.$bmu['y']);
					array_push($adjustDist,0);
					
					$this->adjustWeightRecursively($values,$adjustedMap,$adjustQueue,$adjustDist,$rad, $affecttingLr);
					
					
					
				//}
			}
			
		}
		
		
		
		
		for ($it=0; $it<$totalIterations; $it++){
			foreach ($input as $item=>$values){
			
			$initLR = $this->getLrate()/3;
			$initRadius = $this->getRadius();
			
			
				//for ($rad=$initRadius; $rad>=$initRadius;$rad--){
				$rad = ceil($initRadius * ( 1 - ( $it / $totalIterations )));
					
					$affecttingLr = ($initLR * ($rad))/$initRadius;
					
					$bmu = $this->map->getBMU($values);
					$adjustedMap = array();
						$adjustQueue = array();
						$adjustDist = array();
						
						array_push($adjustQueue,$bmu['x'].','.$bmu['y']);
						array_push($adjustDist,0);
					$this->adjustWeightRecursively($values,$adjustedMap,$adjustQueue,$adjustDist,$rad, $affecttingLr);
				//}
			}
			
		}
		
		
		
		$dimensions = $this->getDimensions();
		
		foreach ($input as $item=>$values){
			$bmu = $this->map->getBMU($values);
			$bmuNode = $this->map->getNodeAtLocation($bmu['x'], $bmu['y']);
			$bmuNode->addItem($item);
		}
		
	//	echo "<br/>Second part ended at ".date("H:i:s");
		
	}
	
	function drawMap($analyze=false, &$fileHandler=null){
		
		//$this->map->drawMap();
		
		if($analyze) { 
			$reportAtt = array();
			
			$report['sp'] = $this->getSpread();
			$report['lr'] = $this->getLrate();
			$report['it'] = $this->getIterations();
			$report['rad']= $this->getRadius();
			
			fwrite($fileHandler, "SP:".$report['sp'].",LR:".$report['lr'].",IT:".$report['it'].",RAD:".$report['rad']."\n");
			
			$report = $this->map->analyze_attrs($fileHandler);
			
			$goodness = $report['red']+$report['blue'];
			
			$q = "INSERT INTO `msc_gsom_galg`.`goodness` (`spread_factor`, `learning_rate`, `iterations`,`radius`,`goodness_score`) VALUES('{$this->getSpread()}','{$this->getLrate()}','{$this->getIterations()}','{$this->getRadius()}','{$goodness}');";
			mysql_query($q);
			echo "<br/>".$q."<br/>";
			
			return $report;
		} else {
			
			$this->map->drawMap();
			$this->dumpVars();
		}
	}
	
	function getTrainingFile(){
		return $this->trainingFile;
	}
	
	function setTrainingFile($trainingFile){
		$this->trainingFile = $trainingFile;
	}
	
	function setDimensionsFromFile(){
		$infile = $this->getTrainingFile();
		echo $infile;
		if (file_exists($infile)){
			$inf = fopen($infile,"r");
			$attrs = explode("\t",fgets($inf));
			$dimensions =  count($attrs) - 1;
			$this->setdimensions($dimensions);
			fclose($inf);
		} else {
			die("Input file specified <strong>$infile</strong> does not exist!");
		}
	}
	
	private function setDimensions($dim){
		$this->dimensions = $dim;
	}
	
	private function getDimensions(){
		return $this->dimensions;
	}
	
	function initMap(){
		$this->map = new Map();
		
		
		$this->map->setTrainingFile($this->getTrainingFile());
		
		$node = new Node($this->getDimensions(), array());
		$this->map->setMapNode(0,0,$node);
		
		$node = new Node($this->getDimensions(), array());
		$this->map->setMapNode(0,1,$node);
				
		$node = new Node($this->getDimensions(), array());
		$this->map->setMapNode(1,0,$node);
		
		$node = new Node($this->getDimensions(), array());
		$this->map->setMapNode(1,1,$node);
	}
	
	public function setMapThreshold($th=80){
		$this->map->setThreshold($th);
	}
	
	public function setMapColorization($c="GENERAL"){
		$this->map->setColorization($c);
	}
	
	
	public function getMap(){
		return $this->map;
	}
	
	private function setMap($map=array()){
		$this->map = $map;
	}
		
	public function setSpread($spread){
		$this->spread = $spread;
		
	}
	
	public function getSpread(){
		return $this->spread;
	}
	
	public function setRadius($radius){
		$this->radius = $radius;
	}
	
	public function getRadius(){
		return $this->radius;
	}
	
	public function setLrate($lrate){
		$this->lrate = $lrate;
	}
	
	public function getLrate(){
		return $this->lrate;
	}


	
	public function setIterations($iterations){
		$this->iterations = $iterations;
	}
	
	public function getIterations(){
		return $this->iterations;
	}
	
	private function setGT($dimensions=0,$spreadFactor=0){
		$this->gt = -1 * $dimensions * log($spreadFactor,2.71828);
	}
	
	private function getGT(){
		return $this->gt;
	}
	
	public function showMapInfo(){
		echo "In SMI";
		
		echo "<div id='dummyMapInfo' class='hidden'>";
		echo "<form method='post'>";
		echo "Dimensions: ".$this->getDimensions()."<br/>";
		echo "Spread: <input type='text' name='sp' value='".$this->getSpread()."'/><br/>";
		echo "Iterations: <input type='text' name='it' value='".$this->getIterations()."'/><br/>";
		echo "Radius: <input type='text' name='radius' value='".$this->getRadius()."'/><br/>";
		echo "Learning Rate: <input type='text' name='lr' value='".$this->getLrate()."'/><br/>";
		echo "Threshold: <input type='text' name='threshold' value='".$this->map->getThreshold()."'/><br/>";
		
		echo "Selected File: <a target='_blank' href='{$this->getTrainingFile()}'>".$this->getTrainingFile()."</a><br/>";
		
		$files = glob('_files/*.in');
		
		$existingFile = $this->getTrainingFile();
		
		echo "<fieldset>";
		echo "<legend>Training File</legend>";
			echo "<ul id='inputFileSelect'>";
			foreach ($files as $k=>$f){
				//echo "$f==$existingFile";
				if ($f==$existingFile){
					$currentFile = " checked = 'checked' ";
				} else {
					$currentFile = "";
				}
				
				$fArr = explode("/",$f);
				$f = $fArr[1];
				
				
				
				echo "<li><input id='file_type_$k' type='radio' name='inputFile' value='$f' $currentFile/><label for='file_type_$k'>$f</label></li>";
			}
			echo "</ul>";
		echo "</fieldset>";
		
		echo "<fieldset>";
		echo "<legend>Colorization method</legend>";
			if ($this->map->getColorization()=="GENERAL"){
				$currentColorizationChurn = "-";
				$currentColorizationClass = "-";
				$currentColorizationGeneral = " checked = 'checked' ";
			} else if ($this->map->getColorization()=="CHURN"){
				$currentColorizationChurn = " checked = 'checked' ";
				$currentColorizationClass = "-";
				$currentColorizationGeneral = "-";
			}  else if ($this->map->getColorization()=="CLASS"){
				$currentColorizationClass = " checked = 'checked' ";
				$currentColorizationGeneral = "-";
				$currentColorizationChurn = "-";
			}
			
			
			echo "<ul id='mapColorization'>";
				echo "<li><input id='colorization_type_churn' type='radio' name='colorization' value='CHURN' $currentColorizationChurn /><label for='colorization_type_churn'>Churn</label></li>";
				echo "<li><input id='colorization_type_general' type='radio' name='colorization' value='GENERAL' $currentColorizationGeneral /><label for='colorization_type_general'>General</label></li>";
				echo "<li><input id='colorization_type_class' type='radio' name='colorization' value='CLASS' $currentColorizationClass /><label for='colorization_type_class'>Class</label></li>";
			
			echo "</ul>";
		
		echo "</fieldset>";
		
		echo "<fieldset>";
		echo "<legend>Class Colours</legend>";
		echo "<table>";
				
		$classColours = $this->map->getClassColours();
		
		foreach ($classColours as $lab=>$cc){
			echo "<tr>
				<td style='background-color:rgb({$cc[0]},{$cc[1]},{$cc[2]});'>&nbsp;&nbsp;</td><td>$lab</td>
			</tr>";
		}
		echo "</table>";
		
		
		
		echo "</fieldset>";
		
		echo "<input type='submit' value='Submit'/>";
		echo "</form>";
		
		echo "</div>";
	}
	
	
	public function showClusterInfo(){
		echo "<div id='dummyClusterInfo' class='hidden'>";
		$map = $this->map->getMap();
		
		foreach ($map as $kx=>$mx){
			foreach ($mx as $ky=>$node){
				echo "[$kx,$ky]: ".implode(", ",$node->getItems())."<br/>";
			}
		}
		echo "</div>";
	}
	
	
} /* End of class GSOM */










