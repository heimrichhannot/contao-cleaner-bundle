<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */
use Contao\DC_Table;
use Contao\System;
use HeimrichHannot\Privacy\Manager\ProtocolManager;
use HeimrichHannot\CleanerBundle\Command\CleanerCommand;
use HeimrichHannot\UtilsBundle\Dca\DateAddedField;

DateAddedField::register('tl_cleaner');

$GLOBALS['TL_DCA']['tl_cleaner'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'enableVersioning' => true,
        'sql' => [
            'keys' => [
                'id' => 'primary',
            ],
        ],
    ],
    'list' => [
        'label' => [
            'fields' => ['title'],
            'format' => '%s',
        ],
        'sorting' => [
            'mode' => 1,
            'fields' => ['title'],
            'headerFields' => ['title'],
            'panelLayout' => 'filter;search,limit',
        ],
        'global_operations' => [
            'all' => [
                'label' => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href' => 'act=select',
                'class' => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset();"',
            ],
        ],
        'operations' => [
            'edit' => [
                'label' => &$GLOBALS['TL_LANG']['tl_cleaner']['edit'],
                'href' => 'act=edit',
                'icon' => 'edit.gif',
            ],
            'copy' => [
                'label' => &$GLOBALS['TL_LANG']['tl_cleaner']['copy'],
                'href' => 'act=copy',
                'icon' => 'copy.gif',
            ],
            'delete' => [
                'label' => &$GLOBALS['TL_LANG']['tl_cleaner']['delete'],
                'href' => 'act=delete',
                'icon' => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\''.($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? null)
                                .'\'))return false;Backend.getScrollOffset()"',
            ],
            'toggle' => [
                'label' => &$GLOBALS['TL_LANG']['tl_cleaner']['toggle'],
                'icon' => 'visible.gif',
                'attributes' => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
                // 'button_callback' => [CleanerContainer::class, 'toggleIcon'],
            ],
            'show' => [
                'label' => &$GLOBALS['TL_LANG']['tl_cleaner']['show'],
                'href' => 'act=show',
                'icon' => 'show.gif',
            ],
        ],
    ],
    'palettes' => [
        '__selector__' => ['type', 'fileDirRetrievalMode', 'addMaxAge'],
        'default' => '{general_legend},type;',
        CleanerCommand::TYPE_ENTITY => '{general_legend},type,title,description;{config_legend},dataContainer,period,whereCondition,addMaxAge,useEntityOnDeleteCallback;{publish_legend},published;',
        CleanerCommand::TYPE_DEPENDENT_ENTITY => '{general_legend},type,title,description;{config_legend},dependentTable,whereCondition,dataContainer,dependentField,period,addMaxAge,useEntityOnDeleteCallback;{publish_legend},published;',
        CleanerCommand::TYPE_FILE => '{general_legend},type,title,description;{config_legend},period,fileDirRetrievalMode;{publish_legend},published;',
    ],
    'subpalettes' => [
        'addMaxAge' => 'maxAge,maxAgeField',
        'fileDirRetrievalMode_'
        . CleanerCommand::FILEDIR_RETRIEVAL_MODE_ENTITY_FIELDS => 'dataContainer,entityFields,whereCondition,addMaxAge',
        'fileDirRetrievalMode_'
        . CleanerCommand::FILEDIR_RETRIEVAL_MODE_DIRECTORY => 'directory,addGitKeepAfterClean',
    ],
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'tstamp' => [
            'label' => &$GLOBALS['TL_LANG']['tl_cleaner']['tstamp'],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'type' => [
            'label' => &$GLOBALS['TL_LANG']['tl_cleaner']['type'],
            'exclude' => true,
            'filter' => true,
            'inputType' => 'select',
            'options' => CleanerCommand::TYPES,
            'reference' => &$GLOBALS['TL_LANG']['tl_cleaner']['reference'],
            'eval' => ['tl_class' => 'w50', 'mandatory' => true, 'submitOnChange' => true, 'includeBlankOption' => true],
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'title' => [
            'label' => &$GLOBALS['TL_LANG']['tl_cleaner']['title'],
            'exclude' => true,
            'search' => true,
            'sorting' => true,
            'flag' => 1,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'description' => [
            'label' => &$GLOBALS['TL_LANG']['tl_cleaner']['description'],
            'exclude' => true,
            'search' => true,
            'inputType' => 'textarea',
            'eval' => ['tl_class' => 'long clr'],
            'sql' => 'text NULL',
        ],
        'published' => [
            'label' => &$GLOBALS['TL_LANG']['tl_cleaner']['published'],
            'exclude' => true,
            'filter' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50', 'doNotCopy' => true],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'dataContainer' => [
            'inputType' => 'select',
            'label' => &$GLOBALS['TL_LANG']['tl_cleaner']['dataContainer'],
            // 'options_callback' => [CleanerContainer::class, 'getTables'],
            'eval' => [
                'chosen' => true,
                'includeBlankOption' => true,
                'tl_class' => 'w50 clr',
                'submitOnChange' => true,
                'mandatory' => true,
            ],
            'exclude' => true,
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'addMaxAge' => [
            'label' => &$GLOBALS['TL_LANG']['tl_cleaner']['addMaxAge'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['submitOnChange' => true, 'tl_class' => 'w50 clr'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'maxAge' => [
            'label' => &$GLOBALS['TL_LANG']['tl_cleaner']['maxAge'],
            'exclude' => true,
            'inputType' => 'timePeriod',
            'options' => ['m', 'h', 'd', 'w', 'M', 'y'],
            'reference' => &$GLOBALS['TL_LANG']['tl_cleaner']['maxAge'],
            'eval' => ['mandatory' => true, 'tl_class' => 'w50 clr'],
            'sql' => 'blob NULL',
        ],
        'maxAgeField' => [
            'label' => &$GLOBALS['TL_LANG']['tl_cleaner']['maxAgeField'],
            'exclude' => true,
            'search' => true,
            'inputType' => 'text',
            'default' => 'dateAdded',
            'eval' => ['maxlength' => 255, 'mandatory' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'period' => [
            'label' => &$GLOBALS['TL_LANG']['tl_cleaner']['period'],
            'exclude' => true,
            'inputType' => 'select',
            'options' => ['minutely', 'hourly', 'daily', 'weekly', 'monthly'],
            'reference' => &$GLOBALS['TL_LANG']['tl_cleaner']['period'],
            'eval' => ['mandatory' => true, 'includeBlankOption' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(32) NOT NULL default ''",
        ],
        'whereCondition' => [
            'label' => &$GLOBALS['TL_LANG']['tl_cleaner']['whereCondition'],
            'exclude' => true,
            'search' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'mandatory' => true, 'tl_class' => 'clr w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'fileDirRetrievalMode' => [
            'label' => &$GLOBALS['TL_LANG']['tl_cleaner']['fileDirRetrievalMode'],
            'exclude' => true,
            'filter' => true,
            'inputType' => 'select',
            'options' => CleanerCommand::FILEDIR_RETRIEVAL_MODES,
            'reference' => &$GLOBALS['TL_LANG']['tl_cleaner']['reference'],
            'eval' => ['tl_class' => 'w50', 'submitOnChange' => true, 'mandatory' => true, 'includeBlankOption' => true],
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'entityFields' => [
            'label' => &$GLOBALS['TL_LANG']['tl_cleaner']['entityFields'],
            'exclude' => true,
            'inputType' => 'select',
            // 'options_callback' => [CleanerContainer::class, 'getFieldsAsOptions'],
            'eval' => ['tl_class' => 'long clr', 'mandatory' => true, 'multiple' => true, 'chosen' => true, 'style' => 'width: 97%'],
            'sql' => 'blob NULL',
        ],
        'directory' => [
            'label' => &$GLOBALS['TL_LANG']['tl_cleaner']['directory'],
            'exclude' => true,
            'inputType' => 'fileTree',
            'eval' => ['fieldType' => 'radio', 'mandatory' => true, 'tl_class' => 'w50'],
            'sql' => 'binary(16) NULL',
        ],
        'addGitKeepAfterClean' => [
            'label' => &$GLOBALS['TL_LANG']['tl_cleaner']['addGitKeepAfterClean'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'dependentTable' => [
            'label' => &$GLOBALS['TL_LANG']['tl_cleaner']['dependentTable'],
            'inputType' => 'select',
            // 'options_callback' => [CleanerContainer::class, 'getTables'],
            'eval' => [
                'submitOnChange' => true,
                'includeBlankOption' => true,
                'tl_class' => 'w50 clr',
                'mandatory' => true,
            ],
            'exclude' => true,
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'dependentField' => [
            'label' => &$GLOBALS['TL_LANG']['tl_cleaner']['dependentField'],
            'inputType' => 'select',
            // 'options_callback' => [CleanerContainer::class, 'getFieldsAsOptions'],
            'exclude' => true,
            'eval' => ['includeBlankOption' => true, 'tl_class' => 'w50 ', 'mandatory' => true],
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'useEntityOnDeleteCallback' => [
            'label' => &$GLOBALS['TL_LANG']['tl_cleaner']['useEntityOnDeleteCallback'],
            'inputType' => 'checkbox',
            'exclude' => true,
            'eval' => ['tl_class' => 'clr w50 '],
            'sql' => "char(1) NOT NULL default ''",
        ],
    ],
];

if (\in_array('privacy', \array_keys(System::getContainer()->getParameter('kernel.bundles')))
    && \class_exists('\HeimrichHannot\PrivacyBundle\HeimrichHannotPrivacyBundle'))
{
    $dca = &$GLOBALS['TL_DCA']['tl_cleaner'];
    $protocolManager = new ProtocolManager();

    $fields = [
        'addPrivacyProtocolEntry' => $protocolManager->getSelectorFieldDca(),
        'privacyProtocolEntryArchive' => $protocolManager->getArchiveFieldDca(),
        'privacyProtocolEntryType' => $protocolManager->getTypeFieldDca(),
        'privacyProtocolEntryDescription' => $protocolManager->getDescriptionFieldDca(),
    ];

    $dca['fields'] += $fields;

    $dca['palettes']['__selector__'][] = 'addPrivacyProtocolEntry';
    $dca['subpalettes']['addPrivacyProtocolEntry'] = 'privacyProtocolEntryArchive,privacyProtocolEntryType,privacyProtocolEntryDescription';

    // add to palettes
    foreach ($dca['palettes'] as $palette => &$fields)
    {
        if (in_array($palette, ['__selector__', 'default'])) {
            continue;
        }

        $fields = str_replace(';{publish_legend', ',addPrivacyProtocolEntry;{publish_legend', $fields);
    }
}
