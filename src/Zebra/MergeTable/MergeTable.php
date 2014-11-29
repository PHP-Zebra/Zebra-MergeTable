<?php
/**
 * Created by PhpStorm.
 * User: Jenner
 * Date: 14-11-24
 * Time: 下午5:17
 *
 * 关系型数据库合并表
 */

namespace Zebra\MergeTable;

use Zebra\MergeTable\Exception\DBCallbackIllegalMergeTableException;
use Zebra\MergeTable\Exception\SQLExecuteErrorException;

class MergeTable {

    /**
     * @var callable 数据库驱动回调函数
     */
    protected $database_callback;

    protected $error_callback;

    /**
     * @var 合并表数组
     */
    protected $tables;

    /**
     * @param $database_callback 数据库驱动回调函数
     * @throws Exception\DBCallbackIllegalMergeTableException
     */
    public function __construct($database_callback=null, $error_callback=null){
        if(is_null($database_callback) && is_null($error_callback)) return ;

        if(!is_callable($database_callback)){
            throw new DBCallbackIllegalMergeTableException;
        }
        $this->database_callback = $database_callback;

        if(!is_callable($error_callback)){
            throw new SQLExecuteErrorException;
        }
        $this->error_callback = $error_callback;
    }

    /**
     * @param $table_name 添加合并表
     */
    public function addTable($table_name){
        $this->tables[] = $table_name;
    }

    /**
     * @param $query 执行查询，返回结果
     */
    public function fetchAll($query){
        //获取查询结果
        //合并结果

        //结果运算(SUM、COUNT等)

        //返回结果
    }

    protected function getAllTableResult($parsed_query){
        $creator = new \PHPSQL\Creator();
        try{
            foreach($this->tables as $table){
                $parsed = $parsed_query;
                $parsed['FROM'][0]['table'] = $table;
                $query = $creator->create($parsed);
                $temp_result = $this->database_callback($query);
                if($temp_result === false){
                    return false;
                }

                $result[$table] = $temp_result;
            }
        }catch (\Exception $e){
            if(!empty($this->error_callback)){
                $this->error_callback($query);
            }else{
                throw $e;
            }
        }

        return $result;
    }
} 