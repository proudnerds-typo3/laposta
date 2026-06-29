<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') || die('Access denied.');

ExtensionManagementUtility::addStaticFile('laposta', 'Configuration/TypoScript', 'Laposta');
