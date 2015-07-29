<?php

class Templates {
    protected static $fk_one = 'ALTER TABLE "%1$s" ADD "%2$s_id" INTEGER NOT NULL,'
                               . "\n\t"
                               . 'ADD CONSTRAINT "fk_%1$s_%2$s_id" FOREIGN KEY ("%2$s_id")'
                               . "\n\t"
                               . 'REFERENCES "%2$s" ("%2$s_id");'
                               . "\n\n";
    protected static $fk_many = 'ALTER TABLE "%2$s" ADD CONSTRAINT "fk_%3$s_%1$s_id" FOREIGN KEY ("%1$s_id")'
                                . "\n\t"
                                . 'REFERENCES "%1$s" ("%1$s_id");'
                                . "\n\n";
    protected static $table_query =  'CREATE TABLE "%1$s" (
%2$s
);' . "\n\n";
    protected static $table_id = "    \"%s_id\" SERIAL PRIMARY KEY,\n    ";
    protected static $table_created = "\"%s_created\" timestamp NOT NULL DEFAULT now(),\n";
    protected static $table_updated = "    \"%s_updated\" timestamp NOT NULL DEFAULT now()\n";

    protected static $trigger_query = "CREATE OR REPLACE FUNCTION update_{table}_timestamp()\n"
                                      . "RETURNS TRIGGER AS $$\n"
                                      . "BEGIN\n"
                                      . "\tNEW.{table}_updated = now();\n"
                                      . "\tRETURN NEW;\n"
                                      . "END;\n"
                                      . "$$ language 'plpgsql';\n"
                                      . "CREATE TRIGGER \"tr_{table}_updated\" BEFORE UPDATE ON"
                                      . " \"{table}\" FOR EACH ROW EXECUTE PROCEDURE" 
                                      . " update_{table}_timestamp();\n\n";
}