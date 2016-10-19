<?php
/**
 * @package     Joomplace\Library\JooYii
 *
 * @copyright   Alexandr Kosarev
 * @license     GPL2
 */

namespace Joomplace\Library\JooYii;

$php_min_version = '5.6.0';
if(version_compare(PHP_VERSION, $php_min_version, '<')){
	$error_php_message = 'You need to have PHP version %s + for JooYii Router to work correctly.';
	$error_php_message.= '<br>Caused by `array_filter` flag `ARRAY_FILTER_USE_BOTH`';
	\JFactory::getApplication()->enqueueMessage(sprintf($error_php_message,$php_min_version),'error');
}

/**
 * Base Router class for using with JooYii based extensions
 *
 * Convention for default parsing and building is
 * component/COMPONENTNAME/controller/task/
 * everything else is
 * var/value
 *
 * @package     Joomplace\Library\JooYii
 *
 * @since       1.0
 */
abstract class Router extends \JComponentRouterBase
{
	protected $_namespace;

	/**
	 * Router constructor override to force namespace decalration
	 *
	 * @param \JApplicationCms|null $app
	 * @param \JMenu|null           $menu
	 *
	 * @since 1.0
	 */
	public function __construct($app, $menu)
	{
		parent::__construct($app, $menu);
		$this->setNamespace();
	}

	/**
	 * Force namespace declaration
	 *
	 * @return mixed
	 *
	 * @since 1.0
	 */
	abstract protected function setNamespace();

	/**
	 * Build the route for the com_content component
	 *
	 * @param   array  &$query  An array of URL arguments
	 *
	 * @return  array  The URL arguments to use to assemble the subsequent URL.
	 *
	 * @since   1.0
	 */
	public function build(&$query)
	{
		$option = $query['option'];
		$this->prepareItemidedQuery($query);
		if(isset($query['option'])){
			unset($query['option']);
		}

		$segments = array();
		/** @var Component $component */
		$component = $this->_namespace.'\\Component';
		if(!isset($query['Itemid']) || !$query['Itemid']){
			if(isset($query['controller'])){
				$segments[] = $query['controller'];
				unset($query['controller']);
			}
			if(isset($query['task'])){
				$segments[] = $query['task'];
				unset($query['task']);
			}
		}
		if(isset($query['controller'])){
			unset($query['controller']);
		}
		if(isset($query['task'])){
			unset($query['task']);
		}

		if(isset($query['view']) && $query['view']==$component::getDefaultController()){
			unset($query['view']);
		}

		ksort($query);
		foreach ($query as $k => $qi){
			if(!in_array($k,array('option','Itemid'))){
				$segments[] = $k;
				$segments[] = urlencode($qi);
				unset($query[$k]);
			}
		}

		$query['option'] = $option;
		return $segments;
	}

	/**
	 * Modify passed query to have suitable Itemid and all presets
	 *
	 * @param $initial_query
	 *
	 *
	 * @since 1.0
	 */
	protected function prepareItemidedQuery(&$initial_query){
		if(isset($initial_query['Itemid'])){
			if(count($initial_query)==2 && isset($initial_query['option']) && isset($initial_query['Itemid'])){
				return true;
			}
		}

		/** @var Component $component */
		$component = $this->_namespace.'\\Component';
		$init_query_low_keys = array_map('strtolower',array_keys($initial_query));
		if(!isset($initial_query['view']) && !in_array('view',$init_query_low_keys)){
			$initial_query['view']=$component::getDefaultController();
		}
		if(!isset($initial_query['controller']) && !in_array('controller',$init_query_low_keys)){
			$initial_query['controller']=$component::getDefaultController();
		}
		if(!isset($initial_query['task']) && !in_array('task',$init_query_low_keys)){
			$initial_query['task']=$component::getDefaultTask();
		}

		$query = array_combine(array_map(function($key){
			return strtolower($key);
		},array_keys($initial_query)),array_map(function($value){
			return strtolower($value);
		},$initial_query));

		$db = \JFactory::getDbo();
		$dbq = $db->getQuery(true);
		$dbq->select('`id`,`link`')
			->from('#__menu')
			->where('`published` = "1"')
			->where('`link` LIKE "%option='.$query['option'].'%"')
			->where('`link` LIKE "%view='.$query['view'].'%"');
		$db->setQuery($dbq);
		$links = $db->loadObjectList();
		$links = array_filter($links, function($link){
			if(($link->link = strtolower(str_replace('index.php?','',$link->link, $count))) && $count){
				$link_query = explode('&',$link->link);
				$link->query = array_combine(array_map(function($item){
					$arr = explode('=',$item,2);
					return $arr[0];
				},$link_query),array_map(function($item){
					$arr = explode('=',$item,2);
					return $arr[1];
				},$link_query));
				return $link;
			}else{
				return false;
			}
		});

		$needle = 0;
		$diff = array();
		$diff_length = 1000;
		foreach ($links as $link){
			if(!array_diff_assoc($link->query,$query)){
				$difference = array_diff_assoc($query,$link->query);
				$dc = count($difference);
				if($dc < $diff_length){
					$diff_length = $dc;
					$needle = $link->id;
					$diff = $difference;
					if($dc == 0){
						break;
					}
				}
			}
		}
		if($needle){
			$query = $diff;
		}
		$query_keys = array_keys($query);
		/*
		 * TODO: add another filtering option for PHP prior 5.6.0
		 */
		$initial_query = array_filter($initial_query,function($value, $key) use ($query_keys){
			if(array_search(strtolower($key), $query_keys)!==false){
				return $value;
			}else{
				return false;
			}
		},ARRAY_FILTER_USE_BOTH);

		if($needle){
			$initial_query['Itemid'] = $needle;
		}
	}

	/**
	 * Parse the segments of a URL.
	 *
	 * @param   array  &$segments  The segments of the URL to parse.
	 *
	 * @return  array  The URL attributes to be used by the application.
	 *
	 * @since   1.0
	 */
	public function parse(&$segments)
	{
		$vars = array();
		$request_item_id = \JFactory::getApplication()->input->get('Itemid',0,'INT');
		if($request_item_id || !\JFactory::getApplication()->getMenu()->getActive()->id){
			/*
			 * either no menu item specified or it passed by get
			 * any way must be [0] is controller and [1] is task
			 */
			$vars['controller'] = $segments[0];
			$vars['task'] = $segments[1];
			unset($segments[0]);
			unset($segments[1]);
			$segments = array_values($segments);
		}
		$sc = count($segments);
		for($i=0;$i<=$sc-2;$i++){
			$vars[$segments[$i]] = urldecode($segments[++$i]);
		}

		return $vars;
	}

}
