<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\CleanerBundle\Backend;

use Contao\Controller;
use Contao\System;

class Cleaner extends Controller
{
    public static function getFieldsAsOptions(\DataContainer $objDc)
    {
        if (!$objDc->activeRecord->dataContainer) {
            return [];
        }

        return System::getContainer()->get('huh.utils.dca')->getFields($objDc->activeRecord->dataContainer);
    }
}
