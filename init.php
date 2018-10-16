<?php
/**
 * Permite descargar registros de respuestas de las encuestas de Qualtrics (www.qualtrics.com)
 * @author Luis Arcia <luis.arcia@outlook.com>
 * @version v1.0
 */

require 'method/surveyResponseExports.php';
require 'functions/unzip.php';
require 'functions/formatDate.php';

$config = array(
	'token'			=> '', //Token
	'surveyId' 		=> [], //ID encuestas
	'format' 		=> 'json', //csv, json
	'dataCenter' 	=> 'eu', //eu, us, etc...
	'startDate' 	=> '2018-10-01 00:00:00', //yy-mm-dd H:i:s
	'endDate' 		=> '2018-10-12 23:59:59',
	'dir' 			=> __dir__.'/',
	'fileName' 		=> 'data'
);


$qualt = new surveyResponseExports( $config );
$qualt->init().'<br>';