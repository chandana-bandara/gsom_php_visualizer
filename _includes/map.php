<?php 

class Map{
	private $map = array();
	private $mapping = array(); /* Mapping for x,y and weight. A duplicate of map but in non-oop way */
	private $itemMapping = array();
	private $th; /* Threshold for showing map whether churning node or non-churning node */
	private $colorization; /* Either GENERAL or CHURN */
	private $trainingFile = "";
	private $standardDeviations=array();
	private $classColours = array();
	
	private $statistics =array();
	
	function __construct($th=80, $colorization="GENERAL"){
		
		//echo "<div id='clusterInfo' class='ui-layout-east'></div>";
		//echo "<div id='mapInfo' class='ui-layout-west'></div>";
		
		$this->setThreshold($th);
		$this->setColorization($colorization);
		
	}
	
	public function setClassColors($cc=array()){
		$this->classColours = $cc;
	}
	
	public function getClassColours(){
		return $this->classColours;
	}

	public function setTrainingFile($file=""){
		$this->trainingFile = $file;
	}
	
	public function getTrainingFile(){
		return $this->trainingFile;
	}
	
	private function setSD($sdArr){
		$this->standardDeviations = $sdArr;
	}
	
	public function getSD(){
		return $this->standardDeviations;
	}
	
	public function setColorization($colorization="GENERAL"){
		if (!in_array($colorization,array("GENERAL","CHURN","CLASS"))) throw new Exception("Invalid Colorization. It should be either GENERAL, CHURN OR CLASS, $colorization given");
		
		$this->colorization = $colorization;
	}
	
	public function getColorization(){
		return $this->colorization;
	}
	
	public function setThreshold($th){
		if (($th<=0) || ($th>=100)){
			throw new Exception('Please define the threshold T s.t. 0 < T < 100 !' );
		} else {
			$this->th = $th;	
		}
	}
	
	public function getThreshold(){
		return $this->th;
	}
	
	public function getNodeAtLocation($x,$y){
		//show("Getting node at Location $x,$y");
		return @$this->map[$x][$y];
	}
	
	/* Deals with $itemMapping */
	function addItemToMap($x,$y, $item){
		
	}
	
	function getMap(){
		return $this->map;
	}
	
	function getMapping(){
		return $this->mapping;
	}
	
	function averageWeightVectors($v1, $v2){
		$resultVector = array();
		if (count($v1)==count($v2)){
			foreach ($v1 as $k=>$v){
				$resultVector[$k] = ($v + $v2[$k])/2;
			}
			
		} else {
			throw new Exception('Averaging two vectors with different dimensions!');
		}
		
		return $resultVector;
	}
	
	function addWeightVectors($v1, $v2){
		$resultVector = array();
		if (count($v1)==count($v2)){
			foreach ($v1 as $k=>$v){
				$resultVector[$k] = $v + $v2[$k];
			}
			
		} else {
			throw new Exception('Adding two vectors with different dimensions!');
		}
		
		return $resultVector;
	}
	
	function subtractWeightVectors($v1, $v2){
		$resultVector = array();
		if (count($v1)==count($v2)){
			foreach ($v1 as $k=>$v){
				$resultVector[$k] = $v - $v2[$k];
			}
			
		} else {
			show ("errournous vectors ");
			//show ($v1,true);
			//show ($v2, true);
			
			
			throw new Exception('Subtracting two vectors with different dimensions!');
		}
		
		return $resultVector;
	}
	
	function showMapping(){
		echo "<pre>Mapping";
		print_r($this->mapping);
		echo "</pre>";
	}
	
	function getDimentions(){
		print_r($this->map);
	}
	
