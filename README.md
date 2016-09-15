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
File `/admin/component.php` contents:
```PHP
namespace Joomplace\Example\Admin;

defined('_JEXEC') or die;

class Component extends \Joomplace\Library\JooYii\Component{

	/**
	 * Execute the controller.
	 *
	 * @return  boolean  True if controller finished execution, false if the controller did not
	 *                   finish execution. A controller might return false if some precondition for
	 *                   the controller to run has not been satisfied.
	 *
	 * @since   12.1
	 * @throws  LogicException
	 * @throws  RuntimeException
	 */
	public function execute(){

		$app = $this->getApplication();
    $gconfig = \JFactory::getConfig();
		$input = $this->getInput();
    $cconfig = \JComponentHelper::getParams($input->get('option'));
		$namespace = 'Joomplace\\Example\\Admin';

		$controller = $input->getString('controller','list');
    $task = explode('.',$input->getString('task','index'));
    $action = $task[0];
		$input->set('view',$input->getString('view','list'));

		$controllerClass     = $this->getController($namespace.'\\Controller\\'.$controller);

    \Joomplace\Library\JooYii\Helper::callBindedFunction($controllerClass,$action,array($input,$cconfig,$gconfig));

		return true;
	}

}
```
And from now on it's pretty done deal!

Next files structure and content will look like this:
Controller `/admin/controller/examples.php`:
```PHP
namespace Joomplace\Example\Admin\Controller;

use Joomplace\Library\JooYii\Controller as Controller;

defined('_JEXEC') or die;

class Examples extends Controller
{

  public function index($limit = false, $limitstart = 0){
      $model = $this->getModel();
      $items = $model->getList($limitstart,$limit);
      $pagination = $model->getPagination();
      $vars = array(
        'items' => $items,
        'pagination' => $pagination,
    );
    echo $this->render('examples',$vars);
  }

}
```
Model `/admin/model/examples.php`:
```PHP
namespace Joomplace\Example\Admin\Model;

use Joomplace\Library\JooYii\Model as Model;

defined('_JEXEC') or die;

class Examples extends Model
{
    protected $_table = '#__examples';
    protected $_primary_key = 'id';
    protected $_field_defenitions = array(
      'id' => array(
        'mysql_type' => 'int(10) unsigned',
        'type' => 'hidden',
        'filter' => 'integer',
        'group' => '',
        'fieldset' => 'basic',
        'class' => '',
        'read_only' => null,
        'nullable' => false,
        'default' => null,
        'extra' => 'auto_increment',
      ),
      'asset_id' => array(
        'mysql_type' => 'int(10) unsigned',
        'type' => 'hidden',
        'filter' => 'unset',
        'group' => '',
        'fieldset' => 'basic',
        'class' => '',
        'read_only' => null,
        'nullable' => false,
        'default' => 0,
      ),
      'text' => array(
        'mysql_type' => 'text',
        'type' => 'editor',
        'filter' => 'safehtml',
        'group' => '',
        'fieldset' => 'basic',
        'class' => '',
        'read_only' => null,
        'nullable' => false,
        'default' => null,
        'required' => true,
      ),
      'published' => array(
        'mysql_type' => 'int(1) unsigned',
        'type' => 'radio',
        'class' => 'btn-group',
        'nullable' => false,
        'default' => 0,
        'option' => array(
          0 => 'Unpublished',
          1 => 'Published',
        )
      ),
      'user_id' => array(
        'mysql_type' => 'int(10) unsigned',
        'type' => 'user',
        'group' => '',
        'fieldset' => 'basic',
        'class' => '',
        'read_only' => null,
        'nullable' => false,
        'default' => 0,
      ),
      'ordering' => array(
        'mysql_type' => 'int(10) unsigned',
        'type' => 'hidden',
        'read_only' => null,
        'nullable' => false,
        'default' => 1,
      ),
  );
}
```
And view templates at
`/admin/view/examples/default.php`
```PHP
defined('_JEXEC') or die;
?>
<form id="adminForm" name="adminForm" class="adminForm" method="POST">
<?php
/** @var \Joomplace\Library\JooYii\View  $this */
$this->display('_table');
?>
    <input type="hidden" name="option" value="com_example">
    <input type="hidden" name="controller" value="examples">
    <input type="hidden" name="task" value="index">
    <input type="hidden" name="boxchecked" value="">
</form>
```
And
`/admin/view/examples/default_table.php`
```PHP
/** @var \Joomplace\Library\JooYii\View  $this */
$rows = $this->items;
$columns = $rows[0]->getColumns();
/** @var \JPagination $pagination */
$pagination = $this->pagination;
?>
<table class="table table-bordered table-striped">
  <thead>
    <tr>
      <th width="1%" class="nowrap center hidden-phone">
        <?php echo JHtml::_('searchtools.sort', '', 'ordering', 'ASC', 'ordering', null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
      </th>
      <th width="1%" class="center">
        <?php echo JHtml::_('grid.checkall'); ?>
      </th>
      <?php
      foreach ($columns as $column){
        ?>
        <th>
        <?php
          echo JText::_('TABLE_LIST_HEAD_'.strtoupper($column));
        ?>
        </th>
        <?php
      }
      ?>
      </tr>
  </thead>
  <tbody>
  <?php
  foreach ($rows as $row){
    /** @var \Joomplace\Testimonials\Admin\Model\Testimonials  $row */
    ?>
    <tr>
      <td class="order nowrap center hidden-phone">
        <span class="sortable-handler">
          <span class="icon-menu"></span>
        </span>
        <input type="text" style="display:none" name="order[]" size="5" value="<?php echo $row->ordering; ?>" class="width-20 text-area-order " />
      </td>
      <td class="center">
        <?php echo JHtml::_('grid.id', $row->id, $row->id); ?>
      </td>
      <?php
      foreach ($columns as $column){
        ?>
        <td>
        <?php
          echo $row->renderListControl($column);
        ?>
        </td>
        <?php
      }
      ?>
      </tr>
    <?php
  }
  ?>
  </tbody>
  <tfoot>
    <tr>
      <td colspan="2">
        <?php echo $pagination->getLimitBox(); ?>
      </td>
      <td class="text-right" colspan="<?php echo count($columns); ?>">
        <?php echo $pagination->getListFooter(); ?>
      </td>
    </tr>
  </tfoot>
</table>
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
