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
File `/admin/example.php` contents:
```PHP
namespace Joomplace\Example\Admin;

defined('_JEXEC') or die;
define('DS',DIRECTORY_SEPARATOR);

jimport('jooyii.autoloader',JPATH_LIBRARIES.DS);

$component = new Component();
$component->execute();
```

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
