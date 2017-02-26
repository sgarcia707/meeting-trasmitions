<?php

include_once 'Brodcasting.php';

$data = json_decode(file_get_contents("php://input"));

$brodcasting = new Brodcasting();

$description = $data->description;
$configuration = $data->configuration;

$brodcasting->addConfigurationFfmpeg($description, $configuration);

?>