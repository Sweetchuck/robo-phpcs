# Robo task wrapper for PHP_CodeSniffer

[![CircleCI](https://circleci.com/gh/Sweetchuck/robo-phpcs/tree/4.x.svg?style=svg)](https://circleci.com/gh/Sweetchuck/robo-phpcs/?branch=4.x)
[![codecov](https://codecov.io/gh/Sweetchuck/robo-phpcs/branch/4.x/graph/badge.svg?token=HPYQiBYRNG)](https://app.codecov.io/gh/Sweetchuck/robo-phpcs/branch/4.x)


## About

This package provides a Robo task wrapper for [PHP_CodeSniffer].
It integrates code style checking into your Robo task runner workflows,
allowing you to lint PHP files and parse PHPCS config XML as part
of your automated development processes.

The wrapper simplifies PHPCS integration by providing easy-to-use Robo tasks
that can be composed into complex build and quality assurance workflows.


## When to Use

Use this package when you:

- Are using [Robo] as your task runner
- Need to lint files on Git "pre-commit" hook.
- Prefer to manage code quality checks as composable Robo tasks


## Install

Run `composer require --dev sweetchuck/robo-phpcs`


## Usage

For basic usage, see [RoboFile.php](./RoboFile.php)


[PHP_CodeSniffer]: https://github.com/squizlabs/PHP_CodeSniffer
[Robo]: https://robo.li/
