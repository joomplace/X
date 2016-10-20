# JooYii
[`JooYii`](https://joomplace.github.io/JooYii/) Framework for Joomla!
## Get Started
Autoload simply by
```PHP
jimport('JooYii.autoloader',JPATH_LIBRARIES.DS);
```

### Code generator
Code generation extension is almost ready at https://github.com/joomplace/JooGii

### Extentions examples
See example component at [here](https://github.com/alex7r/com_jyexample)

### Quick usage example
#### Front-end
File `/site/example.php` contents:
```PHP
namespace Joomplace\Example\Site;

defined('_JEXEC') or die;

jimport('JooYii.autoloader',JPATH_LIBRARIES);

$component = new Component();
$component->execute();
```
To allow menu items creation place `metadata.xml` into specific view folder

File `/site/view/examples/metadata.xml` can look like this
```XML
<?xml version="1.0" encoding="utf-8"?>
<metadata>
	<view>
		<options var="additional_request_var">
			<default name="COM_EXAMPLE" msg="COM_EXAMPLE_DESC"/>
			<option name="COM_EXAMPLE_EXT" msg="COM_EXAMPLE_EXT_DESC" value="additional_request_var_value" />
		</options>
	</view>
</metadata>
```
To add routing simple add `router.php` into your site folder
It`s content would be something like this:
 ```PHP
 namespace Joomplace\Example\Site;
 
 use Joomplace\Library\JooYii\Router as BaseRouter;
 
 defined('_JEXEC') or die;
 
 jimport('JooYii.autoloader',JPATH_LIBRARIES);
 
 class Router extends BaseRouter
 {
 	protected function setNamespace()
 	{
 		$this->_namespace = __NAMESPACE__;
 	}
 
 }
 ```
