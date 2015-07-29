<?php

require_once('templates.php');

class GeneratorSQL extends Templates {
	private $data = [];
	private $tables = [];
	private $triggers = [];
    private $foreignKeys = [];

	public function __construct($file) {
		$this->data = yaml_parse_file($file);
        $this->generateTables();
        $this->generateTriggers();
        $this->generateRelations();
	}

    private function oneToMany($relation, $tableOne, $tableMany) {
        if ( $relation == 'one' ) {
            $this->foreignKeys[] = sprintf(parent::$fk_one, 
                lcfirst($tableOne), 
                lcfirst($tableMany)
            );
        }
    }

    private function manyToMany($relation, $mainTable, $secondaryTable) {
        if ( $relation == 'many' ) {
            $mainTable = lcfirst($mainTable);
            $secondaryTable = lcfirst($secondaryTable);
            $key = $mainTable . '_' . $secondaryTable;

            $columns = sprintf("    \"%s_id\" INTEGER NOT NULL,\n",  $mainTable);
            $columns .= sprintf("    \"%s_id\" INTEGER NOT NULL,\n",  $secondaryTable);
            $columns .= sprintf('    UNIQUE ("%s_id", "%s_id")', $mainTable, $secondaryTable);

            $this->tables[$key] = sprintf(parent::$table_query, 
                $key, 
                $columns
            );

            $this->foreignKeys[] = sprintf(parent::$fk_many, 
                $mainTable, 
                $key,
                $mainTable . $secondaryTable
            );

            $this->foreignKeys[] = sprintf(parent::$fk_many, 
                $secondaryTable,
                $key,
                $mainTable . $secondaryTable
            );
        }
    }

    private function parseRelations($relation, $mainTable, $secondaryTable) {
        if ( isset($this->data[$secondaryTable]['relations'][$mainTable] )
         && $secondaryTable != $mainTable ) {

            if ( $this->data[$secondaryTable]['relations'][$mainTable] == 'many' ) {
                $this->oneToMany($relation, $mainTable, $secondaryTable);
                $this->manyToMany($relation, $mainTable, $secondaryTable);
                
                unset($this->data[$mainTable]['relations'][$secondaryTable]);
            }
        }
    }

    private function generateRelations() {
        foreach ( $this->data as $mainTable => $relations ) {
            if ( isset($relations['relations']) ) {
                foreach ( $relations['relations'] as $secondaryTable => $relation ) {
                    $this->parseRelations($relation, 
                        $mainTable, 
                        $secondaryTable
                    );
                }
            }
        }
    }

	private function generateTables() {
        foreach ( $this->data as $table => $fields ) {
            $tableName = lcfirst($table);
            $columns = sprintf(parent::$table_id, $tableName);

            foreach ( $fields['fields'] as $field => $type ) {
                $columns .= sprintf("\"%s_%s\" %s,\n    ", $tableName, $field, $type);
            }

            $columns .= sprintf(parent::$table_created, $tableName);
            $columns .= sprintf(parent::$table_updated, $tableName);

            $this->tables[$table] = sprintf(parent::$table_query, $tableName, rtrim($columns));
        }
    }

    private function generateTriggers() {
        foreach ( array_keys($this->data) as $table ) {
            $table = lcfirst($table);

            $this->triggers[$table] = str_replace('{table}', 
                $table, 
                parent::$trigger_query
            );
        }
    }

    public function dump($file) {
        $out = implode('', $this->tables) 
             . implode('', $this->foreignKeys) 
             . implode('', $this->triggers)
        ;

        file_put_contents($file, $out);
    }
}
    
$generator = new GeneratorSQL('schema.yaml');

$generator->dump('schema.sql');