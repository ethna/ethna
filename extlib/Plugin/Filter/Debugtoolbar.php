<?php
// vim: foldmethod=marker
/**
 *  Ethna_Plugin_Filter_DebugToolbar.php
 *
 *  @author     Sotaro KARASAWA <sotaro.k@gmail.com>
 *  @package    Ether
 */

/**
 *  DebugToolbar Plugin Filter
 *
 *  @description    DebugToolbar plugin standard set
 *  @author         Sotaro KARASAWA <sotaro.k@gmail.com>
 *  @access         public
 *  @package        Ethna_Plugin_Filter_DebugToolbar
 */
class Ethna_Plugin_Filter_Debugtoolbar extends Ethna_Plugin_Filter
{
    var $version = '1.0.0 - $Id$';

    var $type_mapping = array(
        VAR_TYPE_INT      => 'VAR_TYPE_INT',
        VAR_TYPE_FLOAT    => 'VAR_TYPE_FLOAT',
        VAR_TYPE_STRING   => 'VAR_TYPE_STRING',
        VAR_TYPE_DATETIME => 'VAR_TYPE_DATETIME',
        VAR_TYPE_BOOLEAN  => 'VAR_TYPE_BOOLEAN',
        VAR_TYPE_FILE     => 'VAR_TYPE_FILE',
    );

    var $form_type_mapping = array(
        FORM_TYPE_TEXT     => 'FORM_TYPE_TEXT',
        FORM_TYPE_PASSWORD => 'FORM_TYPE_PASSWORD',
        FORM_TYPE_TEXTAREA => 'FORM_TYPE_TEXTAREA',
        FORM_TYPE_SELECT   => 'FORM_TYPE_SELECT',
        FORM_TYPE_RADIO    => 'FORM_TYPE_RADIO',
        FORM_TYPE_CHECKBOX => 'FORM_TYPE_CHECKBOX',
        FORM_TYPE_SUBMIT   => 'FORM_TYPE_SUBMIT',
        FORM_TYPE_FILE     => 'FORM_TYPE_FILE',
        FORM_TYPE_BUTTON   => 'FORM_TYPE_BUTTON',
        FORM_TYPE_HIDDEN   => 'FORM_TYPE_HIDDEN',
    );

    private $_stime;

    public function __destruct() {
    }

    public function preFilter()
    {
        $stime = microtime(true);
        $this->_stime = $stime;
    }

    /**
     *  filter which will be executed at the end.
     *
     *  @access public
     */
    function postFilter()
    {
        if (!is_null($view = $this->ctl->getView()) && !$view->has_default_header) {
            return null;
        }

        $this->init();
        $this->dumpInfo();
        $this->dumpConfig();
        $this->dumpActionForm();
        $this->smartyDebug();

    }

    function init()
    {
        $url = $this->ctl->getConfig()->get('url');
        if (substr($url, -1) != '/') {
            $url .= '/';
        }

        // {{{ CSS
        echo '<style type="text/css">';
        echo <<<EOF

.xdebug-var-dump {
  background: #f0f0f0;
  padding: 2px;
  font-family: monospace;
  line-height: 150%;
}

/* ethna debug style
 */

/*
0 => string 'EMERG' (length=5)
1 => string 'ALERT' (length=5)
2 => string 'CRIT' (length=4)
3 => string 'ERR' (length=3)
4 => string 'WARNING' (length=7)
5 => string 'NOTICE' (length=6)
6 => string 'INFO' (length=4)
7 => string 'DEBUG' (length=5)
*/
.ethna-debug-pre
{
  line-height: 55%;
  border: solid 2px #333;
  padding: 8px;
  margin: 10px;
}
.ethna-debug-pre-blink
{
  color: #f00;
}
.ethna-debug-title ,
.ethna-debug-subtitle {
  font-family: Verdana, "Hiragino Kaku Gothic Pro W3", "Meiryo" !important;
  font-size: 18px;
  font-weight: bold;
  line-height: 2.6em;
}

.ethna-debug {
  font-family: Verdana !important;
  position: fixed;
  bottom: 0px;
  left: 0px;
  width: 100%;
  max-height: 50%;
  padding-top: 20px;
  padding-bottom: 50px;
  overflow: auto;
  background: #ccc !important;
  border-top: solid 3px #fff;
  color: #333 !important;
  display:none;
  font-size: 12px;
  opacity: 0.9;
}
.ethna-debug td,
.ethna-debug th {
  color: #333 !important;
}
.ethna-debug div {
  padding-left: 20px;
}

#ethna-debug-switch-outline {
  background: #666;
  color: #fff;
  margin: 0;
  padding: 0;
  position: fixed;
  bottom: 0px;
  left: 0px;
  font-size: 14px;
  font-family: Verdana, "Hiragino Kaku Gothic Pro W3", "Meiryo" !important;
  opacity: 0.8;
}
#ethna-debug-switch-outline li {
  padding: 7px 10px 7px 22px;
  float:left;
  list-style:none;
  z-index: 1000;
}
li.ethna-debug-switch {
  /* background-position: 2px 12px; */
  background-position: 4px 50%;
  background-repeat: no-repeat;
}
li.ethna-debug-switch:hover {
  background-color: #fff !important;
  color: #333 !important;
}

