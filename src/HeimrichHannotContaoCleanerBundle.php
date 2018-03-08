<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\CleanerBundle;

use HeimrichHannot\BeExplanationBundle\DependencyInjection\ContaoCleanerExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class HeimrichHannotContaoCleanerBundle extends Bundle
{
    /**
     * @return ContaoCleanerExtension
     */
    public function getContainerExtension()
    {
        return new ContaoCleanerExtension();
    }
}
