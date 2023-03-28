[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%208.1-8892BF)](https://php.net/)
[![PHPStan](https://img.shields.io/badge/phpstan-enabled-4BC51D)](https://www.phpstan.com/)
[![codecov](https://codecov.io/gh/123inkt/jbdiff/branch/main/graph/badge.svg)](https://app.codecov.io/gh/123inkt/jbdiff)
[![Build Status](https://github.com/123inkt/jbdiff/actions/workflows/test.yml/badge.svg?branch=main)](https://github.com/123inkt/jbdiff/actions)


# JBDiff

A multi-line diff calculation library based on Jetbrains' powerful IDE diff implementation.<br>
https://github.com/JetBrains/intellij-community/tree/master/platform/util/diff/src/com/intellij/diff

## Installation
```bash
composer require digitalrevolution/jbdiff
```

## Usage


`$textBefore`:
```php
switch ($strategy) {
    case RateLimiterConfig::FIXED_WINDOW:
        return new FixedWindow($this->redisService->getConnection(), $config);
    case RateLimiterConfig::SLIDING_WINDOW:
        return new SlidingWindow($this->redisService->getConnection(), $config);
    default:
        throw new RuntimeException('Invalid Strategy name.', RuntimeException::UNKNOWN);
}
```
`$textAfter`:
```php
return match ($strategy) {
    RateLimiterConfig::FIXED_WINDOW   => new FixedWindow($this->redisService->getConnection(), $config),
    RateLimiterConfig::SLIDING_WINDOW => new SlidingWindow($this->redisService->getConnection(), $config),
    default                           => throw new RuntimeException('Invalid Strategy name.'),
};
```

To create the diff:
```php
use DR\JBDiff\ComparisonPolicy;
use DR\JBDiff\JBDiff;

// line block will contain all the information to partition the strings in removed, unchanged and added parts.
$lineBlocks = (new JBDiff())->compare($textBefore, $textAfter, ComparisonPolicy::DEFAULT);

// to iterate over the string parts
$iterator = new LineBlockTextIterator($textBefore, $textAfter, $lineBlocks);
```

`$iterator` formatted to html:
![docs/example-default.png](docs/example-default.png)

with `ComparisonPolicy::IGNORE_WHITESPACES`
![docs/example-ignore-whitespace.png](docs/example-ignore-whitespace.png)

### Comparison policies
- `DEFAULT`: the standard diff strategy and will take whitespace differences into account.
- `TRIM_WHITESPACES`: will take leading and trailing whitespaces out of the diff.
- `IGNORE_WHITESPACES`: will take all whitespace differences out of the diff.

## Example
To run the example page, start
```shell
composer run example
```
The page will be available at `http://localhost:8000/`

![docs/example-example.png](docs/example-example.png)

## About us

At 123inkt (Part of Digital Revolution B.V.), every day more than 50 development professionals are working on improving our internal ERP 
and our several shops. Do you want to join us? [We are looking for developers](https://www.werkenbij123inkt.nl/zoek-op-afdeling/it).
