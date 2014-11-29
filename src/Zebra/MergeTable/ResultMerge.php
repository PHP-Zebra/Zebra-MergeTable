<?php
/**
 * Created by PhpStorm.
 * User: Jenner
 * Date: 14-11-29
 * Time: 下午3:36
 */

namespace Zebra\MergeTable;


class ResultMerge {

    protected $field_function;

    protected $group;

    protected $order;

    public function merge(array $data, array $parsed_query){
        $merge_result = call_user_func('array_merge', $data);
        $this->initFieldFunction($parsed_query);
        $this->initGroup($parsed_query);
        $this->initOrder($parsed_query);

        if(!empty($this->group)){
            $merge_result = $this->groupResult($data);
        }
        if(!empty($this->order)){
            $merge_result = $this->orderResult($data);
        }
        return $merge_result;
    }

    protected function orderResult(array $data){
        $params[] = $data;
        foreach($this->order as $order){
            $params[] = $order['field'];
            if(strtolower($order['direction'])=='desc'){
                $paras[] = SORT_DESC;
            }else{
                $params[] = SORT_ASC;
            }
        }
        return call_user_func_array([$this, 'arrayOrderby'], $params);
    }


    protected function groupResult(array $data){
        $options = [];
        foreach($this->field_function as $function_name=>$column){
            $options[$function_name] = implode(',', $column);
        }
        $group_result = $this->arrayGroupBy($data, $this->group, $options);
        return $group_result;
    }

    protected function initOrder($parsed_query){
        $order = $parsed_query['ORDER'];
        foreach($order as $value){
            $temp_order['field'] = $value['base_expr'];
            $temp_order['direction'] = $value['direction'];
            $this->order[] = $temp_order;
        }
    }

    protected function initGroup($parsed_query){
        $group = $parsed_query['GROUP'];

        foreach($group as $value){
           $this->group[] = $value['base_expr'];
        }
    }

    protected function initFieldFunction($parsed_query){
        $select_fields = $parsed_query['SELECT'];

        foreach($select_fields as $field){
            if($field['expr_type'] === 'colref') continue;
            elseif($field['expr_type'] === 'aggregate_function'){
                $column = $this->getFieldAsName($field);
                $func = strtolower($field['base_expr']);
                $this->field_function[$func] = $column;
            }
        }
    }

    protected function getFieldAsName($field){
        if(isset($field['alias']) && !empty($field['alias'])){
            return $field['alias']['name'];
        }elseif(!isset($field['sub_tree']) && !empty($field['base_expr'])){
            return $field['base_expr'];
        }
        return false;
    }

    protected function arrayGroupBy(array $arrs,$groupBy,$option){
        $temp = array();
        foreach($arrs as $index=>$arr){
            $groupKey = '';
            foreach($groupBy as $v){
                $groupKey .= $arr[$v].'-';
            }
            $groupKey = trim($groupKey,'-');
            if(isset($temp[$groupKey]))
                array_push($temp[$groupKey],$index);
            else
                $temp[$groupKey][]=$index;
        }

        $result = array();
        foreach($temp as $key=>$value){
            foreach($option as $k=>$f){
                $parts = explode(',',$f);
                $distinct = array();
                foreach($parts as $part){
                    $exarr = explode('|',$part);
                    $filed = $exarr[0];
                    $aliasKey = isset($exarr[1]) ? $exarr[1] : '';
                    $aliasKey = !empty($aliasKey)?$aliasKey:$k.'_'.$filed;
                    if($k=='sum'){
                        $aliasValue = 0;
                        foreach($value as $v){
                            $aliasValue += $arrs[$v][$filed];
                        }
                    }elseif($k=='average'){
                        $aliasValue = 0;
                        foreach($value as $v){
                            $aliasValue += $arrs[$v][$filed];
                        }
                        $aliasValue = (float)$aliasValue/count($temp[$key]);
                    }elseif($k=='count'){
                        $aliasValue = count($value);
                    }elseif($k=='group_concat'){
                        if(strpos($filed,':')){
                            $distinct = explode(':', $filed);
                            $filed = $distinct[1];
                        }
                        $aliasValue = array();
                        foreach($value as $v){
                            $aliasValue[] = $arrs[$v][$filed];
                        }

                        $aliasValue = !empty($distinct) ? implode(',',array_unique($aliasValue)) : implode(',',$aliasValue);
                    }
                    $result[$key][$aliasKey] = $aliasValue;
                }

            }
        }
        foreach ($result as $key => &$value) {
            $key = explode('-', $key);
            for ($i = 0; $i < count($groupBy); $i++) {
                $value[$groupBy[$i]] = $key[$i];
            }
        }
        return array_values($result);
    }

    /**
     * 类似SQL ORDER BY 的多为数组排序函数
     * example: $sorted = arrayOrderby($data, 'volume', SORT_DESC, 'edition', SORT_ASC);
     *
     * @return mixed
     */
    public static function arrayOrderby()
    {
        $args = \func_get_args();
        $data = \array_shift($args);
        foreach ($args as $n => $field) {
            if (\is_string($field)) {
                $tmp = array();
                foreach ($data as $key => $row)
                    $tmp[$key] = $row[$field];
                $args[$n] = $tmp;
            }
        }
        $args[] = & $data;
        \call_user_func_array('array_multisort', $args);
        return \array_pop($args);
    }
} 