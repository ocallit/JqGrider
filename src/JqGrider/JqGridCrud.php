<?php

namespace Ocallit\JqGrider\JqGrider;

use Ocallit\Sqler\SqlExecutor;
use Ocallit\Sqler\DatabaseMetadata;
use Ocallit\Sqler\QueryBuilder;
use Ocallit\Sqler\SqlUtils;

use Exception;
use function array_diff_key;
use function array_flip;
use function basename;
use function file_exists;
use function move_uploaded_file;
use function pathinfo;
use function preg_replace;
use function rtrim;

class JqGridCrud {
    protected string $version = '1.0.0';
    protected SqlExecutor $sql;
    protected string $tableName;
    protected DatabaseMetadata $metadata;
    protected QueryBuilder $queryBuilder;
    protected array $primaryKeys;
    protected ?string $uploadPath;
    protected array $prohibitedInsertColumns;
    protected array $prohibitedUpdateColumns;

    public function __construct(
      SqlExecutor $sql,
      string $tableName,
      ?string $uploadPath = null,
      array $prohibitedInsertColumns = [],
      array $prohibitedUpdateColumns = []
    ) {
        $this->sql = $sql;
        $this->tableName = $tableName;
        $this->metadata = DatabaseMetadata::getInstance();
        $this->queryBuilder = new QueryBuilder();
        $this->primaryKeys = $this->metadata->primaryKeys()[$tableName] ?? [];

        $this->uploadPath = $uploadPath ? rtrim($uploadPath, '/') . '/' : null;
        $this->prohibitedInsertColumns = $prohibitedInsertColumns;
        $this->prohibitedUpdateColumns = $prohibitedUpdateColumns;
    }

    /**
     * Handle CRUD operations based on jqGrid's oper parameter
     * @throws Exception
     */
    public function handleRequest(): array {
        $operation = $_POST['oper'] ?? 'list';
        $response = ['success' => false, 'message' => ''];

        try {
            $data = $_POST;
            if($this->uploadPath && $operation !== 'del') {
                $data = $this->handleFileUploads($data);
            }
            switch($operation) {
                case 'add':
                    $response = $this->add($data);
                    break;
                case 'edit':
                    $response = $this->edit($data);
                    break;
                case 'del':
                    $response = $this->delete($data);
                    break;
                default:
                    throw new Exception("Invalid operation: $operation");
            }
        } catch(Exception $e) {
            $response['success'] = false;
            $response['message'] = $e->getMessage();
            header("HTTP/1.1 500 Datos Invalidos");
            exit;
        }
        return $response;
    }

