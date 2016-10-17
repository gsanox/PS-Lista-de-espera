<?php
/**
 * Lista de espera
 *
 * Listado de clientes a la espera de productos sin stock, con las combinaciones de estos
 * 
 *  @author    David Torres Herrero <gsanox@gmail.com>
 *  @copyright David Torres Herrero
 *  @license   http://creativecommons.org/licenses/by/4.0/ CC BY 4.0
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class ListaDeEspera extends Module
{

    public function __construct()
    {
        $this->name = 'listadeespera'; 
        $this->tab = 'administration'; 
        $this->version = '0.0.1'; 
        $this->author = 'David Torres'; 
        $this->need_instance = 0; 
        $this->bootstrap = true;
        $this->dependencies = array('mailalerts');

        parent::__construct();

        $this->displayName = $this->l('Lista de espera'); 
        $this->description = $this->l('Listado de clientes a la espera de productos'); 
        $this->confirmUninstall = $this->l('Seguro que quieres desinstalarlo?'); 
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }   

    /**
     * Install this module
     * @return boolean
     */
    public function install()
    {
        if ( !parent::install() )
            return false;

        return true;
    }

    /**
     * Uninstall this module
     * @return boolean
     */
    public function uninstall()
    {
        if (!parent::uninstall())
            return false;

        return true;
    }

    /**
     * Configuration page
     */
    public function getContent()
    {
        if ( Tools::isSubmit('delete'.$this->name) ) {
            $this->deleteLine(); 
        }
            
        return $this->initList();
    }

    public function deleteLine()
    {
        list( $id_customer, $id_product, $id_product_attribute, $id_shop, $id_lang ) = explode( "|", Tools::getValue('ids')  );

        $q = "DELETE FROM ". _DB_PREFIX_ ."mailalert_customer_oos WHERE id_product = $id_product ";
        $q .= $id_customer != 0 ? " AND id_customer = $id_customer " : "";
        $q .= $id_product_attribute != 0 ? " AND id_product_attribute = $id_product_attribute " : "";
        $q .= " AND id_shop = $id_shop AND id_lang = $id_lang";

        if (!Db::getInstance()->execute($q))
            die('Error al al borrar el registro');
    }

    private function getData()
    {
        $sql = "SELECT maco.id_customer, maco.id_product, CONCAT(c.firstname, ' ', c.lastname) AS 'customer_name', maco.customer_email, 
        maco.id_product, pl.`name` AS 'product_name', 
        maco.id_product_attribute, 
        maco.id_shop, s.`name` AS 'shop_name', 
        maco.id_lang, l.`name` AS 'language'
        FROM ". _DB_PREFIX_ ."mailalert_customer_oos maco
        LEFT JOIN ". _DB_PREFIX_ ."customer c ON maco.id_customer = c.id_customer
        LEFT JOIN ". _DB_PREFIX_ ."product_lang pl ON ( maco.id_product = pl.id_product )
        LEFT JOIN ". _DB_PREFIX_ ."shop s ON maco.id_shop = s.id_shop
        LEFT JOIN ". _DB_PREFIX_ ."lang l ON maco.id_lang = l.id_lang
        WHERE pl.id_shop = maco.id_shop 
        AND pl.id_lang = maco.id_lang";

        if ($result = Db::getInstance()->ExecuteS($sql))
        {
            $return = array();
            foreach ($result as $v) {
                $product = new Product ($v['id_product'], $v['id_lang']);
                $attrs = $product->getAttributeCombinationsById($v["id_product_attribute"], $v['id_lang']);
                $attr_names = '';
                foreach ($attrs as $a) {
                    $attr_names .= $a["attribute_name"] . ', ';
                }
                $attr_names = rtrim($attr_names, ", ");

                $return[] = array(
                    "ids"                   => $v['id_customer'] . "|" . $v['id_product'] . "|" . $v['id_product_attribute'] . "|" . $v['id_shop'] . "|" . $v['id_lang'],
                    "id_customer"           => $v['id_customer'],
                    "customer_name"         => $v['customer_name'] == NULL ? '' : $v['customer_name'],
                    "customer_email"        => $v['customer_email'],
                    "id_product"            => $v['id_product'],
                    "product_name"          => $v['product_name'] . ' (' . $attr_names . ')',
                    "id_product_attribute"  => $v['id_product_attribute'],
                    "id_shop"               => $v['id_shop'],
                    "shop_name"             => $v['shop_name'],
                    "id_lang"               => $v['id_lang'],
                    "language"              => $v['language']
                    );
            }

        return $return;
    }
    return false;
}

    private function initList()
    {
        $this->fields_list = array(

            'ids' => array(
                'title' => "ids",
                'width' => 300,
                'type' => 'int',
                'class' => 'hidden' // ocultamos visualmente la columna
                ),

            'id_customer' => array(
                'title' => $this->l('ID del Cliente'),
                'width' => 300,
                'type' => 'int'
                ),

            'customer_name' => array(
                'title' => $this->l('Nombre del cliente'),
                'width' => 'auto',
                'type' => 'text'
                ),

            'customer_email' => array(
                'title' => $this->l('Email del cliente'),
                'width' => 'auto',
                'type' => 'text'
                ),

            'id_product' => array(
                'title' => 'id_product',
                'width' => 'auto',
                'type' => 'int',
                'class' => 'hidden' // ocultamos visualmente la columna
                ),

            'product_name' => array(
                'title' => $this->l('Producto'),
                'width' => 'auto',
                'type' => 'text'
                ),

            'id_product_attribute' => array(
                'title' => 'id_product_attribute',
                'width' => 'auto',
                'type' => 'int',
                'class' => 'hidden' // ocultamos visualmente la columna
                ),

            'id_shop' => array(
                'title' => 'id_shop',
                'width' => 'auto',
                'type' => 'int',
                'class' => 'hidden' // ocultamos visualmente la columna
                ),

            'shop_name' => array(
                'title' => $this->l('Tienda'),
                'width' => 'auto',
                'type' => 'text'
                ),

            'id_lang' => array(
                'title' => 'id_lang',
                'width' => 'auto',
                'type' => 'int',
                'class' => 'hidden' // ocultamos visualmente la columna
                ),

            'language' => array(
                'title' => $this->l('Idioma'),
                'width' => 'auto',
                'type' => 'text'
                )
            );

        $helper = new HelperList();
        $helper->shopLinkType = '';
        $helper->identifier = 'ids';
        $helper->actions = array('delete');
        $helper->show_toolbar = true;
        
        $helper->title = $this->displayName;
        
        $helper->table = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->simple_header = true;
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

        $data = $this->getData();

        if(!$data)
            return $this->l("No hay registros");
        
        return $helper->generateList($data, $this->fields_list);
    }

}