li#ethna-debug-switch-EthnaClose {
  background-image: url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAHdSURBVDjLpZNraxpBFIb3a0ggISmmNISWXmOboKihxpgUNGWNSpvaS6RpKL3Ry//Mh1wgf6PElaCyzq67O09nVjdVlJbSDy8Lw77PmfecMwZg/I/GDw3DCo8HCkZl/RlgGA0e3Yfv7+DbAfLrW+SXOvLTG+SHV/gPbuMZRnsyIDL/OASziMxkkKkUQTJJsLaGn8/iHz6nd+8mQv87Ahg2H9Th/BxZqxEkEgSrq/iVCvLsDK9awtvfxb2zjD2ARID+lVVlbabTgWYTv1rFL5fBUtHbbeTJCb3EQ3ovCnRC6xAgzJtOE+ztheYIEkqbFaS3vY2zuIj77AmtYYDusPy8/zuvunJkDKXM7tYWTiyGWFjAqeQnAD6+7ueNx/FLpRGAru7mcoj5ebqzszil7DggeF/DX1nBN82rzPqrzbRayIsLhJqMPT2N83Sdy2GApwFqRN7jFPL0tF+10cDd3MTZ2AjNUkGCoyO6y9cRxfQowFUbpufr1ct4ZoHg+Dg067zduTmEbq4yi/UkYidDe+kaTcP4ObJIajksPd/eyx3c+N2rvPbMDPbUFPZSLKzcGjKPrbJaDsu+dQO3msfZzeGY2TCvKGYQhdSYeeJjUt21dIcjXQ7U7Kv599f4j/oF55W4g/2e3b8AAAAASUVORK5CYII=");
  background-position: center 3px;
  border: none;
  text-indent: -9999px;
}
li#ethna-debug-switch-Ethna {
  padding-right: 0;
  background-image: url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAIAAACQkWg2AAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAABUSURBVHjaYvz//z8DKYCJgVSA34b/YPC+zO0/DLAQo40iJzECzWZkZMRqw4dyd5x++I8XIPuBZCdBPQ10FZFhiM8P1AmlQaiB5FBiISkhAQFAgAEA1FBb2xYZTGEAAAAASUVORK5CYII=");
  /* background-image: url(../images/ethna-debug-switch-Ethna.png); */
}
#ethna-debug-switch-Timer {
  background-image: url("data:image/png;base64,");
}
#ethna-debug-switch-ActionForm {
  background-image: url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAZdJREFUeNqcUz2LwkAQfdFFEAQRRU1zrXYWgiCineX1Xi+50srOwn8gtv4OewsRBTsR/0CKgKCFH/gRNbczR5bEeCD3IOy+nZnH28ms1u12PwHoeAOPx8PHHcex0Ol0vp03MZvNfLzdbhvifr+TEn8uxuMxLMtSXNd11Go12LbNeYPBAIZhYL/fQ8hDjayNRiMkEgkUCgVEIhE0Gg0lMJ1O2f71emWBZrPJ6263g7jdbixQrVY5mRxlMhmsVislQJxyDocDhsOhOjdNkx1wkApdZLPZlw28XC6+2Ha7hZC2tGeB+XyO9XqteDqdRrlcVg694B7Q4WKxwPl8RrFYRDQaDfTALQwIuA7y+bxKSKVSvh4Qd2cgICDvFbhCPB7/c4j+dOANLJdLbDYbxZPJJF+NIP9a0AEVTyYTnoNcLodYLIZ6vf6yB5VKhffhcPhX4Hg8ajQUpVJJWSUhbw+IUw7FyEGr1UK/3ychTciRFaFQiKfPhXfvgnJOpxOvvV6PVzmJQpMN+5LKH2++RvtJ1NS8j+g/+BFgAJmwQl7DhC7TAAAAAElFTkSuQmCC");
}
#ethna-debug-switch-Log {
  background-image: url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAJxSURBVHjajJPLbxJBHMe/+wCk0gJKbW0DxERbG+qD9CAeSjzIAUNibJuY9OaFxIvnXvgnqgkJdy9cDBcS8WT0YNCaphEjjUSgLa+Wx5ZdBJZdnKEBS4uJ32Qyu/Ob32fm9xim2+0iGo0ysVgs4PF4nnSIQETXVVVFq9Vid3Z2kqFQaKNSqWSpTaPRIBKJwOv1gseJWKvVeuMukSzL6APoaLfbsFgsToZhNMFg8EWtVsvjlFicEdl4YiAzz7HkNC0BgVlbW1sNBAIvTSbTDAWfA1BHlmUH41BsIp7Ko1bchXCYQTqdYVyu+yt+v3/TbDYPIPxpAMdx6CgKfhQEvN4u4Ft6D5uLCUwV4ijiGiTDOuPz+VYFQfhJABtDAHoqR66cKUt4mxLwIXsMtlqFtlPB1YsFmEpf0ZAcmJp7CrvdPksO5IibMhSCogJfcnXE8w3sFctQGwIYMQteLoNTJIjJCOSmBJ1Odz4HNGltQvh8UMd2uggSPKxGGb+7TTRECS1SHKmaQ5NAaah9DeWAJ4ZiRYBFKcP3QAvHvIitjgWp/UVYxDTarBG8Vj+o1BCArGJ87AKeLY2jdOsIBnMBpcYvlNs5fNdL0E9O4t6EF6ZLV4bKfuYGPDx3FvBu9z0+ZT/iuF5Bq6FCEbW4bHRifmkFBoNh9A3oIsMyMIyZ8PD6c0yyDiT3t9DSyZi134RzbhnT0zO9+P8JoEba/8YJM5ZvP4Zr4VHvn+M5Umam19q03CMB1Lmf3UGXabihd0EHz/Ojq5DL5WqJROJIUZROHzBqJs58Pp+v2Wy2vwDSGGo4HH71hgj/IfJCD9xut0q//wgwALyXGwTcoR6SAAAAAElFTkSuQmCC");
}
#ethna-debug-switch-Info {
  background-image: url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAH2SURBVHjapFPPS1RRFP7uvfOQQnRsnGkR4iJkwEUFiZsBBXPllHshaJP0w6WgCP0HLURhxkD/gaBFigaSi+El0bJWYRQ0iWI9JycJdebNvNe5P971RbSaB9+99z3O953vnHseC8MQrTwJuTDG7Ifr+Yc9tE0R8oSs+bxD2CAUPmw8241iZXKmFiNA5PvJjvZibuCa09d7BfVGA4xznNUbKO99x5b7zqewxySy8o+AJPf39S7fGcmhfHCIn9XfOCUiJwGJy6kkAoR4XXqLo+qvSSliBW7cftRDmb88mBh3Pn7dR4XIEZFzAS70uf3iBQRBiNVXm9LJ1ffrS7vclDM1PppzvKNjlVlQsBACC3P3MD97l96FQs1voK3NweDATcf0CZFAPpXswKfyAWUTGpzbxioHQrs5qzeRyXTDNFnfAtnPshgxsj0z/1yfY2XIs2CKlrUCKgvjymYU+HR6wjp4UnxphLQTxmLu5EI1f675vrUpMbf4wgapHgjdF3mGHr4dK1CpHq992/eQSXWaerXIeQ9EzIGAd1iBGSzbxMLqZsmXuulLnbZpVsAQJYIggPtmW15jQU1xfJC6upLLY6NDaAZMTd95SbovTYotlVx4nvf3IMVHmbbireGck06nkUgkFPGk5uOH58F1t/8/yi39TK08fwQYAJFWzPPb9QZyAAAAAElFTkSuQmCC");
}
#ethna-debug-switch-SmartyDebug {
  background-image: url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAIAAACQkWg2AAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAJcSURBVHjabFJLT1NREJ7zuo8+eFRAqYoESi0aEgwJaqxRXBQTEzDGiAsNO7f+ALdsTPwFunHlygUbE6NBVBAXjUYN8rCgUIjIK1Sgpff23jOee6sJqJNZzDmZx/d9MyT3+jzsNkTCTJRFFQEQ+MfoX29EW9R28X1JdK0/XxKk7df/pwAJ1Vg4rkd7qXkY3aI3igpW0UZYSDVTGdxPc1CWPADoUuOAFmoAs66ipX/n60MMJngkSQjbydwrg+ReUijGAo3obKOb16taZ+ZyE1PDACzefD3RdEK6YC8/k84WocKfgIhOQdSliBbhnL4YfpWdvXHm5DoV7WPp8Pzi5VSq2938rPoCaD4HymUhay08YlRmMovZ2Tv9fUuxI3ZT9P3NvoX11ccT4+NcCEIIeLB90oQZzkaabIxMTn1Idq4oqKWSckfuZJKdm9NfJvXYLb2hn5oHFRz6WzhFhhqIkuyRXpV+AnCAmmL/RSN2WwnIwSeh1fdg1enWxPzYaE1T45oqUxpSBm/SgbaODln4bq+m3dw7WVyiIF2t9oJe3+M6pfjR5trowNOhSqGDFoDnL8PByMDxlkh+4q4i6W7PoLQVEiGtH8Vv94FQRxin2rsGnxxbWX4rOCwsNfZeSQH8ROl4mhJeXhy6W5MKvRegRerCH6cSo+lqzpimGVevFYBXUjPqbG6U6fmkiSBUJ9RQjtYyFTV68Gyoptvk1Jp74OE2D3nC+MZ3iyKRmKJ46Vx0ZGiQWqw9XgyUWH46S0SlurFyDtlz3qoNDxvBaizMoTotwu0S+jvGMgFlvwQYAL5QCxf9wMU0AAAAAElFTkSuQmCC");
}
#ethna-debug-switch-Config {
  background-image: url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAoJJREFUeNqkU01oE1EQnk02iTFQE7QihUKRkKTF1iU9+FdQCoWYgAcPegkIeiiIWiHgwUvpQXs1Ggo99OYlFwUhWAhYhZJWUmhMxJbYYk1LFDcmJraSv911vjQbevPgg9kZ5vu+eW9n3hM0TaP/WSI+gUCADAYDmUwmEgSBUNRoNJ5jaKjNSyuKsqRjjUaDVFWlWCy2X0BfDJ5nd5r9KxZI0Wh0BuRgMHibcznGrrD/wD6hawwHxBdcLte12dnZGYfDcYOFhkJBpnL5F3Y0IAcMHHB1nYAj+Xw+xHeZ8FSWf1BPTw+trqY2JElyAkilUhsej8dZKhWpu/s4jY+P3+P0s/n5+f0TVCoVqlarL0Oh0KTZbCZZlmlgoN+pqgrBEO/u/iZg4IALTecX+BQX6/X69Xw+v8e7bYqiSMvLy+t+f2AGhhg5YOCAC43+7+T1eh+srCS1hYU32tJSQkun09rg4NA0TwLTIMTIAQMHXGigbU2hVqsZq9UaNZsKKYrKoxRZKDYwKizEyAEDB1xoOk3kzo6xP4PExMT9WyMjl/q2t7+npqYevkBucvLx1d7eE9Li4tutcPjJXEsoCO+z2WxcP0GcC3zmDt8ZHj7bVyyWyO32SLHYOwl4ufyTdna+ELCuriN2nlSEC2x1mshdRZGbkchcSJaLfCOtFI+//prLbRIMMXLAwAEXmk4T+ZLALo+Ojj1PJtc1t7s/bLfbHyUSGQ2GGDlg4IALTesd6Y8JY7JarX6bzTZtsVhOwq+tfdMymZx2MAcOuPrmrSYKaDHRUbZjbIcA8sM6xQ9sADFP4xNf54/t21tnk9kKrG3qBdCLw20T//GCFbY9tj+sVf8KMAACOoVxz9PPRwAAAABJRU5ErkJggg==");
}

