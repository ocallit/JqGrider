<?php
/** @noinspection PhpUnused */
/** @noinspection SqlNoDataSourceInspection */

namespace Ocallit\JqGrider\Lookuper;

use Exception;
use Ocallit\Sqler\SqlExecutor;
use Ocallit\Sqler\SqlUtils;

class LookupManager {
    protected SqlExecutor $sqlExecutor;
    protected string $tableName;
    protected array $lookup_registry = [];
    protected bool $canList;
    protected bool $canAdd;
    protected bool $canEdit;
    protected bool $canDelete;
    protected bool $canReorder;

    public function __construct(SqlExecutor $sqlExecutor, $tableName = '', bool $canList = true, bool $canAdd = true, bool $canEdit = true, bool $canDelete = true, bool $canReorder = true) {
        $this->sqlExecutor = $sqlExecutor;
        $this->tableName = $tableName;
        $this->canList = $canList;
        $this->canAdd = $canAdd;
        $this->canEdit = $canEdit;
        $this->canDelete = $canDelete;
        $this->canReorder = $canReorder;
    }

    /**
     * Handles the AJAX request based on the action.
     *
     * @param array $request The request data.
     * @return array The response data.
     */
    public function handleRequest(array $request): array {
        try {
            $categoria = $request['categoria'] ?? '';
            if(empty($categoria)) {
                return ['success' => FALSE, 'error' => 'Catálogo no proporcionado'];
            }
            if($categoria !== $this->tableName) {
                return ['success' => FALSE, 'error' => 'Catálogo inválido'];
            }
            if(!$this->isCategoriaValid()) {
                return ['success' => FALSE, 'error' => 'Catálogo no reconocido'];
            }
        } catch(Exception) {
            $errorNo = '(' . $this->sqlExecutor->getLastErrorNumber() . ')';
            return ['success' => FALSE, 'error' => "Problemas en la base de datos para determinar el catálogo, intente mas tarde $errorNo",
              'error_no' => $this->sqlExecutor->getLastErrorNumber(),
              'error_log' =>  $this->sqlExecutor->getErrorLog(),
              'php_last_error' => error_get_last()
            ];
        }

        $action = $request['accion'] ?? '';
        try {
            return match ($action) {
                'add' => $this->addCategory($request),
                'update' => $this->updateCategory($request),
                'delete' => $this->deleteCategory($request),
                'reorder' => $this->reorderCategories($request),
                'list' => $this->list(),
                default => ['success' => FALSE, 'error' => 'Solicitud no reconocida'],
            };
        } catch(Exception) {
            $errorNo = '(' . $this->sqlExecutor->getLastErrorNumber() . ')';
            if ($this->sqlExecutor->is_last_error_duplicate_key()) {
                return ['success' => false, 'error' => 'Nombre duplicado, ya está registrado'];
            } elseif ($this->sqlExecutor->is_last_error_invalid_foreign_key()) {
                return ['success' => false,
                  'error' => 'Lo están usando, no se puede borrar. Márquelo como Inactivo y ya no se usará.'];
            } elseif ($this->sqlExecutor->is_last_error_child_records_exist()) {
                return ['success' => false,
                  'error' => 'No se puede borrar porque tiene registros relacionados. Márquelo como Inactivo.'];
            } else {
                return ['success' => false,
                  'error' => "Ocurrió un error inesperado. Por favor, inténtelo de nuevo más tarde. $errorNo"];
            }
        }
    }

