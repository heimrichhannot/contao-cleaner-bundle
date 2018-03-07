<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\CleanerBundle\Backend;

use Contao\Controller;
use HeimrichHannot\Haste\Dca\General;

class Cleaner extends Controller
{
    public static function getFieldsAsOptions(\DataContainer $objDc)
    {
        if (!$objDc->activeRecord->dataContainer) {
            return [];
        }

        return General::getFields($objDc->activeRecord->dataContainer, false);
    }
}
