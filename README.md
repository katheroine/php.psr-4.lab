# PHP.PSR-4.lab

Laboratory of PSR-4: Autoloading Standard.

> This repository is a standalone part of a larger project: **[PHP.lab](https://github.com/katheroine/php.lab)** — a curated knowledge base and laboratory for PHP engineering.

**Usage**

To run the example application with *Docker* use command:

```console
docker compose up -d
```

After creating the *Docker container* the *Composer dependencies* have to be defined and installed:

```console
docker compose exec application composer require --dev squizlabs/php_codesniffer --dev phpunit/phpunit \
&& docker compose exec application composer install
```

Tom make *PHP Code Sniffer commands* easily accessible run:

```console
docker compose exec application bash -c "
    ln -s /code/vendor/bin/phpcs /usr/local/bin/phpcs;
    ln -s /code/vendor/bin/phpcbf /usr/local/bin/phpcbf;
    ln -s /code/vendor/bin/phpunit /usr/local/bin/phpunit;
"
```

To run *PHP Code Sniffer* use command:

```console
docker compose exec application /var/www/vendor/bin/phpcs
```

or, if the shortcut has been created:

```console
docker compose exec application phpcs
```

To run *PHP Unit* use command:

```console
docker compose exec application /var/www/vendor/bin/phpunit
```

or, if the shortcut has been created:

```console
docker compose exec application phpunit
```

To update `Composer dependencies` use command (should be done before the command below):

```console
docker compose exec application composer update
```

To login into the *Docker container* use command:

```console
docker exec -it psr-4-example-app /bin/bash
```

**License**

This project is licensed under the GPL-3.0 - see [LICENSE](LICENSE).

**Official documentation**

[PHP-FIG PSR-4 Official documentation](https://www.php-fig.org/psr/psr-4/)

**What are PSRs**

[**PSR**](https://www.php-fig.org/psr/) stands for *PHP Standard Recommendation*.

## Overview

This PSR describes a specification for autoloading classes from file paths. It is fully interoperable, and can be used in addition to any other autoloading specification, including [PSR-0](https://www.php-fig.org/psr/psr-0). This PSR also describes where to place files that will be autoloaded according to the specification.

-- [PSR Documentation](https://www.php-fig.org/psr/psr-4/#1-overview)

## Specification

The term "class" refers to *classes*, *interfaces*, *traits*, and other similar structures.

### Fully qualified class name form

A *fully qualified class name* has the following form:

```
\<NamespaceName>(\<SubNamespaceNames>)*\<ClassName>
```

-- [PSR Documentation](https://www.php-fig.org/psr/psr-4/#2-specification)

Fully qualified class name meets the following conditions:

1. The *fully qualified class name* **MUST have a top-level namespace name**, also known as a *vendor namespace*.
[🔗](https://www.php-fig.org/psr/psr-4/#2-specification)

* Valid - has vendor namespace `SomeVendor`

```php
namespace SomeVendor;
class SomeClass {}
```

* Invalid - no vendor namespace, bare class

```php
class SomeClass {}
```

2. The *fully qualified class name* **MAY have one or more sub-namespace names**.
[🔗](https://www.php-fig.org/psr/psr-4/#2-specification)

* Zero sub-namespaces (just vendor + class) - valid

```php
namespace SomeVendor;
class SomeClass {}
```

* One sub-namespace - valid

```php
namespace SomeVendor\SomeName;
class SomeClass {}
```

* Multiple sub-namespaces - valid

```php
namespace SomeVendor\Some\More\Names;
class SomeClass {}
```

3. The *fully qualified class name* **MUST have a terminating class name**.
[🔗](https://www.php-fig.org/psr/psr-4/#2-specification)

* Valid - `SomeClass` is the terminating class name

```php
namespace SomeVendor\SomeName;
class SomeClass {}

$someObject = new \SomeVendor\SomeName\SomeClass();
```

* Invalid - the namespace alone (`\SomeVendor\SomeName`) is never enough

```php
namespace SomeVendor\SomeName;
class SomeClass {}

$someObject = new \SomeVendor\SomeName;
```

4. **Underscores have no special meaning** in any portion of the *fully qualified class name*.
[🔗](https://www.php-fig.org/psr/psr-4/#2-specification)

* In PSR-0, underscores were converted to directory separators.

* In PSR-4, they are just ordinary characters.

```php
namespace SomeVendor\SomePackage;
class Some_Class {} // \SomeVendor\SomePackage\Some_Class
class Other_Underscored_Class {} // \SomeVendor\SomePackage\Other_Underscored_Class
```

5. Alphabetic characters in the *fully qualified class name* **MAY be any combination of lower case and upper case**.
[🔗](https://www.php-fig.org/psr/psr-4/#2-specification)

All of these are syntactically valid class names under PSR-4:

```php
class restclient {} // all lower case - fine
class RESTCLIENT {} // all upper case - fine
class RESTClient {} // mixed case (pascal/camel with uppercase acronym) - fine
class restClient {} // camelCase - fine
class RestClient {} // PascalCase - fine (and conventional)
```

Note:
* `class RESTClient {}` and `class RestClient {}` are *PSR-1/PSR-12* compliant
* `class RestClient {}` is both *PSR-1/PSR-12* and *PER 3.0* compliant

6. All class names **MUST be referenced in a case-sensitive fashion**.
[🔗](https://www.php-fig.org/psr/psr-4/#2-specification)

* Valid - correct reference

```php
namespace SomeVendor\SomePackage;
class SomeClass {}

$someObject = new \SomeVendor\SomePackage\SomeClass();
```

* Invalid - can fail on case-sensitive filesystems

```php
namespace SomeVendor\SomePackage;
class SomeClass {}

$someObject = new \somevendor\somepackage\someclass();
```

### Loading a file corresponding to fully qualified class name

When loading a file that corresponds to a *fully qualified class name*:

1. A contiguous series of one or more *leading namespace* and *sub-namespace* names, not including the *leading namespace separator*, in the *fully qualified class name* (a *namespace prefix*) corresponds to at least one *base directory*.
[🔗](https://www.php-fig.org/psr/psr-4/#2-specification)

**Composer example configuration in `composer.json` file**

```json
"autoload": {
    "psr-4": {
        "SomeVendor\\"                  : "src/",
        "SomeVendor\\SomePackage\\"     : "src/",
        "SomeVendor\\OtherPackage\\"    : [
            "src/otherpackage",
            "lib/otherpackage"
        ],
        "OtherVendor\\AnotherPackage\\" : "packages/anotherpackage/src/"
    }
},
"autoload-dev": {
    "psr-4": {
        "SomeVendor\\SomePackage\\"     : "tests/"
    }
}
```

**Imortant**:
* More specific prefixes always take priority over broader ones.
* If the same class name exists in both `src/` and `tests/unit_tests/`, the `dev` entry wins during `development`, which could hide bugs.

**Result rules**:

```
Namespace prefix                                       →   Base directories
─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────

SomeVendor\                                            →   src/

SomeVendor\SomePackage\                                →   src/
                                                       →   tests/

SomeVendor\OtherPackage\                               →   src/otherpackage/
                                                       →   lib/otherpackage/

OtherVendor\AnotherPackage\                            →   packages/anotherpackage/src/

```

**Result examples**:

```
Namespace prefix                                       →   Base directories
─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────

SomeVendor\AnyClass                                    →   src/AnyClass.php

SomeVendor\SomePackage\SomeClass                       →   src/SomeClass.php (checked first)
                                                       →   tests/SomeClass.php (chacked if still not found)

SomeVendor\OtherPackage\SomeClass                      →   src/otherpackage/SomeClass.php (checked first)
                                                       →   lib/otherpackage/SomeClass.php (chacked if still not found)

OtherVendor\AnotherPackage\SomeClass                   →   packages/anotherpackage/src/SomeClass.php

```

2. The contiguous *sub-namespace* names after the *namespace prefix* correspond to a subdirectory within a *base directory*, in which the *namespace separators* represent *directory separators*. The subdirectory name MUST match the case of the sub-namespace names.
[🔗](https://www.php-fig.org/psr/psr-4/#2-specification)

**Composer example configuration in `composer.json` file**

```json
"autoload": {
    "psr-4": {
        "SomeVendor\\SomePackage\\"     : "src/",
        "SomeVendor\\OtherPackage\\"    : [
            "src/otherpackage",
            "lib/otherpackage"
        ],
        "OtherVendor\\AnotherPackage\\" : "packages/anotherpackage/src/"
    }
},
```

**Result rules**:

```
Namespace prefix                                       →   Base directories
─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────

SomeVendor\SomePackage\                                →   src/

SomeVendor\OtherPackage\                               →   src/otherpackage/
                                                       →   lib/otherpackage/

OtherVendor\AnotherPackage\                            →   packages/anotherpackage/src/

```

**Result examples**:

```
Namespace prefix                                       →   Base directories
─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────

SomeVendor\SomePackage\SomeClass                       →   src/SomeClass.php
SomeVendor\SomePackage\SomeName\SomeClass              →   src/SomeName/SomeClass.php
SomeVendor\SomePackage\SomeName\OtherName\SomeClass    →   src/SomeName/OtherName/SomeClass.php

SomeVendor\OtherPackage\SomeClass                      →   src/otherpackage/SomeClass.php (checked first)
                                                       →   lib/otherpackage/SomeClass.php (chacked if still not found)
SomeVendor\OtherPackage\SomeName\SomeClass             →   src/otherpackage/SomeName/SomeClass.php (checked first)
                                                       →   lib/otherpackage/SomeName/SomeClass.php (checked if still not found)

OtherVendor\AnotherPackage\SomeClass                   →   packages/anotherpackage/src/SomeClass.php
OtherVendor\AnotherPackage\SomeName\SomeClass          →   packages/anotherpackage/src/SomeName/SomeClass.php
OtherVendor\AnotherPackage\SomeNam\OtherName\SomeClass →   packages/anotherpackage/src/SomeName/OtherName/SomeClass.php

```

3. The terminating class name corresponds to a file name ending in `.php`. The file name MUST match the case of the terminating class name.
[🔗](https://www.php-fig.org/psr/psr-4/#2-specification)

**Composer example configuration in `composer.json` file**

```json
"autoload": {
    "psr-4": {
        "SomeVendor\\SomePackage\\"     : "src/",
    }
},
```

**Result rules**:

```
Namespace prefix                                       →   Base directories
─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────

SomeVendor\SomePackage\                                →   src/

```

Result examples:

```
Namespace prefix                                       →   Base directories
─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────

SomeVendor\SomePackage\SomeClass                       →   src/SomeClass.php
SomeVendor\SomePackage\SomeName\SomeClass              →   src/SomeName/SomeClass.php
SomeVendor\SomePackage\SomeName\OtherName\SomeClass    →   src/SomeName/OtherName/SomeClass.php

```

#### All cases example

**Composer example configuration in `composer.json` file**

```json
"autoload": {
    "psr-4": {
        "SomeVendor\\"                  : "src/",
        "SomeVendor\\SomePackage\\"     : "src/",
        "SomeVendor\\OtherPackage\\"    : [
            "src/otherpackage",
            "lib/otherpackage"
        ],
        "OtherVendor\\AnotherPackage\\" : "packages/anotherpackage/src/"
    }
},
"autoload-dev": {
    "psr-4": {
        "SomeVendor\\SomePackage\\"     : "tests/unit_tests/"
    }
}
```

**Result rules**:

```
Namespace prefix                                       →   Base directories
─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────

SomeVendor\                                            →   src/

SomeVendor\SomePackage\                                →   src/
                                                       →   tests/

SomeVendor\OtherPackage\                               →   src/otherpackage/
                                                       →   lib/otherpackage/

OtherVendor\AnotherPackage\                            →   packages/anotherpackage/src/

```

**Result examples**:

```
Namespace prefix                                       →   Base directories
──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────

SomeVendor\AnyClass                                    →   src/AnyClass.php
SomeVendor\SomeName\SomeClass                          →   src/SomeName/SomeClass.php

SomeVendor\SomePackage\SomeClass                       →   src/SomeClass.php (checked first)
                                                       →   tests/SomeClass.php (chacked if still not found)
SomeVendor\SomePackage\SomeName\SomeClass              →   src/SomeName/SomeClass.php (checked first)
                                                       →   tests/SomeName/SomeClass.php (chacked if still not found)
SomeVendor\SomePackage\SomeName\OtherName\SomeClass    →   src/SomeName/OtherName/SomeClass.php (checked first)
                                                       →   tests/SomeName/OtherName/SomeClass.php (chacked if still not found)

SomeVendor\OtherPackage\SomeClass                      →   src/otherpackage/SomeClass.php (checked first)
                                                       →   lib/otherpackage/SomeClass.php (chacked if still not found)
SomeVendor\OtherPackage\SomeName\SomeClass             →   src/otherpackage/SomeName/SomeClass.php (checked first)
                                                       →   lib/otherpackage/SomeName/SomeClass.php (checked if still not found)

OtherVendor\AnotherPackage\SomeClass                   →   packages/anotherpackage/src/SomeClass.php
OtherVendor\AnotherPackage\SomeName\SomeClass          →   packages/anotherpackage/src/SomeName/SomeClass.php
OtherVendor\AnotherPackage\SomeNam\OtherName\SomeClass →   packages/anotherpackage/src/SomeName/OtherName/SomeClass.php

```

### Autoloader implementations

Autoloader implementations **MUST NOT throw exceptions**, **MUST NOT raise errors** of any level, and **SHOULD NOT return a value**.

-- [PSR Documentation](https://www.php-fig.org/psr/psr-4/#2-specification)

### Decomposition examples

The table below shows the corresponding file path for a given fully qualified class name, namespace prefix, and base directory.

| Fully Qualified Class Name | Namespace Prefix | Base Directory | Resulting File Path |
|---|---|---|---|
| `\Acme\Log\Writer\File_Writer` | `Acme\Log\Writer` | `./acme-log-writer/lib/` | `./acme-log-writer/lib/File_Writer.php` |
| `\Aura\Web\Response\Status` | `Aura\Web` | `/path/to/aura-web/src/` | `/path/to/aura-web/src/Response/Status.php` |
| `\Symfony\Core\Request` | `Symfony\Core` | `./vendor/Symfony/Core/` | `./vendor/Symfony/Core/Request.php` |
| `\Zend\Acl` | `Zend` | `/usr/includes/Zend/` | `/usr/includes/Zend/Acl.php` |

For example implementations of autoloaders conforming to the specification, please see the [examples file](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader-examples.md). Example implementations MUST NOT be regarded as part of the specification and MAY change at any time.

-- [PSR Documentation](https://www.php-fig.org/psr/psr-4/#3-examples)
