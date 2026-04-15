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

        $this->autoloader->registerNamespacePath('Vendor\Package\\', $path);

        $this->assertClassDoesNotExist('\Vendor\Package\Existent');
    }

    #[Test]
    public function emptyPathRegistrationHasNoEffect()
    {
        $path = $this->getFullFixturePath('/empty');

        $this->autoloader->registerNamespacePath('Vendor\Package\\', $path);

        $this->assertClassDoesNotExist('\Vendor\Package\Existent');
    }

    #[Test]
    public function nonexistentNamespaceRegistrationHasNoEffect()
    {
        $path = $this->getFullFixturePath('/src');

        $this->autoloader->registerNamespacePath('Nonexistent\\', $path);

        $this->assertClassDoesNotExist('\Vendor\Package\Existent');
    }

    #[Test]
    public function nonexistentClassCannotBeLoaded()
    {
        $path = $this->getFullFixturePath('/src');

        $this->autoloader->registerNamespacePath('Vendor\Package\\', $path);

        $this->assertClassDoesNotExist('\Vendor\Package\Nonexistent');
    }

    #[Test]
    public function existentClassFromRegisteredExistentNamespaceAndRegisteredExistentPathCanBeLoaded()
    {
        $path = $this->getFullFixturePath('/src');

        $this->autoloader->registerNamespacePath('Vendor\Package\\', $path);

        $this->assertClassIsInstantiable('\Vendor\Package\Existent');
    }

    #[Test]
    public function registeredCorrectNamespaceWorksWithTrailingBackslash()
    {
        $path = $this->getFullFixturePath('/src');

        $this->autoloader->registerNamespacePath('Vendor\Package\\', $path);

        $this->assertClassIsInstantiable('\Vendor\Package\Existent');
    }

    #[Test]
    public function registeredCorrectNamespaceWorksWithoutTrailingBackslash()
    {
        $path = $this->getFullFixturePath('/src');

        $this->autoloader->registerNamespacePath('Vendor\Package', $path);

        $this->assertClassIsInstantiable('\Vendor\Package\Existent');
    }

    /**
     * Assert class does not exist.
     *
     * @param string $class
     */
    protected static function assertClassDoesNotExist(string $class): void
    {
        $classIncluded = class_exists($class);

        parent::assertFalse($classIncluded);
    }

    #[Test]
    public function caseSensitivityIsEnforcedForRequestedFullyQualifiedClassName()
    {
        $path = $this->getFullFixturePath('/src');

        $this->autoloader->registerNamespacePath('Vendor\Package\\', $path);

        $this->assertClassDoesNotExist('\vendor\package\existent');
        $this->assertClassDoesNotExist('\Vendor\package\existent');
        $this->assertClassDoesNotExist('\Vendor\Package\existent');
    }

    /**
     * Assert there is possibility of creating an instance
     * of the given class.
     *
     * @param string $class
     */
    protected static function assertClassIsInstantiable(string $class): void
    {
        $object = new $class();

        parent::assertInstanceOf($class, $object);

        unset($object);
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
