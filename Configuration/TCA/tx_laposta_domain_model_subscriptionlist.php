<?php
return [
    'ctrl' => [
        'title' => 'LLL:EXT:laposta/Resources/Private/Language/locallang_db.xlf:tx_laposta_domain_model_subscriptionlist',
        'label' => 'list_label',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'versioningWS' => true,
        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'transOrigDiffSourceField' => 'l10n_diffsource',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
        ],
        'searchFields' => 'list_label,list_id, double_opt_in, info',
        'typeicon_classes' => [
            'default' => 'laposta-plugin-subscribe'
        ],
    ],
    'types' => [
        '1' => ['showitem' => 'sys_language_uid, l10n_parent, l10n_diffsource, hidden, list_label, list_id, double_opt_in, info, --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.access, starttime, endtime'],
    ],
    'columns' => [
        'sys_language_uid' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.language',
            'config' => [
                'type' => 'language',
            ],
        ],
        'l10n_parent' => [
            'displayCond' => 'FIELD:sys_language_uid:>:0',
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.l18n_parent',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'default' => 0,
                'items' => [
                    ['label' => '', 'value' => 0],
                ],
                'foreign_table' => 'tx_laposta_domain_model_subscriptionlist',
                'foreign_table_where' => 'AND {#tx_laposta_domain_model_subscriptionlist}.{#pid}=###CURRENT_PID### AND {#tx_laposta_domain_model_subscriptionlist}.{#sys_language_uid} IN (-1,0)',
            ],
        ],
        'l10n_diffsource' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        't3ver_label' => [
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.versionLabel',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'max' => 255,
            ],
        ],
        'hidden' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.visible',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
                'items' => [
                    [
                        'label' => '',
                        'invertStateDisplay' => true
                    ]
                ],
            ],
        ],
        'starttime' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.starttime',
            'config' => [
                'type' => 'datetime',
                'default' => 0,
                'behaviour' => [
                    'allowLanguageSynchronization' => true
                ]
            ],
        ],
        'endtime' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.endtime',
            'config' => [
                'type' => 'datetime',
                'default' => 0,
                'range' => [
                    'upper' => mktime(0, 0, 0, 1, 1, 2038)
                ],
                'behaviour' => [
                    'allowLanguageSynchronization' => true
                ]
            ],
        ],

        'list_label' => [
            'exclude' => true,
            'label' => 'LLL:EXT:laposta/Resources/Private/Language/locallang_db.xlf:tx_laposta_domain_model_subscriptionlist.list_label',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'required' => true
            ],
        ],
        'list_id' => [
            'exclude' => true,
            'label' => 'LLL:EXT:laposta/Resources/Private/Language/locallang_db.xlf:tx_laposta_domain_model_subscriptionlist.list_id',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'required' => true
            ],
        ],
        'double_opt_in' => [
            'exclude' => true,
            'label' => 'LLL:EXT:laposta/Resources/Private/Language/locallang_db.xlf:tx_laposta_domain_model_subscriptionlist.double_opt_in',
            'config' => [
                'type' => 'check',
                'items' => [
                    [
                        'label' => '', ''
                    ]
                ],
            ],
        ],
        'info' => [
            'exclude' => true,
            'label' => 'LLL:EXT:laposta/Resources/Private/Language/locallang_db.xlf:tx_laposta_domain_model_subscriptionlist.info',
            'config' => [
                'type' => 'text',
                'cols' => 40,
                'rows' => 6,
                'eval' => 'trim'
            ]
        ],
    ],
];
