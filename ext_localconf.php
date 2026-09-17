<?php

declare(strict_types=1);

use Proudnerds\Laposta\Controller\SubscriptionlistController;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Log\Writer\FileWriter;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') || die('Access denied.');

ExtensionUtility::configurePlugin(
    'Laposta',
    'Subscribe',
    [SubscriptionlistController::class => 'subscribe, rest'],
    [SubscriptionlistController::class => 'subscribe, rest'],
    ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
);

ExtensionUtility::configurePlugin(
    'Laposta',
    'Unsubscribe',
    [SubscriptionlistController::class => 'unsubscribe, rest'],
    [SubscriptionlistController::class => 'unsubscribe, rest'],
    ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
);

$GLOBALS['TYPO3_CONF_VARS']['LOG']['Proudnerds']['Laposta']['Controller']['SubscriptionlistController']['writerConfiguration'] = array_fill_keys(
    [LogLevel::INFO, LogLevel::NOTICE, LogLevel::WARNING, LogLevel::ERROR, LogLevel::CRITICAL, LogLevel::ALERT],
    [FileWriter::class => ['logFile' => GeneralUtility::fixWindowsFilePath((string)getenv('TYPO3_PATH_APP')) . '/var/log/Laposta.log']]
);
