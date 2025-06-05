# Namespace: Ocallit\JqGrider

## File: `src/EventEmitter.php`

### Trait: `EventEmitter`

#### Methods

##### `attach`
```php
public function attach(string $event, callable $callback): void
```
*   **What it does:** Registers a callback function to be executed when a specific event occurs.
*   **What it's for:** To allow other parts of the code to listen for and react to events emitted by the class using this trait.
*   **What it returns:** `void`
*   **What the return means:** No value is returned.
*   **If it throws:** Does not explicitly throw.
*   **Parameters:**
    *   `event (string)`: The name of the event to listen for.
    *   `callback (callable)`: The function to be executed when the event is triggered.

##### `notify`
```php
public function notify(string $event, ...$args): void
```
*   **What it does:** Triggers all registered callbacks for a specific event, passing along any provided arguments.
*   **What it's for:** To signal that an event has occurred and to invoke all associated listeners.
*   **What it returns:** `void`
*   **What the return means:** No value is returned.
*   **If it throws:** Does not explicitly throw.
*   **Parameters:**
    *   `event (string)`: The name of the event to trigger.
    *   `...args (mixed)`: Optional variable arguments to pass to the event callbacks.

# Namespace: Ocallit\JqGrider\JqGrider

## File: `src/JqGrider/ColModelBuilder.php`

### Class: `ColModelBuilder`

#### Methods

