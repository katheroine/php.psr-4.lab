<?php

/*
 * Copyright (C) 2026 Katarzyna Krasińska
 * PHP.PSR-4.lab - https://github.com/katheroine/php.psr-4.lab
 * Licensed under GPL-3.0 - see LICENSE.md
 */

declare(strict_types=1);

namespace PHPLab\StandardPSR4;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;
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
    public function undoneRegistrationHasNoEffect()
    {
        $this->assertClassDoesNotExist('\Vendor\Package\Existent');
    }

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
    public function unnamespacedClassCannotBeLoaded()
    {
        $path = $this->getFullFixturePath('/src');

        $this->autoloader->registerNamespacePath('Vendor\Package\\', $path);

        $this->assertClassDoesNotExist('Unnamespaced');
        $this->assertClassDoesNotExist('\Unnamespaced');
        $this->assertClassDoesNotExist('\Vendor\Package\Unnamespaced');
    }

    #[Test]
    public function wronglyNamespacedClassCannotBeLoaded()
    {
        $path = $this->getFullFixturePath('/src');

        $this->autoloader->registerNamespacePath('Vendor\Package\\', $path);

        $this->assertClassDoesNotExist('\Package\Existent');
        $this->assertClassDoesNotExist('Package\Existent');
        $this->assertClassDoesNotExist('\Existent');
        $this->assertClassDoesNotExist('Existent');
    }

    #[Test]
    public function properClassCanBeLoaded()
    {
        $path = $this->getFullFixturePath('/src');

        $this->autoloader->registerNamespacePath('Vendor\Package\\', $path);

        $this->assertClassIsInstantiable('\Vendor\Package\Existent');
    }

    #[Test]
    #[DataProvider('existentUnnestedClassFullyQualifiedNamesProvider')]
    public function properClassesCanBeLoaded(string $classFullyQualifiedName)
    {
        $path = $this->getFullFixturePath('/src');

        $this->autoloader->registerNamespacePath('Vendor\Package\\', $path);

        $this->assertClassIsInstantiable($classFullyQualifiedName);
    }

    #[Test]
    #[DataProvider('existentNestedClassFullyQualifiedNamesProvider')]
    public function properNestedClassesCanBeLoaded(string $classFullyQualifiedName)
    {
        $path = $this->getFullFixturePath('/src');

        $this->autoloader->registerNamespacePath('Vendor\Package\\', $path);

        $this->assertClassIsInstantiable($classFullyQualifiedName);
    }

    #[Test]
    public function classIsLoadedAccordingToSpecificNamespace()
    {
        $path = $this->getFullFixturePath('/src');

        $this->autoloader->registerNamespacePath('Vendor\Package\\', $path);

        $this->assertEquals('Unnested', \Vendor\Package\SomeClass::LABEL);
        $this->assertEquals('Nested', \Vendor\Package\Namespace\SomeClass::LABEL);
        $this->assertEquals('Subnested', \Vendor\Package\Namespace\Subnamespace\SomeClass::LABEL);
    }

    #[Test]
    public function classWithMoreSpecificNamespacePrefixIsLoaded()
    {
        $pathOne = $this->getFullFixturePath('/lib');
        $pathTwo = $this->getFullFixturePath('/src');
        $pathThree = $this->getFullFixturePath('/opt');

        $this->autoloader->registerNamespacePath('Vendor\\', $pathOne);
        $this->autoloader->registerNamespacePath('Vendor\Package\\', $pathTwo);
        $this->autoloader->registerNamespacePath('Vendor\\', $pathThree);

        $this->assertEquals('More Specific', \Vendor\Package\Namespace\Subnamespace\OtherClass::LABEL);
    }

    #[Test]
    public function classWithLessSpecificNamespacePrefixIsLoadedWhenPathWithMoreSpecificNamespaceIsNotFound()
    {
        $pathOne = $this->getFullFixturePath('/unexistent');
        $pathTwo = $this->getFullFixturePath('/unexistent');
        $pathThree = $this->getFullFixturePath('/opt');

        $this->autoloader->registerNamespacePath('Vendor\Package\\', $pathTwo);
        $this->autoloader->registerNamespacePath('Vendor\\', $pathThree);
        $this->autoloader->registerNamespacePath('Vendor\\', $pathOne);

        $this->assertEquals('Less Specific', \Vendor\Package\Namespace\Subnamespace\OtherClass::LABEL);
    }

    #[Test]
    public function properClassesFromVariousPathsAreLoaded()
    {
        $pathOne = $this->getFullFixturePath('/lib');
        $pathTwo = $this->getFullFixturePath('/src');
        $pathThree = $this->getFullFixturePath('/opt');

        $this->autoloader->registerNamespacePath('Vendor\Package\\', $pathOne);
        $this->autoloader->registerNamespacePath('Vendor\Package\\', $pathTwo);
        $this->autoloader->registerNamespacePath('Vendor\Package\\', $pathThree);

        $this->assertEquals('Two', \Vendor\Package\AnotherClass::LABEL);
        $this->assertEquals('Three', \Vendor\Package\Namespace\Subnamespace\AnotherClass::LABEL);
        $this->assertEquals('One', \Vendor\Package\Namespace\AnotherClass::LABEL);
    }

    #[Test]
    public function namespacePrefixWorksWithLeadingBackslash()
    {
        $path = $this->getFullFixturePath('/src');

        $this->autoloader->registerNamespacePath('\Vendor\Package', $path);

        $this->assertClassIsInstantiable('\Vendor\Package\Existent');
    }

    #[Test]
    public function namespacePrefixWorksWithTrailingBackslash()
    {
        $path = $this->getFullFixturePath('/src');

        $this->autoloader->registerNamespacePath('Vendor\Package\\', $path);

        $this->assertClassIsInstantiable('\Vendor\Package\Existent');
    }

    #[Test]
    public function namespacePrefixWorksWithLeadingAndTrailingBackslash()
    {
        $path = $this->getFullFixturePath('/src');

        $this->autoloader->registerNamespacePath('\Vendor\Package\\', $path);

        $this->assertClassIsInstantiable('\Vendor\Package\Existent');
    }

    #[Test]
    public function namespacePrefixWorksWithoutLeadingNorTrailingBackslash()
    {
        $path = $this->getFullFixturePath('/src');

        $this->autoloader->registerNamespacePath('Vendor\Package', $path);

        $this->assertClassIsInstantiable('\Vendor\Package\Existent');
    }

    #[Test]
    public function caseSensitivityIsEnforcedForLoadingClassName()
    {
        $path = $this->getFullFixturePath('/src');

        $this->autoloader->registerNamespacePath('Vendor\Package\\', $path);

        $this->assertClassDoesNotExist('\vendor\package\existent');
        $this->assertClassDoesNotExist('\Vendor\package\existent');
        $this->assertClassDoesNotExist('\Vendor\Package\existent');
        $this->assertClassIsInstantiable('\Vendor\Package\Existent');
    }

    public static function existentUnnestedClassFullyQualifiedNamesProvider(): array
    {
        return [
            ['\Vendor\Package\ExistentOne'],
            ['\Vendor\Package\ExistentTwo'],
            ['\Vendor\Package\ExistentThree'],
        ];
    }

    public static function existentNestedClassFullyQualifiedNamesProvider(): array
    {
        return [
            ['\Vendor\Package\Namespace\OneLevelNestedOne'],
            ['\Vendor\Package\Namespace\OneLevelNestedTwo'],
            ['\Vendor\Package\Namespace\OneLevelNestedThree'],
            ['\Vendor\Package\Namespace\Subnamespace\TwoLevelsNestedOne'],
            ['\Vendor\Package\Namespace\Subnamespace\TwoLevelsNestedTwo'],
            ['\Vendor\Package\Namespace\Subnamespace\TwoLevelsNestedThree'],
        ];
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
