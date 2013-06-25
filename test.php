<?php

include ('Valite.php');

$valite = new Valite();

//$url='haha.png';
//$url='2.png';
//$url='apassCodeAction.jpg'; // come on
//$url='33.png';
//$url='clear.png';
//$url='clear_good.png';
$url='clear92.png';
//$url='clear7.png';
//$url='test1.png';
//$url='study/YPE8.png';


/////test start///////
//$valite->setImage($url);
//$valite->getHec();
//
//print_r($valite->DataArray);
//exit();
/////test end///////


$valite->setImage($url);

$valite->getHec();

draw($valite->o);


$ert = $valite->run();



//$ert = "1234";
print_r($ert);
echo '<br><img src="'.$url.'"><br>';

