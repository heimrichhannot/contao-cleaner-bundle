<?php

$GLOBALS['TL_DCA']['tl_cleaner'] = [
    'config'      => [
        'dataContainer'     => 'Table',
        'enableVersioning'  => true,
        'onsubmit_callback' => [
            ['huh.utils.dca', 'setDateAdded'],
        ],
        'sql'               => [
            'keys' => [
                'id' => 'primary',
            ],
        ],
    ],
    'list'        => [
        'label'             => [
            'fields' => ['title'],
            'format' => '%s',
        ],
        'sorting'           => [
            'mode'         => 1,
            'fields'       => ['title'],
            'headerFields' => ['title'],
            'panelLayout'  => 'filter;search,limit',
        ],
        'global_operations' => [
            'all' => [
                'label'      => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'       => 'act=select',
                'class'      => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset();"',
            ],
        ],
        'operations'        => [
            'edit'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_cleaner']['edit'],
                'href'  => 'act=edit',
                'icon'  => 'edit.gif',
            ],
            'copy'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_cleaner']['copy'],
                'href'  => 'act=copy',
                'icon'  => 'copy.gif',
            ],
            'delete' => [
                'label'      => &$GLOBALS['TL_LANG']['tl_cleaner']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm']
                                . '\'))return false;Backend.getScrollOffset()"',
            ],
            'toggle' => [
                'label'           => &$GLOBALS['TL_LANG']['tl_cleaner']['toggle'],
                'icon'            => 'visible.gif',
                'attributes'      => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
                'button_callback' => ['tl_cleaner', 'toggleIcon'],
            ],
            'show'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_cleaner']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.gif',
            ],
        ],
    ],
    'palettes'    => [
        '__selector__'                                                              => ['type', 'fileDirRetrievalMode', 'addMaxAge'],
        'default'                                                                   => '{general_legend},type;',
        \HeimrichHannot\CleanerBundle\Command\CleanerCommand::TYPE_ENTITY           => '{general_legend},type,title;{config_legend},dataContainer,period,whereCondition,addMaxAge;{publish_legend},published;',
        \HeimrichHannot\CleanerBundle\Command\CleanerCommand::TYPE_DEPENDENT_ENTITY => '{general_legend},type,title;{config_legend},dependentTable,whereCondition,dataContainer,dependentField,period,,addMaxAge;{publish_legend},published;',
        \HeimrichHannot\CleanerBundle\Command\CleanerCommand::TYPE_FILE             => '{general_legend},type,title;{config_legend},period,fileDirRetrievalMode;{publish_legend},published;',
    ],
    'subpalettes' => [
        'addMaxAge'                                                                                                          => 'maxAge,maxAgeField',
        'fileDirRetrievalMode_'
        . \HeimrichHannot\CleanerBundle\Command\CleanerCommand::FILEDIR_RETRIEVAL_MODE_ENTITY_FIELDS                         => 'dataContainer,entityFields,whereCondition,addMaxAge',
        'fileDirRetrievalMode_'
        . \HeimrichHannot\CleanerBundle\Command\CleanerCommand::FILEDIR_RETRIEVAL_MODE_DIRECTORY                             => 'directory,addGitKeepAfterClean',
    ],
    'fields'      => [
        'id'                   => [
            'sql' => "int(10) unsigned NOT NULL auto_increment",
        ],
        'tstamp'               => [
            'label' => &$GLOBALS['TL_LANG']['tl_cleaner']['tstamp'],
            'sql'   => "int(10) unsigned NOT NULL default '0'",
        ],
        'dateAdded'            => [
            'label'   => &$GLOBALS['TL_LANG']['MSC']['dateAdded'],
            'sorting' => true,
            'flag'    => 6,
            'eval'    => ['rgxp' => 'datim', 'doNotCopy' => true],
            'sql'     => "int(10) unsigned NOT NULL default '0'",
        ],
        'type'                 => [
            'label'     => &$GLOBALS['TL_LANG']['tl_cleaner']['type'],
            'exclude'   => true,
            'filter'    => true,
            'inputType' => 'select',
            'options'   => \HeimrichHannot\CleanerBundle\Command\CleanerCommand::TYPES,
            'reference' => &$GLOBALS['TL_LANG']['tl_cleaner']['reference'],
            'eval'      => ['tl_class' => 'w50', 'mandatory' => true, 'submitOnChange' => true, 'includeBlankOption' => true],
            'sql'       => "varchar(64) NOT NULL default ''",
        ],
        'title'                => [
            'label'     => &$GLOBALS['TL_LANG']['tl_cleaner']['title'],
            'exclude'   => true,
            'search'    => true,
            'sorting'   => true,
            'flag'      => 1,
            'inputType' => 'text',
            'eval'      => ['mandatory' => true, 'tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'published'            => [
            'label'     => &$GLOBALS['TL_LANG']['tl_cleaner']['published'],
            'exclude'   => true,
            'filter'    => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50', 'doNotCopy' => true],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'dataContainer'        => [
            'inputType'        => 'select',
            'label'            => &$GLOBALS['TL_LANG']['tl_cleaner']['dataContainer'],
            'options_callback' => ['huh.utils.dca', 'getDataContainers'],
            'eval'             => [
                'chosen'             => true,
                'includeBlankOption' => true,
                'tl_class'           => 'w50 clr',
                'submitOnChange'     => true,
                'mandatory'          => true,
            ],
            'exclude'          => true,
            'sql'              => "varchar(255) NOT NULL default ''",
        ],
        'addMaxAge'            => [
            'label'     => &$GLOBALS['TL_LANG']['tl_cleaner']['addMaxAge'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['submitOnChange' => true, 'tl_class' => 'w50 clr'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'maxAge'               => [
            'label'     => &$GLOBALS['TL_LANG']['tl_cleaner']['maxAge'],
            'exclude'   => true,
            'inputType' => 'timePeriod',
            'options'   => ['m', 'h', 'd'],
            'reference' => &$GLOBALS['TL_LANG']['tl_cleaner']['maxAge'],
            'eval'      => ['mandatory' => true, 'tl_class' => 'w50 clr'],
            'sql'       => "blob NULL",
        ],
        'maxAgeField'          => [
            'label'     => &$GLOBALS['TL_LANG']['tl_cleaner']['maxAgeField'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'default'   => 'dateAdded',
            'eval'      => ['maxlength' => 255, 'mandatory' => true, 'tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'period'               => [
            'label'     => &$GLOBALS['TL_LANG']['tl_cleaner']['period'],
            'exclude'   => true,
            'inputType' => 'select',
            'options'   => ['minutely', 'hourly', 'daily', 'weekly', 'monthly'],
            'reference' => &$GLOBALS['TL_LANG']['tl_cleaner']['period'],
            'eval'      => ['mandatory' => true, 'includeBlankOption' => true, 'tl_class' => 'w50'],
            'sql'       => "varchar(32) NOT NULL default ''",
        ],
        'whereCondition'       => [
            'label'     => &$GLOBALS['TL_LANG']['tl_cleaner']['whereCondition'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['maxlength' => 255, 'mandatory' => true, 'tl_class' => 'clr w50'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'fileDirRetrievalMode' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_cleaner']['fileDirRetrievalMode'],
            'exclude'   => true,
            'filter'    => true,
            'inputType' => 'select',
            'options'   => \HeimrichHannot\CleanerBundle\Command\CleanerCommand::FILEDIR_RETRIEVAL_MODES,
            'reference' => &$GLOBALS['TL_LANG']['tl_cleaner']['reference'],
            'eval'      => ['tl_class' => 'w50', 'submitOnChange' => true, 'mandatory' => true, 'includeBlankOption' => true],
            'sql'       => "varchar(64) NOT NULL default ''",
        ],
        'entityFields'         => [
            'label'            => &$GLOBALS['TL_LANG']['tl_cleaner']['entityFields'],
            'exclude'          => true,
            'inputType'        => 'select',
            'options_callback' => ['HeimrichHannot\CleanerBundle\Backend\Cleaner', 'getFieldsAsOptions'],
            'eval'             => ['tl_class' => 'long clr', 'mandatory' => true, 'multiple' => true, 'chosen' => true, 'style' => 'width: 97%'],
            'sql'              => "blob NULL",
        ],
        'directory'            => [
            'label'     => &$GLOBALS['TL_LANG']['tl_cleaner']['directory'],
            'exclude'   => true,
            'inputType' => 'fileTree',
            'eval'      => ['fieldType' => 'radio', 'mandatory' => true, 'tl_class' => 'w50'],
            'sql'       => "binary(16) NULL",
        ],
        'addGitKeepAfterClean' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_cleaner']['addGitKeepAfterClean'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'dependentTable'       => [
            'label'            => &$GLOBALS['TL_LANG']['tl_cleaner']['dependentTable'],
            'inputType'        => 'select',
            'options_callback' => ['HeimrichHannot\CleanerBundle\Backend\Cleaner', 'getTables'],
            'eval'             => [
                'submitOnChange'     => true,
                'includeBlankOption' => true,
                'tl_class'           => 'w50 clr',
                'mandatory'          => true,
            ],
            'exclude'          => true,
            'sql'              => "varchar(64) NOT NULL default ''",
        ],
        'dependentField'       => [
            'label'            => &$GLOBALS['TL_LANG']['tl_cleaner']['dependentField'],
            'inputType'        => 'select',
            'options_callback' => ['HeimrichHannot\CleanerBundle\Backend\Cleaner', 'getFieldsAsOptions'],
            'exclude'          => true,
            'eval'             => ['includeBlankOption' => true, 'tl_class' => 'w50 ', 'mandatory' => true],
            'sql'              => "blob NULL",
        ],
    ],
];

if (System::getContainer()->get('huh.utils.container')->isBundleActive('privacy')) {
    $dca             = &$GLOBALS['TL_DCA']['tl_cleaner'];
    $protocolManager = new \HeimrichHannot\Privacy\Manager\ProtocolManager();
    
    $fields = [
        'addPrivacyProtocolEntry'         => $protocolManager->getSelectorFieldDca(),
        'privacyProtocolEntryArchive'     => $protocolManager->getArchiveFieldDca(),
        'privacyProtocolEntryType'        => $protocolManager->getTypeFieldDca(),
        'privacyProtocolEntryDescription' => $protocolManager->getDescriptionFieldDca(),
    ];
    
    $dca['fields'] += $fields;
    
    $dca['palettes']['__selector__'][]             = 'addPrivacyProtocolEntry';
    $dca['subpalettes']['addPrivacyProtocolEntry'] = 'privacyProtocolEntryArchive,privacyProtocolEntryType,privacyProtocolEntryDescription';
    
    // add to palettes
    foreach ($dca['palettes'] as $palette => &$fields) {
        if (in_array($palette, ['__selector__', 'default'])) {
            continue;
        }
        
        $fields = str_replace(';{publish_legend', ',addPrivacyProtocolEntry;{publish_legend', $fields);
    }
}


class tl_cleaner extends \Backend
{
    public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
    {
        $objUser = \BackendUser::getInstance();
        
        if (strlen(Input::get('tid'))) {
            $this->toggleVisibility(Input::get('tid'), (Input::get('state') === '1'));
            \Controller::redirect($this->getReferer());
        }
        
        // Check permissions AFTER checking the tid, so hacking attempts are logged
        if (!$objUser->isAdmin && !$objUser->hasAccess('tl_cleaner::published', 'alexf')) {
            return '';
        }
        
        $href .= '&amp;tid=' . $row['id'] . '&amp;state=' . ($row['published'] ? '' : 1);
        
        if (!$row['published']) {
            $icon = 'invisible.gif';
        }
        
        return '<a href="' . $this->addToUrl($href) . '" title="' . specialchars($title) . '"' . $attributes . '>' . Image::getHtml($icon, $label)
               . '</a> ';
    }
    
    public function toggleVisibility($intId, $blnVisible)
    {
        $objUser     = \BackendUser::getInstance();
        $objDatabase = \Database::getInstance();
        
        // Check permissions to publish
        if (!$objUser->isAdmin && !$objUser->hasAccess('tl_cleaner::published', 'alexf')) {
            \Controller::log('Not enough permissions to publish/unpublish item ID "' . $intId . '"', 'tl_cleaner toggleVisibility', TL_ERROR);
            \Controller::redirect('contao/main.php?act=error');
        }
        
        $objVersions = new Versions('tl_cleaner', $intId);
        $objVersions->initialize();
        
        // Trigger the save_callback
        if (is_array($GLOBALS['TL_DCA']['tl_cleaner']['fields']['published']['save_callback'])) {
            foreach ($GLOBALS['TL_DCA']['tl_cleaner']['fields']['published']['save_callback'] as $callback) {
                $this->import($callback[0]);
                $blnVisible = $this->{$callback[0]}->{$callback[1]}($blnVisible, $this);
            }
        }
        
        // Update the database
        $objDatabase->prepare("UPDATE tl_cleaner SET tstamp=" . time() . ", published='" . ($blnVisible ? 1 : '') . "' WHERE id=?")->execute($intId);
        
        $objVersions->create();
        \Controller::log('A new version of record "tl_cleaner.id=' . $intId . '" has been created' . $this->getParentEntries('tl_cleaner', $intId),
            'tl_cleaner toggleVisibility()', TL_GENERAL);
    }
}
