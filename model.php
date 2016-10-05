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
    protected static $_integrety_checked = array();
    /** @var  \Joomla\Registry\Registry $_user_state */
    protected $_user_state;
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
        $this->checkIntegrety();
        $this->_columns = $db->getTableColumns($this->_table,false);
        parent::__construct($this->_table, $this->_primary_key, $db);
        if(!$this->onAfterInit()){
            /*
             * TODO: Raise ERRORs
             */
            return false;
        }
    }

    /**
     * @return boolean
     */
    public function isIntegretyChecked()
    {
        return isset(self::$_integrety_checked[$this->_table]);
    }

    protected function checkIntegrety($force = false){
        if(!$this->isIntegretyChecked() || $force){
            $tables = $this->_db->getTableList();
            if(!in_array(str_replace('#__',$this->_db->getPrefix(),$this->_table),$tables)){
                if(!$this->createTable()){
                    /*
                     * TODO: Raise ERRORs
                     */
                }
            }
            $this->_columns = $this->_db->getTableColumns($this->_table,false);
            foreach ($this->_field_defenitions as $field => $defenition){
                $this->checkField($field, $defenition['mysql_type'], $defenition['nullable'],$defenition['default'],base64_encode(json_encode($defenition)), (isset($defenition['extra'])?$defenition['extra']:''));
            }
            self::$_integrety_checked[$this->_table] = true;
        }
        return $this->isIntegretyChecked();
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

    public function store($updateNulls = false)
    {
        if(array_key_exists('ordering',$this->_field_defenitions) && !$this->ordering){
            $this->ordering = $this->getNextOrder();
        }
        return parent::store($updateNulls); // TODO: Change the autogenerated stub
    }

    public function reset()
    {
        // Get the default values for the class from the table.
        foreach ($this->getFields() as $k => $v)
        {
            $this->$k = $v->Default;
        }

        // Reset table errors
        $this->_errors = array();
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
        $sql .= $db->qn($name).' '.$type.' '.($is_null?'NULL':'NOT NULL').' '.(is_null($default)?'':('DEFAULT '.$db->q($default)));
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

    public function getForm($form_name = 'jform'){
        $key = $this->_primary_key;
        $name = str_replace('#__',$this->_db->getPrefix() ,$this->_table).($this->$key?('.'.$this->$key):'');
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><form></form>');
        $fieldset = $xml->addChild('fieldset');
        $set_values = ($this->$key)?1:0;
        foreach ($this->_field_defenitions as $key => $defenition){
            if(!isset($defenition['label'])){
                $defenition['label'] = 'FORMFIELD_'.strtoupper($key).'_LABEL';
            }
            if(!isset($defenition['description'])){
                $defenition['description'] = 'FORMFIELD_'.strtoupper($key).'_DESC';
            }
            if($set_values){
                $defenition['default'] = $this->$key;
            }
            $defenition['name'] = $form_name.'['.$key.']';
            $defenition['id'] = $form_name.'_'.$key.'';
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
        $form = Form::getInstance($name, $xml->asXML(), array(), true, false);
        $form::addFieldPath(\Joomplace\Library\JooYii\Loader::getPathByPsr4(Helper::getClassNameSpace($this).'\\Fields','/','field'));
        $this->preprocessForm($form);
        return $form;
    }

    protected function preprocessForm(Form $form){

    }

    public function clearCache(){
        $this->_cache['conditioner'] = null;
    }

    public function getList($limitstart = false, $limit = false, $conditioner = array(),$by = ''){
        if($limit === false){
            $limit = $this->getState('list.limit');
        }
        if($limitstart === false){
            $limitstart = $this->getState('list.limitstart');
        }
        if($this->_cache['conditioner']===$conditioner && $this->_cache['limit'] === $limit && $this->_cache['by'] === $by && $this->_cache['limitstart'] === $limitstart){
            return $this->_cache['list'];
        }else{
            $this->_cache['conditioner'] = $conditioner;
            $this->_cache['list'] = null;
            $this->_cache['pagination'] = null;
            $this->_cache['by'] = '';
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
            $offset = $this->getStart($limitstart, $limit);
            $db->setQuery($query,$offset,$limit);
            $this->setState('list.limit',$limit,true);
            $this->setState('list.limitstart',$limitstart,true);
        }else{
            $db->setQuery($query);
        }
        $this->_cache['list'] = $db->loadObjectList($by,get_class($this));
        return $this->_cache['list'];
    }

    public function getTotal($conditioner = array()){
        if(!$this->_total && $conditioner === $this->_cache['conditioner']){
            /** @var \JDatabaseDriverMysqli $db */
            $db = $this->_db;
            $cquery = $this->getListQuery($conditioner);
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
        }
        return $this->_total;
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
                throw new \UnexpectedValueException(sprintf('Missing field in database: %s &#160; %s.', get_class($this), $field));
            }

            if(is_int($value)){
                $query->where($this->_db->quoteName($field) . ' = ' . $this->_db->quote($value));
            }else if(is_string($value)){
                $query->where($this->_db->quoteName($field) . ' LIKE ' . $this->_db->quote($value));
            }else if(is_array($value)){
                foreach ($value as &$v){
                    $v = $this->_db->q($v);
                }
                $query->where($this->_db->quoteName($field) . ' IN (' . implode(',', $value) . ')');
            }
        }

        $listOrd = $this->getState('list.ordering');
        $listDir = $this->getState('list.direction');
        if(!in_array($listOrd,$this->getColumns('list',true))){
            $listOrd = $this->_primary_key;
            $listDir = 'ASC';
        }

        $query->order($db->qn($listOrd).' '.$listDir);

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
        }else{
            $fields = $this->_field_defenitions;
        }
        $columns = array_keys($fields);
        return $columns;
    }

    public function renderListControl($field){
        $defenition = $this->_field_defenitions[$field];
        ob_start();
        $field_processer = '_renderListControl'.$field;
        if(method_exists($this,$field_processer)){
            $this->$field_processer($field);
        }else{
            switch ($defenition['type']){
                case 'user':
                    $this->_renderListControlUser($field);
                    break;
                case 'text':
                    $this->_renderListControlText($field);
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

    public function renderListControlActionLink($task, $value, $class = ''){
        $paths = Loader::getPathByPsr4('Joomplace\\Library\\JooYii\\Layouts\\','/');
        $layout = new \JLayoutFile('action-btn');
        $layout->addIncludePaths($paths);
        $key = $this->_primary_key;
        echo $layout->render(array('value'=>$value,'task'=>$task,'class'=>$class,'id'=>$this->$key));
    }

    protected function _renderListControlText($field){
        echo Helper::trimText($this->$field,75);
    }

    protected function _renderListControlEditor($field){
        $this->_renderListControlText($field);
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

    public function getState($var = '', $default = null)
    {
        $this->populateState();
        if($var){
            return $this->_user_state->get($var, $default);
        }else{
            return $this->_user_state;
        }
    }

    public function setState($var, $value, $set_user_state = false)
    {
        $this->populateState();
        $this->_user_state->set($var,$value);
        if($set_user_state){
            \JFactory::getApplication()->setUserState($var,$value);
        }
    }

    public function populateState(){
        if(!$this->_user_state){
            $this->_user_state = new \Joomla\Registry\Registry();
            $this->_user_state->set('list.limitstart',\JFactory::getApplication()->getUserStateFromRequest('list.limitstart','limitstart',0));
            $this->_user_state->set('list.limit',\JFactory::getApplication()->getUserStateFromRequest('list.limit','limit',20));
            $this->_user_state->set('list.ordering',\JFactory::getApplication()->getUserStateFromRequest('list.ordering','filter_order','id'));
            $this->_user_state->set('list.direction',\JFactory::getApplication()->getUserStateFromRequest('list.direction','filter_order_Dir','asc'));
//            $this->_user_state->set('list.ordering',\JFactory::getApplication()->getUserStateFromRequest('order','filter_order','id'));
//            $this->_user_state->set('list.direction',\JFactory::getApplication()->getUserStateFromRequest('direction','filter_order_Dir','asc'));
        }
    }

    /**
     * Saves the manually set order of records.
     *
     * @param   array    $pks    An array of primary key ids.
     * @param   integer  $order  +1 or -1
     *
     * @return  boolean|JException  Boolean true on success, boolean false or JException instance on error
     *
     * @since   12.2
     */
    public function saveorder(array $pks = null, $order = null)
    {
        //$table = $this->getTable();
        //$tableClassName = get_class($table);
        //$contentType = new JUcmType;
        //$type = $contentType->getTypeByTable($tableClassName);
        //$tagsObserver = $table->getObserverOfClass('JTableObserverTags');
        $conditions = array();

        if (empty($pks))
        {
            return \JError::raiseWarning(500, \JText::_('ERROR_NO_ITEMS_SELECTED'));
        }

        // Update ordering values
        foreach ($pks as $i => $pk)
        {
            $this->load((int) $pk);

            // Access checks.
            if (!$this->canEditState())
            {
                // Prune items that you can't change.
                unset($pks[$i]);
                \JLog::add(\JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'), \JLog::WARNING, 'jerror');
            }
            elseif ($this->ordering != $order[$i])
            {
                $this->ordering = $order[$i];

//                if ($type)
//                {
//                    $this->createTagsHelper($tagsObserver, $type, $pk, $type->type_alias, $table);
//                }

                if (!$this->store())
                {
                    $this->setError($this->getError());

                    return false;
                }

                // Remember to reorder within position and client_id
                //$condition = $this->getReorderConditions($table);
                //$found = false;
//
//                foreach ($conditions as $cond)
//                {
//                    if ($cond[1] == $condition)
//                    {
//                        $found = true;
//                        break;
//                    }
//                }
//
//                if (!$found)
//                {
//                    $key = $table->getKeyName();
//                    $conditions[] = array($table->$key, $condition);
//                }
            }
        }

        // Execute reorder for each category.
//        foreach ($conditions as $cond)
//        {
//            $table->load($cond[0]);
//            $table->reorder($cond[1]);
//        }

        // Clear the component's cache
        //$this->cleanCache();

        return true;
    }

    public function canEditState(){
        return true;
    }

    public function remove(array $cid)
    {
        $counter = 0;
        foreach ($cid as $id){
            if($this->delete($id)){
                $counter++;
            }
        }
        if($counter){
            \JFactory::getApplication()->enqueueMessage(\JText::sprintf('ITEMS_DELETED',$counter));
            return $counter;
        }else{
            return false;
        }
    }

    public function reveal(){
        return array_filter(get_object_vars($this),function($key){
            if(strpos($key,'_')===0){
                return false;
            }else{
                return true;
            }
        },ARRAY_FILTER_USE_KEY);
    }

}
