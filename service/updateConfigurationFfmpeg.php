<?php

include_once 'Brodcasting.php';

$data = json_decode(file_get_contents("php://input"));

$brodcasting = new Brodcasting();

$condition = $data->condition;
$json = $data->json;

$brodcasting->updateConfigurationFfmpeg($condition, $json);

?>