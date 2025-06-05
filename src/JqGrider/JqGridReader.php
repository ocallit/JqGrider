<?php 
/** @noinspection PhpUnused */
/** @noinspection SqlNoDataSourceInspection */

namespace Ocallit\JqGrider\JqGrider;
use Ocallit\JqGrider\JqGrider\Filter2Where;
use Ocallit\Sqler\SqlExecutor;
use Ocallit\Sqler\SqlUtils;

use Exception;
use function array_is_list;
use function array_key_exists;
use function ceil;
use function implode;
use function is_numeric;
use function preg_split;
use function str_ends_with;
use function str_starts_with;
use function strcasecmp;
use function substr;
use function trim;
use function ucfirst;

class JqGridReader {
    protected string $version = '1.0.0';
    protected SqlExecutor $sqlExecutor;
    protected array $parameters;

    /**
     * @param SqlExecutor $sqlExecutor
     * @param array|null $parameters null defaults to $_REQUEST
     */
    public function __construct(SqlExecutor $sqlExecutor, array|null $parameters = null) { 
        $this->sqlExecutor = $sqlExecutor;  
        $this->parameters = $parameters ?? $_REQUEST;
    }

    /**
     * Returns jqGrid formatted array from table query with optional columns and filtering
     * @param string $table - Database table name to query
     * @param string $tableAlias - Optional alias for the table in SQL query
     * @param string|array $columns - Columns to select, either comma-separated string or array of column names
     * @param string $extraWhere - Additional WHERE conditions to append to the query
     * @param array $sumColumns - Columns to calculate statistics for footer rows
     * @return array {
     *   page: int - Current page number
     *   total: int - Total number of pages
     *   records: int - Total number of records
     *   rows: array - Data rows for current page
     *   userdata: array - Footer data with statistical summaries
     *   ?error: bool - Present if error occurred
     *   ?message: string - Error message if error occurred
     *   ?code: int - Error code if error occurred
     * }
     */
    public function readTable(string $table, string $tableAlias = '', string|array $columns = "*", string $extraWhere = "", array $sumColumns = []): array {
        if(is_array($columns))
            $columns = implode(', ', $columns);
        $method = __METHOD__;
        $sqlReadRows = "SELECT /*$method*/ $columns FROM $table $tableAlias";
        return $this->readQuery($sqlReadRows, $extraWhere, $sumColumns);
    }

    /**
     * Returns jqGrid formatted array from custom SQL query with optional filtering
     * @see method read
     * @param string $sqlReadRows - Custom SQL query to fetch grid data
     * @param string $extraWhere - Additional WHERE conditions to append to the query
     * @param array $sumColumns - Columns to calculate statistics for footer rows
     * @return array {
     *   page: int - Current page number
     *   total: int - Total number of pages
     *   records: int - Total number of records
     *   rows: array - Data rows for current page
     *   userdata: array - Footer data with statistical summaries
     *   ?error: bool - Present if error occurred
     *   ?message: string - Error message if error occurred
     *   ?code: int - Error code if error occurred
     * }
     */
    public function readQuery(string $sqlReadRows,string $extraWhere = "", array $sumColumns = [] ): array {
        try {

            $where = $this->buildWhereClause();
            if(!empty($where))
                $where = "WHERE $where";
            if(!empty($extraWhere))
                $where = empty($where) ? "WHERE $extraWhere" : "$where AND ($extraWhere)";
            $sqlReadRows .= " $where";

            $method = __METHOD__;
            [ 'toSum' => $toSum, 'multipleFooter' => $multipleFooter ] = $this->sumColumns($sumColumns);
            $sqlTotals = "WITH /*$method*/ JqGrider_cnt AS ($sqlReadRows) SELECT $toSum FROM JqGrider_cnt";

            return $this->read($sqlReadRows, $sqlTotals, $multipleFooter);
        } catch (Exception $e) {
            return [
              'error' => true,
              'message' => $e->getMessage(),
              'code' => $e->getCode()
            ];
        }
    }

    /**
     * Returns array formatted for jqGrid's list response with userData
     * @param string $sqlReadRows - Main SQL query to fetch grid data rows
     * @param string $sqlTotals - SQL query to calculate totals and statistics, defaults to COUNT of $sqlReadRows if empty
     * @param bool $multipleFooter true: Generates separate footer rows for each statistic (total records, sum/avg/min/max/stddev/variance) false: single row
     * @return array {
     *   page: int Current page number
     *   total: int Total number of pages
     *   records: int Total number of records
     *   rows: array Data rows for current page
     *   userdata: array Footer data with statistical summaries
     *   ?error: bool Present if error occurred
     *   ?message: string Error message if error occurred
     *   ?code: int Error code if error occurred
     * }
     */
    public function read(string $sqlReadRows, string $sqlTotals = '', bool $multipleFooter = false):array {
        try {
            if(empty($sqlTotals))
                $sqlTotals = $sqlReadRows;

            $userData = $this->sqlExecutor->row($sqlTotals);

            $totalRows = $userData['totalRows'];
            if($multipleFooter) {
                $footer = ['sum' => ["rn"=>"Total"]];
                foreach($userData as $col => $val) {
                    if(str_starts_with($col, 'max_')) $footer['max'][substr($col, 4)] = $val;
                    elseif(str_starts_with($col, 'avg_')) $footer['avg'][substr($col, 4)] = $val;
                    elseif(str_starts_with($col, 'min_')) $footer['min'][substr($col, 4)] = $val;
                    elseif(str_starts_with($col, 'sum_')) $footer['sum'][substr($col, 4)] = $val;
                    elseif(str_starts_with($col, 'stddev_')) $footer['stddev'][substr($col, 7)] = $val;
                    elseif(str_starts_with($col, 'variance_')) $footer['variance'][substr($col, 9)] = $val;
                    else $footer['sum'][$col] = $val;
                }
                foreach(['max', 'avg', 'min', 'stddev', 'variance'] as $stat) {
                    if(!array_key_exists($stat, $footer))
                        continue;
                    $footer[$stat]["rn"] = ucfirst($stat);
                }
            }
            $rows = $this->getRows();
            $totalPages = ceil($totalRows / $rows);
            $page = $this->getPage($totalPages);
            $orderBy = $this->buildOrderBy();
            $limit = $this->buildLimit($page, $rows);

            return [
              'page' => $page,
              'total' => $totalPages,
              'records' => $totalRows,
              'rows' => $this->sqlExecutor->array("$sqlReadRows $orderBy $limit"),
              'userdata' => $multipleFooter ? array_values( $footer) : $userData,
            ];
        } catch (Exception $e) {
            return [
              'error' => true,
              'message' => $e->getMessage(),
              'code' => $e->getCode()
            ];
        }
    }

