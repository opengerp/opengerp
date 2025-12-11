<?php

namespace Opengerp\System;

class ModuliRepository
{

    private $file_schema;


    public function __construct($file_schema="./lib/schema/gerp_modules.xml")
    {


        $this->file_schema = $file_schema;


    }

    public function fetchModule($id)
    {

        $vett = $this->fetchAll();

        if ( !isset($vett[$id])) {
            return [];
        }

        return $vett[$id];
    }


    public function fetchAll()
    {



        $vett = [];

        $obj_schema = simplexml_load_file($this->file_schema);

        foreach ($obj_schema->children() as $modulo) {

            $json = json_encode($modulo);
            $lin = (array) json_decode($json, true);

            $lin['Des_Ext'] = $lin['Des_Ext'] ?? '';


            if (is_array($lin['Des_Ext'])) {
                $lin['Des_Ext'] = '';
            }
            $vett[$lin['ID']] = $lin;

        }


        return $vett;


    }

    public function install($id)
    {


        $file_moduli = $this->file_schema;

        $obj_schema = simplexml_load_file($file_moduli);

        if (!$obj_schema) {

            throw new \Exception('File dello schema non trovato');

        }
        $vett_parameters = [];

        foreach($obj_schema->children() as $modulo) {




            if ($modulo->ID == $id) {


                $descrizione = (string) $modulo->Descrizione;
                $voce_menu = (string) $modulo->Voce_Menu;
                $id_parent = (string) $modulo->ID_Parent;
                $controller = (string) $modulo->Controller;
                $des_ext = (string) $modulo->Des_Ext;
                $get_var = (string) $modulo->Get_Vars;

                foreach ($modulo->parameters as $key=>$parameters) {
                    foreach ($parameters->children() as $parameter) {
                        $vett_parameters[(string)$parameter->name] = (string)$parameter->default;
                    }
                }

                $json_parameters = json_encode($vett_parameters);


                $db_mod = new \Opengerp\Core\DbObjects\Moduli();
                $db_mod->ID = $id;
                $db_mod->Descrizione = $descrizione;
                $db_mod->Descrizione_Ext  = $des_ext;
                $db_mod->Voce_Menu = $voce_menu;
                $db_mod->ID_Parent = $id_parent;
                $db_mod->Controller = $controller;
                $db_mod->Get_Vars = $get_var;
                $db_mod->Json_Config = $json_parameters;

                $db_mod->insert();



                if ($modulo->documents) {
                    self::checkDocuments($modulo->documents);

                }


                if ($modulo->attributes) {

                    foreach ($modulo->attributes->children() as $param) {



                        $obj_param = \Gerp\Core\AttributeFactory::createFromXml($param);

                        $vett_attribute = $obj_param->toArray();

                        if ( ! \Gerp\Core\AttributeRepository::fetchByCode($vett_attribute['name'])) {

                            \Gerp\Core\AttributeRepository::create($vett_attribute);


                        }


                    }
                }

                if ($id == 'cog_cespiti') {

                    if (file_exists(  './lib/schema/conti_cespiti.sql')) {

                        $query = file_get_contents(  './lib/schema/conti_cespiti.sql');

                        $vett_query = explode('--', $query);

                        gsql_multi_query($vett_query);

                    }

                }

                \Opengerp\Core\Console\Console::appendSuccess('Modulo <a href="'. $_SERVER['PHP_SELF'] .'?manage='. $id .'">%s</a> installato correttamente',[$descrizione]);


                return true;

            }


        }

        return false;


    }


