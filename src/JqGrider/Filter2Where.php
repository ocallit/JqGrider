<?php
/** @noinspection PhpComposerExtensionStubsInspection */
/** @noinspection PhpUnused */

namespace Ocallit\JqGrider\JqGrider;

/**
 * jQGrid filter and _searchField to sql where
 *
 * @version 1.1.0
 */

/**
 * Class Filter2Where jQGrid filter and _searchField to sql where
 * @package ia\JqGrid
 */
class Filter2Where {
    protected string $version = '1.0.0';
    /**
     * @var array $opToStandard Translate from gird operators to jqGrid standard operators, serves as valid operator list
     */
    protected  array $opToStandard = [
        'eq' => 'eq',
        'ne' => 'ne',
        'lt' => 'lt',
        'le' => 'le',
        'gt' => 'gt',
        'ge' => 'ge',
        'bt' => 'bt', // between

        'bw' => 'bw',  // begins with
        'bn' => 'bn',  // not begins with
        'ma' => 'ma',  // match() against()
        'cn' => 'cn',  // contains
        'nc' => 'nc',  // not contains
        'ew' => 'ew',  // ends with
        'en' => 'en',  // not ends

        'in' => 'in',  // is in set
        'ni' => 'ni',  // is not in set
        'not in' => 'ni',  // is not in set

        'nu' => 'nu',  // is null
        'nn' => 'nn',  // not null

        'is null' => 'nu',  // is null
        'is not null' => 'nn',  // not null
    ];

    /**
     * @var array $opOperator
     */
    protected array   $opOperator = [
        'eq' => '=',
        'ne' => '<>',
        'lt' => '<',
        'le' => '<=',
        'gt' => '>',
        'ge' => '>=',

        '=' => '=',
        '<>' => '<>',
        '<' => '<',
        '<=' => '<=',
        '>' => '>',
        '>=' => '>=',

        'in' => '=',  // fallback por si no mandan array con in clause
        'ni' => '<>', // fallback por si no mandan array con in clause
    ];

    protected array $fullTextFields = [];
    protected int $innodb_ft_min_token_size;
    protected int $innodb_ft_max_token_size;
    protected bool $usesFullText = false;

    /**
     * Filter2where constructor.
     * @param array $fullTextFields ['fullTextField1','fullTextField2'] en fulltext cambia cn por match
     * @param int $innodb_ft_min_token_size default 3, en fulltext si una palabra es menor en longitud usa like
     * @param int $innodb_ft_max_token_size default 84, en fulltext si una palabra es mayor en longitud usa like
     */
    public function __construct(array $fullTextFields = [],
                                int $innodb_ft_min_token_size = 3, int $innodb_ft_max_token_size = 84) {
        $this->fullTextFields = array_flip( $fullTextFields);
        $this->innodb_ft_min_token_size = $innodb_ft_min_token_size;
        $this->innodb_ft_max_token_size = $innodb_ft_max_token_size;
    }

    /**
     * Converts an array to postData.filter
     *
     * @param array $array ['field1'=>'value1',...]
     * @param string $groupOp AND, OR, ...
     * @param string $op eq, gt, ...
     * @return array ['groupOp'=>$groupOp, 'rules'=>[['field1' => 'field1', 'op' => $op, 'data' => 'value1], ... ]]
     */
    public  function array2Filter($array, $groupOp = 'AND', $op = 'eq'):array {
        if(empty($array)) {
            return [];
        }
        $rules = [];
        foreach($array as $fieldName => $value) {
            $rules[] = ['field' => $fieldName, 'op' => $op, 'data' => $value];
        }
        return  ['groupOp' => $groupOp, 'rules' => $rules];
    }

