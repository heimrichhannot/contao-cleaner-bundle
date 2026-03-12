<?php

namespace HeimrichHannot\CleanerBundle\Cron;

use Contao\CoreBundle\ServiceAnnotation\CronJob;
use HeimrichHannot\CleanerBundle\Command\CleanerCommand;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class CleanerCron
{

    public function __construct(
        private CleanerCommand           $command,
        private readonly LoggerInterface $contaoCronLogger,
    ) {}

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ExceptionInterface
     * @throws \Throwable
     *
     * @CronJob("minutely")
     */
    public function minutely(): string
    {
        return $this->run(CleanerCommand::INTERVAL_MINUTELY);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ExceptionInterface
     * @throws \Throwable
     *
     * @CronJob("hourly")
     */
    public function hourly(): string
    {
        return $this->run(CleanerCommand::INTERVAL_HOURLY);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ExceptionInterface
     * @throws \Throwable
     *
     * @CronJob("daily")
     */
    public function daily(): string
    {
        return $this->run(CleanerCommand::INTERVAL_DAILY);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ExceptionInterface
     * @throws \Throwable
     *
     * @CronJob("weekly")
     */
    public function weekly(): string
    {
        return $this->run(CleanerCommand::INTERVAL_WEEKLY);
    }

    /**
     * Run cleaner:execute with given interval.
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ExceptionInterface
     * @throws \Throwable
     */
    public function run(string $interval): string
    {
        try {
            $input = new ArrayInput([
                '--interval' => $interval,
            ]);
            $output = new BufferedOutput();

            $isMinutely = CleanerCommand::INTERVAL_MINUTELY === $interval;

            if (!$isMinutely) {
                $this->contaoCronLogger->log(LogLevel::INFO, 'Running CleanerCron with interval ' . $interval);
            }

            $this->command->run($input, $output);
            $return = $output->fetch();

            if (!$isMinutely || $return) {
                $this->contaoCronLogger->log(LogLevel::INFO, $return ?: 'CleanerCron returned empty handed');
            }

            return $return;
        } catch (\Throwable $e) {
            $this->contaoCronLogger->log(LogLevel::ERROR, $e->getMessage());
            throw $e;
        }
    }
}
