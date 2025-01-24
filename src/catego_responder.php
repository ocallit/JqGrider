<?php
/** @noinspection PhpMissingParamTypeInspection */
/** @noinspection PhpRedundantOptionalArgumentInspection */




$response = ['success' => FALSE];
$categoria = sTrim($_POST['categoria'] ?? '');
$accion = $_POST['accion'] ?? '';
$sqlExecutor = new SqlExecutor();
function sTrim($s) {return trim($s);}

switch($accion) {
    case 'list':
        $meta = $sqlExecutor->row("
        SELECT categoria, label_singular, label_plural, min_selected, max_selected 
        FROM categoria 
        WHERE categoria = ?", [$categoria]);

        $values = $sqlExecutor->array("
        SELECT catego_id, label, activo
        FROM catego 
        WHERE categoria = ?
        ORDER BY orden, label", [$categoria]);

        echo json_encode([
          'categoria' => $meta['categoria'],
          'label_singular' => $meta['label_singular'],
          'label_plural' => $meta['label_plural'],
          'min_selected' => (int)$meta['min_selected'],
          'max_selected' => (int)$meta['max_selected'],
          'values' => $values
        ]);
        break;
    case 'add':
        $label = trim($_POST['label'] ?? '');
        if($label) {
            $result = $sqlExecutor->execute(
              "INSERT INTO catego (categoria, label) VALUES (?, ?)",
              [$categoria,  sTrim($label)]
            );
            if($result) {
                $response = [
                  'success' => TRUE,
                  'id' => $sqlExecutor->lastInsertId()
                ];
            }
        }
        break;

    case 'update':
        $catego_id = (int)($_POST['catego_id'] ?? 0);
        $label = sTrim($_POST['label'] ?? '');
        $activo = $_POST['activo'] === 'Si' ? 'Activo' : 'Inactivo';

        if($catego_id && $label) {
            $result = $sqlExecutor->execute(
              "UPDATE catego SET label = ?, activo = ? WHERE catego_id = ? AND categoria = ?",
              [$label, $activo, $catego_id, $categoria]
            );
            $response['success'] = (bool)$result;
        }
        break;

    case 'delete':
        $catego_id = (int)($_POST['catego_id'] ?? 0);
        if($catego_id) {
            $result = $sqlExecutor->execute(
              "DELETE FROM catego WHERE catego_id = ? AND categoria = ?",
              [$catego_id, $categoria]
            );
            $response['success'] = (bool)$result;
        }
        break;

    case 'reorder':
        $order = $_POST['order'] ?? [];
        if($order && is_array($order)) {
            $sqlExecutor->beginTransaction();
            try {
                foreach($order as $position => $catego_id) {
                    $sqlExecutor->execute(
                      "UPDATE catego SET orden = ? WHERE catego_id = ? AND categoria =?",
                      [$position + 1, $catego_id, $categoria]
                    );
                }
                $sqlExecutor->commit();
                $response['success'] = TRUE;
            } catch(Exception $e) {
                $sqlExecutor->rollBack();
                $response['error'] = 'Error updating order';
            }
        }
        break;
}

header('Content-Type: application/json');
echo json_encode($response);