##### `__construct`
```php
public function __construct(SqlExecutor $sqlExecutor)
```
*   **What it does:** Initializes the `ColModelBuilder` with a SQL executor.
*   **What it's for:** To set up the necessary dependencies, primarily the `SqlExecutor` for database interactions and metadata retrieval.
*   **What it returns:** `void` (implicitly, as it's a constructor).
*   **What the return means:** An instance of `ColModelBuilder` is created.
*   **If it throws:** Does not explicitly throw.
*   **Parameters:**
    *   `sqlExecutor (SqlExecutor)`: An instance of `SqlExecutor` used for database operations.

##### `buildFromQuery`
```php
public function buildFromQuery(string $query): array
```
*   **What it does:** Builds the `colModel` array for jqGrid based on the metadata of an SQL SELECT query.
*   **What it's for:** To dynamically generate the column configuration for a jqGrid by analyzing the result set of a given query.
*   **What it returns:** `array` - Shape: `[['name' => string, 'index' => string, 'key' => bool?, 'hidden' => bool?, '__colmoAddSelect__' => string?, '__getTemplate__' => string?], ...]`
*   **What the return means:** An array representing the `colModel` configuration for jqGrid. Each element is an associative array defining a column.
*   **If it throws:** `Exception`.
*   **Parameters:**
    *   `query (string)`: The SQL SELECT query to derive the `colModel` from.

##### `toJson`
```php
public function toJson($colModel): string
```
*   **What it does:** Converts a `colModel` array into a JSON string with special formatting for JavaScript function calls.
*   **What it's for:** To prepare the `colModel` array for embedding within JavaScript code, allowing parts to be treated as function calls.
*   **What it returns:** `string`
*   **What the return means:** A JSON string representation of the `colModel` with placeholders like `...colmoAddSelect(...)` and `...getTemplate(...)`.
*   **If it throws:** Does not explicitly throw.
*   **Parameters:**
    *   `colModel (mixed)`: The `colModel` array to be converted to JSON.

## File: `src/JqGrider/Filter2Where.php`

### Class: `Filter2Where`

#### Methods

##### `__construct`
```php
public function __construct(array $fullTextFields = [], int $innodb_ft_min_token_size = 3, int $innodb_ft_max_token_size = 84)
```
*   **What it does:** Initializes the `Filter2Where` class with optional configurations for full-text search.
*   **What it's for:** To set up parameters related to how full-text search operations are constructed.
*   **What it returns:** `void` (implicitly, as it's a constructor).
*   **What the return means:** An instance of `Filter2Where` is created.
*   **If it throws:** Does not explicitly throw.
*   **Parameters:**
    *   `fullTextFields (array)`: List of field names for full-text search. Default `[]`.
    *   `innodb_ft_min_token_size (int)`: Min token length for full-text search. Default `3`.
    *   `innodb_ft_max_token_size (int)`: Max token length for full-text search. Default `84`.

##### `array2Filter`
```php
public function array2Filter($array, $groupOp = 'AND', $op = 'eq'): array
```
*   **What it does:** Converts a simple associative array of field-value pairs into a jqGrid filter structure.
*   **What it's for:** To programmatically create a jqGrid filter object from a basic key-value map.
*   **What it returns:** `array` - Shape: `['groupOp' => string, 'rules' => [['field' => string, 'op' => string, 'data' => mixed], ...]]` or `[]`.
*   **What the return means:** An array structured like jqGrid's `postData.filter` object.
*   **If it throws:** Does not explicitly throw.
*   **Parameters:**
    *   `array (array)`: Associative array `['field1' => 'value1', ...]`.
    *   `groupOp (string)`: Grouping operator for rules (e.g., 'AND', 'OR'). Default `'AND'`.
    *   `op (string)`: Comparison operator for rules (e.g., 'eq', 'gt'). Default `'eq'`.

##### `filter2Where`
```php
public function filter2Where($filters, $groupOp = 'AND', $filterFieldOverride = null): string
```
*   **What it does:** Converts a jqGrid `postData.filter` (JSON string or array) into an SQL WHERE clause.
*   **What it's for:** To translate complex filter structures from jqGrid into an SQL WHERE condition.
*   **What it returns:** `string`
*   **What the return means:** SQL WHERE clause string (without "WHERE"), or empty string if no filters.
*   **If it throws:** Does not explicitly throw.
*   **Parameters:**
    *   `filters (string|array)`: jqGrid filter data (JSON string or array).
    *   `groupOp (string)`: Default grouping operator. Default `'AND'`.
    *   `filterFieldOverride (null|callable)`: Optional callback `function($ruleSolved):string` to customize SQL for rules.

##### `rule2Sql`
```php
public function rule2Sql($r, $filterFieldOverride = null): string
```
*   **What it does:** Converts a single jqGrid filter rule into an SQL condition string.
*   **What it's for:** To process an individual filter rule and generate SQL, allowing override via callback.
*   **What it returns:** `string`
*   **What the return means:** SQL condition string for the rule (e.g., "`fieldName` = 'value'"), or empty string.
*   **If it throws:** Does not explicitly throw.
*   **Parameters:**
    *   `r (array)`: jqGrid rule array with 'field', 'op', 'data' keys.
    *   `filterFieldOverride (null|callable)`: Optional callback `function($ruleSolved):string` to modify SQL for this rule.

## File: `src/JqGrider/JqGridCrud.php`

### Class: `JqGridCrud`

#### Methods

##### `__construct`
```php
public function __construct(
  SqlExecutor $sql,
  string $tableName,
  ?string $uploadPath = null,
  array $prohibitedInsertColumns = [],
  array $prohibitedUpdateColumns = []
)
```
*   **What it does:** Initializes `JqGridCrud` with DB connection, table name, and configurations.
*   **What it's for:** Sets up context for CRUD: target table, DB executor, upload path, prohibited columns.
*   **What it returns:** `void` (implicitly, as it's a constructor).
*   **What the return means:** `JqGridCrud` instance created.
*   **If it throws:** Does not explicitly throw.
*   **Parameters:**
    *   `sql (SqlExecutor)`: `SqlExecutor` instance.
    *   `tableName (string)`: Database table name.
    *   `uploadPath (?string)`: Optional file upload directory path. Default `null`.
    *   `prohibitedInsertColumns (array)`: Columns to exclude from inserts. Default `[]`.
    *   `prohibitedUpdateColumns (array)`: Columns to exclude from updates. Default `[]`.

##### `handleRequest`
```php
public function handleRequest(): array
```
*   **What it does:** Processes POST requests from jqGrid for add, edit, or delete operations.
*   **What it's for:** Main entry for jqGrid edits, dispatching to internal methods; handles file uploads.
*   **What it returns:** `array`
*   **What the return means:** Associative array with operation outcome. Success: `['success' => true, 'message' => string, 'id' => mixed? (for 'add')]`. Failure: `['success' => false, 'message' => string]` (exits with HTTP 500).
*   **If it throws:** Catches internal `Exception`s; may `exit`. PHPDoc shows `@throws Exception`.
*   **Parameters:** None (uses `$_POST`, `$_FILES`).

##### `uploadFile`
```php
public function uploadFile(
  string $savePath,
  string $fileKey,
  string $forceFileName = '',
  bool   $consecutiveSuffix = TRUE,
  bool   $timestampCopy = FALSE,
  array  $validExtensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'csv', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx']
): string
```
*   **What it does:** Manages uploading a single file from a jqGrid form submission.
*   **What it's for:** Robust file upload handling: validation, renaming, timestamped copies.
*   **What it returns:** `string`
*   **What the return means:** Full path (including filename) where the file was saved.
*   **If it throws:** `Exception` on upload failure (no file, error, invalid extension, move/copy fail).
*   **Parameters:**
    *   `savePath (string)`: Directory path to save the file.
    *   `fileKey (string)`: Key in `$_FILES` array.
    *   `forceFileName (string)`: Optional name for the saved file (no extension). Default `''`.
    *   `consecutiveSuffix (bool)`: If true and file exists, add numeric suffix. Default `TRUE`.
    *   `timestampCopy (bool)`: If true, save an additional timestamped copy. Default `FALSE`.
    *   `validExtensions (array)`: Allowed file extensions. Default common types.

## File: `src/JqGrider/JqGridReader.php`

### Class: `JqGridReader`

#### Methods

##### `__construct`
```php
public function __construct(SqlExecutor $sqlExecutor, array|null $parameters = null)
```
*   **What it does:** Initializes with SQL executor and request parameters.
*   **What it's for:** Sets up DB connection and jqGrid parameter source.
*   **What it returns:** `void` (implicitly, as it's a constructor).
*   **What the return means:** `JqGridReader` instance created.
*   **If it throws:** Does not explicitly throw.
*   **Parameters:**
    *   `sqlExecutor (SqlExecutor)`: `SqlExecutor` instance.
    *   `parameters (array|null)`: jqGrid request parameters; defaults to `$_REQUEST`.

##### `readTable`
```php
public function readTable(string $table, string $tableAlias = '', string|array $columns = "*", string $extraWhere = "", array $sumColumns = []): array
```
*   **What it does:** Fetches/formats data from a DB table for jqGrid.
*   **What it's for:** Provides data from a single table, applying jqGrid features.
*   **What it returns:** `array` - jqGrid response: `{page: int, total: int, records: int, rows: array, userdata: array, ?error: bool, ?message: string, ?code: int}`
*   **What the return means:** Data and metadata for jqGrid.
*   **If it throws:** Catches `Exception`, returns error structure.
*   **Parameters:**
    *   `table (string)`: DB table name.
    *   `tableAlias (string)`: Optional table alias. Default `''`.
    *   `columns (string|array)`: Columns to select. Default `'*'`.
    *   `extraWhere (string)`: Additional WHERE conditions. Default `""`.
    *   `sumColumns (array)`: Columns for footer summaries. Default `[]`.

##### `readQuery`
```php
public function readQuery(string $sqlReadRows, string $extraWhere = "", array $sumColumns = [] ): array
```
*   **What it does:** Fetches/formats data using a custom SQL query for jqGrid.
*   **What it's for:** Provides data via custom SQL, applying jqGrid features.
*   **What it returns:** `array` - jqGrid response, similar to `readTable`.
*   **What the return means:** Data and metadata for jqGrid.
*   **If it throws:** Catches `Exception`, returns error structure.
*   **Parameters:**
    *   `sqlReadRows (string)`: Custom SQL SELECT query.
    *   `extraWhere (string)`: Additional WHERE conditions. Default `""`.
    *   `sumColumns (array)`: Columns for footer summaries. Default `[]`.

##### `read`
```php
public function read(string $sqlReadRows, string $sqlTotals = '', bool $multipleFooter = false): array
```
*   **What it does:** Core method to fetch data with SQL for rows/totals, formats for jqGrid.
*   **What it's for:** Central data retrieval/formatting logic for other `read*` methods.
*   **What it returns:** `array` - jqGrid response.
*   **What the return means:** Data and metadata for jqGrid.
*   **If it throws:** Catches `Exception`, returns error structure.
*   **Parameters:**
    *   `sqlReadRows (string)`: Main SQL query for rows.
    *   `sqlTotals (string)`: SQL query for totals/summaries. Default `''` (count from `$sqlReadRows`).
    *   `multipleFooter (bool)`: `true` for separate statistic footer rows. Default `false`.

##### `sumColumns`
```php
public function sumColumns(array $sumColumns = []): array
```
*   **What it does:** Generates SQL SELECT part for summary statistics.
*   **What it's for:** Constructs SQL for aggregates in jqGrid footer.
*   **What it returns:** `array` - `['toSum' => string, 'multipleFooter' => bool]`
*   **What the return means:** SQL for summaries and multi-footer flag. `toSum` is "COUNT(*) as 'totalRows', SUM(`col1`) AS 'col1_sum', ..."; `multipleFooter` is `true` if 'all' or multiple stats per column requested.
*   **If it throws:** Does not explicitly throw.
*   **Parameters:**
    *   `sumColumns (array)`: Specifies columns and stats (e.g., `['col1', 'col2' => 'AVG', 'col3' => 'ALL']`). Default `[]`.

##### `buildWhereClause`
```php
public function buildWhereClause(): string
```
*   **What it does:** Constructs SQL WHERE clause from jqGrid filter parameters.
*   **What it's for:** Translates jqGrid request filter parameters into SQL.
*   **What it returns:** `string`
*   **What the return means:** The SQL WHERE clause (without "WHERE" keyword itself), or an empty string if no filters are active.
*   **If it throws:** Does not explicitly throw.
*   **Parameters:** None (uses instance's `$this->parameters`).

# Namespace: Ocallit\JqGrider\Lookuper

## File: `src/Lookuper/LookupManager.php`

### Class: `LookupManager`

#### Constants

##### `EVENT_MODIFIED`
```php
const EVENT_MODIFIED = 'lookup_modified';
```
*   **What it does:** Defines the event name for lookup table modifications.
*   **What it's for:** Consistent event name for observers of lookup table changes.
*   **Value:** `'lookup_modified'`

#### Methods

##### `__construct`
```php
public function __construct(SqlExecutor $sqlExecutor, $tableName = '', bool $canList = TRUE, bool $canAdd = TRUE, bool $canEdit = TRUE, bool $canDelete = TRUE, bool $canReorder = TRUE)
```
*   **What it does:** Initializes `LookupManager` with SQL executor, table name, and permissions.
*   **What it's for:** Sets up the manager for a specific lookup table with DB access and operation permissions.
*   **What it returns:** `void` (implicitly, as it's a constructor).
*   **What the return means:** `LookupManager` instance created.
*   **If it throws:** Does not explicitly throw.
*   **Parameters:**
    *   `sqlExecutor (SqlExecutor)`: `SqlExecutor` instance.
    *   `tableName (string)`: Name of the lookup table. Default `''`.
    *   `canList (bool)`: Permission to list. Default `TRUE`.
    *   `canAdd (bool)`: Permission to add. Default `TRUE`.
    *   `canEdit (bool)`: Permission to edit. Default `TRUE`.
    *   `canDelete (bool)`: Permission to delete. Default `TRUE`.
    *   `canReorder (bool)`: Permission to reorder. Default `TRUE`.

##### `handleRequest`
```php
public function handleRequest(array $request): array
```
*   **What it does:** Processes AJAX requests for actions (list, add, update, delete, reorder) on the lookup table.
*   **What it's for:** Main entry point for external (UI) interactions with the lookup table.
*   **What it returns:** `array`
*   **What the return means:** Operation result and any requested data (e.g., `['success' => TRUE/FALSE, 'error' => string?, 'values' => array?, ...]`).
*   **If it throws:** Catches internal `Exception`s, returns them in the response's `error` field.
*   **Parameters:**
    *   `request (array)`: Request data, including `categoria` (table name), `accion`, and action-specific params.

##### `attach` (inherited from `EventEmitter`)
```php
public function attach(string $event, callable $callback): void
```
*   **What it does:** Registers a callback for an event (e.g., `EVENT_MODIFIED`).
*   **What it's for:** Allows reaction to lookup table changes.
*   **What it returns:** `void`
*   **What the return means:** No value returned.
*   **If it throws:** Does not explicitly throw.
*   **Parameters:**
    *   `event (string)`: Event name (e.g., `LookupManager::EVENT_MODIFIED`).
    *   `callback (callable)`: Function to execute on event.

##### `notify` (inherited from `EventEmitter`)
```php
public function notify(string $event, ...$args): void
```
*   **What it does:** Triggers registered callbacks for an event.
*   **What it's for:** Signals lookup table modifications to listeners.
*   **What it returns:** `void`
*   **What the return means:** No value returned.
*   **If it throws:** Does not explicitly throw; callbacks might.
*   **Parameters:**
    *   `event (string)`: Event name to trigger.
    *   `...args (mixed)`: Optional arguments for callbacks (e.g., operation type, table name, ID).

# JavaScript Assets

## File: `src/assets/JqGrider/colmo.js`

### Global Objects

#### `jqGridTemplates`
*   **Definition/Structure:** A global JavaScript object literal: `var jqGridTemplates = { ... };` containing various properties, many of which are objects defining column model templates.
*   **What it is:** A collection of predefined default column configurations for jqGrid.
*   **What it's for:** To provide a starting set of jqGrid column options (like alignment, width, formatter, search options, edit rules) for common MySQL data types, simplifying grid setup. These are primarily used by the `getTemplate` function.

##### Properties
*   `darkColor (string)`: Intended as a default dark color for styling (e.g., `'#87b6d9'`).
*   `lightColor (string)`: Intended as a default light color for styling (e.g., `'#ebf4fd'`).
*   **Integer Type Templates (`tinyint`, `smallint`, `int`, `bigint`, and their `_unsigned` variants)** `(object)`:
    *   Templates for integer-based MySQL types.
    *   Define default jqGrid options for alignment, width, sorting (`integer`), formatting (`integer`), search, and editing (including min/max validation).
    *   Example common structure for `tinyint`: `{ align: 'right', width: 40, sorttype: 'integer', formatter: 'integer', formatoptions: {thousandsSeparator: ',', defaultValue: '0'}, cellAttributes: function(...){...}, searchoptions: { sopt: [...] }, editrules:{integer:true, maxValue: ..., minValue: ...}}`
*   **String Type Templates (`varchar`, `char`)** `(object)`:
    *   Templates for text-based MySQL types.
    *   Define defaults for alignment, text sorting, and string-based search options. The `getTemplate` function further customizes these by adding `editrules: {maxlength: N}` based on the type definition (e.g., `varchar(255)`).
*   **Date/Time Type Templates (`date`, `datetime`)** `(object)`:
    *   Templates for date/time MySQL types.
    *   Provide date formatting, date sorting, and integrate with jQuery UI Datepicker/Datetimepicker for search and edit functionalities within the grid.
*   **`enum_yesno` (as Enum Example)** `(object)`:
    *   An example template for `ENUM` types, configured for select dropdowns in editing and searching. The `getTemplate` function dynamically populates select options based on the actual enum values from the MySQL type definition.
*   **`decimal_10_2` (as Decimal Example)** `(object)`:
    *   An example template for `DECIMAL` types.
    *   Provides numeric formatting and validation. The `getTemplate` function dynamically adjusts precision, scale, min/max values for `editrules` and `formatoptions` based on the MySQL type definition.
*   *(Other similar templates for various MySQL types like `set_colors`, `mediumint_unsigned` also exist within this object.)*

#### `jqGUtil`
*   **Definition/Structure:** An object literal: `var jqGUtil = { version: '...', method1: function() {}, ... };`
*   **What it is:** A utility object providing helper functions for common jqGrid tasks.
*   **What it's for:** To extend jqGrid functionality with features like dynamic select search options, CSV export, and custom footer calculations.

##### Properties
*   `version (string)`: Represents the version number of the `jqGUtil` object (e.g., `'1.0.0'`).

##### Methods

###### `addSearch`
*   **Signature:** `addSearch: function (data, selectColumns)`
*   **What it does:** Iterates through data to collect unique values from specified columns, seemingly to build select options.
*   **What it's for:** Potentially for creating dynamic filter options based on the data present in the grid (current implementation in the provided code appears incomplete regarding its direct output/application).
*   **What it returns:** `undefined` (implicitly).
*   **What the return means:** No value is returned.
*   **If it throws:** Does not explicitly throw.
*   **Parameters:**
    *   `data (Array)`: An array of objects representing the grid data.
    *   `selectColumns (Array)`: An array of column names (strings) for which to find unique values.

###### `addSelectSearch`
*   **Signature:** `addSelectSearch: function(gridId, columnName, options)`
*   **What it does:** Creates and configures a select (dropdown) filter in the jqGrid toolbar for a specified column, populating it with unique values from that column.
*   **What it's for:** To provide users with a dropdown list of existing values for filtering a column, making it easier to find specific data.
*   **What it returns:** `undefined` (implicitly).
*   **What the return means:** No value is returned.
*   **If it throws:** Does not explicitly throw.
*   **Parameters:**
    *   `gridId (string)`: The ID of the jqGrid HTML element (without '#').
    *   `columnName (string)`: The name of the column in the `colModel` to add the select filter to.
    *   `options (object)`: Optional configuration object (e.g., `allText` for the "All" option, `searchOptions`, `toolbarOptions`).

###### `navButtonExportCsv`
*   **Signature:** `navButtonExportCsv: function(gridId, pagerId)`
*   **What it does:** Adds a button to the jqGrid pager to export currently visible grid data to a CSV file, for grids with local data (`datatype: 'local'` or `loadonce: true`).
*   **What it's for:** To allow users to easily download the grid data in CSV format.
*   **What it returns:** `undefined` (implicitly).
*   **What the return means:** No value is returned.
*   **If it throws:** Does not explicitly throw.
*   **Parameters:**
    *   `gridId (string)`: The ID of the jqGrid HTML element.
    *   `pagerId (string)`: The ID of the jqGrid pager element (defaults to `gridId + "Pager"` if undefined).

###### `footerMaxAvgMin`
*   **Signature:** `footerMaxAvgMin: function(ev)`
*   **What it does:** Intends to calculate aggregate values (max, average, min, based on `colModel`'s `footerValue`) for columns and set them in the grid's footer. **Note:** The provided code has a potential issue with local `footerData` variable usage.
*   **What it's for:** To display summary statistics like max, average, and minimum in the jqGrid footer.
*   **What it returns:** `undefined` (implicitly).
*   **What the return means:** No value is returned.
*   **If it throws:** Does not explicitly throw; potential runtime error due to variable usage.
*   **Parameters:**
    *   `ev (Event)`: The event object (e.g., from `jqGridAfterLoadComplete`), where `ev.target` is the grid.

###### `footerColumnTotal`
*   **Signature:** `footerColumnTotal: function(ev)`
*   **What it does:** Calculates and sets totals (or other aggregates like 'sum', 'max' specified by `footerValue` in `colModel`) for columns in the jqGrid footer, typically on an event like `jqGridAfterLoadComplete`.
*   **What it's for:** To display column-wise aggregations (e.g., sum of quantities) in the footer row(s) of the grid.
*   **What it returns:** `undefined` (implicitly).
*   **What the return means:** No value is returned.
*   **If it throws:** Does not explicitly throw.
*   **Parameters:**
    *   `ev (Event)`: The event object (e.g., from `jqGridAfterLoadComplete`), where `ev.target` is the grid.

###### `footerFormatedUserData`
*   **Signature:** `footerFormatedUserData: function(ev)`
*   **What it does:** Populates the jqGrid footer using the `userData` object received from the server response. It supports `userData` as a single object (for one footer row) or an array of objects (for multiple footer rows).
*   **What it's for:** To display pre-calculated summary data (sent by the server) in the grid's footer.
*   **What it returns:** `undefined` (implicitly).
*   **What the return means:** No value is returned.
*   **If it throws:** Does not explicitly throw.
*   **Parameters:**
    *   `ev (Event)`: The event object (e.g., from `jqGridAfterLoadComplete`), where `ev.target` is the grid.

### Global Functions

#### `getTemplate`
*   **Signature (Definition):**
    ```javascript
    function getTemplate(mysqlType, options) { /* ... */ }
    ```
*   **What it does:** Generates a jqGrid column model configuration by selecting and customizing a base template from `jqGridTemplates` based on the MySQL data type string (e.g., `varchar(N)`, `enum(...)`, `decimal(P,S)`), then merges with any provided `options`.
*   **What it's for:** To simplify creating jqGrid `colModel` entries by auto-deriving configurations from MySQL type definitions.
*   **What it returns:** `object` - A jqGrid column configuration object (e.g., with `align`, `width`, `formatter`, `editrules`). Returns an empty object `{}` (extended with `options`) if no specific template matches.
*   **What the return means:** A set of options ready for a column definition in a jqGrid `colModel`.
*   **If it throws:** Does not explicitly throw. Malformed `mysqlType` strings might lead to a generic template.
*   **Parameters:**
    *   `mysqlType (string)`: The MySQL data type definition string (e.g., "varchar(100)", "decimal(8,2)").
    *   `options (object)`: Optional object with additional jqGrid column options to merge, overriding template defaults.

#### `colmoAddSelect`
*   **Signature (Definition):**
    ```javascript
    function colmoAddSelect(keyValue) { /* ... */ }
    ```
*   **What it does:** Returns a jqGrid column model configuration object for a column that should be rendered as a select (dropdown) for editing and searching.
*   **What it's for:** To quickly define a jqGrid column as a dropdown list using provided key-value pairs for the select options.
*   **What it returns:** `object` - A jqGrid column configuration object with `formatter: 'select'`, `edittype: 'select'`, `stype: 'select'`, and populated `editoptions` and `searchoptions`.
    *   Shape example: `{ formatter: 'select', edittype: 'select', stype: "select", editoptions: { value: keyValue }, searchoptions: { sopt: ['eq','ne'], value: {'':'', ...keyValue } } }`
*   **What the return means:** A configuration suitable for a jqGrid column that uses a dropdown for data display, editing, and searching.
*   **If it throws:** Does not explicitly throw. Uses modern JavaScript (spread operator).
*   **Parameters:**
    *   `keyValue (object)`: An object where keys are the submission values and values are the display text for the select options (e.g., `{1: 'Active', 2: 'Inactive'}`).
