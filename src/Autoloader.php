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
    /**
     * Register autoloading function.
     */
    public function register(): void
    {
        require(__DIR__ . '/../tests/fixtures/src/Existent.php');
    }

    /**
     * Unregister autoloading function.
     */
    public function unregister(): void
    {
    }

    /**
     * Register namespace and assign a directory path.
     *
     * @param string $namespace
     * @param string $path
     */
    public function registerNamespacePath(string $namespacePrefix, string $path): void
    {
    }
}
