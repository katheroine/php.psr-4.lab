<?php

/*
 * Copyright (C) 2026 Katarzyna Krasińska
 * PHP.PSR-4.lab - https://github.com/katheroine/php.psr-4.lab
 * Licensed under GPL-3.0 - see LICENSE.md
 */

declare(strict_types=1);

namespace PHPLab\StandardPSR4;

class Autoloader
{
    private const string AUTOLOADER_FUNCTION_NAME = 'loadClass';

    /**
     * Namespaces with assigned directory path.
     *
     * @var string[] | array[]
     */
    protected array $namespacePaths = [];

    /**
     * Actually processed namespaced class name.
     *
     * @var string
     */
    protected string $processedNamespacedClassName = '';

    /**
     * Register namespace and assign a directory path.
     *
     * @param string $namespace
     * @param string $path
     */
    public function registerNamespacePath(string $namespace, string $path): void
    {
        $this->namespacePaths[$namespace][] = $path;
    }

    /**
     * Register autoloading function.
     */
    public function register(): void
    {
        spl_autoload_register([$this, self::AUTOLOADER_FUNCTION_NAME], true);
    }

    /**
     * Unregister autoloading function.
     */
    public function unregister(): void
    {
        spl_autoload_unregister([$this, self::AUTOLOADER_FUNCTION_NAME]);
    }

    /**
     * Search for the class file path and load it.
     *
     * @param string $fullyQualifiedClassName
     *
     * @return boolean
     */
    public function loadClass(string $fullyQualifiedClassName): bool
    {
        $this->extractClassParameters($fullyQualifiedClassName);

        $classFilePath = $this->findClassFilePath();
        $classFileNotFound = is_null($classFilePath);

        if ($classFileNotFound) {
            return false;
        }

        require($classFilePath);

        return true;
    }

    /**
     * Extract class paramaters like namespace or class name
     * needed in file searching process
     * and assign their values to the strategy class variables.
     *
     * @param string $fullyQualifiedClassName
     */
    protected function extractClassParameters(string $fullyQualifiedClassName): void
    {
        // Removing '\' characters from the beginning of the fully qualified class name
        $this->processedNamespacedClassName = ltrim(
            string: $fullyQualifiedClassName,
            characters: '\\'
        );
    }

    /**
     * Find full path of the file that contains
     * the declaration of the automatically loaded class.
     *
     * @return string | null
     */
    protected function findClassFilePath(): ?string
    {
        foreach ($this->namespacePaths as $registeredNamespacePrefix => $registeredBaseDirPaths) {
            if (! $this->processedNamespacedClassNameContainsPrefix($registeredNamespacePrefix)) {
                continue;
            }

            foreach ($registeredBaseDirPaths as $registeredBaseDirPath) {
                $unprefixedNamespacedClassName =
                    $this->unprefixProcessedNamespacedClassName($registeredNamespacePrefix);

                $classFilePath = $this->buildClassFilePath($registeredBaseDirPath, $unprefixedNamespacedClassName);

                $classFileExists = is_file($classFilePath);

                if ($classFileExists) {
                    return $classFilePath;
                }
            }
        }

        return null;
    }

    private function processedNamespacedClassNameContainsPrefix(string $prefix): bool
    {
        $namespacePrefix = substr(
            string: $this->processedNamespacedClassName,
            offset: 0,
            length: strlen($prefix)
        );
        $processedNamespaceHasReisteredPrefix = ($prefix == $namespacePrefix);

        return $processedNamespaceHasReisteredPrefix;
    }

    private function unprefixProcessedNamespacedClassName(string $prefix): string
    {
        $unprefixedNamespacedClassName = substr(
            string: $this->processedNamespacedClassName,
            offset: strlen($prefix)
        );

        return $unprefixedNamespacedClassName;
    }

    private function buildClassFilePath(string $baseDirPath, string $namespacedClassName): string
    {
        $classFilePathWithinBaseDir = ltrim(
            string: str_replace(
                search: '\\',
                replace: DIRECTORY_SEPARATOR,
                subject: $namespacedClassName
            ),
            characters: '/'
        )
        . '.php';

        $classFilePath = $baseDirPath
            . DIRECTORY_SEPARATOR
            . $classFilePathWithinBaseDir;

        return $classFilePath;
    }
}