    /**
     * Checks if the categoria exists in the lookup_registry table.
     *
     * @return bool True if the categoria exists, false otherwise.
     * @throws Exception
     */
    protected function isCategoriaValid(): bool {
        $method = __METHOD__;
        $sql = "SELECT /*$method*/ * FROM lookup_registry WHERE tabla = ?";
        try {
            $this->lookup_registry = $this->sqlExecutor->row($sql, [$this->tableName]);
            return !empty($this->lookup_registry);
        } catch(Exception $e) {
            if(!$this->sqlExecutor->is_last_error_table_not_found())
                throw $e;
        }
        $tableSql = "
            CREATE /*$method*/ TABLE IF NOT EXISTS lookup_registry (
                id MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                tabla VARCHAR(191) NOT NULL,
                label VARCHAR(191) NOT NULL,
                plural VARCHAR(191) NULL DEFAULT NULL,
                activo ENUM ('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
                orden SMALLINT UNSIGNED NOT NULL DEFAULT 100,
                registrado DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                registrado_por VARCHAR(16) NOT NULL DEFAULT '?',
                ultimo_cambio DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                ultimo_cambio_por VARCHAR(16) NOT NULL DEFAULT '?',
                UNIQUE KEY unico(tabla),
                KEY label(label)
            )";
        $this->sqlExecutor->query($tableSql);
        $this->lookup_registry = $this->sqlExecutor->row($sql, [$this->tableName]);
        return !empty($this->lookup_registry);
    }

    /**
     * Adds a new category.
     *
     * @param array $request The request data.
     * @return array The response data.
     * @throws Exception
     */
    protected function addCategory(array $request): array {
        if (!$this->canAdd) {
            return ['success' => FALSE, 'error' => 'Sin Permiso'];
        }
        $label = $request['label'] ?? '';
        if(empty($label)) {
            return ['success' => FALSE, 'error' => 'Falto el nombr'];
        }
        $method = __METHOD__;
        $tableName = SqlUtils::fieldIt($this->tableName);
        $query = "INSERT /*$method*/ INTO $tableName(label, activo) VALUES (?, 'Activo')";
        $this->sqlExecutor->query($query, [$label]);

        $id = $this->sqlExecutor->last_insert_id();
        return ['success' => TRUE, 'id' => $id];
    }

    /**
     * Updates an existing category.
     *
     * @param array $request The request data.
     * @return array The response data.
     * @throws Exception
     */
    protected function updateCategory(array $request): array {
        if (!$this->canEdit) {
            return ['success' => FALSE, 'error' => 'Sin Permiso'];
        }
        $id = $request['id'] ?? 0;
        $label = $request['label'] ?? '';
        $activo = $request['activo'] ?? 'Activo';

        if(empty($id) || empty($label)) {
            return ['success' => FALSE, 'error' => 'ID and Label are required'];
        }
        $method = __METHOD__;
        $tableName = SqlUtils::fieldIt($this->tableName);
        $query = "UPDATE /*$method*/ $tableName SET label = ?, activo = ? WHERE id = ?";
        $this->sqlExecutor->query($query, [$label, $activo, $id]);

        return ['success' => TRUE];
    }

    /**
     * Deletes a category.
     *
     * @param array $request The request data.
     * @return array The response data.
     * @throws Exception
     */
    protected function deleteCategory(array $request): array {
        if (!$this->canDelete) {
            return ['success' => FALSE, 'error' => 'Sin Permiso'];
        }
        $id = $request['id'] ?? 0;
        if(empty($id)) {
            return ['success' => FALSE, 'error' => 'ID is required'];
        }
        $method = __METHOD__;
        $tableName = SqlUtils::fieldIt($this->tableName);
        $query = "DELETE /*$method*/ FROM $tableName WHERE id = ?";
        $this->sqlExecutor->query($query, [$id]);

        return ['success' => TRUE];
    }

    /**
     * Reorders the categories.
     *
     * @param array $request The request data.
     * @return array The response data.
     * @throws Exception
     */
    protected function reorderCategories(array $request): array {
        if (!$this->canReorder) {
            return ['success' => FALSE, 'error' => 'Sin Permiso'];
        }
        $order = $request['order'] ?? [];
        if(empty($order)) {
            return ['success' => FALSE, 'error' => 'No llego el orden nuevo'];
        }
        $method = __METHOD__;
        $tableName = SqlUtils::fieldIt($this->tableName);
        foreach($order as $index => $id) {
            $query = "UPDATE /*$method*/ $tableName SET orden = ? WHERE id = ?";
            $this->sqlExecutor->query($query, [$index + 1, $id]);
        }
        return ['success' => TRUE];
    }

    /**
     * Retrieves the catalog of categories.
     *
     * @return array The response data.
     * @throws Exception
     */
    protected function list(): array {
        if(!$this->canList)
            return ['success' => FALSE, 'error' => 'Sin Permiso'];
        $method = __METHOD__;
        $tableName = SqlUtils::fieldIt($this->tableName);
        $query = "SELECT /*$method*/ id, label, activo FROM $tableName ORDER BY orden, label";
        try {
            $result = $this->sqlExecutor->array($query);
        } catch(Exception $e) {
            if($this->sqlExecutor->is_last_error_table_not_found()) {
                if(!$this->createTable())
                    throw $e;
                $result = $this->sqlExecutor->array($query);
            } else
                throw $e;
        }

        return [
          'success' => TRUE,
          'categoria' => $this->tableName,
          'label' => $this->lookup_registry['label'],
          'label_plural' => $this->lookup_registry['plural'] ?? $this->lookup_registry['label'],
          'values' => $result,
          'permissions' => [
            'list' => $this->canList,
            'add' => $this->canAdd,
            'edit' => $this->canEdit,
            'delete' => $this->canDelete,
            'reorder' => $this->canReorder,
          ]
        ];
    }

    protected function createTable():bool {
        try {
            $method = __METHOD__;
            $tableName = SqlUtils::fieldIt($this->tableName);
            $sql = "
            CREATE /*$method*/ TABLE $tableName (
                id MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                label VARCHAR(191) NOT NULL,
                activo ENUM ('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
                orden SMALLINT UNSIGNED NOT NULL DEFAULT 100,
                registrado DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                registrado_por VARCHAR(16) NOT NULL DEFAULT '?',
                ultimo_cambio DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                ultimo_cambio_por VARCHAR(16) NOT NULL DEFAULT '?',
                UNIQUE KEY nombre_unico(label)
            )";
            $this->sqlExecutor->query($sql);
            return TRUE;
        } catch(Exception) {
            return FALSE;
        }
    }

}