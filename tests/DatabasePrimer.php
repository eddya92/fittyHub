<?php

namespace App\Tests;

use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

class DatabasePrimer
{
    public static function prime($kernel): void
    {
        $application = new Application($kernel);
        $application->setAutoExit(false);

        // Drop and recreate the database schema
        $application->run(new ArrayInput([
            'command' => 'doctrine:schema:drop',
            '--env' => 'test',
            '--force' => true,
            '--quiet' => true,
        ]), new NullOutput());

        $application->run(new ArrayInput([
            'command' => 'doctrine:schema:create',
            '--env' => 'test',
            '--quiet' => true,
        ]), new NullOutput());
    }
}