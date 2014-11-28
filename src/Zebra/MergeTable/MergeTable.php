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

class MergeTable {

    /**
     * @var callable 数据库驱动回调函数
     */
    protected $database_callback;

    /**
     * @var 合并表数组
     */
    protected $tables;

    /**
     * @param $database_callback 数据库驱动回调函数
     * @throws Exception\DBCallbackIllegalMergeTableException
     */
    public function __construct($database_callback=null){
        if(is_null($database_callback)) return ;

        if(!is_callable($database_callback)){
            throw new DBCallbackIllegalMergeTableException;
        }
        $this->database_callback = $database_callback;
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
        //SQL语句分析、拆分
        $parser = new \PHPSQL\Parser($query, true);
        $parsed = $parser->parsed;

        print_r($parsed);

        //获取查询结果

        //合并结果

        //结果运算(SUM、COUNT等)

        //返回结果
    }
} 