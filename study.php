<?php
include ('Valite.php');

function study($url, $keys){
	if(is_array($url)){
		$total=array();
		foreach($url as $pair){
			if(is_string($pair)){
				$pair=array($pair, $pair);
			}
			$total=array_merge($total,study($pair[0], $pair[1]));
		}
		return $total;
	}
	if(!isset($keys)){
		$keys=$url;
	}
	$url='study/'.$url;
	$size = getimagesize($url);
	switch ($size['mime']){
		case 'image/png':
			$res = imagecreatefrompng($url);
			break;
		case 'image/jpeg':
			$res = imagecreatefromjpeg($url);
			break;
	}
	$data = array();
	for($i=0; $i < $size[1]; ++$i)
	{
		for($j=0; $j < $size[0]; ++$j)
		{
			$rgb = imagecolorat($res,$j,$i);
			$rgbarray = imagecolorsforindex($res, $rgb);
			if($rgbarray['red'] < 125 || $rgbarray['green']<125
			|| $rgbarray['blue'] < 125)
			{
				$data[$i][$j]=1;
			}else{
				$data[$i][$j]=0;
			}
		}
	}

	// 去空白
	// 1.横向
	$op=zero($data, HSPACE);

	// 2.竖向, 并生成关键字序列
	$data=$op;
	$op=array();
	$len;
	$lens=array();
	foreach(trans($data) as $row){
		$sum=array_sum($row);
		if($sum>VSPACE){
			$op[]=$row;
		}else{
			$len=count($op);
			// todo
			// 关键字序列超过最大自动分割

			if($len>0){
				$lens[]=$len;
			}
		}
	}
	$lens=array_values(array_unique($lens));
	$lens[]=count($op); // 消除图片右端无空的bug

	$pos=0;
	$els=array();
	$el=array();
	foreach($op as $index => $row){
		if($index<$lens[$pos]){
			$el[]=$row;
		}else{
			$els[]=$el;
			$el=array($row);
			$pos++;
		}
	}
	$els[]=$el;

	//转置,去全零
	$ret=array();
	foreach($els as $key=>$el){
		$val=zero(trans($el));
		$els[$key]=$val;
		$ret[getKey($val)]=$keys[$key];
	}

	return $ret;
}


$url=array(
	array('clear4.png','H5S7'),
	array('clear.png','3485'),
	'clear.png'
);

$url=getUrl('study');

$newKey= study($url,'');
$oldKeyStr=file_get_contents('key');
$oldKey=json_decode($oldKeyStr, true);
if($oldKey){
	$newKey=array_merge($oldKey, $newKey);
}
$newKeyStr=json_encode($newKey);

file_put_contents('key',$newKeyStr);

echo 'old:'.strlen($oldKeyStr).'<br>now:'.strlen($newKeyStr);