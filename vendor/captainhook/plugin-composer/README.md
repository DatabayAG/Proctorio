# Composer-Plugin for [CaptainHook](https://github.com/captainhookphp/captainhook)

This is a composer-plugin that installs _CaptainHook_ and the corresponding git hooks. For more information visit its [Website](https://github.com/captainhookphp/captainhook).

[![Latest Stable Version](https://poser.pugx.org/captainhook/plugin-composer/v/stable.svg?v=1)](https://packagist.org/packages/captainhook/plugin-composer)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%207.1-8892BF.svg)](https://php.net/)
[![Downloads](https://img.shields.io/packagist/dt/captainhook/plugin-composer.svg?v1)](https://packagist.org/packages/captainhook/plugin-composer)
[![License](https://poser.pugx.org/captainhook/plugin-composer/license.svg?v=1)](https://packagist.org/packages/captainhook/plugin-composer)

## Installation:

As this is a composer-plugin the preferred method is to use composer for installation.
 
```bash
$ composer require --dev captainhook/plugin-composer
```

Everything else will happen automagically.

## Customize

You can set a custom name for your hook configuration and a custom path to your .git directory
if it is not located in the same directory as your *composer.json* file.
Just add these values to your *composer.json* in the *extra* section first.
```json
{
  "extra": {
    "captainhook-config": "hooks.json",
    "captainhook-git-dir": "../.git"
  }
  
}

```

If you want to see the installation in action have a look at this short installation video.

[![Install demo](http://img.youtube.com/vi/agwTZ0jhDDs/0.jpg)](https://www.youtube.com/watch?v=agwTZ0jhDDs)

## A word of warning

It is still possible to commit without invoking the hooks. 
So make sure you run appropriate backend-sanity checks on your code!
