<?php
/**
 * Created by PhpStorm.
 * User: Alexandr
 * Date: 22.06.2017
 * Time: 16:18
 */

namespace JoomPlaceX;

use Joomla\Registry\Registry;
use JTable;

abstract class Model extends \JTable
{
    /**
     * Array of tables names as key and state of integrety as value
     *
     * @var boolean $_integrety_checked
     *
     * @since 1.0
     */
    protected static $_integrety_checked = false;
    /**
     * Array of tables columns existing in DB
     *
     * @var array $_columns
     *
     * @since 1.0
     */
    protected static $_columns = array();
    protected static $_field_defenitions
        = array(
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
            'ordering' => array(
                'mysql_type' => 'int(11) unsigned',
                'type' => 'hidden',
            )
        );
    /**
     * Use this field to extend $_field_defenitions
     * in final class impementation
     *
     * @var array $_fields
     *
     * @since 1.0
     */
    protected static $_fields = array();
    /**
     * Used to store public cache
     *
     * @var \Joomla\Registry\Registry|null $_public_instances
     *
     * @since 1.0
     */
    protected static $_public_instances = null;
    /** @var  \JDatabaseDriver $_db */
    protected $_db;
    /** @var  \Joomla\Registry\Registry $_user_state */
    protected $_user_state;
    /** @var string $_table */
    protected $_table;
    /** @var string $_context Used for gerating Asset name and other */
    protected $_context;
    protected $_ignore_in_xml
        = array(
            'hide_at',
        );
    protected $_total = 0;
    protected $_offset = 0;
    protected $_limit = 0;
    /**
     * Used for storing last list results
     *
     * @var array $_cache
     *
     * @since 1.0
     */
    protected $_cache
        = array(
            'conditioner' => null,
            'limit' => null,
            'limitstart' => null,
            'list' => null,
            'pagination' => null,
        );
    /** @var string $_primary_key */
    protected $_primary_key = 'id';
    /**
     * Table and table fields charset
     *
     * @var string $_charset
     *
     * @since 1.0
     */
    protected $_charset = 'utf8';
    /**
     * Table and table fields collation
     *
     * @var string $_collation
     *
     * @since 1.0
     */
    protected $_collation = 'unicode_ci';

    public function __construct($conditions = null, $reset = true)
    {
        $this->determine();
//	    \JPluginHelper::importPlugin( 'jooyii' );
//	    $dispatcher = \JEventDispatcher::getInstance();
//	    $results = $dispatcher->trigger( 'onCdAddedToLibrary', array( &$artist, &$title ) );
        $db = $this->_db = \JFactory::getDbo();
        $this->_charset = ($db->hasUTF8mb4Support()) ? 'utf8mb4' : 'utf8';
        if (!$this->onBeforeInit()) {
            /*
             * TODO: Raise ERRORs
             */
            return false;
        }
        $this->checkIntegrety();
        parent::__construct($this->_table, $this->_primary_key, $db);

        if (!$this->onAfterInit()) {
            /*
             * TODO: Raise ERRORs
             */
            return false;
        }
        if ($conditions) {
            if (!is_array($conditions)) {
                if (!$reset && static::$_public_instances->get($this->_table . '.' . $conditions)) {
                    $this->bind(static::$_public_instances->get($this->_table . '.' . $conditions));
                } else {
                    $this->load($conditions);
                    static::$_public_instances->set($this->_table . '.' . $conditions, $this->getProperties());
                }
            } else {
                $this->load($conditions);
            }
        }
    }

    abstract protected function determine();

    /**
     * Use for tweaking of initiation process
     *
     * @return bool State of initiation
     *
     * @since 1.0
     */
    protected function onBeforeInit()
    {
        return true;
    }

    /**
     * Triggers integrety fixing
     *
     * @param bool $force A flag for forcing recheck
     *
     * @return bool Integrety check status
     *
     * @since 1.0
     */
    protected function checkIntegrety($force = false)
    {
        if (!static::isIntegretyChecked() || $force) {
            if (self::$_public_instances === null) {
                self::$_public_instances = new \Joomla\Registry\Registry();
            }
            // TODO: redo check as we have Gfield\subfield now
            $tables = $this->_db->getTableList();
            if (!in_array(str_replace('#__', $this->_db->getPrefix(),
                $this->_table), $tables)
            ) {
                if (!$this->createTable()) {
                    /*
                     * TODO: Raise ERRORs
                     */
                }
            }

            $_column_defenitions = array();
            foreach (static::getDefinitions() as $field => $defenition) {
                if (strpos($field, '.')) {
                    /*
                     * JSON grouping column
                     */
                    list($field) = explode('.', $field);
                    if (!in_array($field, $_column_defenitions)) {
                        static::processAsJson($field);
                        $defenition = array(
                            'mysql_type' => 'varchar(2056)',
                            'default' => '{}',
                        );
                    }
                }
                $_column_defenitions[$field] = new \Joomla\Registry\Registry($defenition);
            }
            self::$_columns[$this->_table] = $this->_db->getTableColumns($this->_table, false);
            /**
             * @var string $field
             * @var Registry $defenition
             */
            foreach ($_column_defenitions as $field => $defenition) {
                $this->checkField($field, $defenition->get('mysql_type'),
                    $defenition->get('nullable'), $defenition->get('default'),
                    base64_encode($defenition->toString()),
                    $defenition->get('extra', ''));
            }
            static::isIntegretyChecked(true);
            self::$_columns[$this->_table] = $this->_db->getTableColumns($this->_table, false);
        }

        return static::isIntegretyChecked();
    }

