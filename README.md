# Quick Router for PHP

[![Build Status](https://jenkins.matthewwendel.info/job/quick-router/job/main/badge/icon)](https://jenkins.matthewwendel.info/job/quick-router/job/main/)

A quick & easy way to start developing an API / web service application using PHP & Apache.

## Requirements
  - PHP (7.3 or later)
  - Apache
    - Rewrite extension enabled
    - `.htaccess` files enabled.



## Installation
This package can be installed with `composer`:
```
composer require ooobii/quick-router
```

**Heads Up!** This composer package will automatically write an `.htaccess` file to the root of your project directory to ensure
that requests are always directed to the `index.php` file. If an `.htaccess` file already exists, this package will append the required
rules to the end of the file. This update/creation is triggered on every `composer require` or `composer update` execution.



### Usage
See `index.php` for examples on how to use this library.