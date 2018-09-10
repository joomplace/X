# JoomPlaceX
State of art library for Joomla! CMS

## Description
### That's not a framework
That's not a yet another framework, because it simply do not FRAME you work, it gives you power to do great.

## Installation
### Manual installation
Either download or clone the repo to the `/libraries/JoomPlaceX` and install [autoloader plugin](https://github.com/joomplace/Xautoloader).

Navigate to the `/libraries/JoomPlaceX` folder and do the `composer install` [`composer update`]
### Package installation
Use [the link to our CI packaged version](https://github.com/joomplace/X/releases/download/0.10.3/package.zip) which includes both library (ready to use) and autoloader plugin.

You still will need to enable autoloader plugin manually.

## What's in it
### 3rd Party [use original documentation of those]
ORM:
- Eloquent
- Doctrine

Templating:
- Blade
- Edge

### Maintained be JoomPlace
[Joomla PSR-4 Autoloader](https://github.com/joomplace/X/blob/laravel-it/Loader.php)

[Component Bootstraper](https://github.com/joomplace/X/blob/laravel-it/ComponentStarter.php)

[Dispatcher](https://github.com/joomplace/X/blob/laravel-it/Dispatcher.php) with [Migrations](https://github.com/joomplace/X/blob/laravel-it/Helper/Migrations.php)

Base MVC:
- [Model](https://github.com/joomplace/X/blob/laravel-it/Model.php)
- [View](https://github.com/joomplace/X/blob/laravel-it/View.php)
- [Controller](https://github.com/joomplace/X/blob/laravel-it/Controller.php)
- [Controller for JSON API](https://github.com/joomplace/X/blob/laravel-it/ControllerAPI.php)

Controller additions:
- Trait for [RESTful](https://github.com/joomplace/X/blob/laravel-it/Helper/Restful.php)
- Trait for [Input injection](https://github.com/joomplace/X/blob/laravel-it/Helper/Injector.php)

View additions:
- Trait for Blade [renderer](https://github.com/joomplace/X/blob/laravel-it/Renderer/Edge.php)
- Trait for JSON [renderer](https://github.com/joomplace/X/blob/laravel-it/Renderer/Api.php)
- Trait for PlainPHP [renderer](https://github.com/joomplace/X/blob/laravel-it/Renderer/PlainPHP.php)

Model additions:
- Basic ACL [trait](https://github.com/joomplace/X/blob/laravel-it/Helper/ACL/Base.php)
- Dummy ACL [trait](https://github.com/joomplace/X/blob/laravel-it/Helper/ACL/Dummy.php)
- *IN DEVELOPMENT* Core ACL trait
