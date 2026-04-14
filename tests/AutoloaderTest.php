<?php

/*
 * Copyright (C) 2026 Katarzyna Krasińska
 * PHP.PSR-4.lab - https://github.com/katheroine/php.psr-4.lab
 * Licensed under GPL-3.0 - see LICENSE.md
 */

declare(strict_types=1);

namespace PHPLab\StandardPSR4;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

#[RunTestsInSeparateProcesses]
class AutoloaderTest extends TestCase
{
    protected const string FIXTURES_DIRECTORY_RELATIVE_PATH = '/fixtures';

    /**
     * Instance of tested class.
     *
     * @var Autoloader
     */
    private Autoloader $autoloader;

    #[Test]
    public function nonexistentPathRegistrationHasNoEffect()
    {
        $path = $this->getFullFixturePath('/nonexistent');

        $this->autoloader->registerNamespacePath('Vendor\Package', $path);

        $this->assertClassDoesNotExist('\Vendor\Package\Existent');
    }

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->autoloader = new Autoloader();
        $this->autoloader->register();
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown(): void
    {
        $this->autoloader->unregister();
    }

    /**
     * Get full path for given partial path of autoloaded class fixture files.
     *
     * @param string $partialPath
     *
     * @return string
     */
    protected function getFullFixturePath(string $partialPath): string
    {
        $path = (__DIR__) . self::FIXTURES_DIRECTORY_RELATIVE_PATH . $partialPath;

        return $path;
    }
}
