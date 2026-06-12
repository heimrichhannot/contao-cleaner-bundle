<?php

namespace HeimrichHannot\CleanerBundle\Cron;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCronJob;
use Contao\CoreBundle\Util\ProcessUtil;
use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Promise\PromiseInterface;
use HeimrichHannot\CleanerBundle\Command\CleanerCommand;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Exception\ExceptionInterface;

class CleanerCron
{
    public function __construct(
        private readonly LoggerInterface $contaoCronLogger,
        private readonly ProcessUtil $processUtil,
    ) {
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ExceptionInterface
     * @throws \Throwable
     */
    #[AsCronJob('minutely')]
    public function minutely(): PromiseInterface
    {
        return $this->run(CleanerCommand::INTERVAL_MINUTELY);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ExceptionInterface
     * @throws \Throwable
     */
    #[AsCronJob('hourly')]
    public function hourly(): PromiseInterface
    {
        return $this->run(CleanerCommand::INTERVAL_HOURLY);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ExceptionInterface
     * @throws \Throwable
     */
    #[AsCronJob('daily')]
    public function daily(): PromiseInterface
    {
        return $this->run(CleanerCommand::INTERVAL_DAILY);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ExceptionInterface
     * @throws \Throwable
     */
    #[AsCronJob('weekly')]
    public function weekly(): PromiseInterface
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
    public function run(string $interval): PromiseInterface
    {
        $isMinutely = CleanerCommand::INTERVAL_MINUTELY === $interval;
        $process = $this->processUtil->createSymfonyConsoleProcess('cleaner:execute', '--interval='.$interval);

        $promise = new Promise(
            static function () use ($process, &$promise, $isMinutely): void {
                $process->wait();

                if ($process->isSuccessful()) {
                    $output = $process->getOutput();
                    if (!$isMinutely && '' !== $output) {
                        $this->contaoCronLogger->log(LogLevel::INFO, $process->getOutput());
                    }
                    $promise->resolve($process->getOutput());
                } else {
                    $promise->reject($process->getErrorOutput() ?: $process->getOutput());
                }
            },
        );

        $process->start();

        return $promise;
    }
}
