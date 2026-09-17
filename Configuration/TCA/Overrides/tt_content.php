<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') || die('Access denied.');

$pluginSignatures = [
    ExtensionUtility::registerPlugin(
        'Laposta',
        'Subscribe',
        'LLL:EXT:laposta/Resources/Private/Language/locallang_db.xlf:tx_laposta_subscribe.name',
        'laposta-plugin-subscribe',
        'plugins',
        'LLL:EXT:laposta/Resources/Private/Language/locallang_db.xlf:tx_laposta_subscribe.description',
        'FILE:EXT:laposta/Configuration/Flexforms/Flexform.xml'
    ),
    ExtensionUtility::registerPlugin(
        'Laposta',
        'Unsubscribe',
        'LLL:EXT:laposta/Resources/Private/Language/locallang_db.xlf:tx_laposta_unsubscribe.name',
        'laposta-plugin-subscribe',
        'plugins',
        'LLL:EXT:laposta/Resources/Private/Language/locallang_db.xlf:tx_laposta_unsubscribe.description',
        'FILE:EXT:laposta/Configuration/Flexforms/Flexform.xml'
    ),
];

// The list records are looked up by uid through the repository, so the editor needs the record
// storage page; a plugin CType inherits its showitem from "header", which has neither field.
foreach ($pluginSignatures as $pluginSignature) {
    ExtensionManagementUtility::addToAllTCAtypes(
        'tt_content',
        'pages, recursive',
        $pluginSignature,
        'after:pi_flexform'
    );
}
