<?php

/**
 * PHP session handling with MySQL-DB
 *
 * Created on 12.03.2008
 * @license    http://www.opensource.org/licenses/cpl.php Common Public License 1.0
 */
class Sesion
{
    /**
     * a database connection resource
     * @var resource
     */
    private static $_sess_db;

    public function __construct()
    {

    }

    public function getCacheExpire()
    {
        return session_cache_expire();
    }

    public function setCacheExpire($segundos)
    {
        session_cache_expire($segundos);
    }

    public function setCacheLimiter($limitador_del_cache = 'nocache,private')
    {
        session_cache_limiter($limitador_del_cache);
    }

    public function getCacheLimiter()
    {
        return session_cache_limiter();
    }

    public function destroy()
    {
        return session_destroy();
    }

    public function getCookieParams()
    {
        return session_get_cookie_params();
    }

    public function getId()
    {
        return session_id();
    }

    public function setId($id)
    {
        session_id($id);
    }

    public function isRegistered($nombre)
    {
        if (isset($_SESSION[$nombre])) {
            return true;
        }
        return false;
    }

    public function setName($nombre)
    {
        session_name($nombre);
    }

    public function getName()
    {
        return session_name();
    }

    public function regenerateId($borrar_sesion_vieja = false)
    {
        return session_regenerate_id($borrar_sesion_vieja);
    }

    public function set($key, $valor)
    {
        $_SESSION[$key] = $valor;
    }

    public function get($key)
    {
        if ($this->isRegistered($key)) {
            return $_SESSION[$key];
        }
        return NULL;
    }

    public function setSavePath($path)
    {
        session_save_path($path);
    }

    public function getSavePath()
    {
        return session_save_path();
    }

    public function setCookieParams($duracion = 0, $path = '/', $dominio = '', $segura = false)
    {
        session_set_cookie_params($duracion, $path, $dominio, $segura);
    }

    public function start()
    {
        session_start();
    }

    public function unregister($key)
    {
        unset($_SESSION[$key]);
    }

    public function clear()
    {
        $_SESSION = array();
    }


    private function setSaveHandler()
    {
        //ini_set('session.save_handler', 'user');
        session_set_save_handler(array('Sesion', "openBd"),
            array('Sesion', "closeBd"),
            array('Sesion', "readBd"),
            array('Sesion', "writeBd"),
            array('Sesion', "destroyBd"),
            array('Sesion', "gcBd")
        );
    }

    /**
     * Open the session
     * @return bool
     */
    private static function openBd()
    {

        if (self::$_sess_db = mysql_connect('localhost',
            'root',
            'almeriajal')
        ) {
            return mysql_select_db('mvc', self::$_sess_db);
        }
        return false;
    }

    /**
     * Close the session
     * @return bool
     */
    private static function closeBd()
    {
        return mysql_close(self::$_sess_db);
    }

    /**
     * Read the session
     * @param int session id
     * @return string string of the sessoin
     */
    private static function readBd($id)
    {
        $id = mysql_real_escape_string($id);
        $sql = sprintf("SELECT `session_data` FROM `sessions` " .
            "WHERE `session` = '%s'", $id);
        if ($result = mysql_query($sql, self::$_sess_db)) {
            if (mysql_num_rows($result)) {
                $record = mysql_fetch_assoc($result);
                return $record['session_data'];
            }
        }
        return '';
    }

    /**
     * Write the session
     * @param int session id
     * @param string data of the session
     */
    private static function writeBd($id, $data)
    {
        $sql = sprintf("REPLACE INTO `sessions` VALUES('%s', '%s', '%s')",
            mysql_real_escape_string($id),
            mysql_real_escape_string(time()),
            mysql_real_escape_string($data)
        );
        return mysql_query($sql, self::$_sess_db);
    }

    /**
     * Destoroy the session
     * @param int session id
     * @return bool
     */
    private static function destroyBd($id)
    {
        $sql = sprintf("DELETE FROM `sessions` WHERE `session` = '%s'", $id);
        return mysql_query($sql, self::$_sess_db);
    }

    /**
     * Garbage Collector
     * @param int life time (sec.)
     * @return bool
     * @see session.gc_divisor      100
     * @see session.gc_maxlifetime 1440
     * @see session.gc_probability    1
     * @usage execution rate 1/100
     *        (session.gc_probability/session.gc_divisor)
     */
    private static function gcBd($max)
    {
        $sql = sprintf("DELETE FROM `sessions` WHERE `session_expires` < '%s'",
            mysql_real_escape_string(time() - $max));
        return mysql_query($sql, self::$_sess_db);
    }
}

/*
//ini_set('session.gc_probability', 50);
ini_set('session.save_handler', 'user');

session_set_save_handler(array('Session', 'open'),
                         array('Session', 'close'),
                         array('Session', 'read'),
                         array('Session', 'write'),
                         array('Session', 'destroy'),
                         array('Session', 'gc')
                         );

if (session_id() == "") session_start();
//session_regenerate_id(false); //also works fine
if (isset($_SESSION['counter'])) {
    $_SESSION['counter']++;
} else {
    $_SESSION['counter'] = 1;
}
echo '<br/>SessionID: '. session_id() .'<br/>Counter: '. $_SESSION['counter'];
*/


/*

CREATE TABLE IF NOT EXISTS `sessions` (
  `session` varchar(255) character set utf8 collate utf8_bin NOT NULL,
  `session_expires` int(10) unsigned NOT NULL default '0',
  `session_data` text collate utf8_unicode_ci,
  PRIMARY KEY  (`session`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
maria at junkies dot jp
09-Dec-2007 03:51
blow example and ta summary of these comments.
and using the simple native functions of mysql.

*/

?>