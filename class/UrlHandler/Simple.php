<?php
/**
 *  Simple.php
 *
 *  @author     Keisuke SATO <riaf@me.com>
 *  @package    Ethna
 *  @version    $Id$
 */

/**
 *  URLハンドラクラス
 *
 *  @author     Keisuke SATO <riaf@me.com>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_UrlHandler_Simple
{
    /** @var    array   アクションマッピング */
    protected $action_map = array(
        // 'index' => '/',
        // 'wozozo_index' => '/wozozo',
        // 'wozozo_message' => '/wozozo/{message}',
        // 'message_show' => array(
        //     'path' => '/message/{id}',
        //     'patterns' => array(
        //         'id' => '\d+',
        //     ),
        //     'defaults' => array(
        //         'id' => 123,
        //     ),
        // ),
    );

    /**
     *  アクションをユーザリクエストに変換する
     *
     *  @access public
     */
    public function actionToRequest($action, $param=array())
    {
        $ret = null;
        $action_map = $this->getActionMap();

        if (isset($action_map[$action])) {
            $def = $action_map[$action];

            foreach ($def['path'] as $path) {
                if (strpos($path, '{') && preg_match_all('/\{(.*?)\}/', $path, $matches)) {
                    $keys = array_unique($matches[1]);
                    $replaces = array();

                    foreach ($keys as $key) {
                        $val = null;
                        if (isset($param[$key])) {
                            $val = $param[$key];
                        } else if (isset($def['defaults']) && isset($def['defaults'][$key])) {
                            $val = $def['defaults'][$key];
                        }

                        if (!is_null($val)) {
                            $replaces['{'.$key.'}'] = $val;
                        } else {
                            continue;
                        }
                    }

                    $ret = array(str_replace(array_keys($replaces), array_values($replaces), $path), $keys);
                    break;
                } else {
                    $ret = array($path, array());
                    break;
                }
            }
        }

        return $ret;
    }

    /**
     * ユーザリクエストをアクションに変換する
     *
     * @param array $http_vars
     */
    public function requestToAction($http_vars)
    {
        $path = '/';

        if (isset($http_vars['__url_info__'])) {
            $path = $http_vars['__url_info__'];
        }

        foreach ($this->getActionMap() as $action => $def) {
            foreach ($def['path'] as $pattern) {
                if (strpos($pattern, '{')) {
                    $action = $this->getActionByRegex($path, $action, $def, $http_vars);

                    if (!is_null($action)) {
                        $http_vars['action_'.$action] = true;
                    }
                } else {
                    if ($path === $pattern) {
                        $http_vars['action_'.$action] = true;
                        break;
                    }
                }
            }
        }

        return $http_vars;
    }

    /**
     * 正規表現でマッチさせる
     *
     * @param string $path
     * @param string $action
     * @param array $def
     * @param array $http_vars
     */
    protected function getActionByRegex($path, $action, $def, &$http_vars)
    {
        $ret_action = null;
        $regex_pattern = array();
        $request_keys = array();

        foreach ($def['path'] as $pattern) {
            if (preg_match_all('/\{(.*?)\}/', $pattern, $matches)) {
                $request_keys = $matches[1];

                $replaces = array();
                foreach ($matches[0] as $i => $from) {
                    $key = $matches[1][$i];
                    $to = '(.+?)';

                    if (isset($def['patterns']) && isset($def['patterns'][$key])) {
                        $to = '('.$def['patterns'][$key].')';
                    }

                    $replaces[preg_quote($from, '/')] = $to;
                }

                $regex_pattern = '/^'.str_replace(array_keys($replaces), array_values($replaces), preg_quote($pattern, '/')).'$/';

                if (preg_match($regex_pattern, $path, $match)) {
                    $ret_action = $action;

                    foreach ($request_keys as $i => $key) {
                        if (isset($match[$i+1])) {
                            $http_vars[$key] = $match[$i+1];
                        }
                    }
                }
            }
        }

        return $ret_action;
    }

    /**
     *  Ethna_UrlHandlerクラスのインスタンスを取得する
     *
     *  $name がクラス名 ('_'を含む) の場合はそのクラスを、
     *  そうでないときはプラグイン名とみなしてインスタンスを返す
     *
     *  @access public
     */
    public static function getInstance($name = null)
    {
        static $instance = array();
        if ($name === null) {
            $name = __CLASS__;
        }
        if (isset($instance[$name])) {
            return $instance[$name];
        }

        if (strpos($name, '_') !== false) {
            $instance[$name] = new $name();
        } else {
            // get instance with plugin
            $ctl = Ethna_Controller::getInstance();
            $plugin = $ctl->getPlugin();
            $instance[$name] = $plugin->getPlugin('Urlhandler', $name);
        }

        return $instance[$name];
    }

    /**
     * get action_map
     * より複雑な定義などを行うときはこれをオーバーライドする
     *
     * @return array
     */
    protected function getActionMap()
    {
        foreach ($this->action_map as &$def) {
            if (is_string($def)) {
                $def = array(
                    'path' => array($def),
                );
            } else if (is_array($def) && isset($def['path']) && is_string($def['path'])) {
                $def['path'] = array($def['path']);
            }
        }

        return $this->action_map;
    }
}

