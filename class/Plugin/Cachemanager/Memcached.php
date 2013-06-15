<?php
/**
 *  Memcached.php
 *
 *  @author     Sotaro Karasawa <sotaro.k /at/ gmail.com>
 *  @package    Ethna
 *  @version    $Id$
 */

/**
 *  キャッシュマネージャクラス(pecl::memcached 版)
 *
 *  @author     Sotaro Karasawa <sotaro.k /at/ gmail.com>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_Plugin_Cachemanager_Memcached extends Ethna_Plugin_Cachemanager
{
    /**#@+  @access private */

    /** @var    object  Memcached    Memcached オブジェクト */
    private $m = null;

    /** @var    array   plugin configure */
    public $config_default = array(
        'host' => 'localhost',
        'port' => '11211',
        'use_pconnect' => false,
        'retry' => 3,
        'timeout' => 3,
    );

    protected $_get_data_cache = array();

    /**#@-*/

    /**
     *  _load
     *
     *  @access protected
     */
    protected function _load()
    {
        parent::_load();

        if ($this->opt['use_pconnect']) {
            $this->m = new Memcached($this->ctl->getAppId());
        }
        else {
            $this->m = new Memcached();
        }

        if (isset($this->opt['servers']) && is_array($this->opt['servers'])) {
            $this->m->addServers($this->opt['servers']);
        }
        else {
            $this->m->addServer($this->opt['host'], $this->opt['port']);
        }

        $this->m->setOption(Memcached::OPT_CONNECT_TIMEOUT, $this->opt['timeout'] * 1000);
        //$this->m->setOption(Memcached::OPT_CONNECT_TIMEOUT, $this->opt['retry']);
    }

    /**
     *  get cached data
     *
     *  キャッシュに値が設定されている場合はキャッシュ値
     *  が戻り値となる。キャッシュに値が無い場合やlifetime
     *  を過ぎている場合、エラーが発生した場合はEthna_Error
     *  オブジェクトが戻り値となる。
     *
     *  @access public
     *  @param  string  $key        cache key
     *  @param  int     $lifetime   cache lifetime
     *  @param  string  $namespace  namespace
     *  @return mixed   value
     */
    public function get($key, $lifetime = null, $namespace = null)
    {
        $cache_key = $this->_getCacheKey($namespace, $key);
        if ($cache_key == null) {
            return Ethna::raiseError('invalid cache key (too long?)', E_CACHE_NO_VALUE);
        }

        if (isset($this->_get_data_cache[$cache_key])) {
            return $this->_get_data_cache[$cache_key]['data'];
        }

        $value = $this->m->get($cache_key);
        if (!$value) {
            return Ethna::raiseWarning(
                sprintf('no such cache, key="%s", message="%s"', $key, $this->m->getResultMessage()),
                E_CACHE_NO_VALUE
            );
        }

        $time = $value['time'];
        $data = $value['data'];

        // ライフタイムチェック
        if ($lifetime !== null) {
            if (($time + $lifetime) < time()) {
                return Ethna::raiseWarning('lifetime expired', E_CACHE_EXPIRED);
            }
        }

        // result cache
        $this->_get_data_cache[$cache_key] = $value;

        return $data;
    }

    /**
     *  キャッシュの最終更新日時を取得する
     *
     *  @access public
     *  @param  string  $key        cache key
     *  @param  string  $namespace  cache namespace
     *  @return int     unixtime
     */
    public function getLastModified($key, $namespace = null)
    {
        $cache_key = $this->_getCacheKey($namespace, $key);
        if ($cache_key == null) {
            return Ethna::raiseError('invalid cache key (too long?)', E_CACHE_NO_VALUE);
        }

        $value = $this->get($cache_key);
        if (Ethna::isError($value)) {
            return $value;
        }

        return $value['time'];
    }

    /**
     *  check cache exists
     *
     *  @access public
     *  @param  string  $key        cache key
     *  @param  int     $lifetime   lifetime
     *  @param  string  $namespace  namespace
     */
    public function isCached($key, $lifetime = null, $namespace = null)
    {
        $r = $this->get($key, $lifetime, $namespace);

        return !Ethna::isError($r);
    }

    /**
     *  set cache
     *
     *  @access public
     *  @param  string  $key        cache key
     *  @param  mixed   $value      cache value
     *  @param  int     $timestamp  timestamp of last modified
     *  @param  string  $namespace  namespace
     *  @param  int     $lifetime   expiration
     */
    public function set($key, $value, $timestamp = null, $namespace = null, $expiration = null)
    {
        $cache_key = $this->_getCacheKey($namespace, $key);
        if ($cache_key === null) {
            return Ethna::raiseError('invalid cache key (too long?)', E_CACHE_NO_VALUE);
        }

        $time = $timestamp ? $timestamp : time();
        $expiration = $expiration ? $expiration : 0;
        if (!$this->m->set($cache_key, array('time' => $time, 'data' => $value), $expiration)) {
            return Ethna::raiseError(
                sprintf('failed to set cache, key="%s", message="%s"', $key, $this->m->getResultMessage()),
                E_CACHE_GENERAL
            );
        }

        return true;
    }

    /**
     *  delete cache
     *
     *  @access public
     *  @param  string  $key        キャッシュキー
     *  @param  string  $namespace  キャッシュネームスペース
     */
    public function clear($key, $namespace = null)
    {
        $cache_key = $this->_getCacheKey($namespace, $key);
        if ($cache_key === null) {
            return Ethna::raiseError('invalid cache key (too long?)', E_CACHE_NO_VALUE);
        }

        if (!$this->m->delete($cache_key)) {
            return Ethna::raiseError(
                sprintf('failed to clear cache, key="%s", message="%s"', $key, $this->m->getResultMessage()),
                E_CACHE_NO_VALUE
            );
        }

        return true;
    }

    /**
     *  キャッシュデータをロックする
     *
     *  @access public
     *  @param  string  $key        キャッシュキー
     *  @param  int     $timeout    ロックタイムアウト
     *  @param  string  $namespace  キャッシュネームスペース
     *  @return bool    true:成功 false:失敗
     */
    public function lock($key, $timeout = 5, $namespace = null)
    {
        // not supported
        return true;
    }

    /**
     *  キャッシュデータのロックを解除する
     *
     *  @access public
     *  @param  string  $key        キャッシュキー
     *  @param  string  $namespace  キャッシュネームスペース
     *  @return bool    true:成功 false:失敗
     */
    public function unlock($key, $namespace = null)
    {
        // not supported
        return true;
    }

    /**
     *  set option of pecl::memcached directly
     *
     *  @access public
     *  @param  string  $opt    option key
     *  @param  string  $value  opeion value
     *  @return bool
     *  @see http://jp.php.net/manual/memcached.setoption.php
     */
    public function setMemcachedOption($opt, $value)
    {
        return $this->m->setOption($opt, $value);
    }

    /**
     *  get option of pecl::memcached directly
     *
     *  @access public
     *  @param  string  $opt    option key
     *  @return mixed
     *  @see http://jp.php.net/manual/memcached.getoption.php
     */
    public function getMemcachedOption($opt)
    {
        return $this->m->getOption($opt);
    }

    /**
     *  ネームスペースからキャッシュキーを生成する
     *
     *  @access private
     */
    private function _getCacheKey($namespace, $key)
    {
        $namespace = $this->getNamespace($namespace);

        $key = str_replace(":", "_", $key);
        $cache_key = $namespace . "::" . $key;
        if (strlen($cache_key) > 250) {
            return null;
        }
        return $cache_key;
    }
}
