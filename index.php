<?php
include_once "Geo.php";
include_once "Translate.php";

/*$translate = new Translate();
$params = new stdClass();
$params->q = 'Naples';
$params->source = 'en';
$params->target = 'it';
echo json_encode($translate->request('translate', $params));
die();*/

$params = new stdClass();
$geo = new \Kunikida\Geo('it');

$type = 'search';
$params->q = 'Napoli';
/*$type = 'reverse';
$params->lat = 40.929477;
$params->lng = 14.35318;*/

$result = $geo->request($type, $params);
if(count($result) > 0):
    echo $result[0]->label . "\n\n";
endif;
echo json_encode($result) . "\n";