#ethna-debug-timewindow {
}
#ethna-debug-logwindow {
}

.ethna-debug-log {
  margin: 0;
  padding: 2px 10px;
  color: #000;
}

.ethna-debug-log-EMERG {
}
.ethna-debug-log-ALERM {
}
.ethna-debug-log-CRIT {
}
.ethna-debug-log-ERR {
  background: #ffaaaa;
}
.ethna-debug-log-WARNING {
  background: #ffaaaa;
}
.ethna-debug-log-NOTICE {
  background: #ffcccc;
}
.ethna-debug-log-INFO {
  background: #ccccff;
}
.ethna-debug-log-DEBUG {
  background: #ccc;
}

.ethna-debug-log-loglevel {
  font-weight: bold;
}

.ethna-debug-log-loglevel-EMERG {
  color: #f00;
}
.ethna-debug-log-loglevel-ALERM {
  color: #f00;
}
.ethna-debug-log-loglevel-CRIT {
  color: #f00;
}
.ethna-debug-log-loglevel-ERR {
  color: #f00;
}
.ethna-debug-log-loglevel-WARNING {
  color: #f00;
}
.ethna-debug-log-loglevel-NOTICE {
  color: #f66;
}
.ethna-debug-log-loglevel-INFO {
  color: #00f;
}
.ethna-debug-log-loglevel-DEBUG {
}

