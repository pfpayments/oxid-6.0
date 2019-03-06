<?php
/**
 * PostFinanceCheckout OXID
 *
 * This OXID module enables to process payments with PostFinanceCheckout (https://www.postfinance.ch/).
 *
 * @package Whitelabelshortcut\PostFinanceCheckout
 * @author customweb GmbH (http://www.customweb.com/)
 * @license http://www.apache.org/licenses/LICENSE-2.0  Apache Software License (ASL 2.0)
 */


namespace Pfc\PostFinanceCheckout\Core;

use \Monolog\Logger;
use \Monolog\Handler\StreamHandler;
use \PostFinanceCheckout\Sdk\ApiClient;
use PostFinanceCheckout\Sdk\Model\TransactionState;
use Pfc\PostFinanceCheckout\Core\Provider\Language as LanguageProvider;
use Pfc\PostFinanceCheckout\Application\Model\Transaction;

/**
 * Class PostFinanceCheckoutModule
 * Handles module setup, provides additional tools and module related helpers.
 *
 * @codeCoverageIgnore
 */
class PostFinanceCheckoutModule extends \OxidEsales\Eshop\Core\Module\Module
{
    const FALLBACK_LANGUAGE = 'en-US';
    const PAYMENT_PREFIX = 'oxidpfc';
    /**
     * @var Logger
     */
    private $logger = null;
    /**
     * @var ApiClient
     */
    private $apiClient = null;
    /**
     * @var Settings
     */
    private $settings = null;

    /**
     * @var \OxidEsales\Eshop\Core\Request
     */
    private $request;
    
    /**
     * 
     * @var PostFinanceCheckoutModule
     */
    private static $instance = null;

    /**
     * Class constructor.
     * Sets current module main data and loads the rest module info.
     * 
     * (Left public because while internally used as singleton, may be required externally by oxid to be public)
     *
     * @throws \InvalidArgumentException If the log file is wrong (not a string / resource). Should never occur without modifications to this file.
     */
    public function __construct()
    {
        $sModuleId = 'pfcPostFinanceCheckout';

        $this->setModuleData(array(
            'id' => $sModuleId,
            'title' => 'PFC PostFinanceCheckout',
            'description' => 'PFC PostFinanceCheckout Module'
        ));

        $this->load($sModuleId);

        $this->settings = new Settings();
        $this->initLogger();
        
        if(version_compare("6.0.0", $this->getConfig()->getVersion()) <= 0) {
        	$this->request = \OxidEsales\Eshop\Core\Registry::get(\OxidEsales\Eshop\Core\Request::class);
		}
        \OxidEsales\Eshop\Core\Registry::set(get_class(), $this);
    }
    
    private function getOxVersion(){
    	$this->getConfig()->getVersion();
    }

    public static function getMappedFolder($state)
    {
        switch ($state) {
            case TransactionState::PROCESSING:
            case TransactionState::CONFIRMED:
            case TransactionState::AUTHORIZED:
            case TransactionState::PENDING:
            case TransactionState::COMPLETED:
            case TransactionState::FULFILL:
                return 'ORDERFOLDER_NEW';
            case TransactionState::VOIDED:
            case TransactionState::DECLINE:
            case TransactionState::FAILED:
            default:
                return 'ORDERFOLDER_PROBLEMS';
        }
    }

    public static function settings()
    {
        return self::instance()->getSettings();
    }

    public static function renderJson($json)
    {
        ini_set('display_errors', 0);
        header("Cache-Control: no-cache, must-revalidate");
        header("Pragma: no-cache");
        header("Content-type: text/json");

        die(json_encode($json));
    }
    
    public static function getControllerUrl($controller, $action = null, $oxid = null, $utmNoOverride = null)
    {
    	$baseUrl = \OxidEsales\Eshop\Core\Registry::getConfig()->getShopUrl();
    	$params = array(
    		'cl' => $controller
    	);
    	if ($action) {
    		$params['fnc'] = $action;
    	}
    	if ($oxid) {
    		$params['oxid'] = $oxid;
    	}
    	if($utmNoOverride !== null) {
    		$params['utm_nooverride'] = $utmNoOverride;
    	}
    	return self::getUtilsUrl()->cleanUrlParams(self::getUtilsUrl()->appendUrl($baseUrl, $params), '&');
    }
    