    /**
     * Converts a jqGrid postData.filter to an sql where clause
     *
     * @param string|array $filters a json string or array from postData.filter
     * @param string $groupOp AND or OR
     * @param null|callable $filterFieldOverride function($ruleSolved):string
     *      param array ruleSolved ['field'=>'fieldName', 'op'=>'eq', 'data'=>'value', 'clause'=>'(a=3)']
     *      return string sql clause or '' = don't filter
     *      example rule2sql($r, 'overrideClause'); function overrideClause($ruleSolved);
     *      example rule2sql($r, [$reFilter, "overrideClause"]); class reFilter { public function overrideClause($ruleSolved);}
     *      example rule2sql($r, "reFilter::overrideClause"); class reFilter { public  function overrideClause($ruleSolved);}
     * @return string an sql where clause
     */
    public  function filter2Where($filters, $groupOp = 'AND', $filterFieldOverride = null):string {
        $this->usesFullText = false;
        if(empty($filters)) {
            return '';
        }
        //   echo "<pre>filters in=".print_r($filters,true);
        if(is_string($filters)) {
            $filters = json_decode($filters, true);
        }

        $where = '';
        //   echo "<pre>req=".print_r($_REQUEST,true);
         //   echo "<pre>filters queda=".print_r($filters,true);
        foreach($filters as $key => $f) {
            if(empty($f)) {
                continue;
            }
            if($key === 'groupOp') {
                $groupOp = $this->strim($f);
            } elseif($key === 'rules') {
                $clause = $this->rules($f, $groupOp, $filterFieldOverride);
                if(!empty($clause)) {
                    $where .= empty($where) ? $clause : " $groupOp $clause";
                }
            } elseif($key === 'groups') {
                if(array_key_exists('rules', $f)) {
                    $clause = $this->filter2where($f, $groupOp, $filterFieldOverride);
                    if(!empty($clause)) {
                        $where .= empty($where) ? $clause : "$groupOp ( $clause ) ";
                    }
                } else
                    foreach($f as $g) {
                        $clause = $this->filter2where($g, $groupOp, $filterFieldOverride);
                        if(!empty($clause)) {
                            $where .= empty($where) ? " $clause" : " $groupOp ( $clause ) ";
                        }
                    }
            }
        }
        if(empty($where))
            return '';
        return strcasecmp('NOT',$groupOp) ? $where : 'NOT (' . $where . ')';
    }

    /**
     * A wrapper function for $this->rule2sqlDo so $filterFieldOverride may override the result
     *
     * @param array $r ['field'=>'fieldName', 'op'=>'eq', 'data'=>'value'] standard from jqGrid's postdata.filter.*.rule
     * @param null|callable $filterFieldOverride function($ruleSolved):string
     *      param array ruleSolved ['field'=>'fieldName', 'op'=>'eq', 'data'=>'value', 'clause'=>'(a=3)']
     *      return string sql clause or '' = don't filter
     *      example rule2sql($r, 'overrideClause'); function overrideClause($ruleSolved);
     *      example rule2sql($r, [$reFilter, "overrideClause"]); class reFilter { public function overrideClause($ruleSolved);}
     *      example rule2sql($r, "reFilter::overrideClause"); class reFilter { public  function overrideClause($ruleSolved);}
     * @return string
     */
    public  function rule2Sql($r, $filterFieldOverride = null):string {
        $clause = $this->rule2SqlDo($r);
        if($filterFieldOverride === null || $clause === '') {
            return $clause;
        }

        $r['clause'] = $clause;
        return call_user_func($filterFieldOverride, $r);
    }

    protected  function rules(&$rules, $groupOp, $filterFieldOverride):string {
        $where = '';
        if(is_array($rules) && !empty($rules))
            foreach($rules as $r) {
                if(array_key_exists('Data',$r) ) {
                    $r['data'] = $r['Data'];
                }
                $clause = $this->rule2Sql($r, $filterFieldOverride);

                if(!empty($clause)) {
                    $where .= empty($where) ? ' ' . $clause : ' ' . $groupOp . ' ' . $clause;
                }
            }
        return empty($where) ? '' : '('.$where.')';
    }

