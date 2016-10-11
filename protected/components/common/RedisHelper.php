<?php
/**
 * Created by JetBrains PhpStorm.
 * User: lww
 * Date: 13-9-20
 * Time: 下午3:32
 * To change this template use File | Settings | File Templates.
 */
class RedisHelper
{
    private $serverIp;
    private $serverPort;
    private $password;
    private $database;

    public function  __construct($serverIp = '', $serverPort = '', $psd = '', $db = 0){
        if(empty($serverIp)) {
            $this->serverIp = Yii::app()->params['redis']['host'];
        } else {
            $this->serverIp = $serverIp;
        }

        if(empty($serverPort)) {
            $this->serverPort = Yii::app()->params['redis']['port'];
        } else {
            $this->serverPort = $serverPort;
        }

        if(empty($psd)){
            $this->password = Yii::app()->params['redis']['password'];
        } else{
            $this->password = $psd;
        }

        if(empty($db)){
            $this->database = Yii::app()->params['redis']['database'];
        } else{
            $this->database = $db;
        }

    }

    public  function hashAdd($hashKey, $field, $value){
        $redis = $this->getRedisClient();

        $redis->hSet($hashKey, $field, $value);
        $redis->close();
    }

    public function hashRemove($hashKey, $field) {
        $redis = $this->getRedisClient();

        $redis->hDel($hashKey, $field);
        $redis->close();
    }

    public function hashGet($hashKey, $field) {
        $redis = $this->getRedisClient();

        $value = $redis->hGet($hashKey, $field);
        $redis->close();

        return $value;
    }

    public function hashGetAll($hashKey) {
        $redis = $this->getRedisClient();

        $keyValues = $redis->hGetAll($hashKey);
        $redis->close();

        return $keyValues;
    }

    private function hashMSet($hashKey, $fieldValues){
        $redis = $this->getRedisClient();

        $result = $redis->hMSet($hashKey, $fieldValues);
        $redis->close();

        return $result;
    }

    public function objectAdd($key, $object)
    {
        if (!isset($object))
            return false;

        $fieldValues = (array)$object;

        return $this->hashMSet($key, $fieldValues);
    }

    public function objectRemove($key)
    {
        return $this->del($key);
    }

    /**
     * @param $key
     * @return array 原对象的 属性=>值 的集合
     */
    public function objectGet($key) {
        $result = $this->hashGetAll($key);
        return $result;
    }

    public function keys($keyword) {
        $redis = $this->getRedisClient();

        $result = $redis->keys($keyword);
        $redis->close();

        return $result;

    }

    public function keyExpire($key, $seconds) {
        $redis = $this->getRedisClient();

        $result = $redis->expire($key, $seconds);

        if($result != 1) {
            //执行失败，记录warning类型的日志：
            Yii::log('设置Redis Key 的超时时间失败：key:' . $key . ', expires in ' . $seconds . ' seconds.', 'warning');
        }

        $redis->close();

        return $result;
    }

    public function setString($key, $value){
        $redis = $this->getRedisClient();

        $result = $redis->set($key, $value);

        $redis->close();

        return $result;

    }

    public function getString($key) {
        $redis = $this->getRedisClient();

        $result = $redis->get($key);

        $redis->close();

        return $result;
    }

    public function del($key){
        $redis = $this->getRedisClient();

        $result = $redis->del($key);
        $redis->close();

        return $result;
    }

    /**
     * @return Redis
     */
    private function getRedisClient()
    {
        $redis = new Redis();
        $redis->connect($this->serverIp, $this->serverPort);

        if(!empty($this->password)){
            if($redis->auth($this->password) === false){
                throw new Exception('Redis authentication failed!');
            }

            if($this->database > 0){
                $redis->select($this->database);
            }
        }

        return $redis;
    }

}
