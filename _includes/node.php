<?php 
class Node{
	
	private $weights;
	private $items;
	private $totalError;
	private $id;
	private $attribSDs;
	private $colour;
	
	
	function __construct($dimensions,$weights=array(),$id=0){
		$this->setId($id);
		
		$this->items =array();
		$this->weights = array();
		$this->setWeights($dimensions, $weights);
		
	} /* End of function __construct */


	function setColour($c=""){
		$this->colour = $c;
	}
	
	function getColour(){
		return $this->colour;
	}
	
	function setSD($attrSD=array()){
		$this->attribSDs = $attrSD;
	}	
	
	function getSD(){
		return $this->attribSDs;
	}
	
	public function setId($id=0){
		$this->id = $id;
	}
	
	public function getId(){
		return $this->id;
	}
	
	function addItem($item){
		$this->items[]=$item;
	}
	function getItems(){
		return $this->items;
	}
	
	public function setWeights($d,$w){
		
		$this->weights = array();
		
		for ($i=0; $i<$d; $i++){
			$this->weights[] = (isset($w[$i])) ? $w[$i] : 0.5;
		}
	}
	
	public function getWeights(){
		return $this->weights;
	}
	
	public function getTotalError(){
		return $this->totalError;
	}
	
	public function setTotalError($te){
		$this->totalError = $te;
	}
	
	public function incTotalError($incErr){
		$this->totalError += $incErr;
	}
	
} /* End of class Node */
	
	
