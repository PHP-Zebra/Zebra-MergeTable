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

class MergeTable
{

    /**
     * @var callable 数据库驱动回调函数
     */
    protected $database_callback;

    protected $sql_error_handle;

    /**
     * @var 合并表数组
     */
    protected $tables;

    /**
     * @param $database_callback 数据库驱动回调函数
     * @param null $error_callback
     * @throws Exception\DBCallbackIllegalMergeTableException
     * @throws Exception\SQLExecuteErrorException
     */
    public function __construct($database_callback = null, $sql_error_handle = null)
    {
        if (is_null($database_callback) && is_null($sql_error_handle)) return;

        if (!is_callable($database_callback)) {
            throw new DBCallbackIllegalMergeTableException;
        }
        $this->database_callback = $database_callback;

        if (!is_callable($sql_error_handle)) {
            throw new SQLExecuteErrorException;
        }
        $this->sql_error_handle = $sql_error_handle;
    }

    /**
     * @param $table_name 添加合并表
     */
    public function addTable($table_name)
    {
        $this->tables[] = $table_name;
    }

    /**
     * @param $query 执行查询，返回结果
     */
    public function fetchAll($query)
    {
        //获取查询结果
        $parser = new \PHPSQL\Parser($query);
        $parsed_query = $parser->parsed;

        $result = $this->getAllTableResult($parsed_query);
        //合并结果
        $result_merge_model = new ResultMerge();
        $merge_result = $result_merge_model->merge($result, $parsed_query);
        //返回结果
        return $merge_result;
    }

    protected function getAllTableResult($parsed_query)
    {
        $creator = new \PHPSQL\Creator();
        try {
            foreach ($this->tables as $table) {
                $parsed = $parsed_query;
                $parsed['FROM'][0]['table'] = $table;
                $query = $creator->create($parsed);
                $temp_result = $this->database_callback($query);
                if ($temp_result === false) {
                    return false;
                }

                $result[$table] = $temp_result;
            }
        } catch (\Exception $e) {
            if (!empty($this->error_callback)) {
                $this->sql_error_handle($query);
            } else {
                throw $e;
            }
        }

        return $result;
    }
} 