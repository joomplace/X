# JoomPlaceX
State of art library for Joomla! CMS

## Description
### That's not a framework
That's not a yet another framework, because it simply do not FRAME you work, it gives you power to do great.

## Installation
### Manual installation
Either download or clone the repo to the `/libraries/JoomPlaceX` and install [autoloader plugin](https://github.com/joomplace/Xautoloader).

Navigate to the `/libraries/JoomPlaceX` folder and do the `composer install` [`composer update`]
### Package installation [comming soon]
Use the link to our CI packaged version which includes both library (ready to use) and autoloader plugin

## What's in it
### 3rd Party [use original documentation of those]
ORM:
- Eloquent
- Doctrine

Templating:
- Blade
- Edge

### Maintained be JoomPlace
Joomla PSR-4 Autoloader

Base MVC:
- Model
- View
- Controller

Controller additions:
- Traits for RESTful API
- Traits for Input injection

View additions:
- Trait for Blade render
- Trait for JSON [API] render
