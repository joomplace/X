<?php
/**
 * @package     Joomplace\Library\JooYii
 *
 * @copyright   Alexandr Kosarev
 * @license     GPL2
 */

namespace Joomplace\Library\JooYii;

/**
 * View class for implementing V letter of new Joomla!CMS MVC
 * (JooYii MVC)
 *
 * @package     Joomplace\Library\JooYii
 *
 * @since       1.0
 */
class View implements \JView
{
	/** @var string $_view_name */
	protected $_view_name;
	/** @var string $_layout */
	protected $_layout = 'default';
	/** @var string $_namespace Used for autoloading purposes */
	protected $_namespace = __NAMESPACE__;

	/**
	 * View constructor.
	 *
	 * @param string $view_name Name of the view can also be
	 *                          view.layout for specifying default layout
	 *
	 * @since 1.0
	 */
	public function __construct($view_name)
	{
		if (strpos($view_name, '.'))
		{
			list($this->_view_name, $this->_layout) = explode('.', $view_name, 2);
		}
		else
		{
			$this->_view_name = $view_name;
		}
		$this->setLayout(\JFactory::getApplication()->input->get('layout', $this->_layout));
	}

	/**
	 * Not declared properties setter
	 *
	 * @param $var   Property name
	 * @param $value Property value
	 *
	 *
	 * @since 1.0
	 */
	public function setVar($var, $value)
	{
		$this->$var = $value;
	}

	/**
	 * Method to apply needed escaping logic
	 *
	 * @param string $output Input string
	 *
	 * @return string Escaped string
	 *
	 * @since 1.0
	 */
	public function escape($output)
	{
		return $output;
	}

	/**
	 * Proxy for echoing rendered murkup
	 *
	 * @param string $sublayout See render()
	 * @param array  $vars      See render()
	 *
	 *
	 * @since 1.0
	 */
	public function display($sublayout = '', $vars = array())
	{
		echo $this->render($sublayout, $vars);
	}

	/**
	 * Method for rendering of HTML markup
	 *
	 * @param string $sublayout  Layout postfix
	 * @param array  $local_vars Array of vars for local scope
	 *
	 * @return string Html markup
	 *
	 * @since 1.0
	 */
	public function render($sublayout = '', $local_vars = array())
	{
		extract($local_vars);
		$toolbar = \JToolbar::getInstance();
		$sidebar = \JHtmlSidebar::render();
		$toolbar_html = '';
		if(\JFactory::getApplication()->isSite() && !$sublayout){
			$path    = Loader::findViewLayoutByNS($this->_view_name, 'toolbar', $this->getNamespace());
			if($path){
				include $path;
				$toolbar_html = ob_get_contents();
				ob_end_clean();
			}
		}
		$path    = Loader::findViewLayoutByNS($this->_view_name, $this->getLayout() . $sublayout, $this->getNamespace());
		ob_start();
		include $path;
		$return = ob_get_contents();
		ob_end_clean();

		return $toolbar_html.$return;
	}

	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	public function getLayout()
	{
		return $this->_layout;
	}

	/**
	 * @param string $layout
	 *
	 * @since 1.0
	 */
	public function setLayout($layout)
	{
		$this->_layout = $layout;
	}

	/**
	 * Returns current working namespace
	 *
	 * @return string Working namespace
	 *
	 * @since 1.0
	 */
	public function getNamespace()
	{
		return $this->_namespace;
	}

	/**
	 * @param string $namespace Current working namespace
	 *
	 *
	 * @since 1.0
	 */
	public function setNamespace($namespace)
	{
		$this->_namespace = $namespace;
	}

	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	public function getViewName()
	{
		return $this->_view_name;
	}

}
