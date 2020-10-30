<?php

$mcache = new class
{
    public $host = 'localhost';
    public $port = 11211;
    public $default_ttl = 2592000;
    private $_obj;
    protected $global_prefix = '';
    public $enabled = true;

    public function __construct()
    {
        global $settings;

        if(!class_exists(Memcache)) $this->enabled = false;

        if($settings_host = $settings->get('memcache_host')) $this->host = $settings_host;
        if($settings_port = $settings->get('memcache_port')) $this->port = $settings_port;
        if($prefix = $settings->get('memcache_global_prefix')) $this->global_prefix = $prefix;

		if(isset($_GET['flush_cache'])) $this->flush();
    }

    public function connect($force = false)
    {
        if(!$this->enabled) return;

        if(!$this->_obj || $force)
        {
            $this->_obj = new Memcache();
            $this->_obj->addServer($this->host, $this->port);
        }
    }

    public function set($key, $value, $ttl = false)
    {
        if(!$this->enabled) return false;
        $this->connect();
        $setttl = !$ttl ? $this->default_ttl : $ttl;
        return $this->_obj->set($this->getKey($key), $value, $setttl);
    }

    public function get($key)
    {
        if(!$this->enabled) return false;
        $this->connect();
        return $this->_obj->get($this->getKey($key));
    }

    public function getAllData()
    {
        $this->connect();
        return $this->_obj->getMulti($this->_obj->getAllKeys());
    }

    public function flush()
    {
        $this->connect();
        return $this->_obj->flush();
    }

    public function delete($key)
    {
        $this->connect();
        return $this->_obj->delete($this->getKey($key));
    }

    protected function getKey($key)
    {
        return $this->global_prefix . '_' . $key;
    }

};