    /**
     * 
     * @return \OxidEsales\Eshop\Core\UtilsUrl
     */
    public static function getUtilsUrl() {
    	if(version_compare("6.0.0", \OxidEsales\Eshop\Core\Registry::getConfig()->getVersion()) <= 0) {
    		return \OxidEsales\Eshop\Core\Registry::getUtilsUrl();
    	}
    	return \OxidEsales\Eshop\Core\Registry::get(\OxidEsales\Eshop\Core\UtilsUrl::class);
    }
    
    /**
     *
     * @return \OxidEsales\Eshop\Core\Utils
     */
    public static function getUtils() {
    	return \OxidEsales\Eshop\Core\Registry::getUtils();
    }
    
    /**
     *
     * @return \OxidEsales\Eshop\Core\UtilsObject
     */
    public static function getUtilsObject() {
    	if(version_compare("6.0.0", \OxidEsales\Eshop\Core\Registry::getConfig()->getVersion()) <= 0) {
    		return \OxidEsales\Eshop\Core\Registry::getUtilsObject();
    	}
    	return \OxidEsales\Eshop\Core\UtilsObject::getInstance();
    }

    /**
     * 
     * @return \OxidEsales\Eshop\Core\UtilsView
     */
    public static function getUtilsView() {	
    	if(version_compare("6.0.0", \OxidEsales\Eshop\Core\Registry::getConfig()->getVersion()) <= 0) {
    		return \OxidEsales\Eshop\Core\Registry::getUtilsView();
    	}
    	return \OxidEsales\Eshop\Core\Registry::get(\OxidEsales\Eshop\Core\UtilsView::class);
    }
    
    /**
     * Adds a message to the next rendered page.
     * (Currently creates an error message)
     * 
     * @param string $message
     */
    public static function addMessage($message) {
    	self::getUtilsView()->addErrorToDisplay($message);
    }
    
    public static function instance()
    {
    	if(self::$instance === null) {
    		self::$instance = new self();
    	}
    	return self::$instance;
    }

    public static function createOxidPaymentId($paymentId)
    {
        return  self::PAYMENT_PREFIX . $paymentId;
    }

    public static function isPostFinanceCheckoutPayment($paymentId)
    {
        return substr($paymentId, 0, strlen(self::PAYMENT_PREFIX)) === self::PAYMENT_PREFIX;
    }

    public static function extractPostFinanceCheckoutId($paymentId)
    {
        return substr($paymentId, 7);
    }

    public function createWebhookUrl()
    {
        return self::getControllerUrl('pfc_postFinanceCheckout_Webhook', 'notify');
    }

    public function getRequestParameter($parameter, $default = null)
    {
    	if($this->request){
        	return $this->request->getRequestParameter($parameter, $default);
    	}else{
    		$param = $this->getConfig()->getRequestParameter($parameter);
    		if($param === null){
    			$param = $default;
    		}
    		return $param;
    	}
    }

    /**
     * Module activation script.
     * @return bool
     * @throws \Exception
     */
    public static function onActivate()
    {
        $bool = 0;
        $bool += self::_dbEvent('install.sql', 'Error activating module: ');
        $bool += self::migrate();
        return $bool > 0;
    }

    /**
     * Runs migration scripts.
     * Migration scripts should be numerically prefixed, and end with _migration.sql.
     *
     * @return bool|int
     * @throws \Exception
     */
    protected static function migrate() {
        $bool = 0;
        $files = glob(__DIR__ . '/../docs/*_migration.sql');

        sort($files);
        $settings = new Settings();
        $currentVersion = (int)$settings->getMigration();
        $unprocessed = array_splice($files, $currentVersion);
        foreach ($unprocessed as $file) {
            $bool += self::_dbEvent(basename($file), "Error migrating $file.");
            if ($bool === false) {
                break;
            }
            $currentVersion++;
        }
        $settings->setMigration($currentVersion);
        return $bool;
    }

