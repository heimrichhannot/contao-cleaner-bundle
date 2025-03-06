<?php

namespace HeimrichHannot\CleanerBundle\Cron;

use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\CoreBundle\ServiceAnnotation\CronJob;
use Contao\System;
use HeimrichHannot\CleanerBundle\Command\CleanerCommand;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Throwable;

class CleanerCron
{
    /**
     * @var EventDispatcherInterface
     */
    protected EventDispatcherInterface $eventDispatcher;
    /**
     * @var ParameterBagInterface $parameterBag
     */
    protected ParameterBagInterface $parameterBag;
    /**
     * @var CleanerCommand
     */
    private CleanerCommand $command;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ParameterBagInterface $parameterBag,
        CleanerCommand $command
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->parameterBag = $parameterBag;
        $this->command = $command;
    }

    /**
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ExceptionInterface
     * @throws \Throwable
     * @CronJob("minutely")
     */
    public function minutely(): string
    {
        return $this->run(CleanerCommand::INTERVAL_MINUTELY);
    }

    /**
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ExceptionInterface
     * @throws \Throwable
     * @CronJob("hourly")
     */
    public function hourly(): string
    {
        return $this->run(CleanerCommand::INTERVAL_HOURLY);
    }

    /**
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ExceptionInterface
     * @throws \Throwable
     * @CronJob("daily")
     */
    public function daily(): string
    {
        return $this->run(CleanerCommand::INTERVAL_DAILY);
    }

    /**
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ExceptionInterface
     * @throws \Throwable
     * @CronJob("weekly")
     */
    public function weekly(): string
    {
        return $this->run(CleanerCommand::INTERVAL_WEEKLY);
    }

    /**
     * Run cleaner:execute with given interval.
     *
     * @param string $interval
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ExceptionInterface
     * @throws Throwable
     */
    public function run(string $interval): string
    {
        $context = ['contao' => new ContaoContext(__METHOD__, ContaoContext::CRON)];
        $logger = System::getContainer()->get('monolog.logger.contao');

        try
        {
            $input = new ArrayInput(['--interval' => $interval]);
            $output = new BufferedOutput();


            $isMinutely = $interval === CleanerCommand::INTERVAL_MINUTELY;

            if (!$isMinutely) {
                $logger->log(LogLevel::INFO, 'Running CleanerCron with interval ' . $interval, $context);
            }

            $this->command->run($input, $output);
            $return = $output->fetch();

            if (!$isMinutely || $return) {
                $logger->log(LogLevel::INFO, $return ?: 'CleanerCron returned empty handed', $context);
            }

            return $return;
        }
        catch (\Throwable $e)
        {
            $logger->log(LogLevel::ERROR, $e->getMessage(), $context);
            throw $e;
        }
    }
}
