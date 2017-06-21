<?php

namespace CustomerManagementFrameworkBundle;

use CustomerManagementFrameworkBundle\ActionTrigger\Event\SingleCustomerEventInterface;
use CustomerManagementFrameworkBundle\Controller\Plugin\UrlActivityTracker;
use CustomerManagementFrameworkBundle\Model\AbstractObjectActivity;
use CustomerManagementFrameworkBundle\Model\ActivityInterface;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use CustomerManagementFrameworkBundle\Model\CustomerSegmentInterface;
use Pimcore\API\Plugin as PluginLib;
use Pimcore\Model\Object\ActivityDefinition;

class Plugin extends PluginLib\AbstractPlugin implements PluginLib\PluginInterface
{

    public function init()
    {

        parent::init();




        \Pimcore::getEventManager()->attach('system.maintenance', function(\Zend_EventManager_Event $e) {
            Factory::getInstance()->getSegmentManager()->executeSegmentBuilderMaintenance();
        });


        \Pimcore::getEventManager()->attach('plugin.ObjectMerger.postMerge', function(\Zend_EventManager_Event $e){

            $sourceCustomer = Factory::getInstance()->getCustomerProvider()->getById($e->getParam('sourceId'));
            $targetCustomer = Factory::getInstance()->getCustomerProvider()->getById($e->getParam('targetId'));

            if($sourceCustomer && $targetCustomer) {
                Factory::getInstance()->getCustomerMerger()->mergeCustomers($sourceCustomer, $targetCustomer, false);
            }

        });


        $front = \Zend_Controller_Front::getInstance();

        $front->registerPlugin(new UrlActivityTracker(), 1666);

    }


    public static function install()
    {
        $installer = new Installer();

        return $installer->install();
    }

    public static function uninstall()
    {
        // implement your own logic here
        return true;
    }

    public static function isInstalled()
    {
        // implement your own logic here
        return file_exists(PIMCORE_WEBSITE_PATH . '/config/plugins/CustomerManagementFramework/config.php');
    }

    private static function getConfigFile() {
        return \Pimcore\Config::locateConfigFile("plugins/CustomerManagementFramework/config.php");
    }

    /**
     * @param string $language
     * @return null|string
     */
    public static function getTranslationFile($language)
    {
        return sprintf('/CustomerManagementFramework/texts/%s.csv', $language);
    }

    protected static $config = null;
    public static function getConfig() {
        if(is_null(self::$config)) {
            $file = self::getConfigFile();

            if(file_exists($file)) {
                self::$config = new \Zend_Config(include($file));;

            } else {
                throw new \Exception($file . " doesn't exist");
            }
        }


        return self::$config;
    }
}