    public function sumColumns(array $sumColumns = []):array {

        $toSum = "COUNT(*) as 'totalRows'";
        if(empty($sumColumns))
            return ['toSum' => $toSum, 'multipleFooter' => false];
        $multipleFooter = false;

        if(array_is_list($sumColumns))
            foreach($sumColumns as $col)
                $toSum .= ", SUM(" . SqlUtils::fieldIt($col) . ") AS " . SqlUtils::strIt($col);
        else
            foreach($sumColumns as $col => $stat) {
                if(strcasecmp($stat, 'all') === 0) {
                    $toSum .= "\r\n, SUM(" . SqlUtils::fieldIt($col) . ") AS " . SqlUtils::strIt("sum_$col") .
                      ", MAX(" . SqlUtils::fieldIt($col) . ") AS " . SqlUtils::strIt("max_$col") .
                      ", AVG(" . SqlUtils::fieldIt($col) . ") AS " . SqlUtils::strIt("avg_$col") .
                      ", MIN(" . SqlUtils::fieldIt($col) . ") AS " . SqlUtils::strIt("min_$col") .
                      ", STDDEV(" . SqlUtils::fieldIt($col) . ") AS " . SqlUtils::strIt("stddev_$col") .
                      ", VARIANCE(" . SqlUtils::fieldIt($col) . ") AS " . SqlUtils::strIt("variance_$col") . "\r\n";
                      "\r\n";
                    $multipleFooter = true;
                } elseif(array_key_exists(strtolower( $stat), ['sum'=>1, 'max'=>1, 'min'=>1, 'avg'=>1, 'count'=>1, 'stddev'=>1, 'variance'=>1]))
                    $toSum .= ", $stat(" . SqlUtils::fieldIt($col) . ") AS " . SqlUtils::strIt($col);
            }
        return ['toSum' => $toSum, 'multipleFooter' => $multipleFooter];
    }

    public function buildWhereClause(): string {
        $filter2where = new Filter2Where();
        $where = [];
        if(!empty($this->parameters['filters'])) {
            $filters = $filter2where->filter2where($this->parameters['filters']);
            if(!empty($filters))
                $where[] = "($filters)";
        }
        $search = strcasecmp($this->parameters['_search'] ?? '', 'true') === 0;
        $searchField = $this->parameters['searchField'] ?? '';
        $searchOper = $this->parameters['searchOper'] ?? '';
        if($search && !empty($searchField) && !empty($searchOper)) {
            $simpleFilter = $filter2where->rule2sql(['field'=>$searchField, 'op'=> $searchOper, 'data'=>$this->parameters['searchString'] ?? '']);
            if(!empty($simpleFilter))
                $where[] = "($simpleFilter)";
        }
        return implode(' AND ', $where);
    }

    protected function getRows():int {
        $rows = (int)($this->parameters['rows'] ?? 10);
        return $rows < 1 ? 10: $rows;
    }

    protected function getPage(int $totalPages):int {
        $page = (int)($this->parameters['page'] ?? 1);
        if($page < 1)
            return 1;
        if($page > $totalPages)
            return $totalPages;
        return $page;
    }

    protected function buildOrderBy(): string {
        $orderBy = [];
        $orderString = trim(($this->parameters['sidx'] ?? '') . ' ' . ($this->parameters['sord'] ?? ''));
        foreach(preg_split('/\\s+/S', $orderString) as $clause) {
            $clause = trim($clause);
            if(empty($clause))
                continue;
            if( $clause === ',') {
                $orderBy[] = ',';
                continue;
            }

            $prefix = $suffix = "";
            if(str_starts_with($clause, ',')) {
                $prefix  = ", ";
                $clause = substr($clause, 1);
            }
            if(str_ends_with($clause, ',')) {
                $suffix  = ",";
                $clause = substr($clause, 0, -1);
            }

            if(strcasecmp($clause, 'ASC') === 0) {
                $orderBy[] = $prefix . "ASC$suffix";
                continue;
            }
            if(strcasecmp($clause, 'DESC') === 0) {
                $orderBy[] = $prefix . "DESC$suffix";
                continue;
            }
            if(is_numeric($clause))
                $orderBy[] = $prefix . $clause . $suffix;
            else
                $orderBy[] = $prefix . SqlUtils::fieldIt($clause) . $suffix;
        }
        return empty($orderBy) ? "" : " ORDER BY " . implode(' ', $orderBy);
    }

    protected function buildLimit(int $page, int $rows): string {
        if($page < 1)
            $page = 1;
        return  " LIMIT " . (($page - 1) * $rows) . ", $rows";
    }

}