    public function checkModules()
    {

        $db = \Opengerp\Database\DbObject::getDefaultDb();

        $file_moduli = $this->file_schema;

        if (!file_exists($file_moduli)) {

            gerp_display_error("File moduli %s non corretto",[$file_moduli]);
            return false;

        }


        $obj_schema = simplexml_load_file($file_moduli);
        $vett_sql = array();

        foreach ($obj_schema->children() as $module) {
            //  echo $module->ID;
            $ris = $db->query("SELECT * FROM INT_Moduli WHERE ID = '$module->ID'");
            if ($lin = $ris->fetch($ris)) {
                //    echo " OK!<br>";

                if (isset($module->Get_Vars) && $module->Get_Vars == 1) {
                    $get_vars = 1;
                } else {
                    $get_vars = 0;

                }
                if (!$lin['Custom']) {

                    $controller = doppio_apice($module->Controller);

                    $vett_sql[] = "UPDATE INT_Moduli SET
                    Controller = '$controller',
                    ID_Parent = '$module->ID_Parent',
                    Widget = '$module->Widget'
                    WHERE ID = '$module->ID '";
                }

            } else {

                $required = (bool) $module['required'] ?? false;
                $cod_modulo = (string)$module->ID;

                if ($required) {

                    $this->install($cod_modulo);
                    $this->checkDocumentStatus($cod_modulo);


                }

            }

            $db->query(" DELETE FROM INT_Moduli_Lang WHERE ID_Modulo = '$module->ID' ");



            foreach ($module->children()->languages as $languages) {


                foreach ($languages->children()->INT_Moduli_Lang as $lang) {
                    $lang->Descrizione = $db->escape_string($lang->Descrizione);
                    $lang->Voce_Menu = $db->escape_string($lang->Voce_Menu);

                    $db->query("INSERT INTO INT_Moduli_Lang (ID_Modulo, Cod_Lingua, Descrizione, Voce_Menu) VALUES ('$module->ID','$lang->Cod_Lingua','$lang->Descrizione','$lang->Voce_Menu')  ");
                }


            }


        }

    }



    public function checkDocumentStatus($id)
    {
        $file_moduli = $this->file_schema;
        $obj_schema = simplexml_load_file($file_moduli);

        foreach ( $obj_schema as $module ) {

            if ( $id == $module->ID ) {

                $id = (string)$module->ID;

                if ( isset($module->documents_status) ) {

                    foreach ( $module->documents_status->children() as $status ) {

                        $id_status = (int)$status['id'];

                        if ( ! carica_des_stato_doc($id_status) ) {

                            $obj_status_configurator = new \Gerp_Configurazione_Stati();

                            $vett_status['ID'] = $id_status;
                            $vett_status['Descrizione'] = trim((string)$status);
                            $vett_status['Documento'] = (string)$module->documents_status['code'];
                            $vett_status['Stato_Attivo'] = (string)$status['active'];

                            $vett_params = [];

                            if ((string)$status['color']) {
                                $vett_params['background_color'] = (string)$status['color'];
                            }

                            if ((string)$status['icon']) {
                                $vett_params['icon'] = (string)$status['icon'];
                            }

                            if (!empty($vett_params)) {
                                $vett_status['parametri'] = $vett_params;
                            }


                            $obj_status_configurator->salva_stato($vett_status, 'insert');

                            \Opengerp\Core\Console\Console::appendSuccess('Stato %s configurato correttamente', [$vett_status['Descrizione']]);
                        }


                    }


                }

            }
        }

    }


    public static function checkDocuments(\SimpleXMLElement $xml_documents)
    {
        foreach ($xml_documents->children() as $document) {

            $db_document = new \Gerp\Core\DbObjects\DocumentType();

            foreach ($document->children() as $element) {

                $key = $element->getName();

                if (property_exists($db_document, $key)) {
                    $db_document->$key = (string)$element;
                }


            }

            if (!carica_des_tipo_doc($db_document->ID_Tipo_Doc)) {

                $db_document->Serie_Doc = date('y');

                if (!$db_document->insert()) {
                    throw new \Exception('Tipo documento non creato');
                }


            }


            if ($document->printmodel) {

                $db_printmodel = new \Gerp\Core\DbObjects\DocumentTypePrintModel();
                $db_printmodel->ID_Tipo_Doc = $db_document->ID_Tipo_Doc;
                $db_printmodel->File_Stampa = $document->printmodel['filename'];
                $db_printmodel->Cod_Stampa = $document->printmodel['cod_stampa'] ?? 1;
                $db_printmodel->Des_File_Stampa = 'default';
                $db_printmodel->insert();


            }

            if ($document->printables) {
                $printables = $document->printables;
                foreach ($printables->children() as $print) {
                    $print = (array)$print;
                    $db_printmodel = new \Gerp\Core\DbObjects\DocumentTypePrintModel();
                    $db_printmodel->ID_Tipo_Doc = $db_document->ID_Tipo_Doc;
                    $db_printmodel->File_Stampa = $print['filename'];
                    $db_printmodel->Cod_Stampa = $print['cod'];
                    $db_printmodel->Des_File_Stampa = $print['des'];
                    $db_printmodel->insert();
                }

            }


        }

    }


}