	function analyze_attrs($fileHandler=null){
		
		
	
		$minX = $this->mapping[0]['x'];
		$minY = $this->mapping[0]['y'];
		$maxX = $this->mapping[0]['x'];
		$maxY = $this->mapping[0]['y'];
		$minW = $this->mapping[0]['weights'][0];
		$maxW = $this->mapping[0]['weights'][0];
		
		foreach ($this->mapping as $m){
			if ($m['x']<$minX) $minX = $m['x'];
			if ($m['x']>$maxX) $maxX = $m['x'];
			if ($m['y']<$minY) $minY = $m['y'];
			if ($m['y']>$maxY) $maxY = $m['y'];
			
			if ($m['weights'][0] < $minW) $minW = $m['weights'][0];
			if ($m['weights'][0] > $maxW) $maxW = $m['weights'][0];
		}
		
		$colorization = $this->getColorization();
		
		echo "COLORIZATION = ".$colorization;
		$cusFile = fopen($this->getTrainingFile(),'r');
		
		if ($colorization == "CLASS"){ 
			$labFile = fopen('_files/class.labels','r');
		} else if ($colorization==""){
			$labFile = fopen('_files/bal_label.labels','r');
		} else {
		
		}
		$labFile = fopen('_files/bal_label.labels','r');
		
		
		$cus = array();
		
		
		
		while (!feof($cusFile)){
			
			$cusLine = fgets($cusFile);
			$cusArr = explode("\t",$cusLine);
			
			$labLine = fgets($labFile);
			
			$cus[$cusArr[0]] = $labLine; /* This array is used in colouring the nodes  */
			echo $labLine;
			$cusAttr[$cusArr[0]] = array_slice($cusArr,1);
		}
		
		
		
		$drawMap = array();
		
		foreach ($this->mapping as $m){
			$curW = $m['weights'][0];
			$drawMap[$m['x']][$m['y']] = number_format((($curW - $minW+1)/($maxW-$minW+1)) * 235 , 0 , "." , "," );
		}
		
		$minItems = -1;
		$maxItems = -1;
		
		$rangeX = $maxX - $minX;
		$rangeY = $maxY - $minY;
		
		$blockWidth = number_format((400/$rangeX),0,"","");
		$blockHeight = number_format((400/$rangeY),0,"","");
		
		if ($blockHeight>$blockWidth){
			$blockHeight = $blockWidth;
		} else {
			$blockWidth = $blockHeight;
		}
		
		
		for ($y=$minY; $y<=$maxY; $y++){
			for ($x=$minX; $x<=$maxX; $x++){
				
				if (is_object($node = @$this->map[$x][$y])){
					if ($minItems==-1) $minItems = count($node->getItems());
					if ($maxItems==-1) $maxItems = count($node->getItems());
					
					$nodeItems = @$node->getItems();
					$count = @count($nodeItems);
					
					if ($count<$minItems) $minItems = $count;
					if ($count>$maxItems) $maxItems = $count;
				} else {
					
				}
			}
		}
		
		$range = $maxItems - $minItems+1;
		
	//	echo "Min item count : ".$minItems." Max item count ".$maxItems." range =".$range;
		//echo "<div style='width:0px; height:0px; float:left; overflow:visible;position:absolute; top:0px; left:0px;'><div id='nodeInformer' style=''>aaaa</div></div>";
		
		$blueCount = 0;
		$redCount = 0;
		$totalNodes = 0;
		$minCusCount = 10;
		
		$goodness = 0;
		
			for ($y=$minY; $y<=$maxY; $y++){
				for ($x=$minX; $x<=$maxX; $x++){
					
					if (isset($this->map[$x][$y]) && is_object($node  = $this->map[$x][$y]) && (count($node->getItems())>0)) {
						
						$churnCount = 0;
						$nonChurnCount =0;
						
						if (is_object($node)){
							$totalNodes++;
							
							$nodeItems = @$node->getItems();
							$curItemCount = @count($nodeItems);

							
							
							switch ($colorization){
								
								case "CHURN": 
									try {
										/* Start of section to define color for churn specific */
										$isChurn = true; //Whether the node is only with churners
										$isNonChurn = true; // Whether the node is only with Non-churners
										
										foreach ($nodeItems as $ni){
											if (!isset($cus[$ni])) throw new Exception("Churn labels are not available for this input dataset.");
											
											if ($cus[$ni]==1){
												$isChurn = false;
												$nonChurnCount++;
											}
											
											if ($cus[$ni]==-1){
												$isNonChurn = false;
												$churnCount++;
											}
										}
										
										$threshold = $this->getThreshold();
										$totalCusts = $churnCount + $nonChurnCount;
										
										if ((($churnCount /$totalCusts) > ($threshold/100) ) && ($totalCusts >= 10))  {
											$color = "red";
											$redCount++;
										} elseif (($nonChurnCount/$totalCusts > ($threshold/100) ) && ($totalCusts >= 10 )){
											$color = "blue";
											if ($totalCusts>=$minCusCount) $blueCount++;
										} else {
											$color = "gray";
										}
										/* End of section to define color for churn specific */	
										} catch (Exception $e){
											echo $e->getMessage();
										}
									break;
								case "CLASS":
									
								break;
								
								default : 
									/* Start of section to define color for general use */

									$color = number_format((($curItemCount - $minItems) / $range) * 265,0,".","");
									
									$color = 255-$color;
									$color  ="rgb($color, $color, $color)";
									
									/* End of section to define color for general use */
									break;
								
							}

							/* Calculating the Standard deviation of the attributes */
							
							$attributeSD = array();
							$tmpAttrArray = array();
							
							foreach ($nodeItems as $item){
								$attIndex = 0;
								foreach ($cusAttr[$item] as $att){
									$tmpAttrArray[$attIndex++][] = $att;
								}
							}
							
							foreach ($tmpAttrArray as $k=>$attArr){
								$attributeSD[$k] = getStandardDeviation($attArr);
							}
							
							$this->setSD($attributeSD);
							
							//fwrite($fileHandler,implode());
							
							if ($color=="blue"){
								fwrite($fileHandler, "BLUE::".implode(",",$attributeSD)."\n");
							} elseif ($color=="red"){
								fwrite($fileHandler, "RED::".implode(",",$attributeSD)."\n");
							} else {
								
							}
							
							
							
							
						} else {
							
							
							
							//$color = 0;
						}
						
						
						
						$node = $this->getNodeAtLocation($x,$y);
						
						switch ($colorization){
							case "CHURN":
								echo count($node->getItems()); 
							break;
							default:
								echo count($node->getItems()); 
						}
						
				
						
					
						
					} else {
						
						
					}
				}

			}
			
	
		$goodNodeRatio = number_format(($blueCount + $redCount)/$totalNodes,2,".","");
		$this->statistics['blues'] = $blueCount;
		$this->statistics['red'] = $redCount;
		$this->statistics['all'] = $totalNodes;
		$this->statistics['standaraDeviations'] = $attributeSD;
	
	//echo "<br/> ($blueCount + $redCount)/$totalNodes ($goodNodeRatio) >= $goodCoverageRatio <br/>";
	echo "<br/> ";
		
		$retArr = array(
			'blue' => $blueCount,
			'red' => $redCount,
			'total' => $totalNodes,
			'minCusCount' => $minCusCount,
			'sds' => implode(",",$attributeSD)
		);
		
		
		return $retArr;
		
	}
	
	
	/* Function getFitness
	 * This function MUST be run after running drawMap. Because this function depends on the node colours 
	 * which are being populated from the drawmap function
	 * 
	 * returns map score
	 */ 
	 
