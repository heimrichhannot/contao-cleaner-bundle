<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\CleanerBundle\Backend;

use Contao\Controller;
use Contao\DataContainer;
use Contao\System;
use HeimrichHannot\FormHybrid\Backend\Module;

class Cleaner extends Controller
{
    public static function getFieldsAsOptions(DataContainer $objDc)
    {
        if (!$objDc->activeRecord->dataContainer) {
            return [];
        }

        return System::getContainer()->get('huh.utils.dca')->getFields($objDc->activeRecord->dataContainer);
    }

    /**
     * get tables.
     *
     * @return array
     */
    public static function getTables(DataContainer $dc)
    {
        return Module::getDataContainers($dc);
    }
}