    /**
     * Return table integrety status
     * for current model table
     *
     * @return bool
     *
     * @since 1.0
     */
    public static function isIntegretyChecked($state = false)
    {
        static $_integrety_checked = false;
        if ($state) {
            $_integrety_checked = $state;
        }

        return $_integrety_checked;
    }

    /**
     * Attempt to create table
     *
     * @return mixed Table creation attempt result
     *
     * @since 1.0
     */
    protected function createTable()
    {
        $db = $this->_db;
        $sql = "CREATE TABLE " . $db->qn($this->_table) . " (
				" . $db->qn($this->_primary_key) . " int(10) unsigned NOT NULL AUTO_INCREMENT, 
				PRIMARY KEY (" . $db->qn($this->_primary_key) . ")
			) ENGINE=InnoDB DEFAULT CHARSET=" . $this->_charset . " COLLATE="
            . $this->_charset . "_" . $this->_collation . "";

        return $db->setQuery($sql)->execute();
    }

    protected static function getDefinitions()
    {
        static $definitions = null;
        if (!$definitions) {
            $definitions = static::gatherDefinitions();
        }
        return $definitions;
    }

    protected static function gatherDefinitions()
    {
        $defs = static::$_field_defenitions;
        $xml_file = static::getXmlFile();
        if (is_file($xml_file)) {
            $xml = simplexml_load_file($xml_file);
            $fields = static::parseXmlDefinitions($xml);
            $defs = array_merge($defs, $fields);
        }
        if (static::$_fields) {
            $defs = array_merge($defs, static::$_fields);
        }
        return $defs;
    }

    protected static function getXmlFile()
    {
        $rf = new \ReflectionClass(static::class);
        return dirname($rf->getFileName()) . DIRECTORY_SEPARATOR . 'definitions' . DIRECTORY_SEPARATOR . strtolower($rf->getShortName()) . '.xml';
    }

    protected static function parseXmlDefinitions(
        \SimpleXMLElement $xmlElement,
        $fieldsetprefix = '',
        $grouping_field = ''
    ) {
        $fields = $_fields = array();
        $fieldsetname = trim($fieldsetprefix . ($xmlElement->getName() != 'fields' ? '.' . (string)$xmlElement['name'] : ''),
            '.');
        if (isset($xmlElement->attributes()['addfieldpath'])) {
            static::additionalFieldsPaths((string)$xmlElement->attributes()['addfieldpath']);
        }
        array_map(function (\SimpleXMLElement $x) use (&$fields, $fieldsetname, $grouping_field) {
            $field = array();
            /**
             * @var string $key
             * @var \SimpleXMLElement $value
             */
            foreach ($x->attributes() as $key => $value) {
                $field[$key] = (string)$value;
            }
            foreach ($x as $k => $content) {
                if (!isset($field[$k])) {
                    $field[$k] = array();
                }
                if ($content['value']) {
                    $field[$k][(string)$content['value']] = (string)$content;
                } else {
                    $field[$k][] = (string)$content;
                }
            }

            $field['fieldset'] = $fieldsetname ? $fieldsetname : 'basic';
            $fields[($grouping_field ? $grouping_field . '.' : '') . $field['name']] = $field;
            return true;
        }, $xmlElement->xpath('field'));

        if ($xmlElement->xpath('fieldset')) {
            foreach ($xmlElement->xpath('fieldset') as $xml) {
                $fields = array_merge($fields, static::parseXmlDefinitions($xml, $fieldsetname, $grouping_field));
            }
        }

        if ($xmlElement->xpath('fields')) {
            foreach ($xmlElement->xpath('fields') as $xml) {
                $fields = array_merge($fields, static::parseXmlDefinitions($xml, $fieldsetname,
                    ($grouping_field ? $grouping_field . '.' : '') . $xml['name']));
            }
        }

        return $fields;
    }

    protected static function additionalFieldsPaths($add = null)
    {
        static $paths = array();
        if ($add) {
            if (is_array($add)) {
                array_map(function ($path) {
                    static::additionalFieldsPaths($path);
                }, $add);
            } else {
                if (strpos($add, JPATH_SITE) === false) {
                    $add = JPATH_SITE . $add;
                }
                if (!in_array($add, $paths)) {
                    $paths[] = $add;
                    \JFormHelper::addFieldPath($add);
                }
            }
        }
        return $paths;
    }

    protected static function processAsJson($new = null)
    {
        static $jsonFields = array();
        if ($new && !in_array($new, $jsonFields)) {
            $jsonFields[] = $new;
        }
        return $jsonFields;
    }

    /**
     * Checking columns state
     *
     * @param        $name    Column name
     * @param string $type Column type
     * @param bool $is_null Is nullable
     * @param bool $default Column default value
     * @param string $comment Comment for JooYii
     * @param string $extra Column extas
     *
     *
     * @since 1.0
     */
    protected function checkField(
        $name,
        $type = 'text',
        $is_null = false,
        $default = false,
        $comment = '',
        $extra = ''
    ) {
        /** @var \JDatabaseDriver $db */
        $db = $this->_db;
        $column = isset(self::$_columns[$this->_table][$name])
            ? ((array)self::$_columns[$this->_table][$name]) : array();
        $sql = $this->fieldSql($name, $type, $is_null, $default, $comment,
            $extra);
        $chitem = \JSchemaChangeitem::getInstance($db, null, $sql);
        if ($chitem->checkQueryExpected) {
            if ($chitem->check() !== -2) {
                /*
                 * check isn't failed need to check deeper
                 */
                if ($column['Type'] != $type) {
                    $chitem->checkStatus = -2;
                } elseif ($column['Collation']
                    && $column['Collation'] != $this->_charset . '_'
                    . $this->_collation
                ) {
                    $chitem->checkStatus = -2;
                } elseif (($column['Null'] == 'NO' && !$is_null)
                    || ($column['Null'] == 'YES' && $is_null)
                ) {
                    /*
                     * is_null sheck commented out because of causing some issues.
                     * Might need so help to bring it back
                     */
                    /*$chitem->checkStatus = -2;*/
                } elseif ($column['Default'] != $default) {
                    $chitem->checkStatus = -2;
                } elseif ($column['Comment'] != $comment) {
                    $chitem->checkStatus = -2;
                }
            }
            if ($chitem->checkStatus === -2) {
                $chitem->fix();
            }
        }
    }

    /**
     * Generating alter table SQL for column
     *
     * @param        $name    Column name
     * @param string $type Column type
     * @param bool $is_null Is nullable
     * @param bool $default Column default value
     * @param string $comment Comment for JooYii
     * @param string $extra Column extas
     *
     * @return string ALTER TABLE SQL
     *
     * @since 1.0
     */
    protected function fieldSql(
        $name,
        $type = 'text',
        $is_null = false,
        $default = false,
        $comment = '',
        $extra = ''
    ) {
        $db = $this->_db;
        $sql = 'ALTER TABLE ' . $db->qn($this->_table) . ' '
            . (array_key_exists($name, self::$_columns[$this->_table])
                ? 'MODIFY' : 'ADD COLUMN') . ' ';
        if (strpos($type, 'text') !== false) {
            $type .= ' COLLATE ' . $this->_charset . '_' . $this->_collation;
        } elseif (strpos($type, 'varchar') !== false) {
            $type .= ' CHARACTER SET ' . $this->_charset . ' COLLATE '
                . $this->_charset . '_' . $this->_collation;
        }
        $sql .= $db->qn($name) . ' ' . ($type ? $type : 'text') . ' '
            . ($is_null ? 'NULL' : 'NOT NULL') . ' ' . ((is_null($default)
                || strpos($type, 'text') !== false) ? ''
                : ('DEFAULT ' . $db->q($default)));
//        $sql .= ' COMMENT ' . $db->q($comment);
        $sql .= ' ' . $extra;

        return $sql;
    }

    /**
     * Use for tweaking of initiation process
     *
     * @return bool State of initiation
     *
     * @since 1.0
     */
    protected function onAfterInit()
    {
        return true;
    }

    public function bind($src, $ignore = array())
    {
        /*
         * TODO: implement JSON_process with observers
         * onBeforeStore encode
         *
         * onAfterStore decode
         * onAfterLoad decode
         */
//        foreach (static::$_JSON_process as $field){
//            if(is_string($src[$field])){
//                $src[$field] = json_decode($src[$field]);
//            }
//        }

        $return = parent::bind($src, $ignore);
        /*
         * TODO: move to observer
         */
        foreach (static::processAsJson() as $fn) {
            if (is_string($this->$fn)) {
                $this->$fn = new Registry($this->$fn);
            }
        }
        return $return;
    }

    // TODO: need to add read and write 'addfieldpath=""' functionality

    /**
     *  Reset current state of Objects public properties
     *
     * @since 1.0
     */
    public function reset()
    {
        // Get the default values for the class from the table.
        foreach ($this->getFields() as $k => $v) {
            $this->$k = $v->Default;
        }

        // Reset table errors
        $this->_errors = array();
    }

    /**
     * Get Joomla form base on xml generated for current model (table defenition)
     *
     * @param string $form_name Grouping prefix for fields
     *
     * @return \JForm Joomla form object
     *
     * @since 1.0
     */
    public function getForm($form_name = 'jform')
    {
        $key = $this->_primary_key;
        $name = str_replace('#__', $this->_db->getPrefix(),
                $this->_table) . ($this->$key ? ('.' . $this->$key) : '');
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><form></form>');
        $defs = new Registry();
        foreach (static::getDefinitions() as $k => $f) {
            $defs->set($k, $f);
        }
        foreach ($defs as $field => $def) {
            $this->buildXmlElement($field, $def, $xml);
        }

        $form = \JForm::getInstance($name, $xml->asXML(),
            array('control' => 'jform'), true, false);
        $form->bind($this->getProperties());
        if (\JFactory::getApplication()->getUserState($this->getContext())) {
            $form->bind(\JFactory::getApplication()
                ->getUserState($this->getContext()));
        }

        $this->preprocessForm($form);

        return $form;
    }

    protected function buildXmlElement($name, $definition, \SimpleXMLElement $xml)
    {
        if (!is_array($definition)) {
            /*
             * We got grouping field, fields tag
             */
            $field_group = $xml->addChild('fields');
            $field_group->addAttribute('name', $name);
            foreach ($definition as $k => $def) {
                $this->buildXmlElement($k, $def, $field_group);
            }
        } else {
            /*
             * We are dealing with field
             */
            $def = new Registry($definition);
            if ($xml->getName() != 'fieldset') {
                $def->def('fieldset', 'basic');
            }
            $fieldset_structure = $def->get('fieldset', null);
            $fieldset_structure = explode('.', $fieldset_structure);
            $existing_xpath[] = array_shift($fieldset_structure);
            while ($xml->xpath(implode('/', array_map(function ($name) {
                return 'fieldset[@name="' . $name . '"]';
            }, $existing_xpath)))) {
                $existing_xpath[] = array_shift($fieldset_structure);
            }
            $fieldset_name = array_pop($existing_xpath);
            if ($existing_xpath) {
                $xml = $xml->xpath(implode('/', array_map(function ($name) {
                    return 'fieldset[@name="' . $name . '"]';
                }, $existing_xpath)))[0];
            }

            $def->set('fieldset', implode('.', $fieldset_structure));
            if ($fieldset_name) {
                $xml = $xml->addChild('fieldset');
                $xml->addAttribute('name', $fieldset_name);
                $xml->addAttribute('label', strtoupper($fieldset_name . '_label'));
                $this->buildXmlElement($name, $def->toArray(), $xml);
            } else {
                /** @var \SimpleXMLElement $field */
                $field = $xml->addChild('field');
                /*
                 * Force because of
                 *
                 * <fieldset name="site"
                 * <fields name="seo"
                 * <fieldset name="seo"
                 * <field name="yandex.metrika"
                 *
                 * results in [seo][yandex][yandex.metrika]
                 */
                /*if(!isset($definition['name'])){*/
                $definition['name'] = $name;
                /*}*/
                if (!isset($definition['label'])) {
                    $definition['label'] = strtoupper(static::getShortName() . '_' . $definition['name'] . '_LABEL');
                }
                if (!isset($definition['description'])) {
                    $definition['description'] = $definition['label'] . '_DESC';
                }
                foreach ($definition as $attr => $attr_value) {
                    if (!in_array($attr, $this->_ignore_in_xml)) {
                        if (in_array($attr, array('option'))) {
                            foreach ($attr_value as $kopt => $opt) {
                                $option = $field->addChild('option', $opt);
                                $option->addAttribute('value', $kopt);
                            }
                        } else {
                            $field->addAttribute($attr, $attr_value);
                        }
                    }
                }
            }
        }
    }

    /**
     * List of field names as key for field describing arrays
     *
     * @var array $_field_defenitions
     *
     * @since 1.0
     */
    public static function getShortName()
    {
        static $shortName = '';
        if (!$shortName) {
            $ns = explode('\\', static::class);
            $shortName = array_pop($ns);
        }
        return $shortName;
    }

    /**
     * @return string
     *
     * @since 1.1
     */
    public function getContext()
    {
        return $this->_context;
    }

    /**
     * Use for needed form customizations
     *
     * @param \JForm $form Joomla Form
     *
     *
     * @since 1.0
     */
    protected function preprocessForm(\JForm &$form)
    {
        if (array_key_exists('asset_id', $this->getFields())) {
            $xml = file_get_contents(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'form' . DIRECTORY_SEPARATOR . 'permissions.xml');
            $contextArr = explode('.', $this->_context);
            $component = array_shift($contextArr);
            $xml = str_replace('{{component}}', $component, $xml);
            $permissionsLoaded = $form->load($xml, false);
            /*
             * TODO: remove as soon as joomla is flexible enough
             * Fix of joomla permissions check script
             */
            $options = new Registry;
            $options->set('relative', true);
            $options->set('pathOnly', true);
            $file = 'form/permissions-fix.js';
            $path = \JHtml::script($file, $options->toArray());
            if (!$path) {
                $path = \JLoader::getNamespaces('psr4')['JoomPlaceX'][0] . DIRECTORY_SEPARATOR . $file;
                $path = str_replace(JPATH_SITE, '', $path);
            }
            \JFactory::getDocument()->addScript($path);
        }
    }

    /**
     * Clear cache conditions
     *
     * @since 1.0
     */
    public function clearCache()
    {
        $this->_cache['conditioner'] = null;
    }

    /**
     * Get list of objects matching passed conditions
     *
     * @param bool $limitstart Start offset
     * @param bool $limit Limit
     * @param array $conditioner Conditions for select
     * @param string $by Column name to use as array key
     *
     * @return mixed Array of current instances
     *
     * @since 1.0
     */
    public function getList(
        $limitstart = false,
        $limit = false,
        $conditioner = array(),
        $class = null,
        $by = ''
    ) {
        if ($limit === false) {
            $limit = $this->getState('list.limit');
        }
        if ($limitstart === false) {
            $limitstart = $this->getState('list.limitstart');
        }
        if ($this->_cache['conditioner'] === $conditioner
            && $this->_cache['limit'] === $limit
            && $this->_cache['by'] === $by
            && $this->_cache['limitstart'] === $limitstart
        ) {
            return $this->_cache['list'];
        } else {
            $this->_cache['conditioner'] = $conditioner;
            $this->_cache['list'] = null;
            $this->_cache['pagination'] = null;
            $this->_cache['by'] = '';
        }
        /** @var \JDatabaseDriverMysqli $db */
        $db = $this->_db;
        $query = $this->getListQuery($conditioner);

        if ($limit) {
            /*
             * prepare pagination
             */
            $this->_limit = $limit;
            $cquery = clone $query;
            if ($cquery->type == 'select'
                && $cquery->group === null
                && $cquery->union === null
                && $cquery->unionAll === null
                && $cquery->having === null
            ) {
                $cquery->clear('select')->clear('order')->clear('limit')
                    ->clear('offset')->select('COUNT(*)');
                $this->_total = $db->setQuery($cquery)->loadResult();
            } else {
                $cquery->clear('limit')->clear('offset');
                $db->setQuery($cquery);
                $db->execute();
                $this->_total = (int)$db->getNumRows();
            }
            $offset = $this->getStart($limitstart, $limit);
            $db->setQuery($query, $offset, $limit);
            $this->setState('list.limit', $limit, true);
            $this->setState('list.limitstart', $limitstart, true);
        } else {
            $db->setQuery($query);
        }
        $this->_cache['list'] = $db->loadObjectList($by, $class ? $class : get_class($this));
        if (is_subclass_of($class ? $class : get_class($this), \JTable::class)) {
            $result = true;
            /** @var Model $row */
            foreach ($this->_cache['list'] as &$row) {
                $row->_observers->update('onAfterLoad', array(&$result, $row->getProperties()));
                /*
                 * TODO: remove bind & link(&$row)
                 * Temp hack - until observers are not implemented for JSON
                 */
                $row->bind($row->getProperties());
            }
        }

        return $this->_cache['list'];
    }

    /**
     * Get user state or it's specific entry
     *
     * @param string $var Path
     * @param null $default Default value
     *
     * @return \Joomla\Registry\Registry|mixed
     *
     * @since 1.0
     */
    public function getState($var = '', $default = null)
    {
        $this->populateState();
        if ($var) {
            return $this->_user_state->get($var, $default);
        } else {
            return $this->_user_state;
        }
    }

    /**
     * State auto-population
     *
     * @since 1.0
     */
    public function populateState()
    {
        if (!$this->_user_state) {
            $this->_user_state = new \Joomla\Registry\Registry();
            $this->_user_state->set('list.limitstart',
                \JFactory::getApplication()
                    ->getUserStateFromRequest('list.limitstart', 'limitstart',
                        0));
            $this->_user_state->set('list.limit', \JFactory::getApplication()
                ->getUserStateFromRequest('list.limit', 'limit', 20));
            $this->_user_state->set('list.ordering', \JFactory::getApplication()
                ->getUserStateFromRequest('list.ordering', 'filter_order',
                    array_key_exists('ordering', $this->getFields()) ? 'ordering' : 'id'));
            $this->_user_state->set('list.direction',
                \JFactory::getApplication()
                    ->getUserStateFromRequest('list.direction',
                        'filter_order_Dir', 'asc'));
        }
    }

    /**
     * Generate query for conditions
     *
     * @param array $conditioner Conditions
     *
     * @return \JDatabaseQuery Query
     *
     * @since 1.0
     */
    public function getListQuery($conditioner = array())
    {
        if (!is_array($conditioner)) {
            $conditioner = array($this->_primary_key => $conditioner);
        }

        /** @var \JDatabaseDriverMysqli $db */
        $db = $this->_db;

        /*
         * Initialise the query
         */
        $query = $db->getQuery(true)
            ->select($db->qn('a') . '.*')
            ->from($db->qn($this->_table, 'a'));
        $fields = array_keys($this->getProperties());

        foreach ($conditioner as $field => $value) {
            /*
             *  Check that $field is in the table.
             */
            if (!in_array($field, $fields)) {
                throw new \UnexpectedValueException(sprintf('Missing field in database: %s &#160; %s.',
                    get_class($this), $field));
            }

            if (is_int($value)) {
                $query->where($this->_db->quoteName('a.' . $field) . ' = '
                    . $this->_db->quote($value));
            } else {
                if (is_string($value)) {
                    $query->where($this->_db->quoteName('a.' . $field)
                        . ' LIKE ' . $this->_db->quote($value));
                } else {
                    if (is_array($value)) {
                        if (array_keys($value) == range(0, count($value) - 1)) {
                            foreach ($value as &$v) {
                                $v = $this->_db->q($v);
                            }
                            $query->where($this->_db->quoteName('a.' . $field)
                                . ' IN (' . implode(',', $value) . ')');
                        } else {
                            if ($value['strict']) {
                                $query->where($this->_db->quoteName('a.' . $field)
                                    . ' ' . $value['strict']);
                                unset($value['strict']);
                            }
                            $type = $value['type'] ? $value['type'] : 'AND';
                            unset($value['type']);
                            $where = array();
                            foreach ($value AS $o => $v) {
                                $where[] = $this->_db->quoteName('a.' . $field)
                                    . ' ' . $o . ' ' . $this->_db->quote($v);
                            }
                            if ($where) {
                                $query->where('(' . implode(' ' . $type . ' ', $where) . ')');
                            }
                        }
                    }
                }
            }
        }

        $listOrd = $this->getState('list.ordering');
        $listDir = $this->getState('list.direction');
        if (!in_array($listOrd, $this->getColumns('list', true))) {
            $listOrd = $this->_primary_key;
            $listDir = 'ASC';
        }

        $query->order($db->qn('a.' . $listOrd) . ' ' . $listDir);

        return $query;
    }

    /**
     * Get columns visiable according to context
     *
     * @param string $lrf Context
     * @param bool $include_hidden Force hidden to be included
     *
     * @return array Columns names
     *
     * @since 1.0
     */
    public function getColumns($lrf = 'list', $include_hidden = false)
    {
        if (!$include_hidden) {
            $fields = array_filter(static::getDefinitions(),
                function ($field) use ($lrf) {
                    if ($field['type'] == 'hidden'
                        || (isset($field['hide_at'])
                            && in_array($lrf, $field['hide_at']))
                    ) {
                        return false;
                    }

                    return true;
                });
        } else {
            $fields = static::getDefinitions();
        }
        $columns = array_keys($fields);

        return $columns;
    }

    /**
     * Recalculate limit start according to total
     *
     * @param $start Limit start
     * @param $limit Limit
     *
     * @return int|mixed New limit start
     *
     * @since 1.0
     */
    protected function getStart($start, $limit)
    {
        $this->_offset = $start;
        if ($start > $this->_total - $limit) {
            $this->_offset = max(0,
                (int)(ceil($this->_total / $limit) - 1) * $limit);
        }

        return $this->_offset;
    }

    /**
     * Set value for specific state entry
     *
     * @param      $var            Path
     * @param      $value          Value
     * @param bool $set_user_state Flag of need to set as User state
     *
     * @since 1.0
     */
    public function setState($var, $value, $set_user_state = false)
    {
        $this->populateState();
        $this->_user_state->set($var, $value);
        if ($set_user_state) {
            \JFactory::getApplication()->setUserState($var, $value);
        }
    }

    /**
     * Get total count of matches for current conditions
     *
     * @param array $conditioner Conditions
     *
     * @return int|mixed Total
     *
     * @since 1.0
     */
    public function getTotal($conditioner = array())
    {
        if (!$this->_total && $conditioner === $this->_cache['conditioner']) {
            /** @var \JDatabaseDriverMysqli $db */
            $db = $this->_db;
            $cquery = $this->getListQuery($conditioner);
            if ($cquery->type == 'select'
                && $cquery->group === null
                && $cquery->union === null
                && $cquery->unionAll === null
                && $cquery->having === null
            ) {
                $cquery->clear('select')->clear('order')->clear('limit')
                    ->clear('offset')->select('COUNT(*)');
                $this->_total = $db->setQuery($cquery)->loadResult();
            } else {
                $cquery->clear('limit')->clear('offset');
                $db->setQuery($cquery);
                $db->execute();
                $this->_total = (int)$db->getNumRows();
            }
        }

        return $this->_total;
    }

    /**
     * Get current pagination
     *
     * @return mixed JPagination object
     *
     * @since 1.0
     */
    public function getPagination()
    {
        if (!$this->_cache['pagination']) {
            $this->_cache['pagination'] = new \JPagination($this->_total,
                $this->_offset, $this->_limit);
        }

        return $this->_cache['pagination'];
    }

    /**
     * Render control for the list view
     *
     * @param $field Column name
     *
     * @return string Control Html
     *
     * @since 1.0
     */
    public function renderListControl($field)
    {
        $defenition = static::getDefinitions()[$field];

        $field_processer = '_renderListControl' . $field;
        if (method_exists($this, $field_processer)) {
            $layout = $this->$field_processer($field);
        } else {
            $field_processer = '_renderListControl' . $defenition['type'];
            if (method_exists($this, $field_processer)) {
                $layout = $this->$field_processer($field);
            } else {
                if (method_exists(static::getDefinitions()[$field]['type'],
                    'renderHtml')) {
                    $fieldClass = static::getDefinitions()[$field]['type'];
                    // TODO: change to user call
                    $fieldClass = new $fieldClass;
                    $layout = $fieldClass->renderHtml($this->getProperties(), $field);
                } else {
                    $layout = \JLayoutHelper::render('fields.list.' . $field, $this->$field,
                        dirname(__FILE__) . DIRECTORY_SEPARATOR . 'layouts');
                    if (!$layout) {
                        $layout = \JLayoutHelper::render('fields.list.' . static::getDefinitions()[$field]['type'],
                            $this->$field, dirname(__FILE__) . DIRECTORY_SEPARATOR . 'layouts');
                        if (!$layout) {
                            if (\JFactory::getConfig()->get('debug')) {
                                ob_start();
                                echo $this->$field;
                                echo "<pre>";
                                print_r(static::getDefinitions()[$field]);
                                echo "</pre>";
                                $layout = ob_get_contents();
                                ob_end_clean();
                            } else {
                                $layout = $this->$field;
                            }
                        }
                    }
                }
            }
        }
        return $layout;
    }

    function __get($name)
    {
        if (strpos($name, '.')) {
            $complex_name = explode('.', $name);
            $field = array_shift($complex_name);
            if (is_string($this->$field)) {
                $this->$field = new \Joomla\Registry\Registry($this->$field);
            }
            return $this->$field->get(implode('.', $complex_name));
        } else {
            return $this->$name;
        }
    }

    /**
     * Saves the manually set order of records.
     *
     * @param   array $pks An array of primary key ids.
     * @param   integer $order +1 or -1
     *
     * @return  boolean|JException  Boolean true on success, boolean false or JException instance on error
     *
     * @since   1.0
     */
    public function saveorder(array $pks = null, $order = null)
    {
        //$table = $this->getTable();
        //$tableClassName = get_class($table);
        //$contentType = new JUcmType;
        //$type = $contentType->getTypeByTable($tableClassName);
        //$tagsObserver = $table->getObserverOfClass('JTableObserverTags');
        $conditions = array();

        if (empty($pks)) {
            return \JError::raiseWarning(500, \JText::_('ERROR_NO_ITEMS_SELECTED'));
        }

        // Update ordering values
        foreach ($pks as $i => $pk) {
            $this->load((int)$pk);

            // Access checks.
            if (!$this->canEditState()) {
                // Prune items that you can't change.
                unset($pks[$i]);
                \JLog::add(\JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'),
                    \JLog::WARNING, 'jerror');
            } elseif ($this->ordering != $order[$i]) {
                $this->ordering = $order[$i];

//                if ($type)
//                {
//                    $this->createTagsHelper($tagsObserver, $type, $pk, $type->type_alias, $table);
//                }

                if (!$this->store()) {
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

    /**
     * @return bool If state is editable by current user
     *
     * @since 1.0
     */
    public function canEditState()
    {
        return true;
    }

    /**
     * Store current Object into DB
     *
     * @param bool $updateNulls Is update of null columns should
     *                          be triggered
     *
     * @return bool Storing attempt result
     *
     * @since 1.0
     */
    public function store($updateNulls = false)
    {
        if (array_key_exists('ordering', static::getDefinitions())
            && !$this->ordering
        ) {
            $this->ordering = $this->getNextOrder();
        }

        foreach (static::getDefinitions() as $field => $fdata) {
            if (isset($fdata['type'])) {
                if (method_exists($fdata['type'], 'onBeforeStore')) {
                    if (!call_user_func_array(array(
                        $fdata['type'],
                        'onBeforeStore'
                    ), array(&$this, $field, $fdata))
                    ) {
                        return false;
                    }
                }
            }
        };

        /*
         * TODO: move to observer
         */
        foreach (static::processAsJson() as $fn) {
            if (!is_string($this->$fn)) {
                if ($this->$fn instanceof Registry) {
                    $this->$fn = $this->$fn->toString();
                } else {
                    $this->$fn = json_encode($this->$fn);
                }
            }
        }

        /*
         * Process onfly category creation
         */
        $categoryeditField = array_search('categoryedit', array_column(static::getDefinitions(), 'type', 'name'));
        if ($categoryeditField) {
            $formData = \JFactory::getApplication()->input->get('jform', array(), 'ARRAY');
            \JLoader::register('CategoriesHelper',
                JPATH_ADMINISTRATOR . '/components/com_categories/helpers/categories.php');
            // Cast catid to integer for comparison
            $catid = (int)$formData[$categoryeditField];
            $cat_extension = $this->_context;
            // Check if New Category exists
            if ($catid > 0) {
                $catid = \CategoriesHelper::validateCategoryId($formData[$categoryeditField], $cat_extension);
            }
            $gcontext = explode('.', $this->_context)[0];
            // Save New Categoryg
            if ($catid == 0 && \JFactory::getUser()->authorise('core.create', $gcontext)) {
                $table = array();
                $table['title'] = $formData[$categoryeditField];
                $table['parent_id'] = 1;
                $table['extension'] = $cat_extension;
                $table['language'] = $formData['language'] ? $formData['language'] : '*';
                $table['published'] = 1;
                // Create new category and get catid back
                $this->$categoryeditField = \CategoriesHelper::createCategory($table);
            }
        }

        $return = parent::store($updateNulls);

        /*
         * TODO: move to observer
         */
        foreach (static::processAsJson() as $fn) {
            if (is_string($this->$fn)) {
                $this->$fn = new Registry($this->$fn);
            }
        }

        foreach (static::getDefinitions() as $field => $fdata) {
            if (isset($fdata['type'])) {
                if (method_exists($fdata['type'], 'onAfterStore')) {
                    if (!call_user_func(array($fdata['type'], 'onAfterStore'),
                        array(&$this, $field, $fdata))
                    ) {
                        $return = false;
                    }
                }
            }
        }

        return $return;
    }

    /**
     * @param array $cid Ids to delete
     *
     * @return bool|int Count of affected entries
     *
     * @since 1.0
     */
    public function remove(array $cid)
    {
        $counter = 0;
        foreach ($cid as $id) {
            if ($this->delete($id)) {
                $counter++;
            }
        }
        if ($counter) {
            \JFactory::getApplication()
                ->enqueueMessage(\JText::sprintf('ITEMS_DELETED', $counter));

            return $counter;
        } else {
            return false;
        }
    }

    public function toStd()
    {
        return (object)$this->getProperties();
    }

    protected function _getAssetName()
    {
        return $this->_context . '.' . $this->id;
    }

    protected function _getAssetParentId(JTable $table = null, $id = null)
    {
        /** @var \JTableAsset $asset */
        $asset = JTable::getInstance('Asset');
        $context = explode('.', $this->_context);
        while ($context) {
            $asset->loadByName(implode('.', $context));
            if ($asset->id) {
                break;
            }
            array_pop($context);
        }
        if (!$asset->id) {
            return parent::_getAssetParentId($table, $id);
        } else {
            return $asset->id;
        }
    }

}