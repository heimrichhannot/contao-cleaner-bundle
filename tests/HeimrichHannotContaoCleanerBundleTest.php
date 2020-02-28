<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\BeExplanationBundle\Tests;

use HeimrichHannot\BeExplanationBundle\DependencyInjection\ContaoCleanerExtension;
use HeimrichHannot\CleanerBundle\HeimrichHannotContaoCleanerBundle;
use PHPUnit\Framework\TestCase;

class HeimrichHannotContaoCleanerBundleTest extends TestCase
{
    public function testGetContainerExtension()
    {
        $bundle = new HeimrichHannotContaoCleanerBundle();

        $this->assertInstanceOf(ContaoCleanerExtension::class, $bundle->getContainerExtension());
    }
}
