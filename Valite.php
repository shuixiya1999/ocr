<?php

//define('WORD_WIDTH',9);
//define('WORD_HIGHT',13);
//define('OFFSET_X',7);
//define('OFFSET_Y',3);
//define('WORD_SPACING',4);

define('WORD_WIDTH',9); // ������
define('WORD_HIGHT',13); // ������
define('OFFSET_X',0);
define('OFFSET_Y',0);
define('WORD_SPACING',0);

define('HSPACE',2);
define('VSPACE',0);
define('STRICT',0);

define('MAX_WIDTH',17);
define('MIN_WIDTH',8);


function getUrl($path){
	$ret=array();
	$d=dir($path);
	while(($filename=$d->read())!==false){
		if($filename!=='.' && $filename!=='..'){
			$ret[]=$filename;
		}
	}
	$d->close();
	return $ret;
}
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
function splitArray($el, $indexs){
	$ret=array();
	$e=array();
	$pos=0;
	foreach($el as $index => $row){
		if($indexs[$pos]==$index){
			$pos++;
			$ret[]=trans($e);
			$e=array($row);
		}else{
			$e[]=$row;
		}
	}
	$ret[]=trans($e);
	return $ret;
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
	// Where RGB values = 0 �� 255.
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
		// HSL results = 0 �� 1
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
		// �淴

		// ȥ�հ�
		// 1.����
		$op=zero($data, HSPACE);

		// 2.����, �����ɹؼ�������
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
				// �ؼ������г�������Զ��ָ�

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

		//�Ż�els
		$els_op=array();
		foreach($els as $el){
			$len=count($el);
			if($len>MAX_WIDTH){
				// todo �ָ�
				$ave=array();
				$deltas=array();
				$lastH=0;
				foreach($el as $index=>$row){//����ÿһ�е�ƽ��rgb
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

					$h=rgb2hsl($color_ave);
					$h=$h[0];
//					echo "$index: h: $h <br>";// just test

					if($index!==0){
						$deltas[$index]=abs($h-$lastH);
					}
					$lastH=$h;
				}//foreach
				arsort($deltas);

				// ���ﲻ��ȷ
				$num=round($len/13) - 1;

				$indexs=array();
				$i=0;
				foreach($deltas as $idx=>$val){
					if($i == $num){
						break;
					}else{
						$indexs[]=$idx;
						$i++;
					}
				}
				sort($indexs);
				$els_op = array_merge($els_op, splitArray($el, $indexs));

//				echo 'fuck'; exit;
				
				// todo �޳�
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
		// ����4������
		$data = array();
		foreach($this->els_op as $el){
			$data[]=getKey($el);
		}

//		var_dump($data);

		// ���йؼ���ƥ��
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
		// �������ƥ������
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
