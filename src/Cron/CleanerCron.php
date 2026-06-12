<?php

namespace HeimrichHannot\CleanerBundle\Cron;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCronJob;
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
        private readonly CleanerCommand $command,
        private readonly LoggerInterface $contaoCronLogger,
    ) {
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ExceptionInterface
     * @throws \Throwable
     */
    #[AsCronJob('minutely')]
    public function minutely(): void
    {
        $this->run(CleanerCommand::INTERVAL_MINUTELY);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ExceptionInterface
     * @throws \Throwable
     */
    #[AsCronJob('hourly')]
    public function hourly(): void
    {
        $this->run(CleanerCommand::INTERVAL_HOURLY);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ExceptionInterface
     * @throws \Throwable
     */
    #[AsCronJob('daily')]
    public function daily(): void
    {
        $this->run(CleanerCommand::INTERVAL_DAILY);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ExceptionInterface
     * @throws \Throwable
     */
    #[AsCronJob('weekly')]
    public function weekly(): void
    {
        $this->run(CleanerCommand::INTERVAL_WEEKLY);
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
