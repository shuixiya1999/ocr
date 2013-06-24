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
				$last=array(
					'red'=>0,
					'green'=>0,
					'blue'=>0
				);
				$c=0;
				$part=array();
				foreach($el as $row){//计算每一行的平均rgb
					$c++;
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
					$a=($color_ave['red']-$last['red'])*($color_ave['red']-$last['red'])
						+($color_ave['green']-$last['green'])*($color_ave['green']-$last['green'])
						+($color_ave['blue']-$last['blue'])*($color_ave['blue']-$last['blue']);

					if($c===1 || sqrt($a)<60){
						$part[]=$row;
					}else{
						if(count($part)>5) $els_op[]=trans($part);
						$part=array($row);// 初始化
					}

					echo $c.': '.sqrt($a).'<br>';

					$ave[]=$last=$color_ave;
				}
				var_dump($ave[12]);echo '<br>';
				var_dump($ave[13]);echo '<br>';
				var_dump($ave[14]);
				if(count($part)>5) $els_op[]=trans($part);
//				echo 'fuck'; exit;
				
				// todo 剔除
			}else if($len<MIN_WIDTH){

			}else{
				$els_op[]=trans($el);
			}
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