.ethna-debug-window {

}

.ethna-debug-table {
  border-collapse: collapse;
  border: solid 1px #333;
}
.ethna-debug-table th ,
.ethna-debug-table td {
  padding: 3px 5px;
  border-collapse: collapse;
  border: solid 1px #333;
  font-size: 12px;
}
.ethna-debug-table th {
  background: #9c9;
}
.ethna-debug-table td.e {
  background: #aca;
}
EOF;
        echo '</style>';
        // }}}

        // {{{ load JavaScript
        echo <<<EOL
<link rel="stylesheet" href="{$url}Debugtoolbar/css/ether.css" type="text/css" />
<script type="text/javascript" src="http://www.google.com/jsapi"></script>
<script type="text/javascript">
    google.load("jquery", "1.3");
</script>
EOL;
        // }}}

        // {{{ jquery.cookie.plugin
        echo <<<EOF
<script type="text/javascript">
/**
 * Cookie plugin
 *
 * Copyright (c) 2006 Klaus Hartl (stilbuero.de)
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 */

/**
 * Create a cookie with the given name and value and other optional parameters.
 *
 * @example $.cookie('the_cookie', 'the_value');
 * @desc Set the value of a cookie.
 * @example $.cookie('the_cookie', 'the_value', { expires: 7, path: '/', domain: 'jquery.com', secure: true });
 * @desc Create a cookie with all available options.
 * @example $.cookie('the_cookie', 'the_value');
 * @desc Create a session cookie.
 * @example $.cookie('the_cookie', null);
 * @desc Delete a cookie by passing null as value. Keep in mind that you have to use the same path and domain
 *       used when the cookie was set.
 *
 * @param String name The name of the cookie.
 * @param String value The value of the cookie.
 * @param Object options An object literal containing key/value pairs to provide optional cookie attributes.
 * @option Number|Date expires Either an integer specifying the expiration date from now on in days or a Date object.
 *                             If a negative value is specified (e.g. a date in the past), the cookie will be deleted.
 *                             If set to null or omitted, the cookie will be a session cookie and will not be retained
 *                             when the the browser exits.
 * @option String path The value of the path atribute of the cookie (default: path of page that created the cookie).
 * @option String domain The value of the domain attribute of the cookie (default: domain of page that created the cookie).
 * @option Boolean secure If true, the secure attribute of the cookie will be set and the cookie transmission will
 *                        require a secure protocol (like HTTPS).
 * @type undefined
 *
 * @name $.cookie
 * @cat Plugins/Cookie
 * @author Klaus Hartl/klaus.hartl@stilbuero.de
 */