    /**
     * @param array $r ['field'=>'fieldName', 'op'=>'eq', 'data'=>'value'] standard from jqGrid's postdata.filter.*.rule
     * @return string
     */
    protected  function rule2SqlDo(array $r):string {
        // validate rule
            if(!array_key_exists('field',$r) || !array_key_exists('op',$r) || !array_key_exists('data',$r) ) {
                return ''; // malformed filter, ignore it
            }
            $fieldSent = $this->strim($r['field']);
            if(empty($fieldSent)) {
                return ''; // malformed filter, ignore it
            }
            $field = $this->fieldIt($fieldSent);

            $op = $this->opToStandard[strtolower($this->strim($r['op'] ?? ''))] ?? '';
            if(empty($op)) {
                return '';  // malformed filter op not recognized, ignore it
            }

        // special in, ni, bt data may be an array
            if(is_array($r['data']) && $op !== 'in' && $op !== 'ni' && $op !== 'bt' ) {
                $op = $op === 'eq' ? 'in' : 'ni';
            }

            if(($op === 'in' || $op === 'ni')) {
                if(!is_array($r['data'])) {
                    if(substr($r['data'],0,1) === ',') {
                        $r['data'] = substr($r['data'],1);
                    }
                    $r['data'] = explode(',', $r['data']);
                }
                $data = [];
                foreach($r['data'] as $d) {
                    $data[] = $this->strIt($d);
                }
                if(empty($data)) {
                    return '';
                }
                return $field . ($op === 'ni' ? ' NOT IN(' : '  IN(') . implode(',', $data) . ')';
            }

            if($op === 'bt') {
                if(!is_array($r['data'])) {
                    $r['data'] = explode(',', $r['data']);
                }
                if(count( $r['data']) === 0) {
                    return '';
                }

                $from = $this->strim($r['data'][0] ?? '');
                $to = $this->strim($r['data'][1] ?? '');
                if($to === '')
                    return $this->isDate($from) ?
                        $this->fixDate2DateTime($field, '>=', $from) :
                        $field . '>=' . $this->strIt($from);

                if($from === '')
                    return $this->isDate($to) ?
                        $this->fixDate2DateTime($field, '<=', $to) :
                        $field . '<=' . $this->strIt($to);
                if($from > $to) {
                    $swap = $to;
                    $to = $from;
                    $from = $swap;
                }
                if(!$this->isDate($to)) {
                    return $field . ' BETWEEN ' . $this->strIt($from) .' AND ' . $this->strIt($to);
                }
                return '(' .
                    $this->fixDate2DateTime($field, '>=', $from) . ' AND ' .
                    $this->fixDate2DateTime($field, '<=', $to) .
                ')';
            }

        // standard operator
            $value = $this->strim($r['data']);
            if(array_key_exists($op,$this->opOperator) && $this->isDate($value)) {
                return $this->fixDate2DateTime($field, $this->opOperator[$op], $value);
            }
            switch($op) {
                case 'eq':
                case '=': return $field . '=' . $this->strIt($value);
                case 'ne':
                case '<>': return $field . '<>' . $this->strIt($value);
                case 'gt':
                case '>': return $field . '>' . $this->strIt($value);
                case 'ge':
                case '>=': return $field . '>=' . $this->strIt($value);
                case 'lt':
                case '<': return $field . '<' . $this->strIt($value);
                case 'le':
                case '<=': return $field . '<=' . $this->strIt($value);

                case 'bw': return $field . ' LIKE '. $this->strIt($this->strLike($value) . '%');
                case 'bn': return $field . ' NOT LIKE '. $this->strIt($this->strLike($value) . '%');
                case 'ma': return $this->fullTextSearch($field, $value);
                case 'cn':
                    if(array_key_exists($fieldSent, $this->fullTextFields) && strlen($value) > $this->innodb_ft_min_token_size) {
                        return $this->fullTextSearch($field, $value);
                    }
                    return $field . ' LIKE '. $this->strIt('%' . $this->strLike($value) . '%');
                case 'nc': return $field . ' NOT LIKE '. $this->strIt( '%' . $this->strLike($value) . '%');
                case 'ew': return $field . ' LIKE '. $this->strIt( '%' . $this->strLike($value));
                case 'en': return $field . ' NOT LIKE '. $this->strIt( '%' . $this->strLike($value));
                case 'nu': return "$field IS NULL";
                case 'nn': return "$field IS NOT NULL";

                default:
                    return '';
            }

    }

/*

https://paiza.io/projects/I8dECgVIQXCowja7wslzbg?language=mysql

  CREATE FULLTEXT INDEX texto ON gato(aba);
INSERT INTO gato VALUES('unote largone'),('otro unox largone ok'),('baba bab largone asdfa'),('aglo se realizo aqui '),('rojo verde auzul azul'),('purpura unote morado viejo'),('rontando ase va el azul')
,('asdf asdf asdf asdf'),('atri se la cuenta do vino chipo')
;

select * from gato where MATCH(aba) AGAINST(' +unox' IN BOOLEAN MODE);

 */
    protected function fullTextSearch($field, $value):string {
        $like = [];
        $against = [];
        foreach(explode(' ', $value) as $token) {
            $len = strlen($token);
            if($len <= $this->innodb_ft_min_token_size || $len >= $this->innodb_ft_max_token_size) {
                $like[] = $field . ' LIKE ' .  $this->strIt( '%' . $this->strLike( $token ) . '%' );
                continue;
            }
            $against[] = $token[0] !== '+' && $token[0] !== '-' ? '+' . $token : $token;
        }

        if( count($against) > 0 ) {
            $this->usesFullText = true;
            $return = "MATCH($field) AGAINST(" . $this->strIt(implode(' ', $against)) . " IN BOOLEAN MODE)";
        }
        if( count($like) > 0 ) {
            $like =  "(" . implode(' AND ', $like ) . ')';
            return isset($return) ? "($return AND $like)" : $like;
        }
        return isset($return) ? $return : '';
    }

