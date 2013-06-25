<?php

//define('WORD_WIDTH',9);
//define('WORD_HIGHT',13);
//define('OFFSET_X',7);
//define('OFFSET_Y',3);
//define('WORD_SPACING',4);

define('WORD_WIDTH',9); // 不变了
define('WORD_HIGHT',13); // 不变了
define('OFFSET_X',0);
define('OFFSET_Y',0);
define('WORD_SPACING',0);

define('HSPACE',2);
define('VSPACE',0);
define('STRICT',0);

define('MAX_WIDTH',17);
define('MIN_WIDTH',8);

function trans($arr){
	$ret=array();
	if(!isset($arr[0])) return $ret;
	for($i=0; $i<count($arr[0]); $i++){
		$ret[]=array();
	}
	foreach($arr as $rowIndex => $row){
		foreach($row as $cellIndex => $cell){
			$ret[$cellIndex][$rowIndex]=$cell;
		}
	}
	return $ret;
}

function array_xsum($arr){
	$sum=0;
	foreach($arr as $num){
		if($num!==0){
			$sum++;
		}
	}
	return $sum;
}
function zero($arr,$limit=0){
	$op=array();
	foreach($arr as $row){
		$sum=array_xsum($row);
		if($sum>$limit){
			$op[]=$row;
		}
	}
	return $op;
}
function draw($el){
	$ret='';
	$ret.='<br>';
	foreach($el as $row){
		foreach($row as $cell){
			if($cell !==0){
				$ret.='1';
			}else{
				$ret.='0';
			}
		}
		$ret.='<br>';
	}
	$ret.='<br>';
	echo $ret;
}
function drawArr($arr){
	foreach($arr as $el){
		draw($el);
	}
}
function getKey($el){
	$ret='';
	foreach($el as $row){
		foreach($row as $cell){
			if($cell !==0){
				$ret.='1';
			}else{
				$ret.='0';
			}
		}
	}
	return $ret;
}
function getColor($res, $xy){
	$xy=explode('&',$xy);
	$rgb = imagecolorat($res,$xy[0],$xy[1]);
	$rgbarray = imagecolorsforindex($res, $rgb);
	return $rgbarray;
}
function rgb2hsl($rgb){
	$rgb=array_values($rgb);
	// Where RGB values = 0 ÷ 255.
	$var_R = $rgb[0] / 255;
	$var_G = $rgb[1] / 255;
	$var_B = $rgb[2] / 255;

	// Min. value of RGB
	$var_Min = min($var_R, $var_G, $var_B);
	// Max. value of RGB
	$var_Max = max($var_R, $var_G, $var_B);
	// Delta RGB value
	$del_Max = $var_Max - $var_Min;

	$L = ($var_Max + $var_Min) / 2;

	if ( $del_Max == 0 ) {
		// This is a gray, no chroma...
		// HSL results = 0 ÷ 1
		$H = 0;
		$S = 0;
	} else {
		// Chromatic data...
		if ($L < 0.5) {
			$S = $del_Max / ($var_Max + $var_Min);
		} else {
			$S = $del_Max / ( 2 - $var_Max - $var_Min );
		}

		$del_R = ((($var_Max - $var_R) / 6) + ($del_Max / 2)) / $del_Max;
		$del_G = ((($var_Max - $var_G) / 6) + ($del_Max / 2)) / $del_Max;
		$del_B = ((($var_Max - $var_B) / 6) + ($del_Max / 2)) / $del_Max;

		if ($var_R == $var_Max) {
			$H = $del_B - $del_G;
		} else if ($var_G == $var_Max) {
			$H = ( 1 / 3 ) + $del_R - $del_B;
		} else if ($var_B == $var_Max) {
			$H = ( 2 / 3 ) + $del_G - $del_R;
		}

		if ($H < 0) {
			$H += 1;
		}
		if ($H > 1) {
			$H -= 1;
		}
	}

	return array($H, $S, $L);
}

