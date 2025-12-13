<?php

namespace Opengerp\App;

class Config
{
    const GERP_RELEASE = 'dev';
    const GERP_VERSION = 'dev';
    const GERP_VERSION_DATE = 'dev';

    const GERP_ERRORS_REPORTING_HTML = 'html';
    const GERP_ERRORS_REPORTING_CONSOLE = 'console';

    private static $vett_config = [];
    private static $vett_config_modules = [];
    public static $gerp_errors_reporting;

    public static function reload()
    {
        self::$vett_config = [];
        self::load();
        self::loadItemsConfig();

    }

    private static function load()
    {
        $ris = gsql_query("SELECT ID, Json_Config FROM INT_Moduli ") or die('Errore database manca tabella configurazione');

        while($lin = gsql_fetch_assoc($ris)) {

            $lin['Json_Config'] = $lin['Json_Config'] ?? '';

            self::$vett_config[$lin['ID']] = (array) json_decode($lin['Json_Config'], true);
        }
    }

    public function __construct()
    {
        self::load();
        self::loadItemsConfig();

    }

    public static function setErrorsConsole()
    {
        self::$gerp_errors_reporting = self::GERP_ERRORS_REPORTING_CONSOLE;
    }

    public static function isErrorReportingConsole()
    {
        if (self::$gerp_errors_reporting == self::GERP_ERRORS_REPORTING_CONSOLE) {
            return true;
        }

        return false;
    }

    /**
     * Restituisce il path di Gerp, con "/" finale, prendendolo da uno sei seguenti:
     * .1 GERP_PATH
     * .2 getSiteConfig()['gerp_root']
     * .3 $include_prefix
     *
     * @return string
     */
    public static function getGerpPath(): string
    {
        global $include_prefix;

        // GERP_PATH: se esiste è stato settato manualmente con path assoluto
        if (defined('GERP_PATH')) {
            return \Gerp_System_Files::addSlashAfterDir(trim(GERP_PATH));
        }

        // $include_prefix: dovrebbe esserci sempre ma potrebbe contenere path relativo
        if (!empty($include_prefix) && !isset(self::getSiteConfig()['gerp_root'])){
            return \Gerp_System_Files::addSlashAfterDir(trim($include_prefix));
        }

        // gerp_root: path di Gerp nei siti/ecommerce (nel vendor o in root/office)
        if (isset(self::getSiteConfig()['gerp_root'])) {
            $path = self::getSiteConfig()['gerp_root'];
            return \Gerp_System_Files::addSlashAfterDir(trim($path));
        }

        // mantengo funzionamento standard
        return Gerp_System_Files::addSlashAfterDir($include_prefix);
    }

    public static function getSiteParameter($param_name)
    {
        return self::getEcommerceParameter($param_name);
    }




    // rif: https://www.iana.org/assignments/media-types/media-types.xhtml
    public static function getElFinderDefaultConfig($path)
    {
        $opts = array(
            'debug' => false,

            'roots' => array(

                array(
                    'driver' => 'LocalFileSystem',
                    'path' => $path,
                    'tmpPath' => 'tmp',
                    'uploadAllow' => array(
                        'application/excel',
                        'application/illustrator',
                        'application/mp4',
                        'application/msword',
                        'application/pdf',
                        'application/photoshop',
                        'application/pkcs7-mime',
                        'application/postscript',
                        'application/vnd.ms-excel',
                        'application/vnd.oasis.opendocument.text',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'application/x-iwork-keynote-sffkey',
                        'application/x-7z-compressed',
                        'application/x-iwork-pages-sffpages',
                        'application/x-photoshop',
                        'application/xml',
                        'application/zip',
                        'image/gif',
                        'image/jpeg',
                        'image/png',
                        'image/tiff',
                        'image/vnd.adobe.photoshop',
                        'image/vnd.dwg',
                        'message/rfc822',
                        'text/csv',
                        'text/plain',
                        'text/html',
                        'text/markdown',
                        'text/plain',
                        'text/rtf',
                        'text/x-markdown',
                        'text/x-yaml',
                        'text/xml',
                        'audio/x-wav',
                        'audio/mpeg',
                        'video/mp4'
                    ),
                    'uploadDeny' => array('all'),
                    'uploadOrder' => 'deny,allow',

                    'attributes' => array(

                        'read' => true,
                        'write' => true,

                        //this hides the .tmb directory
                        array(
                            'pattern' => '/.tmb/',
                            'read' => false,
                            'write' => false,
                            'hidden' => true,
                            'locked' => false
                        ),//end array
                        //this hides the .tmb directory
                        array(
                            'pattern' => '/.quarantine/',
                            'read' => false,
                            'write' => false,
                            'hidden' => true,
                            'locked' => false
                        )//end array
                    )//end attributes array

                )


            )

        );

        return $opts;

    }


    public static function get($module, $cod_parametro, $default = null)
    {
        if (defined($cod_parametro)) {

            // return
            return constant($cod_parametro);
        }


        if (isset(self::$vett_config[$module][$cod_parametro])) {
            return self::$vett_config[$module][$cod_parametro];
        }

        return $default;
    }

    public static function getParametersAsArray($module)
    {
        if (isset(self::$vett_config[$module])) {
            return self::$vett_config[$module];
        }

        return array();
    }

    public static function set($module, $cod_parametro, $value)
    {
        if (defined($cod_parametro)) {

            throw new \Exception('Parameter set in constant');
        }

        self::$vett_config[$module][$cod_parametro] = $value;
    }

    public static function getItemModule($cod_module)
    {
        $ris = gsql_query("SELECT * FROM ART_Moduli WHERE Progressivo = '$cod_module' ");

        if ($lin = gsql_fetch_assoc($ris)) {

            return $lin;

        }

        return null;
    }

    public static function hasItemModule($cod_module)
    {
        if (is_array(self::getItemModule($cod_module))) {

            return true;
        }


        return false;
    }



    public static function loadItemsConfig()
    {
        $ris = gsql_query("SELECT * FROM ART_Moduli  ");

        while ( $lin = gsql_fetch_assoc($ris) ) {

            if ( isset($lin['Parametri']) && $lin['Parametri'] ) {
                $vett = (array) json_decode($lin['Parametri'],true);
                self::$vett_config_modules[$lin['Progressivo']] = $vett;
            }

        }

    }



    public static function getItemModuleParameter($cod_module, $param_name, $default = null)
    {

        if ( ! isset(self::$vett_config_modules[$cod_module])) {

            return $default;

        }

        $vett = self::$vett_config_modules[$cod_module];

        if ( ! isset($vett[$param_name])) {

            return $default;

        }

        return $vett[$param_name];

    }

    public static function setItemModuleParameter($cod_modulo, $param_name, $param_value)
    {

        self::$vett_config_modules[$cod_modulo][$param_name] = $param_value;

    }

    public static function hasModule($module)
    {
        if (isset(self::$vett_config[$module])) {
            return true;
        }

        return false;
    }

    public static function getSystemNotifierEmailConfiguration()
    {

        $vett_azienda = \Opengerp\App\Com();

        $mail_conf = new \Gerp\Core\Mailer\Config();
        $mail_conf->email = 'notificatore@gerp.it';
        $mail_conf->name_sender = 'Notificatore gerp '.$vett_azienda['Ragione_Sociale'];
        $mail_conf->smtp = 'gerp.it';
        $mail_conf->smtp_username = 'notificatore.gerp';
        $mail_conf->smtp_password = 'theagent';
        $mail_conf->smtp_port = '587';

        return json_encode($mail_conf);
    }





}
