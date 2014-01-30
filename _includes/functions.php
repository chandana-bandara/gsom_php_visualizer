<?php

function getParamsFromFileName($fileName){
	$fileName = explode("/",$fileName);
	
	$fileName = $fileName[1];
	
	$fileInfo = explode("_",$fileName);
	$fileInfo[7] = explode(".",@$fileInfo[7]);
	
	foreach ($fileInfo as &$f){
		$f = str_replace("-",".",$f);
	}
	
	
	$retArr['sp'] = $fileInfo[3];
	$retArr['iterations'] = $fileInfo[4];
	$retArr['radius'] = $fileInfo[5];
	$retArr['lr'] = $fileInfo[6][0];
	return $retArr;
}

function show($msg='',$array=false){

	if (0){
		if (!$array){
			echo "<br/> $msg<br/>";
		} else {
			echo "<br/><pre>";
			print_r($msg);
			echo "</pre><br/>";
		}
	}
	
}


    //038-2235067 038-2235067 blue water


function logMsg($msg,$array=false){
	
	
	if (0){
		$of = fopen('log.log','a');
		if ($array){
			fwrite($of,implode(',',$msg)."\n");
		} else {
			fwrite($of,$msg."\n");
		}
		fclose($of);
	}

}

function getMean($inputs=array()){
	$inputLength = count($inputs);
	
	if ($inputLength>0){
		return (array_sum($inputs) / $inputLength);
	} else {
		throw new Exception("Getting the mean of an empty array");
	}
}


function getVariance($inputs=array(),$population=true){
	
	if (!empty($inputs)){
		$mean = getMean($inputs);
		
		$n = count($inputs);
		
		//if (!($n>1)) throw new Exception("Taking sample variance of a single element is not permitted, (n-1) =< 0  ");
		
		$sumSquared = 0;
		
		foreach ($inputs as $i){
			$sumSquared += pow(($mean-$i),2);
		}
		
		if ($population){
			$variance = $sumSquared / $n;
		} else {
			/* Sample varience */
			$variance = $sumSquared / ($n-1);
		}
		
		return $variance;
		
	} else {
		throw new Exception("Taking variance of an empty array");
	}
	
}

function getStandardDeviation($inputs=array(),$population=true){
	
	return sqrt(getVariance($inputs,$population));
	
}