    /**
     * Handle file uploads for the request
     * @throws Exception
     */
    protected function handleFileUploads(array $data): array {
        if(empty($_FILES)) {
            return $data;
        }

        if(!is_dir($this->uploadPath) || !is_writable($this->uploadPath)) {
            throw new Exception("Upload directory is not writable: {$this->uploadPath}");
        }

        foreach($_FILES as $fieldName => $fileInfo) {
            if($fileInfo['error'] === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            if($fileInfo['error'] !== UPLOAD_ERR_OK) {
                $errorMessages = [
                  UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
                  UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
                  UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
                  UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
                  UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                  UPLOAD_ERR_EXTENSION => 'File upload stopped by extension',
                ];
                throw new Exception($errorMessages[$fileInfo['error']] ?? 'Unknown upload error');
            }

            // Sanitize filename from the database field
            if(!isset($data[$fieldName]) || empty($data[$fieldName])) {
                continue;
            }

            $filename = $this->sanitizeFilename($data[$fieldName]);
            $uploadFile = $this->uploadPath . $filename;

            // Ensure unique filename
            $baseFilename = pathinfo($filename, PATHINFO_FILENAME);
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            $counter = 1;
            while(file_exists($uploadFile)) {
                $newFilename = $baseFilename . '_' . $counter . '.' . $extension;
                $uploadFile = $this->uploadPath . $newFilename;
                $counter++;
            }

            if(!move_uploaded_file($fileInfo['tmp_name'], $uploadFile)) {
                throw new Exception("Failed to move uploaded file to {$uploadFile}");
            }

            // Update the field name in data with the actual saved filename
            $data[$fieldName] = basename($uploadFile);
        }

        return $data;
    }

    /**
     * Sanitize filename to prevent directory traversal and invalid characters
     */
    protected function sanitizeFilename(string $filename): string {
        // Remove any directory components
        $filename = basename($filename);

        // Remove any non-alphanumeric characters except dots, dashes, and underscores
        $filename = preg_replace('/[^A-Za-z0-9._-]/', '', $filename);

        // Ensure the filename isn't empty after sanitization
        if(empty($filename)) {
            throw new Exception('Invalid filename');
        }

        return $filename;
    }

    /**
     * Add new record
     * @throws Exception
     */
    protected function add(array $data): array {

        unset($data['oper'], $data['id']);
        $data = array_diff_key($data, array_flip($this->prohibitedInsertColumns));
        $query = $this->queryBuilder->insert($this->tableName, $data);
        $this->sql->query($query['query'], $query['parameters']);

        return [
          'success' => true,
          'message' => 'Record added successfully',
          'id' => $this->sql->last_insert_id()
        ];
    }

    /**
     * Edit existing record
     * @throws Exception
     */
    protected function edit(array $data): array {
        if(empty($this->primaryKeys)) {
            throw new Exception("No primary key defined for table {$this->tableName}");
        }

        $where = [];
        foreach($this->primaryKeys as $key) {
            if(!isset($data[$key])) {
                throw new Exception("Primary key $key not provided");
            }
            if($key !== 'id')
                unset($data['id']);
            $where[$key] = $data[$key];
        }

        unset($data['oper']);
        foreach($this->primaryKeys as $key) {
            unset($data[$key]);
        }
        $data = array_diff_key($data, array_flip($this->prohibitedUpdateColumns));
        $query = $this->queryBuilder->update($this->tableName, $data, $where);

        $this->sql->query($query['query'], $query['parameters']);

        return [
          'success' => true,
          'message' => 'Datos Guardados!'
        ];
    }

    /**
     * Delete record
     * @throws Exception
     */
    protected function delete(array $data): array {
        if(empty($this->primaryKeys)) {
            throw new Exception("No primary key defined for table {$this->tableName}");
        }
        $primaryKey = array_key_first($this->primaryKeys);

        if(empty($data['id'] ?? NULL)) {
            throw new Exception("Falto el ID");
        }

        $query = "DELETE FROM " . SqlUtils::fieldIt($this->tableName) . " WHERE " . SqlUtils::fieldIt($primaryKey) . "=?";

        $where = [$data["id"]];
        $this->sql->query($query, $where);

        return [

          'success' => true,
          'message' =>  ' record(s) deleted successfully'
        ];
    }


    /**
     * Uploads a file from a jqGrid file upload.
     *
     * @param string $savePath Directory path where the file will be saved.
     * @param string $fileKey Key name in the $_FILES array.
     * @param string $forceFileName If provided, this name will be used (without extension) instead of the uploaded filename.
     * @param bool $consecutiveSuffix If true, a consecutive suffix is added when a file exists; if false, any existing file is replaced.
     * @param bool $timestampCopy If true, a copy is also saved with a timestamp suffix (yyyy_mm_dd_H_i_s).
     * @param array $validExtensions Array of valid file extensions (e.g., ['jpg', 'png', 'pdf']).
     * @return string Full path (including filename and extension) where the file was saved.
     * @throws Exception if the upload fails or if the file extension is invalid.
     */
    public function uploadFile(
      string $savePath,
      string $fileKey,
      string $forceFileName = '',
      bool   $consecutiveSuffix = TRUE,
      bool   $timestampCopy = FALSE,
      array  $validExtensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'csv', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'],
    ): string {
        // Ensure the save path ends with a directory separator.
        $savePath = rtrim($savePath, '/\\') . DIRECTORY_SEPARATOR;

        // Check if the file was uploaded.
        if(!isset($_FILES[$fileKey])) {
            throw new Exception("No file uploaded with key '{$fileKey}'.");
        }

        $file = $_FILES[$fileKey];

        // Check for upload errors.
        if($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("File upload error: " . $file['error']);
        }

        // Retrieve original file information.
        $originalName = $file['name'];
        $fileInfo = pathinfo($originalName);
        // Use forced file name if provided, otherwise use the uploaded file's base name.
        $baseName = empty($forceFileName) ? $fileInfo['filename'] : $forceFileName;
        $extension = isset($fileInfo['extension']) ? strtolower($fileInfo['extension']) : '';

        // Validate file extension if a list of valid extensions is provided.
        if(!empty($validExtensions)) {
            $allowed = array_map('strtolower', $validExtensions);
            if(!in_array($extension, $allowed)) {
                throw new Exception("Invalid file extension '{$extension}'. Allowed extensions: " . implode(', ', $allowed));
            }
        }

        // Construct the target file path.
        $targetFile = $savePath . $baseName . '.' . $extension;

        // If the file exists, either add a consecutive suffix or replace it.
        if(file_exists($targetFile)) {
            if($consecutiveSuffix) {
                $counter = 1;
                do {
                    $targetFile = $savePath . $baseName . '_' . $counter . '.' . $extension;
                    $counter++;
                } while(file_exists($targetFile));
            }
            // If $consecutiveSuffix is false, the existing file will be replaced.
        }

        // Move the uploaded file to the target location.
        if(!move_uploaded_file($file['tmp_name'], $targetFile)) {
            throw new Exception("Failed to move the uploaded file.");
        }

        // If the timestamp flag is set, create a copy with the timestamp appended.
        if($timestampCopy) {
            $timestamp = date('Y_m_d_H_i_s');
            $timestampFile = $savePath . $baseName . '_' . $timestamp . '.' . $extension;
            if(!copy($targetFile, $timestampFile)) {
                throw new Exception("Failed to create a timestamped copy of the uploaded file.");
            }
        }

        // Return the full path where the file was saved.
        return $targetFile;
    }



}
