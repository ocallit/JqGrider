<?php

namespace Ocallit\JqGrider\JqGrider;

use Exception;
use JsonSerializable;
use Ocallit\Sqler\DatabaseMetadata;
use Ocallit\Sqler\SqlExecutor;
use Ocallit\Sqler\SqlUtils;


class ColModelBuilder implements JsonSerializable {
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
        $metadata = $this->metadata->query($query);

        foreach ($metadata as $field) {
            $column = [
              'name' => $field['name'],
              'index' => $field['index']
            ];

            if ($field['flags'] & MYSQLI_PRI_KEY_FLAG) {
                $column['key'] = true;
            }

            if ($field['flags'] & MYSQLI_PRI_KEY_FLAG ||
              preg_match('/_id$/', $field['name'])) {
                $column['hidden'] = true;
            }

            if (!empty($field['orgtable'])) {
                $fkConfig = $this->handleForeignKey($field['orgtable'], $field['orgname']);
                if ($fkConfig) {
                    $column['template'] = $fkConfig;
                    $this->colModel[] = $column;
                    continue;
                }
            }


            $template = $this->getTemplateForField($field);
            if ($template) {
                $column['template'] = $template;
            }

            $this->colModel[] = $column;
        }

        return $this->colModel;
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
        // Get foreign key information from information schema
        $sql = "SELECT /" . __METHOD__ . "/ 
                kcu.REFERENCED_TABLE_NAME,
                kcu.REFERENCED_COLUMN_NAME
            FROM information_schema.KEY_COLUMN_USAGE kcu
            WHERE kcu.TABLE_SCHEMA = DATABASE()
                AND kcu.TABLE_NAME = ?
                AND kcu.COLUMN_NAME = ?
                AND kcu.REFERENCED_TABLE_NAME IS NOT NULL";

        $fk = $this->sqlExecutor->row($sql, [$tableName, $columnName]);
        if (empty($fk)) {
            return null;
        }

        // Get the first non-PK column from referenced table for labels
        $referencedTable = $fk['REFERENCED_TABLE_NAME'];
        $referencedColumn = $fk['REFERENCED_COLUMN_NAME'];

        $labelColumnSql = "SELECT /" . __METHOD__ . "/ COLUMN_NAME 
            FROM information_schema.COLUMNS 
            WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = ?
                AND COLUMN_NAME != ?
            ORDER BY ORDINAL_POSITION
            LIMIT 1";

        $labelColumn = $this->sqlExecutor->firstValue($labelColumnSql, [$referencedTable, $referencedColumn]);
        if (!$labelColumn) {
            return null;
        }

        // Build the dynamic select query with proper field escaping
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

        if ($field['flags'] & MYSQLI_UNSIGNED_FLAG) {
            $baseType = preg_replace('/\s*unsigned/', '', $type);
            return "getTemplate('{$baseType} unsigned')";
        }

        if (preg_match('/^enum\((.*)\)$/i', $type, $matches)) {
            return "getTemplate(\"$type\")";
        }

        if (preg_match('/^set\((.*)\)$/i', $type, $matches)) {
            return "getTemplate(\"$type\")";
        }

        if (preg_match('/^decimal\((\d+),(\d+)\)$/i', $type, $matches)) {
            return "getTemplate('decimal({$matches[1]},{$matches[2]})')";
        }

        if (preg_match('/^(var)?char\((\d+)\)$/i', $type, $matches)) {
            return "getTemplate(\"$type\")";
        }

        $basicTypes = [
          'tinyint', 'smallint', 'mediumint', 'int', 'bigint',
          'date', 'datetime', 'timestamp'
        ];

        if (in_array(strtolower($type), $basicTypes)) {
            return "getTemplate('$type')";
        }

        return null;
    }

    /**
     * Custom JSON serialization to handle template strings
     */
    public function jsonSerialize(): array {
        return array_map(function($col) {
            if (isset($col['template'])) {
                // Store template string to be processed later
                $col['__template__'] = $col['template'];
                unset($col['template']);
            }
            return $col;
        }, $this->colModel);
    }

    /**
     * Gets JSON string with proper template formatting
     */
    public function toJson(): string {
        $json = json_encode($this, JSON_PRETTY_PRINT);

        // Replace the template placeholder with actual JavaScript
        return preg_replace(
          '/"__template__":\s*"(getTemplate\([^)]+\))"/m',
          '...\\1',
          $json
        );
    }
}