class Valite{
	public function setImage($Image)
	{
		$this->ImagePath = $Image;
	}
	public function getData()
	{
		return $data;
	}
	public function getResult()
	{
		return $DataArray;
	}
	public function getHec()
	{
		$size = getimagesize($this->ImagePath);
		switch ($size['mime']){
			case 'image/png':
				$res = imagecreatefrompng($this->ImagePath);
				break;
			case 'image/jpeg':
				$res = imagecreatefromjpeg($this->ImagePath);
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
					|| $rgbarray['blue'] < 125){
					$data[$i][$j]="$j&$i";
				}else{
					$data[$i][$j]=0;
				}
			}
		}
		$this->o=$data;
		// todo
		// 逆反

		// 去空白
		// 1.横向
		$op=zero($data, HSPACE);

		// 2.竖向, 并生成关键字序列
		$data=$op;
		$op=array();
		$len;
		$lens=array();
		foreach(trans($data) as $row){
			$sum=array_xsum($row);
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

		//优化els
		$els_op=array();
		foreach($els as $el){
			$len=count($el);
			if($len>MAX_WIDTH){
				// todo 分割
				$ave=array();
				$lastH=0;
				$sumDeltaH=0;
				$c=0;
				$part=array();
				$i=0;
				foreach($el as $row){//计算每一行的平均rgb
					$c++;
					$i++;
					$color_ave=array(
						'red'=>0,
						'green'=>0,
						'blue'=>0
					);
					$count=0;
					foreach($row as $cell){
						if($cell!==0){
							$color=getColor($res, $cell);
							$color_ave['red']+=$color['red'];
							$color_ave['green']+=$color['green'];
							$color_ave['blue']+=$color['blue'];
							$count++;
						}
					}
					$color_ave['red']/=$count;
					$color_ave['green']/=$count;
					$color_ave['blue']/=$count;
//					$a=($color_ave['red']-$lastH['red'])*($color_ave['red']-$lastH['red'])				// 这个kpi 太不靠谱
//						+($color_ave['green']-$lastH['green'])*($color_ave['green']-$lastH['green'])
//						+($color_ave['blue']-$lastH['blue'])*($color_ave['blue']-$lastH['blue']);
					$h=rgb2hsl($color_ave);
					$h=$h[0];
					echo "$i: h: $h<br>";// just test
					
					
					if($c>4 && abs($h-$lastH)>5*$sumDeltaH/($c-1) ){ // todo define 大5倍, 0.1
						if(abs($h-$lastH)/$lastH>0.1){
							$els_op[]=trans($part);
							$part=array($row);// 初始化
							$c=0;
							$sumDeltaH=0;
						}else{
							$part[]=$row;
						}
					}else{
						$part[]=$row;
					}

					$ave[]=$color_ave;

					if($c!==1){
						$sumDeltaH+=abs($h-$lastH);

						// test
						$x=abs($h-$lastH);
						$y=$sumDeltaH/($c-1);
						echo "$i: ".$x/$lastH.'<br>';
						echo "$i: $x: average: $y<br>";// just test
					}
					$lastH=$h;
				}
				if(count($part)>5) $els_op[]=trans($part);

//				echo 'fuck'; exit;
				
				// todo 剔除
			}else if($len<MIN_WIDTH){

			}else{
				$els_op[]=trans($el);
			}
		}

		foreach($els_op as $el){
			draw($el);
		}

		$this->els = $els;
		$this->els_op = $els_op;
		$this->DataArrayZ = $op;
		$this->DataArray = trans($op);//$data;
		$this->ImageSize = $size;
	}
	public function run()
	{
		$result="";
		// 查找4个数字
		$data = array();
		foreach($this->els_op as $el){
			$data[]=getKey($el);
		}

//		var_dump($data);

		// 进行关键字匹配
		foreach($data as $numKey => $numString)
		{
			$max=0.0;
			$num = 0;
			foreach($this->Keys as $value => $key)
			{
				$percent=0.0;
				similar_text($value, $numString,$percent);
				if($percent > $max){
					$max = $percent;
					$num = $key;
					if(intval($percent) > 95)
						break;
				}
			}
			$result.=$num;
		}
		$this->data = $result;
		// 查找最佳匹配数字
		return $result;
	}

	public function __construct(){
		$keyStr=file_get_contents('key');
		$this->Keys = json_decode($keyStr, true);
	}
	protected $ImagePath;
	public $DataArray;
	public $DataArrayZ;
	public $els;
	public $els_op;
	public $o;
	protected $ImageSize;
	protected $data;
	protected $Keys;
	protected $NumStringArray;
}
?>