	function getFitness(){
		
		$score = 0;
		
		$minX = $this->mapping[0]['x'];
		$minY = $this->mapping[0]['y'];
		$maxX = $this->mapping[0]['x'];
		$maxY = $this->mapping[0]['y'];
		
		foreach ($this->mapping as $m){
			if ($m['x']<$minX) $minX = $m['x'];
			if ($m['x']>$maxX) $maxX = $m['x'];
			if ($m['y']<$minY) $minY = $m['y'];
			if ($m['y']>$maxY) $maxY = $m['y'];
		}
		
		
		for ($y=$minY; $y<=$maxY; $y++){
			for ($x=$minX; $x<=$maxX; $x++){
			
				if (isset($this->map[$x][$y]) && is_object($node  = $this->map[$x][$y]) && (($itemCount =count($node->getItems()))>0)) {
					$myColour = $node->getColour();
					if ($myColour=="blue" || $myColour=="red"){
						$score += ($itemCount * $itemCount);
						
						if (isset($this->map[$x][$y-1]) && is_object($topNode  = $this->map[$x][$y-1]) && ($myColour==$topNode->getColour())) $score+= ($itemCount * count($topNode->getItems()));
						if (isset($this->map[$x+1][$y]) && is_object($rightNode  = $this->map[$x+1][$y]) && ($myColour==$rightNode->getColour())) $score+=($itemCount * count($rightNode->getItems()));;
						if (isset($this->map[$x][$y+1]) && is_object($bottomNode  = $this->map[$x][$y+1]) && ($myColour==$bottomNode->getColour())) $score+=($itemCount * count($bottomNode->getItems()));
						if (isset($this->map[$x-1][$y]) && is_object($leftNode  = $this->map[$x-1][$y]) && ($myColour==$leftNode->getColour())) $score+=($itemCount * count($leftNode->getItems()));
					}
				}
			}
		}
		return $score;
	}
	
