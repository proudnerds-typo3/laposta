<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') || die('Access denied.');

ExtensionUtility::registerPlugin('Laposta', 'Subscribe', 'Laposta subscribe');
ExtensionUtility::registerPlugin('Laposta', 'Unsubscribe', 'Laposta unsubscribe');

// Uncomment to set the storage PID via TypoScript instead:
// $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['laposta_subscribe'] = 'recursive,pages';

$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['laposta_subscribe'] = 'pi_flexform';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['laposta_unsubscribe'] = 'pi_flexform';

ExtensionManagementUtility::addPiFlexFormValue(
    'laposta_subscribe',
    'FILE:EXT:laposta/Configuration/Flexforms/Flexform.xml'
);

ExtensionManagementUtility::addPiFlexFormValue(
    'laposta_unsubscribe',
    'FILE:EXT:laposta/Configuration/Flexforms/Flexform.xml'
);
