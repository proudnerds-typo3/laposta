<?php
defined('TYPO3_MODE') || die('Access denied.');

call_user_func(
    function () {
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            'Laposta',
            'Subscribe',
            'Laposta subscribe'
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            'Laposta',
            'Unsubscribe',
            'Laposta unsubscribe'
        );
    }
);

# Uncomment if you like to use typoscript to set PID
#$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['laposta_subscribe'] = 'recursive,pages';

$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['laposta_subscribe'] = 'pi_flexform';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['laposta_unsubscribe'] = 'pi_flexform';

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    'laposta_subscribe',
    'FILE:EXT:laposta/Configuration/Flexforms/Flexform.xml'
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    'laposta_unsubscribe',
    'FILE:EXT:laposta/Configuration/Flexforms/Flexform.xml'
);
