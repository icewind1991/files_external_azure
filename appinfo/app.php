<?php

require_once __DIR__ . '/../vendor/autoload.php';

$l = \OC::$server->getL10N('files_external_azure');

OC_Mount_Config::registerBackend('\OCA\Files_External_Azure\Azure', [
	'backend' => (string)$l->t('Azure'),
	'priority' => 100,
	'configuration' => [
		'name' => (string)$l->t('Account Name'),
		'key' => (string)$l->t('Account Key'),
		'container' => (string)$l->t('Container')
	],
]);
