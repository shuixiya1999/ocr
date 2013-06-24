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
function zero($arr,$limit){
	$op=array();
	foreach($arr as $row){
		$sum=array_sum($row);
		if($sum>$limit){
			$op[]=$row;
		}
	}
	return $op;
}
function draw($arr){
	echo '<br>';
	foreach($arr as $row){
		echo join($row);
		echo '<br>';
	}
	echo '<br>';
}

class Valite
{
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
				|| $rgbarray['blue'] < 125)
				{
					$data[$i][$j]=1;
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
//			draw(trans($el));

			if(count($el)>10){// 英文字母
				//todo
				$els_op[]=trans($el);
				draw(trans($el));
			}else{
				// 横向扩展
				$delta=count($el)-WORD_WIDTH;
				if($delta>0){
					array_splice($el,2,$delta);
				}else if($delta<0){
					
				}

				// 竖向扩展
				$el=trans($el);
				$el=zero($el, STRICT);// 先删全0

				$delta=count($el)-WORD_HIGHT;
				if($delta>0){
					array_splice($el,WORD_HIGHT-2,$delta);
				}else if($delta<0){
					
				}
				
				$els_op[]=$el;
				draw($el);
			}

			
		}

		$this->els = $els;
		$this->els_op = $els_op;
		$this->DataArrayZ = $op;
		$this->DataArray = trans($op);//$data;
//		$this->DataArray = $data;
		$this->ImageSize = $size;
	}
	public function run()
	{
		$result="";
		// 查找4个数字
		$data = array();
		foreach($this->els_op as $el){
			$str='';
			foreach($el as $row){
				$str.=join($row);
			}
			$data[]=$str;
		}

//		var_dump($data);

		// 进行关键字匹配
		foreach($data as $numKey => $numString)
		{
			$max=0.0;
			$num = 0;
			if(strlen($numString)>9*13){
				foreach($this->letter as $key => $value){
					$percent=0.0;
					similar_text($value, $numString,$percent);
					if(intval($percent) > $max){
						$max = $percent;
						$num = $key;
						if(intval($percent) > 95)
							break;
					}
				}
			}else{
				foreach($this->Keys as $key => $value)
				{
					$percent=0.0;
					similar_text($value, $numString,$percent);
					if(intval($percent) > $max)
					{
						$max = $percent;
						$num = $key;
						if(intval($percent) > 95)
							break;
					}
				}
			}

			/////////////// test ////////////////////////
//			echo "<br>";
//			if($num==3){
//				echo '三的相似度: '.$max.'<br>';
//				echo '实际的值: '.$numString.'<br>';
//				echo '三的标准: '.$value.'<br>';
//				echo '<br>';
//				echo '七的标准: '.$this->Keys[7].'<br>';
//
//				similar_text($this->Keys[7], $numString,$percent);
//				echo '七的相似度: '.$percent.'<br>';
//
//
//			}
//			echo "<br><br><br>";
			//////////////////////////////////////////////

			$result.=$num;
		}
		$this->data = $result;
		// 查找最佳匹配数字
		return $result;
	}

	public function __construct()
	{
		$this->Keys = array(
			'0'=>'000111000011111110011000110110000011110000011110000011110000011110000011110000011110000011011000110011111110000111000',
			'1'=>'000111000011111000011111000000011000000011000000011000000011000000011000000011000000011000000011000011111111011111111',
			'2'=>'011111000111111100100000110000000111000000110000001100000011000000110000001100000011000000110000000011111110111111110',
//			'3'=>'011111000111111110100000110000000110000001100011111000011111100000001110000000111000000110100001110111111100011111000',
			'3'=>'001111100110000110100000011000000011000000011000000110000111000000000110000000011000000011000000011100000110011111000',
			'4'=>'000001100000011100000011100000111100001101100001101100011001100011001100111111111111111111000001100000001100000001100',
			'5'=>'111111110111111110110000000110000000110000000111110000111111100000001110000000111000000110100001110111111100011111000',
			'6'=>'000111100001111110011000010011000000110000000110111100111111110111000111110000011110000011011000111011111110000111100',
	//		'7'=>'111111111111111111110000011100000010000000110000001100000001000000011000000010000000110000000110000001100000001100000',
			'7'=>'111111111111111111000000011000000010000000110000001100000001000000011000000010000000110000000110000001100000001100000',
			'8'=>'001111100011111110011000110011000110011101110001111100001111100011101110110000011110000011111000111011111110001111100',
			'9'=>'001111000011111110111000111110000011110000011111000111011111111001111011000000011000000110010000110011111100001111000',
			'S'=>'001111100010000110110000010110000010110000000111000000011111000000111110000000111000000011000000111110000110011111000',
		);
//		$this->Keys = $char;
		$this->letter=array(
			'H'=>'111111111',
			'U'=>'111111000011111111111100011111001110000000110001110000000110001110000000110001110000000110001110000000110001110000000110001110000000110001110000000110001110000000110001110000000110000111000001100000111111111000000001111110000',
			'T'=>'111111111111111111111111111100011100011110001110001100000111000000000011100000000001110000000000111000000000011100000000001110000000000111000000000011100000000001110000000000111000000000111110000',
			'K'=>'1111110001111000110000011000001100001100000011000110000000110011000000001101100000000011110000000000111110000000001101100000000011001100000000110001100000001100001100000011000001100011111000001111',
			'R'=>'111111111000001111111111100000111000111000001110000111000011100001110000111000011100001110000110000011111111100000111111110000001110001100000011100011100000111000011100001110000111000011100000111111111100000111',
			'N'=>'111100000011111111111000001111110011110000001100001111000000110000111110000011000011011100001100001100110000110000110011100011000011000111001100001100001110110000110000011011000011000000111100001100000001110011111000000111001111110000011100',
			'E'=>'1111111111100110000001001100000010011000000000110000010001100000100011111111000110000010001100000100011000000000110000000001100000010011000000111111111111',
		);
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