    /**
     * Module deactivation script.
     * @return bool
     * @throws \Exception
     */
    public static function onDeactivate()
    {
        return self::_dbEvent('uninstall.sql', 'Error deactivating module: ');
    }

    public static function log($level, $message, $context = array())
    {
        self::instance()->logger->addRecord($level, $message, $context);
    }

    /**
     * Checks if the given state is authorized or above.
     *
     * @param $state
     * @return bool
     */
    public static function isAuthorizedState($state)
    {
        return in_array($state,
            [
                'POSTFINANCECHECKOUT_' . TransactionState::AUTHORIZED,
                'POSTFINANCECHECKOUT_' . TransactionState::FULFILL,
                'POSTFINANCECHECKOUT_' . TransactionState::COMPLETED
            ]);
    }

    /**
     * @param bool $refresh
     * @return ApiClient
     */
    public function getApiClient($refresh = false)
    {
        if ($this->apiClient === null || $refresh) {
            $this->apiClient = new ApiClient($this->getSettings()->getUserId(), $this->getSettings()->getAppKey());
            $this->apiClient->setBasePath($this->getSettings()->getBaseUrl() . '/api');
            if ($this->getSettings()->isLogCommunications()) {
                self::log(Logger::DEBUG, 'Enabling logging on ApiClient.');
                $this->apiClient->enableDebugging();
                $this->apiClient->setDebugFile($this->getSettings()->getCommunicationsLog());
            }
        }
        return $this->apiClient;
    }

    /**
     * Clean temp folder content.
     *
     * @param string $sClearFolderPath Sub-folder path to delete from. Should be a full, valid path inside temp folder.
     *
     * @return boolean
     */
    public static function clearTmp($sClearFolderPath = '')
    {
        $sFolderPath = self::_getFolderToClear($sClearFolderPath);
        $hDirHandler = opendir($sFolderPath);

        if (!empty($hDirHandler)) {
            while (false !== ($sFileName = readdir($hDirHandler))) {
                $sFilePath = $sFolderPath . DIRECTORY_SEPARATOR . $sFileName;
                self::_clear($sFileName, $sFilePath);
            }

            closedir($hDirHandler);
        }

        return true;
    }

    /**
     * Get translated string by the translation code.
     *
     * @param string $sCode
     * @param boolean $blUseModulePrefix If True - adds the module translations prefix, if False - not.
     *
     * @param array $args
     * @return string
     */
    public function translate($sCode, $blUseModulePrefix = true, $args = array())
    {
        if ($blUseModulePrefix) {
            $sCode = 'pfc_postFinanceCheckout_' . $sCode;
        }
        
        $lang = \OxidEsales\Eshop\Core\Registry::getLang()->getTplLanguage();

        $translated = \OxidEsales\Eshop\Core\Registry::getLang()->translateString($sCode, $lang, $this->isAdmin());

        if (is_array($args) && !empty($args)) {
            $translated = str_replace(array_keys($args), array_values($args), $translated);
        }
        return $translated;
    }

    /**
     * Retrieves the currently active language from the given map, or default / fallback, or the first element.
     * @param array $map
     * @return string
     */
    public function PostFinanceCheckoutTranslate(array $map)
    {
    	$lang = oxNew(\OxidEsales\Eshop\Core\Language::class);
        /* @var $lang \OxidEsales\Eshop\Core\Language */
    	$abbr = $lang->getLanguageAbbr(\OxidEsales\Eshop\Core\Registry::getLang()->getBaseLanguage());
        $primary = LanguageProvider::instance()->findPrimary($abbr);
        if (isset($map[$primary->getIetfCode()])) {
            return $map[$primary->getIetfCode()];
        }
        $secondary = LanguageProvider::instance()->findByIsoCode($abbr);
        if (isset($map[$secondary->getIetfCode()])) {
            return $map[$secondary->getIetfCode()];
        }
        if (isset($map[self::FALLBACK_LANGUAGE])) {
            return $map[self::FALLBACK_LANGUAGE];
        }
        return reset($map);
    }

