<?php
/**
 * @package     Joomplace\Library\JooYii
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */

namespace Joomplace\Library\JooYii;

jimport( 'joomla.database.table' );

class Model extends \JTable
{
    protected $_db;
    protected $_columns;
    protected $_table = '#__jtable_test';
    protected $_total = 0;
    protected $_offset = 0;
    protected $_limit = 0;
    protected $_cache = array(
        'conditioner' => null,
        'limit' => null,
        'limitstart' => null,
        'list' => null,
        'pagination' => null,
    );
    protected $_primary_key = 'id';
    protected $_charset = 'utf8';
    protected $_collation = 'unicode_ci';
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
            'hide_at' => array('list','read','form'),
        ),
    );

    /**
     * Constructor
     *
     * @param   \JDatabaseDriver  &$db  Database connector object
     *
     * @since   1.6
     */
    public function __construct()
    {
        $db = $this->_db = \JFactory::getDbo();
        $this->_charset = ($db->hasUTF8mb4Support())?'utf8mb4':'utf8';
        if(!$this->onBeforeInit()){
            /*
             * TODO: Raise ERRORs
             */
            return false;
        }
        $tables = $db->getTableList();
        if(!in_array(str_replace('#__',$db->getPrefix(),$this->_table),$tables)){
            if(!$this->createTable()){
                /*
                 * TODO: Raise ERRORs
                 */
            }
        }
        $this->_columns = $db->getTableColumns($this->_table,false);
        foreach ($this->_field_defenitions as $field => $defenition){
            $this->checkField($field, $defenition['mysql_type'], $defenition['nullable'],$defenition['default'],base64_encode(json_encode($defenition)), (isset($defenition['extra'])?$defenition['extra']:''));
        }
        $this->_columns = $db->getTableColumns($this->_table,false);
        parent::__construct($this->_table, $this->_primary_key, $db);
        if(!$this->onAfterInit()){
            /*
             * TODO: Raise ERRORs
             */
            return false;
        }
    }

    protected function checkField($name, $type = 'text', $is_null = false, $default = false, $comment = '', $extra = ''){
        /** @var \JDatabaseDriver $db */
        $db = $this->_db;
        $column = isset($this->_columns[$name])?((array)$this->_columns[$name]):array();
        $sql = $this->fieldSql($name, $type, $is_null, $default, $comment, $extra);
        $chitem = \JSchemaChangeitem::getInstance($db,null,$sql);
        if($chitem->checkQueryExpected){
            if($chitem->check() !== -2)
            {
                /*
                 * check isn't failed need to check deeper
                 */
                if ($column['Type']!=$type){
                    $chitem->checkStatus = -2;
                }elseif ($column['Collation'] && $column['Collation'] != $this->_charset.'_'.$this->_collation){
                    $chitem->checkStatus = -2;
                }elseif (($column['Null']=='NO' && !$is_null) || ($column['Null']=='YES' && $is_null)){
                    $chitem->checkStatus = -2;
                }elseif ($column['Default'] != $default){
                    $chitem->checkStatus = -2;
                }elseif ($column['Comment'] != $comment){
                    $chitem->checkStatus = -2;
                }
            }
            if($chitem->checkStatus === -2){
                $chitem->fix();
            }
        }
    }

    protected function onBeforeInit(){
        return true;
    }

    protected function onAfterInit(){
        return true;
    }

    protected function fieldSql($name, $type = 'text', $is_null = false, $default = false, $comment = '', $extra = ''){
        $db = $this->_db;
        $sql = 'ALTER TABLE '.$db->qn($this->_table).' '.(array_key_exists($name,$this->_columns)?'MODIFY':'ADD COLUMN').' ';
        if(strpos($type,'text')!==false){
            $type .= ' COLLATE '.$this->_charset.'_'.$this->_collation;
        }elseif (strpos($type,'varchar')!==false){
            $type .= ' CHARACTER SET '.$this->_charset.' COLLATE '.$this->_charset.'_'.$this->_collation;
        }
        $sql .= $name.' '.$type.' '.($is_null?'NULL':'NOT NULL').' '.(is_null($default)?'':('DEFAULT '.$db->q($default)));
        $sql .= ' COMMENT '.$db->q($comment);
        $sql .= ' '.$extra;
        return $sql;
    }

    protected function createTable(){
        $db = $this->_db;
        $sql = "CREATE TABLE ".$db->qn($this->_table)." (
				".$db->qn($this->_primary_key)." int(10) unsigned NOT NULL AUTO_INCREMENT, 
				PRIMARY KEY (".$db->qn($this->_primary_key).")
			) ENGINE=InnoDB DEFAULT CHARSET=".$this->_charset." COLLATE=".$this->_charset."_".$this->_collation."";
        return $db->setQuery($sql)->execute();
    }

    public function getForm(){
        $key = $this->_primary_key;
        $name = str_replace('#__',$this->_db->getPrefix() ,$this->_table).($this->$key?('.'.$this->$key):'');
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><form></form>');
        $fieldset = $xml->addChild('fieldset');
        foreach ($this->_field_defenitions as $key => $defenition){
            if(!isset($defenition['label'])){
                $defenition['label'] = 'FORMFIELD_'.strtoupper($key).'_LABEL';
            }
            if(!isset($defenition['description'])){
                $defenition['description'] = 'FORMFIELD_'.strtoupper($key).'_DESC';
            }
            $defenition['name'] = $key;
            $field = $fieldset->addChild('field');
            foreach ($defenition as $attr => $attr_value){
                if(in_array($attr,array('option'))){
                    foreach ($attr_value as $kopt => $opt){
                        $option = $field->addChild('option',$opt)->addAttribute('value',$kopt);
                    }
                }else{
                    $field->addAttribute($attr,$attr_value);
                }
            }
        }
        $form = \JForm::getInstance($name, $xml->asXML(), array(), true, false);
        $this->preprocessForm($form);
        return $form;
    }

    protected function preprocessForm(\JForm $form){

    }

    public function clearCache(){
        $this->_cache['conditioner'] = null;
    }

    public function getList($limitstart, $limit, $conditioner = array()){
        if($this->_cache['conditioner']===$conditioner && $this->_cache['limit'] === $limit && $this->_cache['limitstart'] === $limitstart){
            return $this->_cache['list'];
        }else{
            $this->_cache['conditioner'] = $conditioner;
            $this->_cache['list'] = null;
            $this->_cache['pagination'] = null;
        }
        /** @var \JDatabaseDriverMysqli $db */
        $db = $this->_db;
       $query = $this->getListQuery($conditioner);

        if($limit){
            //prepare pagination
            $this->_limit = $limit;
            $cquery = clone $query;
            if($cquery->type == 'select'
                && $cquery->group === null
                && $cquery->union === null
                && $cquery->unionAll === null
                && $cquery->having === null)
            {
                $cquery->clear('select')->clear('order')->clear('limit')->clear('offset')->select('COUNT(*)');
                $this->_total = $db->setQuery($cquery)->loadResult();
            }else{
                $cquery->clear('limit')->clear('offset');
                $db->setQuery($cquery);
                $db->execute();
                $this->_total = (int) $db->getNumRows();
            }
            $offset = $this->getStart($limitstart*$limit, $limit);
            $db->setQuery($query,$offset,$limit);
        }else{
            $db->setQuery($query);
        }
        $this->_cache['list'] = $db->loadObjectList('',get_class($this));
        return $this->_cache['list'];
    }

    public function getListQuery($conditioner = array()){
        if(!is_array($conditioner)){
            $conditioner = array($this->_primary_key => $conditioner);
        }

        /** @var \JDatabaseDriverMysqli $db */
        $db = $this->_db;

		// Initialise the query.
		$query = $db->getQuery(true)
            ->select('*')
            ->from($this->_table);
		$fields = array_keys($this->getProperties());

		foreach ($conditioner as $field => $value)
        {
            // Check that $field is in the table.
            if (!in_array($field, $fields))
            {
                throw new UnexpectedValueException(sprintf('Missing field in database: %s &#160; %s.', get_class($this), $field));
            }

            if(is_int($value)){
                $query->where($this->_db->quoteName($field) . ' = ' . $this->_db->quote($value));
            }else if(is_string($value)){
                $query->where($this->_db->quoteName($field) . ' LIKE ' . $this->_db->quote($value));
            }else if(is_array($value)){
                $query->where($this->_db->quoteName($field) . ' IN (' . implode(',', $value) . ')');
            }
        }

        return $query;
    }

    protected function getStart($start, $limit){
        $this->_offset = $start;
        if ($start > $this->_total - $limit)
        {
            $this->_offset = max(0, (int) (ceil($this->_total / $limit) - 1) * $limit);
        }
        return $this->_offset;
    }

    public function getPagination(){
        if(!$this->_cache['pagination']){
            $this->_cache['pagination'] = new \JPagination($this->_total, $this->_offset, $this->_limit);
        }
        return $this->_cache['pagination'];
    }

    public function getColumns($lrf = 'list', $include_hidden = false){
        if(!$include_hidden){
            $fields = array_filter($this->_field_defenitions, function($field) use ($lrf){
                if($field['type']=='hidden' || (isset($field['hide_at']) && in_array($lrf,$field['hide_at']))){
                    return false;
                }
                return true;
            });
        }
        $columns = array_keys($fields);
        return $columns;
    }

    public function renderListControl($field){
        $defenition = $this->_field_defenitions[$field];
        ob_start();
        switch ($defenition['type']){
            case 'user':
                $this->_renderListControlUser($field);
                break;
            case 'editor':
                $this->_renderListControlEditor($field);
                break;
            case 'radio':
                $this->_renderListControlRadio($field);
                break;
            default:
                echo "<pre>";
                print_r($this->_field_defenitions[$field]);
                echo "</pre>";
                break;
        }
        $layout = ob_get_contents();
        ob_end_clean();
        return $layout;
    }

    protected function _renderListControlUser($field){
        $user = \JFactory::getUser($this->$field);
        if($user->id){
            echo $user->name.' ('.$user->username.')';
        }else{
            echo \JText::_('ANONYMOUS');
        }
    }

    protected function _renderListControlEditor($field){
        echo Helper::trimText($this->$field,5);
    }

    protected function _renderListControlRadio($field, $active = false){
        if(in_array($field,array('published','featured'))){
            /*
             * TODO: check permissions to verb
             */
            $active = true;
        }
        $paths = Loader::getPathByPsr4('Joomplace\\Library\\JooYii\\Layouts\\','/');
        if($active){
            $layout = new \JLayoutFile('publish');
            $layout->addIncludePaths($paths);
            $key = $this->_primary_key;
            echo $layout->render(array('value'=>$this->$field,'task'=>'publish-task','id'=>$this->$key));
        }else{
            $layout = new \JLayoutFile('state');
            $layout->addIncludePaths($paths);
            echo $layout->render($this->$field);
        }
    }

    public function publish($pks = null, $state = 1, $userId = 0)
    {
        /*
         * Hack (reroute) because of JTable publish doesn't suite by params
         */
        return Helper::callBindedFunction($this,'setPublished');
    }

    public function setPublished(Array $cid, $state = 1)
    {
        $counter = 0;
        foreach ($cid as $id){
            $this->load($id, true);
            if($this->published != $state){
                $this->published = $state;
                if($this->store()){
                    $counter++;
                }
            }
        }
        if($state && $counter){
            \JFactory::getApplication()->enqueueMessage(\JText::sprintf('ITEMS_PUBLISHED',$counter));
        }

        return $counter;
    }

    public function unpublish(Array $cid){
        $counter = $this->setPublished($cid,$state = 0);
        if($counter){
            \JFactory::getApplication()->enqueueMessage(\JText::sprintf('ITEMS_UNPUBLISHED',$counter));
        }
        return $counter;
    }
}