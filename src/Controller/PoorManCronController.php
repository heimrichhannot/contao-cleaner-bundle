<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\CleanerBundle\Controller;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\System;
use HeimrichHannot\CleanerBundle\Command\CleanerCommand;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PoorManCronController
{
    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;
    /**
     * @var ContaoFramework
     */
    private $framework;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(
        ContainerInterface $container,
        ContaoFramework $framework,
        EventDispatcherInterface $eventDispatcher,
        LoggerInterface $logger
    ) {
        $this->container = $container;
        $this->framework = $framework;
        $this->eventDispatcher = $eventDispatcher;
        $this->logger = $logger;
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
        $command = new CleanerCommand($this->framework, $this->eventDispatcher);
        $command->setContainer(System::getContainer());

        $input = new ArrayInput(
            [
                '--interval' => $interval,
            ]
        );

        $output = new BufferedOutput();

        $logger = $this->container->get('monolog.logger.contao');
        $context = ['contao' => new ContaoContext(__METHOD__, ContaoContext::CRON)];

        try
        {
            $logger->log(LogLevel::INFO, 'Running poor man cron job for cleaner:execute with interval ' . $interval, $context);

            $command->run($input, $output);
            $return = $output->fetch();

            $logger->log(LogLevel::INFO, $return, $context);

            return $return;
        }
        catch (\Throwable $e)
        {
            $logger->log(LogLevel::ERROR, $e->getMessage(), $context);
            throw $e;
        }
    }
}
