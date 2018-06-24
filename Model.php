<?php
/**
 * Copyright (c) 2018. Alexandr Kosarev, @kosarev.by
 */

namespace Joomplace\X;

use Illuminate\Database\Eloquent\Model as BaseModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomplace\X\Helper\ACL\Dummy;

class Model extends BaseModel
{
    use Dummy;

    public $timestamps = false;

    protected $defaults = [];
    protected $placeholders = [];
    protected $labels = [];

    public function __construct(array $attributes = [])
    {
        Loader::bootDatabase();
        parent::__construct($attributes);
    }

    public function getTable()
    {
        if (isset($this->table)) {
            return str_replace('#__', '', $this->table);
        } else {
            return parent::getTable();
        }
    }

    public function getName(){
        return (new \ReflectionClass($this))->getShortName();
    }

    public function getDefaults(){
        if(!$this->defaults){
            /** @var \Illuminate\Database\Connection $connection */
            $connection = $this->getConnection();
            $table = $connection->getTablePrefix() . $this->getTable();
            /** @var \Doctrine\DBAL\Schema\AbstractSchemaManager $schema */
            $schema = $connection->getDoctrineSchemaManager();
            $columns = $schema->listTableColumns($table);
            foreach ($this->fillable as $fillable){
                $this->defaults[$fillable] = $columns[$fillable]->getDefault();
            }
        }
        return $this->defaults;
    }

    public function getDefaultFor($column){
        return $this->getDefaults()[$column];
    }

    public function getPlaceholders(){
        if(!$this->placeholders){
            $table = strtoupper($this->getTable());
            foreach ($this->getDefaults() as $c => $default){
                $this->placeholders[$c] = Text::_('MODEL.'.$table.'_PLACEHOLDER_'.strtoupper($c));
            }
        }
        return $this->placeholders;
    }

    public function getPlaceholderFor($column){
        return $this->getPlaceholders()[$column];
    }

    public function getLabels(){
        if(!$this->labels){
            $table = strtoupper($this->getTable());
            foreach ($this->getDefaults() as $c => $default){
                $this->labels[$c] = Text::_('MODEL.'.$table.'_LABEL_'.strtoupper($c));
            }
        }
        return $this->labels;
    }

    public function getLabelFor($column){
        return $this->getLabels()[$column];
    }
}