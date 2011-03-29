<?php
if(!defined('sugarEntry')) define('sugarEntry', true);

chdir('../../..');
$webservice_class = 'CustomSugarRestService';
$webservice_path = 'custom/service/core/CustomSugarRestService.php';
$webservice_impl_class = 'SugarRestServiceImpl';
$registry_class = 'registry';
$location = '/custom/service/v2/rest.php';
$registry_path = 'service/v2/registry.php';
require_once('service/core/webservice.php');
