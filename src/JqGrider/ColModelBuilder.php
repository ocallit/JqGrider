<?php

namespace Ocallit\JqGrider\JqGrider;

use Exception;
use JsonSerializable;
use Ocallit\Sqler\DatabaseMetadata;
use Ocallit\Sqler\SqlExecutor;
use Ocallit\Sqler\SqlUtils;


class ColModelBuilder {
    protected $version = '1.0.1';
    protected SqlExecutor $sqlExecutor;
    protected array $colModel = [];
    protected DatabaseMetadata $metadata;
    protected array $fkReferences = [];

    public function __construct(SqlExecutor $sqlExecutor) {
        $this->sqlExecutor = $sqlExecutor;
        $this->metadata = DatabaseMetadata::getInstance($sqlExecutor);
    }

    /**
     * Builds the colModel array for jqGrid based on an SQL query
     *
     * @param string $query The SQL SELECT query
     * @return array The colModel configuration
     * @throws Exception
     */
    public function buildFromQuery(string $query): array {
        $colModel = [];
        $metadata = $this->metadata->query($query);
        foreach($metadata as $field) {
            $column = [
              'name' => $field['name'],
              'index' => $field['index'],
            ];

            if($field['flags'] & MYSQLI_PRI_KEY_FLAG) {
                $column['key'] = TRUE;
            }

            if($field['flags'] & MYSQLI_PRI_KEY_FLAG ||
              preg_match('/_id$/', $field['name'])) {
                $column['hidden'] = TRUE;
            }

            if(!empty($field['orgtable'])) {
                $fkConfig = $this->handleForeignKey($field['orgtable'], $field['orgname']);
                if($fkConfig) {
                    $column['__colmoAddSelect__'] = $fkConfig;
                    $colModel[] = $column;
                    continue;
                }
            }


            $template = $this->getTemplateForField($field);
            if($template) {
                $column['__getTemplate__'] = $template;
            }

            $colModel[] = $column;
        }

        return $colModel;
    }

    /**
     * Handles foreign key configuration for a field
     *
     * @param string $tableName
     * @param string $columnName
     * @return string|null Template configuration for foreign key
     * @throws Exception
     */
    protected function handleForeignKey(string $tableName, string $columnName): ?string {
        $sql = "SELECT /*" . __METHOD__ . "*/ 
                kcu.REFERENCED_TABLE_NAME,
                kcu.REFERENCED_COLUMN_NAME
            FROM information_schema.KEY_COLUMN_USAGE kcu
            WHERE kcu.TABLE_SCHEMA = DATABASE()
                AND kcu.TABLE_NAME = ?
                AND kcu.COLUMN_NAME = ?
                AND kcu.REFERENCED_TABLE_NAME IS NOT NULL";

        $fk = $this->sqlExecutor->row($sql, [$tableName, $columnName]);
        if(empty($fk)) {
            return NULL;
        }

        // Get the first non-PK column from referenced table for labels
        $referencedTable = $fk['REFERENCED_TABLE_NAME'];
        $referencedColumn = $fk['REFERENCED_COLUMN_NAME'];

        $labelColumnSql = "SELECT /*" . __METHOD__ . "*/ COLUMN_NAME 
            FROM information_schema.COLUMNS 
            WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = ?
                AND COLUMN_NAME != ?
            ORDER BY ORDINAL_POSITION
            LIMIT 1";

        $labelColumn = $this->sqlExecutor->firstValue($labelColumnSql, [$referencedTable, $referencedColumn]);
        if(!$labelColumn) {
            return NULL;
        }

        $referencedTableEsc = SqlUtils::fieldIt($referencedTable);
        $referencedColumnEsc = SqlUtils::fieldIt($referencedColumn);
        $labelColumnEsc = SqlUtils::fieldIt($labelColumn);

        return sprintf(
          "colmoAddSelect(<?php echo json_encode(\$sqlExecutor->keyValue('SELECT %s, %s FROM %s ORDER BY %s')); ?>)",
          $referencedColumnEsc,
          $labelColumnEsc,
          $referencedTableEsc,
          $labelColumnEsc
        );
    }


    /**
     * Creates the template string for a field based on its MySQL type
     */
    protected function getTemplateForField(array $field): ?string {

        $type = $field['Type'];
        if(str_contains($type, "unsigned" ))
            return "getTemplate('$type')";
        if(str_starts_with($type, "enum"))
            return "getTemplate('$type')";
        if(str_starts_with($type, "set"))
            return "getTemplate('$type')";
        if(str_starts_with($type, "set"))
            return "getTemplate('$type')";
        if(str_starts_with($type, "decimal"))
            return "getTemplate('$type')";
        if(str_contains($type, "char"))
            return "getTemplate('$type')";

        $basicTypes = [
          'tinyint', 'smallint', 'mediumint', 'int', 'bigint',
          'date', 'datetime', 'timestamp',
        ];
        if(in_array(strtolower($type), $basicTypes)) {
            return "getTemplate('$type')";
        }

        return NULL;
    }

    public function toJson($colModel): string {
        $json = json_encode($colModel, JSON_PRETTY_PRINT);
        $pattern = '/"__colmoAddSelect__":\s*"colmoAddSelect\((.*?)\)/';
        $replacement = '...colmoAddSelect($1)';
        $json = preg_replace($pattern, $replacement, $json);

        $pattern = '/"__getTemplate__":\s*"getTemplate(\(.*\))"/';
        return preg_replace($pattern, '...getTemplate$1', $json);
    }
}
