# JooYii
JooYii Framework for Joomla!
## Get Started
Autoload simply by
```PHP
jimport('jooyii.autoloader',JPATH_LIBRARIES.DS);
```

### Extentions structure
Let's  check it out on `com_example`:

#### Back-end
[`/admin/example.php`](https://github.com/joomplace/JooYii/wiki/_com_example-admin-example_php)

[`/admin/component.php`](https://github.com/joomplace/JooYii/wiki/_com_example-admin-component_php)

[`/admin/controller/examples.php`](https://github.com/joomplace/JooYii/wiki/_com_example-admin-controller-example_php)

[`/admin/model/examples.php`](https://github.com/joomplace/JooYii/wiki/_com_example-admin-model-example_php)

[`/admin/view/examples/default.php`](https://github.com/joomplace/JooYii/wiki/_com_example-admin-views-examples-default_php)

[`/admin/view/examples/default_table.php`](https://github.com/joomplace/JooYii/wiki/_com_example-admin-views-examples-default-table_php)

#### Front-end
File `/site/example.php` contents:
```PHP
namespace Joomplace\Example\Site;

defined('_JEXEC') or die;
define('DS',DIRECTORY_SEPARATOR);

jimport('jooyii.autoloader',JPATH_LIBRARIES.DS);

$component = new Component();
$component->execute();
```