	function drawMap($fileHandler=null){
		
		$redNodeCount = 0;
		$blueNodeCount =0;
		$totalNodeCount = 0;
		
		$minX = $this->mapping[0]['x'];
		$minY = $this->mapping[0]['y'];
		$maxX = $this->mapping[0]['x'];
		$maxY = $this->mapping[0]['y'];
		$minW = $this->mapping[0]['weights'][0];
		$maxW = $this->mapping[0]['weights'][0];
		
		foreach ($this->mapping as $m){
			if ($m['x']<$minX) $minX = $m['x'];
			if ($m['x']>$maxX) $maxX = $m['x'];
			if ($m['y']<$minY) $minY = $m['y'];
			if ($m['y']>$maxY) $maxY = $m['y'];
			
			if ($m['weights'][0] < $minW) $minW = $m['weights'][0];
			if ($m['weights'][0] > $maxW) $maxW = $m['weights'][0];
		}
		
		$cusFile = fopen($this->getTrainingFile(),'r');
		
		$colorization = $this->getColorization();
		
		echo "COLORIZATION = ".$colorization;
		$cusFile = fopen($this->getTrainingFile(),'r');
		
		if ($colorization == "CLASS"){ 
			$labFile = fopen($this->getTrainingFile().".label",'r');
			
			
			$colourbank = array(
				array(198,27,27),
				array(27,104,198),
				array(102,212,91),
				array(211,91,212),
				array(249,193,60),
				array(60,238,249),
				array(90,20,87),
				array(100,2,155),
				array(50,145,200)
				
			);
			
			$classColour = array();
			$classIndex = 0;
			
			
		} else if ($colorization==""){
			$labFile = fopen('_files/bal_label.labels','r');
		} else {
		
		}
		
		$cus = array();
		
		
		
		
		while (!feof($cusFile)){
			
			$cusLine = fgets($cusFile);
			$cusArr = explode("\t",$cusLine);
			
			
			$labLine = fgets($labFile);
			
			$labLine = str_replace("\n","",$labLine);
			$labLine = str_replace("\r","",$labLine);
			$labLine = str_replace(" ","",$labLine);
					
					
			if ($colorization == "CLASS"){
				if (!array_key_exists($labLine,$classColour)){
					$tmpColour = array();
					
					
					if (isset($colourbank[$classIndex])){
						$tmpColour = $colourbank[$classIndex++];
					} else {
						echo "<br/> Not having colour for index $classIndex";
						$classIndex++;
						$tmpColour = array(rand(0,255), rand(0,255), rand(0,255));
					}
					
					
					
					if ($labLine != "") 
					$classColour[$labLine] = $tmpColour;
					ksort($classColour);
					$this->setClassColors($classColour);
				}
			}
			
			$cus[$cusArr[0]] = $labLine; /* This array is used in colouring the nodes  */
			$cusAttr[$cusArr[0]] = array_slice($cusArr,1);
		}
		
	
	echo "<pre>";
	print_r($classColour);
	echo "</pre>";
		//
		
		
		$drawMap = array();
		
		foreach ($this->mapping as $m){
			$curW = $m['weights'][0];
			$drawMap[$m['x']][$m['y']] = number_format((($curW - $minW+1)/($maxW-$minW+1)) * 235 , 0 , "." , "," );
		}
		
		$minItems = -1;
		$maxItems = -1;
		
		$rangeX = $maxX - $minX;
		$rangeY = $maxY - $minY;
		
		$blockWidth = number_format((400/$rangeX),0,"","");
		$blockHeight = number_format((400/$rangeY),0,"","");
		
		if ($blockHeight>$blockWidth){
			$blockHeight = $blockWidth;
		} else {
			$blockWidth = $blockHeight;
		}
		
		echo "
			<style type='text/css'>
				.mapBlockNoItems {
					width:{$blockWidth}px !important;
					height:{$blockHeight}px !important;
					font-size:{$blockWidth}px !important;
				}
				.mapBlock {
					width:{$blockWidth}px !important;
					height:{$blockHeight}px !important;
					font-size:{$blockWidth}px !important;
				}
				.mapBlockEmpty{
					width:{$blockWidth}px !important;
					height:{$blockHeight}px !important;
					font-size:{$blockWidth}px !important;
				}
			</style>
		";
		for ($y=$minY; $y<=$maxY; $y++){
			for ($x=$minX; $x<=$maxX; $x++){
				
				if (is_object($node = @$this->map[$x][$y])){
					if ($minItems==-1) $minItems = count($node->getItems());
					if ($maxItems==-1) $maxItems = count($node->getItems());
					
					$nodeItems = @$node->getItems();
					$count = @count($nodeItems);
					
					if ($count<$minItems) $minItems = $count;
					if ($count>$maxItems) $maxItems = $count;
				} else {
					
				}
			}
		}
		
		$range = $maxItems - $minItems+1;
		
	//	echo "Min item count : ".$minItems." Max item count ".$maxItems." range =".$range;
		//echo "<div style='width:0px; height:0px; float:left; overflow:visible;position:absolute; top:0px; left:0px;'><div id='nodeInformer' style=''>aaaa</div></div>";
		
		echo "<div id='mapContainer' class='ui-layout-center'>";
			echo "<div id='mapUI'>";
			for ($y=$minY; $y<=$maxY; $y++){
				for ($x=$minX; $x<=$maxX; $x++){
					
					if (isset($this->map[$x][$y]) && is_object($node  = $this->map[$x][$y])) {
						if (count($node->getItems())>0){
							$churnCount = 0;
							$nonChurnCount =0;
							
							if (is_object($node)){
								
								
								$nodeItems = @$node->getItems();
								$curItemCount = @count($nodeItems);

								$colorization = $this->getColorization();
								
								switch ($colorization){
									
									case "CHURN": 
										try {
											/* Start of section to define color for churn specific */
											$isChurn = true; //Whether the node is only with churners
											$isNonChurn = true; // Whether the node is only with Non-churners
											
											foreach ($nodeItems as $ni){
												if (!isset($cus[$ni])) throw new Exception("Churn labels are not available for this input dataset.");
												
												if ($cus[$ni]==1){
													$isChurn = false;
													$nonChurnCount++;
												}
												
												if ($cus[$ni]==-1){
													$isNonChurn = false;
													$churnCount++;
												}
											}
											
											$threshold = $this->getThreshold();
											
											/* if ($churnCount > ($nonChurnCount * ($threshold)/(100-$threshold))){
												$color = "red";
											} elseif ($churnCount < ($nonChurnCount*(100-$threshold)/($threshold))){
												$color = "blue";
											} else {
												$color = "gray";
											} */
											
											$totalCusts = $churnCount + $nonChurnCount;
											$totalNodeCount++;
											if ((($churnCount /$totalCusts) > ($threshold/100) )&& ($totalCusts >=1 )){
												$redNodeCount++;
												$color = "red";
												
											} elseif (($nonChurnCount/$totalCusts > ($threshold/100) ) && ($totalCusts>=1)){
												$blueNodeCount++;
												$color = "blue";
												
											} else {
												$color = "gray";
											}
											
											$node->setColour($color);
											
											/* End of section to define color for churn specific */	
											} catch (Exception $e){
												echo $e->getMessage();
											}
										break;
									case "CLASS":
										try {
											/* Start of section to define color for churn specific */
											
											$color = array(0,0,0);
											$r = 0;
											$g = 0;
											$b = 0;
											//
											$totalItems = count($nodeItems);
											//
											foreach ($nodeItems as $ni){
												if (!isset($cus[$ni])) throw new Exception("Churn labels are not available for this input dataset.");
												//echo $cus[$ni];
												$r += floor(($classColour[$cus[$ni]][0])/$totalItems);
												$g += floor(($classColour[$cus[$ni]][1])/$totalItems);
												$b += floor(($classColour[$cus[$ni]][2])/$totalItems);
												//
											}
											//
											$color = "rgb($r,$g,$b)";
											
											//echo $colour;
											
											
											$node->setColour($color);
											
										} catch (Exception $e){
											echo $e->getMessage();
										}
									break;
									default : 
										/* Start of section to define color for general use */

										$color = number_format((($curItemCount - $minItems) / $range) * 265,0,".","");
										
										$color = 255-$color;
										$color  ="rgb($color, $color, $color)";
										
										/* End of section to define color for general use */
										break;
									
								}




		
								/* Calculating the Standard deviation of the attributes */
								
								$attributeSD = array();
								$tmpAttrArray = array();
								
								foreach ($nodeItems as $item){
									$attIndex = 0;
									foreach ($cusAttr[$item] as $att){
										$tmpAttrArray[$attIndex++][] = $att;
									}
								}
								
								foreach ($tmpAttrArray as $k=>$attArr){
									$attributeSD[$k] = getStandardDeviation($attArr);
								}
								
								$this->setSD($attributeSD);
								
								$this->statistics['redNodeCount'] = $redNodeCount;
								$this->statistics['blueNodeCount'] = $blueNodeCount;
								$this->statistics['totalNodeCount'] = $totalNodeCount;
							
								
							
							} else {
								//$color = 0;
							}
							
							echo "<span id='node-$x-$y' class='mapNode'><div id='mapBlockFor{$x}{$y}' class='mapBlock' style='background:".$color.";'>";
							
							$node = $this->getNodeAtLocation($x,$y);
							
							switch ($colorization){
								case "CHURN":
									echo count($node->getItems()); 
								break;
								default:
									echo count($node->getItems()); 
							}
							echo "</div></span>";
							echo "<div class='nodeItems'>";
							if (is_object($node)){
									
									echo ("<br/>Items : ".implode(", ",$node->getItems()));
									echo ("<br/>Weights: ".implode(", ",$node->getWeights()));
									echo ("<pre>Standard Deviation of each attribute<br/>");
										//print_r($attributeSD);
										foreach ($attributeSD as $a){
											echo "<br/>".number_format($a,3,".","");
										}
									echo ("</pre>");
									
									
									echo "<div class='forToolTip'>";
									echo ("<span onclick='$(\"#mapBlockFor{$x}{$y}\").btOff();' class='closeToolTip'>X</span>");
										switch ($colorization){
												case "CHURN":
													
													echo ("<br/>Churners: $churnCount, Non-Churners: $nonChurnCount");
													$churnRatio = "N/A";
													if ($nonChurnCount >0){
														$churnRatio = number_format($churnCount / $nonChurnCount,2,".",",");
													}
													echo ("<br/>Churners/Non-churners : $churnRatio");
												break;
												
												default:
													
													
													echo ("<br/>Total Items: ". count($node->getItems()));
										}
									echo "</div>"; /* End of tooltip string */
							} else {
								echo "-";
							}	
								echo "</div>
							";
						} else {
							/* A node but with empty items*/
							$color = "white";
							echo "<span id='node-$x-$y' class='mapNode'><div id='mapBlockFor{$x}{$y}' class='mapBlockNoItems' style='background:".$color.";'>";
								echo ".";
							echo "</div></span>";
							
						}
						
						
					} else {
						
						echo "<span id='node-$x-$y' class='mapNode'><div class='mapBlockEmpty'>";
						
						$node = $this->getNodeAtLocation($x,$y);

						echo '.'; 
						
						echo "</div></span>";
						echo "<div class='nodeItems'>";
							if (is_object($node)){
								echo "Items : ".implode(", ",$node->getItems());
								//echo "<br/>Weights: ".implode(", ",$node->getWeights());
							} else {
								echo "-";
							}	
							echo "</div>
						";
					}
				}
				echo "<br/>";
			}
			echo "</div>";
		echo "</div>";
		
		echo "";
	}
	
