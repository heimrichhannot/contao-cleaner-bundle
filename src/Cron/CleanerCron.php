<?php

namespace HeimrichHannot\CleanerBundle\Cron;

use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\CoreBundle\ServiceAnnotation\CronJob;
use HeimrichHannot\CleanerBundle\Command\CleanerCommand;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
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
    protected $eventDispatcher;
    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var ParameterBagInterface $parameterBag
     */
    protected $parameterBag;
    /**
     * @var CleanerCommand
     */
    private $command;

    public function __construct(
        ContainerInterface $container,
        EventDispatcherInterface $eventDispatcher,
        ParameterBagInterface $parameterBag,
        CleanerCommand $command
    ) {
        $this->container = $container;
        $this->eventDispatcher = $eventDispatcher;
        $this->parameterBag = $parameterBag;
        $this->command = $command;
    }

    /**
     * @return string
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Symfony\Component\Console\Exception\ExceptionInterface
     * @throws \Throwable
     * @CronJob("minutely")
     */
    public function minutely(): string
    {
        return $this->run(CleanerCommand::INTERVAL_MINUTELY);
    }

    /**
     * @return string
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Symfony\Component\Console\Exception\ExceptionInterface
     * @throws \Throwable
     * @CronJob("hourly")
     */
    public function hourly(): string
    {
        return $this->run(CleanerCommand::INTERVAL_HOURLY);
    }

    /**
     * @return string
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Symfony\Component\Console\Exception\ExceptionInterface
     * @throws \Throwable
     * @CronJob("daily")
     */
    public function daily(): string
    {
        return $this->run(CleanerCommand::INTERVAL_DAILY);
    }

    /**
     * @return string
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Symfony\Component\Console\Exception\ExceptionInterface
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
        try
        {
            $input = new ArrayInput(['--interval' => $interval]);
            $output = new BufferedOutput();

            $logger = $this->container->get('monolog.logger.contao');
            $context = ['contao' => new ContaoContext(__METHOD__, ContaoContext::CRON)];

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