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
    private const string NAMESPACE_SEPARATOR = '\\';
    private const string CLASS_FILE_EXTENSION = '.php';

    /**
     * Namespace prefixes with assigned directory paths.
     *
     * As in PSR-4 documentation (https://www.php-fig.org/psr/psr-4/#2-specification):
     * A contiguous series of one or more *leading namespace* and *sub-namespace* names,
     * not including the *leading namespace separator*, in the *fully qualified class name*
     * (a *namespace prefix*) corresponds to at least one *base directory*.
     *
     * @var string[] | array[]
     */
    protected array $registeredNamespacePrefixesWithPaths = [];

    /**
     * Register namespace and assign a directory path.
     *
     * @param string $namespace
     * @param string $path
     */
    public function registerNamespacePath(string $namespacePrefix, string $path): void
    {
        $normalisedNamespacePrefix = trim($namespacePrefix, self::NAMESPACE_SEPARATOR)
            . self::NAMESPACE_SEPARATOR;

        $this->registeredNamespacePrefixesWithPaths[$normalisedNamespacePrefix][] = $path;
    }

    /**
     * Register autoloading function.
     */
    public function register(): void
    {
        spl_autoload_register($this->loadClass(...), true);
    }

    /**
     * Unregister autoloading function.
     */
    public function unregister(): void
    {
        spl_autoload_unregister($this->loadClass(...));
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
        $classFilePath = $this->findClassFilePath($fullyQualifiedClassName);
        $classFileNotFound = is_null($classFilePath);

        if ($classFileNotFound) {
            return false;
        }

        require($classFilePath);

        return true;
    }

    /**
     * Find full path of the file that contains
     * the declaration of the automatically loaded class.
     *
     * @return string | null
     */
    protected function findClassFilePath(string $processedNamespacedClassName): ?string
    {
        $reorderedMatchingRegisteredNamespacePrefixesWithPaths = $this->reorderMatchingRegisteredNamespacePrefixesWithPaths(
            $this->findMatchingRegisteredNamespacePrefixesWithPaths($processedNamespacedClassName)
        );

        foreach($reorderedMatchingRegisteredNamespacePrefixesWithPaths as $matchingRegisteredNamespacePrefix => $matchingRegisteredPaths) {
            $unprefixedProcessedNamespacedClassName = $this->unprefixNamespacedClassName(
                $processedNamespacedClassName,
                $matchingRegisteredNamespacePrefix
            );

            foreach ($matchingRegisteredPaths as $registeredPath) {
                $classFilePath = $this->buildClassFilePath(
                    $registeredPath,
                    $unprefixedProcessedNamespacedClassName
                );

                $classFileExists = is_file($classFilePath);

                if ($classFileExists) {
                    return $classFilePath;
                }
            }
        }

        return null;
    }

    private function reorderMatchingRegisteredNamespacePrefixesWithPaths(array $matchingRegisteredNamespacePrefixesWithPaths): array
    {
        uksort($matchingRegisteredNamespacePrefixesWithPaths, fn($a, $b) =>
            $this->countNamespacePrefixSegments($b) <=> $this->countNamespacePrefixSegments($a)
        );

        return $matchingRegisteredNamespacePrefixesWithPaths;
    }

    private function findMatchingRegisteredNamespacePrefixesWithPaths(string $processedNamespacedClassName): array
    {
        $matchingRegisteredNamespacePrefixesWithPaths = [];

        foreach ($this->registeredNamespacePrefixesWithPaths as $registeredNamespacePrefix => $registeredPaths) {
            if (! $this->namespacedClassNameContainsNamespacePrefix(
                $processedNamespacedClassName,
                $registeredNamespacePrefix
            )) {
                continue;
            }

            $matchingRegisteredNamespacePrefixesWithPaths[$registeredNamespacePrefix] = $registeredPaths;
        }

        return $matchingRegisteredNamespacePrefixesWithPaths;
    }

    private function countNamespacePrefixSegments(string $namespacePrefix): int
    {
        return count(
            explode(
                separator: self::NAMESPACE_SEPARATOR,
                string: trim(
                    string: $namespacePrefix,
                    characters: self::NAMESPACE_SEPARATOR
                )
            )
        );
    }

    private function namespacedClassNameContainsNamespacePrefix(
        string $namespacedClassName,
        string $namespacePrefix
    ): bool {
        $extractedNamespacePrefix = substr(
            string: $namespacedClassName,
            offset: 0,
            length: strlen($namespacePrefix)
        );
        $namespacedClassNameContainsNamespacePrefix = ($namespacePrefix == $extractedNamespacePrefix);

        return $namespacedClassNameContainsNamespacePrefix;
    }

    private function unprefixNamespacedClassName(
        string $namespacedClassName,
        string $namespacePrefix): string
    {
        $unprefixedNamespacedClassName = substr(
            string: $namespacedClassName,
            offset: strlen($namespacePrefix)
        );

        return $unprefixedNamespacedClassName;
    }

    private function buildClassFilePath(
        string $baseDirPath,
        string $namespacedClassName
    ): string {
        $classFilePathWithinBaseDir = ltrim(
            string: str_replace(
                search: self::NAMESPACE_SEPARATOR,
                replace: DIRECTORY_SEPARATOR,
                subject: $namespacedClassName
            ),
            characters: DIRECTORY_SEPARATOR
        )
        . self::CLASS_FILE_EXTENSION;

        $classFilePath = $baseDirPath
            . DIRECTORY_SEPARATOR
            . $classFilePathWithinBaseDir;

        return $classFilePath;
    }
}
