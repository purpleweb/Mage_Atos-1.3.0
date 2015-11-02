<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category   Mage
 * @package    Mage_Atos
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
**/
 
class Mage_Atos_Model_Api_Parameters extends Mage_Atos_Model_Abstract
{
    protected $_order;
    protected $_allowedCountryCode = array('be', 'fr', 'de', 'it', 'es', 'en');

    /**
     *  Return current order object
     *
     *  @return	  object
     */
    public function getOrder()
    {
        if (empty($this->_order)) {
            $order = Mage::getModel('sales/order');
            $order->loadByIncrementId($this->getCheckout()->getLastRealOrderId());
            $this->_order = $order;
        }
        return $this->_order;
    }

    /**
     *  Return Language Code
     *
     *  @return	  string
     */
    protected function getLanguageCode() {
        $language = substr(Mage::getStoreConfig('general/locale/code'), 0, 2);

        $Alanguages = $this->getConfig()->getLanguages();

        if (count($Alanguages) === 1) 
		{
            return strtolower($Alanguages[0]);
        }
		
        if (array_key_exists($language, $Alanguages)) 
		{
		    $Acode = array_keys($Alanguages);
			$key = array_search($language, $Acode);

            return strtolower($Acode[$key]);
        }
		
        return 'fr';
    }

    /**
     *  Return Currency Code
     *
     *  @return	  string
     */
    public function getCurrencyCode() 
	{
	    $currencies = $this->getConfig()->getCurrencies();
        $currency_code = $this->getQuote()->getQuoteCurrencyCode();
		
        if (array_key_exists($currency_code, $currencies)) 
		{
            return $currencies[$currency_code];
        } else {
            return false;
        }
    }
	
    /**
     *  Return total orders
     *
     *  @return  numeric
     */	
	public function getGrandTotal()
	{
	    //return number_format($this->getOrder()->getBaseGrandTotal(), 2, '', '');
		return number_format($this->getOrder()->getTotalDue(), 2, '', '');
	}

    /**
     *  Return merchant country
     *
     *  @return  string
     */	
    public function getMerchantCountry()
	{
	    $Acountry = Mage::getStoreConfig('general/country');
		$current_country_code = strtolower($Acountry['default']);
		
		if (in_array($current_country_code, $this->_allowedCountryCode))
		{
	        return $current_country_code;
		} else {
		    return 'en';
		}
	}
	
    /**
     *  Return IP Address
     *
     *  @return	  string
     */
	protected function getIpAddress() {
        if (isset($_SERVER)) 
	    {
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) 
		    {
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            } else {
                $ip = $_SERVER['REMOTE_ADDR'];
            }
        } else {
            if (getenv('HTTP_X_FORWARDED_FOR')) 
		    {
                $ip = getenv('HTTP_X_FORWARDED_FOR');
            } elseif (getenv('HTTP_CLIENT_IP')) {
                $ip = getenv('HTTP_CLIENT_IP');
            } else {
                $ip = getenv('REMOTE_ADDR');
            }
        }

        return $ip;
	}
}