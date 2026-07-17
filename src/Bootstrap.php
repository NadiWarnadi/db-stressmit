<?php

namespace Warnadi\DbStressmit;

use Symfony\Component\Console\Application;
use Warnadi\DbStressmit\Command\StressCommand;

class Bootstrap
{
    public static function getAdapter(): Adapter\AdapterInterface
    {
        // ... sama seperti sebelumnya (deteksi framework)
        $framework = Detector\FrameworkDetector::detect();
        switch ($framework) {
            case 'laravel':
                return new Adapter\LaravelAdapter();
            case 'wordpress':
                return new Adapter\WordPressAdapter();
            default:
                return new Adapter\CliAdapter();
        }
    }

    public static function runConsole(array $argv): void
    {
        $application = new Application('Db-Stressmit', '0.1.0');
        $application->add(new StressCommand());
        $application->run();
    }
}