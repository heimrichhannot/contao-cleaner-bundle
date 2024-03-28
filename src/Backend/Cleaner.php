<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\CleanerBundle\Backend;

use Contao\Controller;
use Contao\DataContainer;
use Contao\System;
use HeimrichHannot\FormHybrid\Backend\Module;
use HeimrichHannot\UtilsBundle\Util\Utils;

class Cleaner extends Controller
{
    public static function getFieldsAsOptions(DataContainer $objDc)
    {
        if (!$objDc->activeRecord->dataContainer) {
            return [];
        }

        $utils = System::getContainer()->get(Utils::class);
        return $utils->dca()->getDcaFields($objDc->activeRecord->dataContainer);
    }

    /**
     * get tables.
     *
     * @return array
     * @noinspection PhpUndefinedClassInspection
     */
    public static function getTables(DataContainer $dc)
    {
        if (class_exists(Module::classs)) {
            return Module::getDataContainers($dc);
        }
        return [];
    }
}
