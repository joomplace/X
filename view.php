<?php
/**
 * @package     Joomplace\Library\JooYii
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */

namespace Joomplace\Library\JooYii;


class View implements \JView
{
    protected $_view_name;
	protected $_layout = 'default';
    protected $_namespace = __NAMESPACE__;

    public function __construct($view_name)
    {
        $this->_view_name = $view_name;
        $this->setLayout(\JFactory::getApplication()->input->get('layout',$this->_layout));
    }

    /**
     * @return string
     */
    public function getNamespace()
    {
        return $this->_namespace;
    }

    /**
     * @param string $namespace
     */
    public function setNamespace($namespace)
    {
        $this->_namespace = $namespace;
    }

	/**
	 * @return string
	 *
	 * @since version
	 */
	public function getLayout()
	{
		return $this->_layout;
	}

	/**
	 * @param string $layout
	 *
	 * @since version
	 */
	public function setLayout($layout)
	{
		$this->_layout = $layout;
	}

	public function setVar($var,$value){
		$this->$var = $value;
	}

	public function escape($output)
    {
        return $output;
    }

    public function render($sublayout = '', $local_vars = array()){
        extract($local_vars);
        $toolbar = \JToolbar::getInstance();
        $path = Loader::findViewLayoutByNS($this->_view_name, $this->getLayout().$sublayout,$this->getNamespace());
        ob_start();
        include $path;
        $return = ob_get_contents();
        ob_end_clean();
        return $return;
	}

    public function display($sublayout = '', $vars = array()){
		echo $this->render($sublayout, $vars);
	}

}