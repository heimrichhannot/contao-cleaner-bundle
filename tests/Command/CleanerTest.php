<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\CleanerBundle\Tests\Command;

use Contao\System;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\CleanerBundle\Command\CleanerCommand;
use HeimrichHannot\CleanerBundle\Model\CleanerModel;

class CleanerTest extends ContaoTestCase
{
    public function setUp()
    {
        parent::setUp();

        if (!defined('TL_ROOT')) {
            define('TL_ROOT', '/');
        }

        $container = $this->mockContainer();
        System::setContainer($container);
    }

    public function testGetMaxAgeCondition()
    {
        $framework = $this->mockContaoFramework();
        $cleaner = new CleanerCommand($framework, 'daily');

        $maxAge = serialize(['unit' => 'm', 'value' => 1]);
        $objCleaner = $this->mockClassWithProperties(CleanerModel::class, ['dataContainer' => 'tl_news', 'maxAge' => $maxAge, 'maxAgeField' => 'dateAdded']);
        $result = $cleaner->getMaxAgeCondition($objCleaner);
        $this->assertSame(' AND (UNIX_TIMESTAMP() > tl_news.dateAdded + 60)', $result);

        $maxAge = serialize(['unit' => 'h', 'value' => 1]);
        $objCleaner = $this->mockClassWithProperties(CleanerModel::class, ['dataContainer' => 'tl_news', 'maxAge' => $maxAge, 'maxAgeField' => 'dateAdded']);
        $result = $cleaner->getMaxAgeCondition($objCleaner);
        $this->assertSame(' AND (UNIX_TIMESTAMP() > tl_news.dateAdded + 3600)', $result);

        $maxAge = serialize(['unit' => 'd', 'value' => 1]);
        $objCleaner = $this->mockClassWithProperties(CleanerModel::class, ['dataContainer' => 'tl_news', 'maxAge' => $maxAge, 'maxAgeField' => 'dateAdded']);
        $result = $cleaner->getMaxAgeCondition($objCleaner);
        $this->assertSame(' AND (UNIX_TIMESTAMP() > tl_news.dateAdded + 86400)', $result);
    }
}