	function setMappingError($x, $y,$totalError){
		
		foreach ($this->mapping as &$m){
			if (($m['x']==$x) && ($m['y']==$y)) {
				$m['totalError'] = $totalError;
					return true;
			}
		}
		
		return false;
	}
	
	function setMapNode($x,$y,&$node){
		
		if ($this->checkMapAvailability($x,$y)) {
			$this->map[$x][$y] = $node;
			$this->mapping[]= array('x'=>$x, 'y'=>$y, 'weights'=>$node->getWeights());
		} else {
			echo "Error occurred trying to set a node ". $node . " for the location $x and $y. Another node already set";
		}
	}
	
	function checkMapAvailability($x, $y){
		return (isset($this->map[$x][$y])) ? false : true;
	}
	
	function setNodeWeight($x, $y, $weights){
		
		$node = $this->map[$x][$y];
		
		$node->setWeights(count($weights), $weights);
		$foundNode = false;
		
		foreach ($this->mapping as &$m){
			if (($m['x']==$x) && ($m['y']==$y)) { 
				$m['weights'] = $weights;
				$foundNode = true;
				break;
			}
		}
		
		if (!$foundNode){
			$this->mapping[] =array(
				'x' => $x,
				'y' => $y,
				'weights' => $weights
			);
		}
		
	}
	
