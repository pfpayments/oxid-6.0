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
namespace Pfc\PostFinanceCheckout\Core\Provider;

/**
 * Abstract implementation of a Provider.
 */
abstract class AbstractProvider
{
    private static $instances;
    private $cacheKey;
    private $data;

    /**
     *
     * @return static
     */
    public static function instance()
    {
        $class = get_called_class();
        if (!isset(self::$instances[$class])) {
            self::$instances[$class] = new $class();
        }
        return self::$instances[$class];
    }

    /**
     * Constructor.
     *
     * @param string $cacheKey
     */
    protected function __construct($cacheKey)
    {
        $this->cacheKey = $cacheKey;
    }

    /**
     * Fetch the data from the remote server.
     *
     * @return array
     */
    abstract protected function fetchData();

    /**
     * Returns the id of the given entry.
     *
     * @param mixed $entry
     * @return string
     */
    abstract protected function getId($entry);

    /**
     * Returns a single entry by id.
     *
     * @param string $id
     * @return mixed
     */
    public function find($id)
    {
        if ($this->data == null) {
            $this->loadData();
        }

        if (isset($this->data[$id])) {
            return $this->data[$id];
        } else {
            return false;
        }
    }

    /**
     * Returns all entries.
     *
     * @return array
     */
    public function getAll()
    {
        if ($this->data == null) {
            $this->loadData();
        }

        return $this->data;
    }

    private function loadData()
    {
    	$cachedData = \OxidEsales\Eshop\Core\Registry::getUtils()->fromFileCache($this->cache_key);
        if ($cachedData) {
            $this->data = unserialize($cachedData);
        } else {
            $this->data = array();
            foreach ($this->fetchData() as $entry) {
                $this->data[$this->getId($entry)] = $entry;
            }
            \OxidEsales\Eshop\Core\Registry::getUtils()->toFileCache($this->cacheKey, serialize($this->data));
            \OxidEsales\Eshop\Core\Registry::getUtils()->commitFileCache();
        }
    }
}