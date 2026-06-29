<?php

declare(strict_types=1);

use Proudnerds\Laposta\Controller\SubscriptionlistController;
use TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Log\Writer\FileWriter;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') || die('Access denied.');

ExtensionUtility::configurePlugin(
    'Laposta',
    'Subscribe',
    [SubscriptionlistController::class => 'subscribe, rest'],
    [SubscriptionlistController::class => 'subscribe, rest']
);

ExtensionUtility::configurePlugin(
    'Laposta',
    'Unsubscribe',
    [SubscriptionlistController::class => 'unsubscribe, rest'],
    [SubscriptionlistController::class => 'unsubscribe, rest']
);

ExtensionManagementUtility::addPageTSConfig(
    'mod {
        wizards.newContentElement.wizardItems.plugins {
            elements {
                subscribe {
                    iconIdentifier = laposta-plugin-subscribe
                    title = LLL:EXT:laposta/Resources/Private/Language/locallang_db.xlf:tx_laposta_subscribe.name
                    description = LLL:EXT:laposta/Resources/Private/Language/locallang_db.xlf:tx_laposta_subscribe.description
                    tt_content_defValues {
                        CType = list
                        list_type = laposta_subscribe
                    }
                }
            }
            show = *
        }
    }'
);

ExtensionManagementUtility::addPageTSConfig(
    'mod {
        wizards.newContentElement.wizardItems.plugins {
            elements {
                unsubscribe {
                    iconIdentifier = laposta-plugin-subscribe
                    title = LLL:EXT:laposta/Resources/Private/Language/locallang_db.xlf:tx_laposta_unsubscribe.name
                    description = LLL:EXT:laposta/Resources/Private/Language/locallang_db.xlf:tx_laposta_unsubscribe.description
                    tt_content_defValues {
                        CType = list
                        list_type = laposta_unsubscribe
                    }
                }
            }
            show = *
        }
    }'
);

GeneralUtility::makeInstance(IconRegistry::class)->registerIcon(
    'laposta-plugin-subscribe',
    BitmapIconProvider::class,
    ['source' => 'EXT:laposta/Resources/Public/Icons/laposta.png']
);

$GLOBALS['TYPO3_CONF_VARS']['LOG']['Proudnerds']['Laposta']['Controller']['SubscriptionlistController']['writerConfiguration'] = array_fill_keys(
    [LogLevel::INFO, LogLevel::NOTICE, LogLevel::WARNING, LogLevel::ERROR, LogLevel::CRITICAL, LogLevel::ALERT],
    [FileWriter::class => ['logFile' => GeneralUtility::fixWindowsFilePath((string)getenv('TYPO3_PATH_APP')) . '/var/log/Laposta.log']]
);
