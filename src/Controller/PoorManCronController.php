<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\CleanerBundle\Controller;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\System;
use HeimrichHannot\CleanerBundle\Command\CleanerCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class PoorManCronController
{
    /**
     * @var ContaoFramework
     */
    private $framework;

    public function __construct(ContaoFramework $framework)
    {
        $this->framework = $framework;
    }

    /**
     * Run cleaner:execute minutely.
     *
     * @throws \Exception When binding input fails. Bypass this by calling {@link ignoreValidationErrors()}.
     *
     * @return string
     */
    public function runMinutely()
    {
        return $this->run(System::getContainer()->getParameter('huh.command.minutely'));
    }

    /**
     * Run cleaner:execute hourly.
     *
     * @throws \Exception When binding input fails. Bypass this by calling {@link ignoreValidationErrors()}.
     *
     * @return string
     */
    public function runHourly()
    {
        return $this->run(System::getContainer()->getParameter('huh.command.hourly'));
    }

    /**
     * Run cleaner:execute daily.
     *
     * @throws \Exception When binding input fails. Bypass this by calling {@link ignoreValidationErrors()}.
     *
     * @return string
     */
    public function runDaily()
    {
        return $this->run(System::getContainer()->getParameter('huh.command.daily'));
    }

    /**
     * Run cleaner:execute weekly.
     *
     * @throws \Exception When binding input fails. Bypass this by calling {@link ignoreValidationErrors()}.
     *
     * @return string
     */
    public function runWeekly()
    {
        return $this->run(System::getContainer()->getParameter('huh.command.weekly'));
    }

    /**
     * Run cleaner:execute with given interval.
     *
     * @throws \Exception When binding input fails. Bypass this by calling {@link ignoreValidationErrors()}.
     *
     * @return string
     */
    public function run(string $interval)
    {
        $command = new CleanerCommand($this->framework);
        $command->setContainer(System::getContainer());

        $input = new ArrayInput(
            [
                '--interval' => $interval,
            ]
        );

        $output = new BufferedOutput();
        $command->run($input, $output);

        return $output->fetch();
    }
}