	function getBMU($targetWeights=array()){
		
		//show("Target weights =".implode(",",$targetWeights));
		
		$tmpW = 0;
		
		$firstItem = $this->map[0][0]->getWeights();
		$tmpNodeRef = null;
		
		$diff = $this->getEucDis($firstItem, $targetWeights);
		
		//print_r($this->mapping);
		//show ("Getting Euc Dis: ");
		
		//show ($this->mapping,true);
		
		foreach ($this->mapping as $m){
			if ($tmpNodeRef==null) $tmpNodeRef =$m;
			$tmpDiff = $this->getEucDis($m['weights'], $targetWeights);

		//	echo $tmpDiff.',';
			
			if ($tmpDiff < $diff) { 
				$diff = $tmpDiff;
				$tmpNodeRef = $m;
			}
		}
		
		return $tmpNodeRef;
		
	}
	
	public function getEucDis($source, $target){
		$d = count($source);
		$total = 0;
		$sqrTotal = 0;
		
		if ($d==count($target)){
			
			for ($i=0; $i<$d; $i++){
				$sqrTotal += pow(($source[$i]-$target[$i]),2);
			}
			
			$dist = $sqrTotal;
			
		} else {
			die('Error getting euclidean distance for arrays '.implode(",",$source).' and '.implode(",",$target));
		}
		
		return $dist;
	}
	
	public function calcErrorVector($sourceX, $sourceY, $targetVec=array()){
		$errorVector = array();
		
		if (!$this->checkMapAvailability($sourceX, $sourceY)){
			$sourceNode = $this->map[$sourceX][$sourceY]->getWeights();
			
			if (count($sourceNode)==count($targetVec)){
				for ($i=0; $i<count($sourceNode); $i++){
					$errorVector[$i] = $sourceNode[$i] - $targetVec[$i];
				}
			} else {
				throw new Exception('Error calculating Error vectors. Source and Target vectors are not in the same dimensions');
			}
		} else {
			throw new Exception("Error calculating error vector for Non-existing source node X: $sourceX, Y: $sourceY.");
		}
		
		return $errorVector;
	}
	
	public function getRootSquardSumFromVector($vector=array()){
		$accVector = 0;
		//print_r($vector);
		if (!empty($vector)){
			foreach ($vector as $e){
				$accVector += pow($e,2);
			}			
			$accVector = sqrt($accVector);
		} else {
			throw new Exception("Empty vector found for accumulated Root Mean Squard calculation");
		}
		return $accVector;
	}
	
	
}
