<?php
defined('TYPO3') || die('Access denied.');

call_user_func(
    function () {
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'Laposta',
            'Subscribe',
            [\Proudnerds\Laposta\Controller\SubscriptionlistController::class => 'subscribe, rest'],
            [\Proudnerds\Laposta\Controller\SubscriptionlistController::class => 'subscribe, rest']
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'Laposta',
            'Unsubscribe',
            [\Proudnerds\Laposta\Controller\SubscriptionlistController::class => 'unsubscribe, rest'],
            [\Proudnerds\Laposta\Controller\SubscriptionlistController::class => 'unsubscribe, rest']
        );

        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
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

        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
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

        $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);

        $iconRegistry->registerIcon(
            'laposta-plugin-subscribe',
            \TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider::class,
            ['source' => 'EXT:laposta/Resources/Public/Icons/laposta.png']
        );

        $projectRootPath = TYPO3\CMS\Core\Utility\GeneralUtility::fixWindowsFilePath(getenv('TYPO3_PATH_APP'));
        $logFilePath = $projectRootPath . '/var/log/Laposta.log';

        $GLOBALS['TYPO3_CONF_VARS']['LOG']['Proudnerds']['Laposta']['Controller']['SubscriptionlistController'] = [
            'writerConfiguration' => [
                \TYPO3\CMS\Core\Log\LogLevel::INFO => [
                    'TYPO3\\CMS\\Core\\Log\\Writer\\FileWriter' => [
                        'logFile' => $logFilePath
                    ]
                ],
                \TYPO3\CMS\Core\Log\LogLevel::NOTICE => [
                    'TYPO3\\CMS\\Core\\Log\\Writer\\FileWriter' => [
                        'logFile' => $logFilePath
                    ]
                ],
                \TYPO3\CMS\Core\Log\LogLevel::WARNING => [
                    'TYPO3\\CMS\\Core\\Log\\Writer\\FileWriter' => [
                        'logFile' => $logFilePath
                    ]
                ],
                \TYPO3\CMS\Core\Log\LogLevel::ERROR => [
                    'TYPO3\\CMS\\Core\\Log\\Writer\\FileWriter' => [
                        'logFile' => $logFilePath
                    ]
                ],
                \TYPO3\CMS\Core\Log\LogLevel::CRITICAL => [
                    'TYPO3\\CMS\\Core\\Log\\Writer\\FileWriter' => [
                        'logFile' => $logFilePath
                    ]
                ],
                \TYPO3\CMS\Core\Log\LogLevel::ALERT => [
                    'TYPO3\\CMS\\Core\\Log\\Writer\\FileWriter' => [
                        'logFile' => $logFilePath
                    ]
                ],
            ]
        ];
    }
);