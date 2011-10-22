<?php
/**
 *  smarty function:url生成
 */
function smarty_function_url($params, &$smarty)
{
    $action = $path = $path_key = null;
    $query = $params;

    foreach (array('action', 'anchor', 'scheme') as $key) {
        if (isset($params[$key])) {
            ${$key} = $params[$key];
        } else {
            ${$key} = null;
        }
        unset($query[$key]);
    }

    $c = Ethna_Controller::getInstance();
    $config = $c->getConfig();
    $url_handler = $c->getUrlHandler();
    list($path, $path_key) = $url_handler->actionToRequest($action, $query);

    if ($path != "") {
        if (is_array($path_key)) {
            foreach ($path_key as $key) {
                unset($query[$key]);
            }
        }
    } else {
        $query = $url_handler->buildActionParameter($query, $action);
    }
    $query = $url_handler->buildQueryParameter($query);

    $url = sprintf('%s%s', $c->getUrl(), $path);

    if (preg_match('|^(\w+)://(.*)$|', $url, $match)) {
        if ($scheme) {
            $match[1] = $scheme;
        }
        $match[2] = preg_replace('|/+|', '/', $match[2]);
        $url = $match[1] . '://' . $match[2];
    }

    $url .= $query ? "?$query" : "";
    $url .= $anchor ? "#$anchor" : "";

    return $url;
}