    /**
     * Get CMS snippet content by identified ID.
     *
     * @param string $sIdentifier
     * @param bool $blNoHtml
     *
     * @return string
     */
    public function getCmsContent($sIdentifier, $blNoHtml = true)
    {
        $sValue = '';

        /** @var \OxidEsales\Eshop\Application\Model\Content|\OxidEsales\Eshop\Core\Model\MultiLanguageModel $oContent */
        $oContent = oxNew(\OxidEsales\Eshop\Application\Model\Content::class);
        $oContent->loadByIdent(trim((string)$sIdentifier));

        if (!empty($oContent->oxcontents__oxcontent)) {
            $sValue = (string)$oContent->oxcontents__oxcontent->getRawValue();
            $sValue = (empty($blNoHtml) ? $sValue : nl2br(strip_tags($sValue)));
        }

        return $sValue;
    }

    /**
     * Get module path.
     *
     * @return string Full path to the module directory.
     */
    public function getPath()
    {
    	return \OxidEsales\Eshop\Core\Registry::getConfig()->getModulesDir() . 'pfc/PostFinanceCheckout/';
    }

    public function getSettings()
    {
        return $this->settings;
    }

    private function initLogger()
    {
        try {
        	if(class_exists(Logger::class)) {
	            $this->logger = new Logger('PostFinanceCheckout',
	                array(
	                    new StreamHandler($this->getSettings()->getLogFile(), $this->getSettings()->getMappedLogLevel())
	                ));
        	}
        } catch (\InvalidArgumentException $e) {
            throw $e;
        } catch (\Exception $e) {
        }
    }

    /**
     * Install/uninstall event.
     * Executes SQL queries form a file.
     *
     * @param string $sSqlFile SQL file located in module docs folder (usually install.sql or uninstall.sql).
     * @param string $sFailureError An error message to show on failure.
     *
     * @return bool
     * @throws \Exception
     */
    protected static function _dbEvent($sSqlFile, $sFailureError = 'Operation failed: ')
    {
    	self::log(Logger::ERROR, __METHOD__);
        /** @var \OxidEsales\Eshop\Core\DbMetaDataHandler $oDbHandler */
        $oDbHandler = oxNew(\OxidEsales\Eshop\Core\DbMetaDataHandler::class);

        try {
            $sSql = file_get_contents(dirname(__FILE__) . '/../docs/' . (string)$sSqlFile);
            $aSql = (array)explode(';', $sSql);
            self::log(Logger::ERROR, print_r($aSql, true));
            $oDbHandler->executeSql($aSql);
        } catch (\Exception $ex) {
            self::log(Logger::ERROR, $ex->getMessage());
            error_log($sFailureError . $ex->getMessage());
            return false;
        }

        self::clearTmp();

        return true;
    }

    /**
     * Check if provided path is inside eShop `tpm/` folder or use the `tmp/` folder path.
     *
     * @param string $sClearFolderPath
     *
     * @return string
     */
    protected static function _getFolderToClear($sClearFolderPath = '')
    {
    	$sTempFolderPath = (string)\OxidEsales\Eshop\Core\Registry::getConfig()->getConfigParam('sCompileDir');

        if (!empty($sClearFolderPath) and (strpos($sClearFolderPath, $sTempFolderPath) !== false)) {
            $sFolderPath = $sClearFolderPath;
        } else {
            $sFolderPath = $sTempFolderPath;
        }

        return $sFolderPath;
    }

    /**
     * Check if resource could be deleted, then delete it's a file or
     * call recursive folder deletion if it's a directory.
     *
     * @param string $sFileName
     * @param string $sFilePath
     */
    protected static function _clear($sFileName, $sFilePath)
    {
        if (!in_array($sFileName, array(
            '.',
            '..',
            '.gitkeep',
            '.htaccess'
        ))) {
            if (is_file($sFilePath)) {
                @unlink($sFilePath);
            } else {
                self::clearTmp($sFilePath);
            }
        }
    }

    /**
     * Attempts to rollback the transaction, and logs the error if not successful.
     */
    public static function rollback()
    {
        try {
            \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->rollbackTransaction();
        } catch (\Exception $e) {
            PostFinanceCheckoutModule::log(Logger::ERROR, "UNABLE TO ROLLBACK: {$e->getMessage()} - {$e->getTraceAsString()}.");
        }
    }
}