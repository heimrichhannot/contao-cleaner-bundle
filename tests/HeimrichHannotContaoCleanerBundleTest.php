<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\BeExplanationBundle\Tests;

use HeimrichHannot\CleanerBundle\HeimrichHannotContaoCleanerBundle;
use PHPUnit\Framework\TestCase;

class HeimrichHannotContaoCleanerBundleTest extends TestCase
{
    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated()
    {
        $bundle = new HeimrichHannotContaoCleanerBundle();

        $this->assertInstanceOf(HeimrichHannotContaoCleanerBundle::class, $bundle);
    }
}
