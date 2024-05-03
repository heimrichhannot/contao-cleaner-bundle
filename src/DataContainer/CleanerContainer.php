<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\CleanerBundle\DataContainer;

use Contao\Backend;
use Contao\BackendUser;
use Contao\Controller;
use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\Database;
use Contao\DataContainer;
use Contao\Image;
use Contao\Input;
use Contao\StringUtil;
use Contao\System;
use Contao\Versions;
use HeimrichHannot\UtilsBundle\Util\Utils;

class CleanerContainer
{
    /**
     * @var Utils $utils
     */
    private $utils;

    /**
     * CleanerContainer constructor.
     */
    public function __construct(Utils $utils) {
        $this->utils = $utils;
    }

    /**
     * @Callback(table="tl_cleaner", target="fields.dataContainer.options")
     * @Callback(table="tl_cleaner", target="fields.dependentTable.options")
     */
    public function getTables(?DataContainer $dc = null): array
    {
        return Database::getInstance()->listTables();
    }

    /**
     * @Callback(table="tl_cleaner", target="fields.entityFields.options")
     * @Callback(table="tl_cleaner", target="fields.dependentField.options")
     */
    public function getFieldsAsOptions(DataContainer $dc): array
    {
        if (!$dc->activeRecord->dataContainer) {
            return [];
        }

        return $this->utils->dca()->getDcaFields($dc->activeRecord->dataContainer);
    }

    /**
     * @Callback(table="tl_cleaner", target="list.operations.toggle.button")
     */
    public function toggleIcon($row, $href, $label, $title, $icon, $attributes): string
    {
        $objUser = BackendUser::getInstance();

        if (strlen(Input::get('tid'))) {
            $this->toggleVisibility(Input::get('tid'), ('1' === Input::get('state')));
            \Controller::redirect(Controller::getReferer());
        }

        // Check permissions AFTER checking the tid, so hacking attempts are logged
        if (!$objUser->isAdmin && !$objUser->hasAccess('tl_cleaner::published', 'alexf')) {
            return '';
        }

        $href .= '&amp;tid='.$row['id'].'&amp;state='.($row['published'] ? '' : 1);

        if (!$row['published']) {
            $icon = 'invisible.gif';
        }

        return \sprintf(
            '<a href="%s" title="%s"%s>%s</a> ',
            Backend::addToUrl($href),
            StringUtil::specialchars($title),
            $attributes,
            Image::getHtml($icon, $label)
        );
    }

    public function toggleVisibility($id, $blnVisible): void
    {
        $objUser = BackendUser::getInstance();
        $db = Database::getInstance();

        // Check permissions to publish
        if (!$objUser->isAdmin && !$objUser->hasAccess('tl_cleaner::published', 'alexf')) {
            Controller::log('Not enough permissions to publish/unpublish item ID "'.$id.'"', 'tl_cleaner toggleVisibility', TL_ERROR);
            Controller::redirect('contao/main.php?act=error');
        }

        $objVersions = new Versions('tl_cleaner', $id);
        $objVersions->initialize();

        // Trigger the save_callback
        if (is_array($GLOBALS['TL_DCA']['tl_cleaner']['fields']['published']['save_callback'])) {
            foreach ($GLOBALS['TL_DCA']['tl_cleaner']['fields']['published']['save_callback'] as $callback) {
                System::importStatic($callback[0]);
                $blnVisible = $this->{$callback[0]}->{$callback[1]}($blnVisible, $this);
            }
        }

        // Update the database
        $db->prepare(\sprintf(
            'UPDATE `tl_cleaner` SET tstamp=%s, published=%s WHERE id=?',
            time(),
            $blnVisible ? 1 : "''"
        ))->execute($id);

        $objVersions->create();

        $parents = $this->getParents($id);

        Controller::log(
            \sprintf(
                'A new version of record "tl_cleaner.id=%s" has been created%s',
                $id,
                empty($parents) ? '' : \sprintf(' (parent records: %s)', \implode(', ', $parents))
            ),
            __METHOD__,
            TL_GENERAL
        );
    }

    /** @see \Contao\Controller::getParentEntries() */
    protected function getParents($id)
    {
        $db = Database::getInstance();

        $pTable = 'tl_cleaner';
        $pId = $id;

        $parents = [];

        do
        {
            $parent = $db->prepare("SELECT pid FROM `$pTable` WHERE id=?")
                ->limit(1)
                ->execute($pId);

            if ($parent->numRows < 1) {
                break;
            }

            $pTable = $GLOBALS['TL_DCA'][$pTable]['config']['ptable'];
            $pId = $parent->id;

            $parents = $pTable . '.id=' . $pId;;

            Controller::loadDataContainer($pTable);
        }
        while ($pId && !empty($GLOBALS['TL_DCA'][$pTable]['config']['ptable']));

        return $parents;
    }
}