    protected  function isDate($value):bool {
        if(strlen($value) !== 10 || strpos($value, '-') === false) {
            return false;
        }
        if($value[4] !== '-' || $value[7] !== '-' ) {
            return false;
        }
        $dateParts = explode('-', $value);
        if(count($dateParts) !== 3) {
            return false;
        }
        return checkdate($dateParts[1], $dateParts[2], $dateParts[0]);
    }

    protected  function fixDate2DateTime($field, $op, $value):string {
        switch($op) {
            case '=':
                return " $field BETWEEN ".$this->strIt($value). " AND " .$this->strIt("$value 23:59:59.99999"). ' ';
            case '<>':
                return " $field NOT BETWEEN ".$this->strIt($value). " AND " .$this->strIt("$value 23:59:59.99999"). ' ';
            case '>':
            case '>=':
                return " $field $op ".$this->strIt($value);
            case '<=':
                return " $field $op ".$this->strIt("$value 23:59:59.99999");
            case '<':
            default:
                return " $field $op ".$this->strIt($value);
        }
    }

    /// helpers
    /// 

    /**
     * Protect with ` quotes a: column name to `column name` respecting . table.column to `table`.`column`
     *
     * @param string $fieldName
     * @return string
     */
    protected function fieldIt($fieldName):string {
        $protected = [];
        $n = explode('.',$fieldName);
        foreach($n as $field) {
            $protected[]= '`'.str_replace('`', '', self::strim($field) ).'`';
        }
        return implode('.', $protected);
    }
    
    /**
     * superTrim trim (including \s utf spaces), and change multiple spaces to one space
     *
     * @param string $str
     * @return string
     */
    protected function strim($str):string {
        if($str === null) {
            return '';
        }
        $s1 = preg_replace('/[\pZ\pC]/muS',' ',$str);
        // @codeCoverageIgnoreStart
        if(preg_last_error()) {
            $s1 = preg_replace('/[\pZ\pC]/muS',' ',  iconv("UTF-8","UTF-8//IGNORE",$str));
            if(preg_last_error())
                return trim(preg_replace('/ {2,}/mS',' ',$str));
        }
        // @codeCoverageIgnoreEnd
        return trim(preg_replace('/ {2,}/muS',' ',$s1));
    }
    
    /**
     * Quote and protect Sql value
     *
     * @param string $str
     * @param integer $maxLength
     * @param bool $maxLengthInCharacters
     * @return string
     *
     * @test "'El Gato' \'ap con slash \\'ap con doble slash, un slash \ x".chr(8).chr(0).chr(26).chr(27)."y es 'felix'"
     */
    protected function strIt($str, $maxLength=0, $maxLengthInCharacters=true):string {
        if($str === null) {
            return 'null';
        }
        if(is_array($str)) {
            $str = json_encode($str);
        }
        if($maxLength) {
            if($maxLengthInCharacters) {
                $str = mb_substr($str , 0, $maxLength);  // operates on characters
            } else {
                $str = mb_strcut($str , 0, $maxLength); // operates on bytes
            }
        }
        return "'".str_replace( array("\\","'",chr(8),chr(0),chr(26),chr(27)), array("\\\\","''",'','','',''),$str)."'";
    }

    /**
     * Protect a string to use in Sql like, so % and _ won't have a special value
     *
     * @param string $str
     * @return string
     */
    protected function strLike($str):string {
        return str_replace(array('%', '_'), array("\\%", "\\_"), $str);
    }
}