/**
 * Get the value of a cookie with the given name.
 *
 * @example $.cookie('the_cookie');
 * @desc Get the value of a cookie.
 *
 * @param String name The name of the cookie.
 * @return The value of the cookie.
 * @type String
 *
 * @name $.cookie
 * @cat Plugins/Cookie
 * @author Klaus Hartl/klaus.hartl@stilbuero.de
 */
jQuery.cookie = function(name, value, options) {
    if (typeof value != 'undefined') { // name and value given, set cookie
        options = options || {};
        if (value === null) {
            value = '';
            options.expires = -1;
        }
        var expires = '';
        if (options.expires && (typeof options.expires == 'number' || options.expires.toUTCString)) {
            var date;
            if (typeof options.expires == 'number') {
                date = new Date();
                date.setTime(date.getTime() + (options.expires * 24 * 60 * 60 * 1000));
            } else {
                date = options.expires;
            }
            expires = '; expires=' + date.toUTCString(); // use expires attribute, max-age is not supported by IE
        }
        // CAUTION: Needed to parenthesize options.path and options.domain
        // in the following expressions, otherwise they evaluate to undefined
        // in the packed version for some reason...
        var path = options.path ? '; path=' + (options.path) : '';
        var domain = options.domain ? '; domain=' + (options.domain) : '';
        var secure = options.secure ? '; secure' : '';
        document.cookie = [name, '=', encodeURIComponent(value), expires, path, domain, secure].join('');
    } else { // only name given, get cookie
        var cookieValue = null;
        if (document.cookie && document.cookie != '') {
            var cookies = document.cookie.split(';');
            for (var i = 0; i < cookies.length; i++) {
                var cookie = jQuery.trim(cookies[i]);
                // Does this cookie string begin with the name we want?
                if (cookie.substring(0, name.length + 1) == (name + '=')) {
                    cookieValue = decodeURIComponent(cookie.substring(name.length + 1));
                    break;
                }
            }
        }
        return cookieValue;
    }
};
</script>
EOF;
        // }}}

        // {{{ JavaScript for Debugtoolbar
        echo <<<EOL
