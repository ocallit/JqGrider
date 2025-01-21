<?php

namespace Ocallit\JqGrider;
/*
    headerrow: true,
    userDataOnHeader: true, // use the userData parameter of the JSON response to display data on header
    $("#jqGrid").jqGrid("headerData", "set",{Country: "<b>Grand Total</b>", Price:"<b>1,2345.00</b>"}, false);
    footerrow: true,
    userDataOnFooter: true, // use the userData parameter of the JSON response to display data on footer
        en json "userdata":{"CategoryName":"","ProductName":"","Country":"Total","Price":"19521.68","Quantity":""},

    - voy en el del count traiga los totales, del user data
    -
    - table => [ userData=>[colName=>sum/max/min/avg/label?], selCols=[], editCols=[], insertCols=[] ]
        auto => del qry ints,decimals => sum except tc,precio,costo
    - colmo
    - jqplugins
    - jqAppend
 */

use Ocallit\Sqler\SqlExecutor;
use Ocallit\Sqler\SqlUtils;

class JqGridReader {
    protected SqlExecutor $sqlExecutor;

    /**
     * @param SqlExecutor $sqlExecutor
     */
    public function __construct(SqlExecutor $sqlExecutor) { $this->sqlExecutor = $sqlExecutor; }


    public function readTable(string $table, string $tableAlias = '', string|array $columns = "*", $extraWhere = "", $sumCols = []): array {
        if(is_array($columns))
            $columns = implode(', ', $columns);
        $sqlReadRows = "SELECT $columns FROM $table $tableAlias";
        return $this->readQuery($sqlReadRows, $extraWhere, $sumCols);
    }

    public function readQuery(string $sqlReadRows, $extraWhere = "", $sumCols = [] ): array {
        try {
            $where = $this->buildWhereClause();
            if(!empty($where))
                $where = "WHERE $where";
            if(!empty($extraWhere))
                $where = empty($where) ? "WHERE $extraWhere" : "$where AND ($extraWhere)";
            $sqlReadRows .= " $where";
            $toSum = "";
            if(array_is_list($sumCols))
                foreach($sumCols as $col)
                    $toSum .= ", SUM(" . SqlUtils::fieldIt($col) . ") AS " . SqlUtils::strIt($col);
            else
                foreach($sumCols as $col => $stat)
                    $toSum .= ", $stat(" . SqlUtils::fieldIt($col) . ") AS " . SqlUtils::strIt($col);

            $userData = $this->sqlExecutor->row("WITH JqGrider_cnt AS ($sqlReadRows) SELECT COUNT(*) as 'totalRows' $toSum FROM JqGrider_cnt");
            $totalRows = $userData['totalRows'];
            $rows = $this->getRows();
            $totalPages = ceil($totalRows / $rows);
            $page = $this->getPage($totalPages);
            $orderBy = $this->buildOrderBy();
            $limit = $this->buildLimit($page, $rows);
            $rows = $this->sqlExecutor->array("$sqlReadRows $orderBy $limit");

            return [
                'page' => $page,
                'total' => $totalPages,
                'records' => $totalRows,
                'rows' => $rows,
                'userdata' => $userData,
                'sumCols' => $sumCols,
            ];
        } catch (\Exception $e) {
            return [
              'error' => true,
              'message' => $e->getMessage(),
              'code' => $e->getCode()
            ];
        }
    }

    protected function getRows():int {
        $rows = (int)($_REQUEST['rows'] ?? 10);
        return $rows < 1 ? 10: $rows;
    }

    protected function getPage(int $totalPages):int {
        $page = (int)($_REQUEST['page'] ?? 1);
        if($page < 1)
            return 1;
        if($page > $totalPages)
            return $totalPages;
        return $page;
    }

    public function buildWhereClause(): string {
        $filter2where = new Filter2Where();
        $where = [];
        if(!empty($_REQUEST['filters'])) {
            $filters = $filter2where->filter2Where($_REQUEST['filters']);
            if(!empty($filters))
                $where[] = "($filters)";
        }
        $search = strcasecmp($_REQUEST['_search'] ?? '', 'true') === 0;
        $searchField = $_REQUEST['searchField'] ?? '';
        $searchOper = $_REQUEST['searchOper'] ?? '';
        if($search && !empty($searchField) && !empty($searchOper)) {
            $simpleFilter = $filter2where->rule2Sql(['field'=>$searchField, 'op'=> $searchOper, 'data'=>$_REQUEST['searchString'] ?? '']);
            if(!empty($simpleFilter))
                $where[] = "($simpleFilter)";
        }
        return implode(' AND ', $where);
    }

    protected function buildOrderBy(): string {
        $orderBy = [];
        $orderString = trim(($_REQUEST['sidx'] ?? '') . ' ' . ($_REQUEST['sord'] ?? ''));
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

    protected function buildLimit(int $page, int $rows): string {return  " LIMIT " . (($page - 1) * $rows) . ", $rows";}

}