<script type="text/javascript">
//jQuery.noConflict();
jQuery(function()
{

    var buttonOutline = document.createElement('ul');
    jQuery(buttonOutline).attr('id', 'ethna-debug-switch-outline');
    jQuery('html > body').append(buttonOutline);

    var buttonEthna = document.createElement('li');
    jQuery(buttonEthna).attr('id', 'ethna-debug-switch-Ethna');
    jQuery(buttonEthna).attr('class', 'ethna-debug-switch');
    jQuery(buttonEthna).text("Ethna");
    jQuery(buttonOutline).append(buttonEthna);

    var state = {};

    jQuery('.ethna-debug').each(function()
    {
        var name = jQuery(this).children('div.ethna-debug-title').text();
        //var stateName = ^

        var showMessage = ' ' + name;
        var hideMessage = ' ' + name;
        state[name] = false;

        var targetId = jQuery(this).attr('id');
        var buttonId = 'ethna-debug-switch-' + name;
        var button = document.createElement('li');
        jQuery(button).attr('id', buttonId);
        jQuery(button).attr('class', 'ethna-debug-switch');
        jQuery(button).text(showMessage);

        jQuery(button).click(function()
        {
            jQuery('.ethna-debug').each(function()
            {
                jQuery(this).hide();
                var local_name = jQuery(this).children('div.ethna-debug-title').text();

                if (name != local_name) {
                    state[local_name] = false;
                    jQuery.cookie(local_name, 0);
                }
            });

            if (!state[name]) {
                jQuery(this).text(hideMessage);
                //jQuery('#ethna-debug-logwindow').show();
                jQuery('#' + targetId).show();
                jQuery.cookie(name, 1);
                state[name] = true;
            }
            else {
                jQuery(this).text(showMessage);
                //jQuery('#ethna-debug-logwindow').hide();
                jQuery('#' + targetId).hide();
                jQuery.cookie(name, 0);
                state[name] = false;
            }
        });


        jQuery(button).hover(function()
        {
            jQuery(this).css('cursor', 'pointer');
        },
        function()
        {
            jQuery(this).css('cursor', 'default');
        });

        jQuery(buttonOutline).append(button);

        if (jQuery.cookie(name) == 1) {
            jQuery('#' + targetId).show();
            state[name] = true;
        }

        // log window coloring
        if(jQuery('#' + targetId)
            .is(":has('.ethna-debug-log-EMERG,.ethna-debug-log-ALERM,.ethna-debug-log-CRIT,.ethna-debug-log-ERR,.ethna-debug-log-WARNING,.ethna-debug-log-NOTICE')"))
        {
            jQuery(button).css('background-color', "#f00")
                .css('color', "#fff");
        }
    });


    // close button
    var closeButtonEthna = document.createElement('li');
    jQuery(closeButtonEthna).attr('id', 'ethna-debug-switch-EthnaClose');
    jQuery(closeButtonEthna).attr('class', 'ethna-debug-switch');
    jQuery(closeButtonEthna).text("close");
    jQuery(closeButtonEthna).click(function(e) {
        jQuery(buttonOutline).hide();
        return false;
    });
    jQuery(buttonOutline).append(closeButtonEthna);

});
</script>
EOL;
        // }}}

        // time
        $etime = microtime(true);
        $time   = sprintf("%.4f", $etime - $this->_stime);

        echo '<div class="ethna-debug" id="ethna-debug-evwindow">';
        echo '<div class="ethna-debug-title">' . ETHNA_VERSION
            . ': ' . $this->controller->getCurrentActionName()  . '</div>';
        echo "<div class=\"ethna-debug-log\">";
        echo ETHNA_VERSION;
        echo "</div> \n";
        echo "<div class=\"ethna-debug-log\">";
        echo "Ethna_Plugin_Debugtoolbar Version" . $this->version;
        echo "</div> \n";

        $time_warning_class ="";
        if (0.5 < $time) {
            $time_warning_class =" ethna-debug-log-WARNING";
        }
        elseif (2 < $time) {
            $time_warning_class =" ethna-debug-log-ERR";
        }
        echo '<div class="ethna-debug-subtitle">Time Elapsed</div>';
        echo "<div class=\"ethna-debug-log $time_warning_class\">" . "${time} sec.";
        echo "</div> \n";

        echo '<div class="ethna-debug-subtitle">Action/View/Forward</div>';
        echo '<div id="ethna-debug-info-env" style="">';
        $info = array(
            'action' => $this->ctl->getCurrentActionName(),
            'action_form' => get_class($this->ctl->getActionForm()),
            'view' => get_class($this->ctl->getView()),
            'forward' => (is_null($view = $this->ctl->getView())) ? "" : $view->getCurrentForwardName(),
            'encoding' => $this->controller->getClientEncoding(),
        );
        self::dumpArray($info);
        echo "</div> \n";
        echo '</div>';
    }

    /**
     * dump php info
     *
     * @access  public
     */
    function dumpInfo()
    {
        echo '<div class="ethna-debug" id="ethna-debug-infowindow">';
        echo '<div class="ethna-debug-title">Info</div>';
        echo "<div class=\"ethna-debug-log\">";

        echo '<div class="ethna-debug-subtitle">PHPINFO</div>';
        echo '<div class="ethna-debug-subtitle" id="ethna-debug-info-env-title"><a href="javascript:;">Environment &gt;&gt;</a></div>';
        echo '<div id="ethna-debug-info-env" style="display:none;">';
        echo $this->parsePHPInfo(INFO_ENVIRONMENT);
        echo "</div> \n";

        echo '<div class="ethna-debug-subtitle" id="ethna-debug-info-var-title"><a href="javascript:;">Variables &gt;&gt;</a></div>';
        echo '<div id="ethna-debug-info-var" style="display:none;">';
        echo $this->parsePHPInfo(INFO_VARIABLES);
        echo "</div> \n";

        echo '<div class="ethna-debug-subtitle" id="ethna-debug-info-modules-title"><a href="javascript:;">Installed Modules &gt;&gt;</a></div>';
        echo '<div id="ethna-debug-info-modules" style="display:none;">';
        echo $this->parsePHPInfo(INFO_MODULES);
        //$this->dumpArray(get_loaded_extensions());
        echo "</div> \n";

        echo <<<EOF
<script type="text/javascript">
jQuery(function()
{
    jQuery("#ethna-debug-info-env-title a").click(function() {
        jQuery("#ethna-debug-info-env").toggle();
    });
    jQuery("#ethna-debug-info-var-title a").click(function() {
        jQuery("#ethna-debug-info-var").toggle();
    });
    jQuery("#ethna-debug-info-modules-title a").click(function() {
        jQuery("#ethna-debug-info-modules").toggle();
    });
});
</script>
EOF;

        echo "</div> \n";
        echo '</div>';

    }


    function parsePHPInfo($info)
    {
        ob_start();
        $phpinfo = phpinfo($info);
        $info = ob_get_contents();
        ob_end_clean();

        $info_html = @simplexml_import_dom(DOMDOcument::loadHTML($info));
        $body = $info_html->xpath("//body");
        return preg_replace("/<table/", "<table class=\"ethna-debug-table ethna-debug-table-info\"", $body[0]->asXML());
    }

    /**
     * dump action form defined values and posted values
     *
     * @access  public
     */
    function dumpActionForm()
    {
        $af = $this->ctl->getActionForm();
        if ($af === null) {
            return ;
        }
        echo '<div class="ethna-debug" id="ethna-debug-afwindow">';
        echo '<div class="ethna-debug-title">ActionForm</div>';
        echo '<div class="ethna-debug-subtitle">Posted Value</div>';
        echo "<div class=\"ethna-debug-log\">";
        self::dumpArray($this->ctl->getActionForm()->getArray());
        echo "</div> \n";
        echo '<div class="ethna-debug-subtitle">Definition</div>';
        echo "<div class=\"ethna-debug-log\">";
        //var_dump($this->controller->action_form->getArray());
        self::dumpArray($this->controller->getActionForm()->getDef());
        echo "</div> \n";
        echo '<div class="ethna-debug-subtitle">$_GET</div>';
        echo "<div class=\"ethna-debug-log\">";
        //var_dump($this->controller->action_form->getArray());
        self::dumpArray($_GET);
        echo "</div> \n";
        echo '<div class="ethna-debug-subtitle">$_POST</div>';
        echo "<div class=\"ethna-debug-log\">";
        //var_dump($this->controller->action_form->getArray());
        self::dumpArray($_POST);
        echo "</div> \n";
        echo '</div>';
    }

    function dumpConfig()
    {
        $config = $this->controller->getConfig();
        echo '<div class="ethna-debug" id="ethna-debug-configwindow">';
        echo '<div class="ethna-debug-title">Config</div>';
        echo "<div class=\"ethna-debug-log\">";
        //var_dump($this->controller->action_form->getArray());
        self::dumpArray($config->config);
        echo "</div> \n";
        echo '</div>';
    }

    function smartyDebug()
    {
        if (!defined('Smarty::SMARTY_VERSION')) {
            return ;
        }
        $c =& Ethna_Controller::getInstance();
        $debug_tpl = $c->getDirectory('template') . "/smarty_debug.tpl";

        //if smarty2
        //if (!file_exists($debug_tpl)) {
        //    Ethna::raiseWarning(sprintf("Smarty debug template not found, please set %s.", $debug_tpl), E_USER_WARNING);
        //    return null;
        //}

        require_once SMARTY_SYSPLUGINS_DIR . 'smarty_internal_debug.php';

        // get template directory
        $r = $c->getRenderer();
        $smarty = $r->engine;

        $vars = Smarty_Internal_Debug::get_debug_vars($smarty);

        //$smarty_original_debugging = $smarty->debugging;
        //$smarty_original_debugtpl = $smarty->debug_tpl;

        //$smarty->debugging = true;
        //$smarty->debug_tpl = $debug_tpl;
        //$smarty->assign('_smarty_debug_output', 'html');

        echo '<div class="ethna-debug" id="ethna-debug-smartydebugwindow">';
        echo '<div class="ethna-debug-title">SmartyDebug</div>';

        echo '<div class="ethna-debug-subtitle">Smarty template vars</div>';
        echo "<div class=\"ethna-debug-log\">";
        foreach ($vars->tpl_vars as $k => $v) {
            echo "$k<br />";
            self::dumpArray($v->value);
        }
        echo "</div> \n";

        echo '<div class="ethna-debug-subtitle">Smarty config vars</div>';
        echo "<div class=\"ethna-debug-log\">";
        foreach ($vars->config_vars as $k => $v) {
            echo "$k<br />";
            self::dumpArray($v->value);
        }
        echo "</div> \n";

        echo "</div> \n";
        echo '</div>';

        //$smarty->debugging = $smarty_original_debugging;
        //$smarty->debug_tpl = $smarty_original_debugtpl;
    }

    function dumpArray(&$array)
    {
        echo "<table class=\"ethna-debug-table\">";
        if (is_scalar($array)) {
            echo "<tr>\n";
            echo "<th>Scalar</th>";
            echo "<td>{$array}</td>";
            echo "</tr>\n";
        }
        elseif (is_object($array)) {
            echo "<tr>\n";
            echo "<th>Object</th>";
            echo "<td>" . get_class($array) . "</td>";
            echo "</tr>\n";
        }
        else foreach ($array as $k => $v) {
            echo "<tr>\n";
            echo "<th>{$k}</th>";
            if (is_array($v)) {
                echo "<td>";
                self::dumpArray($v);
                echo "</td>";
            }
            else {
                if (is_bool($v)) {
                    echo "<td>" . ($v ? '<span style="color: #090;">true</span>' : '<span style="color: #900;">false</span>')  . "</td>";
                }
                else if ($k === 'type' || $k === 'form_type') {
                    echo "<td>";
                    if ($v === null) {
                        echo "Undefined";
                    }
                    else {
                        $key = $k . "_mapping";
                        $ar = $this->$key;
                        echo $ar[$v];
                    }

                    echo "</td>";
                }
                else {
                    echo "<td>{$v}</td>";
                }
            }
            echo "</tr>\n";
        }
        echo "</table>\n";
    }

}
