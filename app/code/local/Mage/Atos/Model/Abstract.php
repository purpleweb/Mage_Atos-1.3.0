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
 *
 * Atos Abstract Payment Module
 *
**/
abstract class Mage_Atos_Model_Abstract extends Mage_Payment_Model_Method_Abstract
{	

    public function getStandard()
	{
	    return Mage::getSingleton('atos/method_standard');
	}
	
    public function getAurore()
	{
	    return Mage::getSingleton('atos/method_aurore');
	}
	
    /**
     * Get Atos API Request Model
     *
     * @return Mage_Atos_Model_Api_Request
     */
    public function getApiRequest()
    {
        return Mage::getSingleton('atos/api_request');
    }
	
    /**
     * Get Atos Api Response Model
     *
     * @return Mage_Atos_Model_Api_Response
     */
    public function getApiResponse()
    {
        return Mage::getSingleton('atos/api_response');
    }
	
    /**
     * Get Atos Api Parameters Model
     *
     * @return Mage_Atos_Model_Api_Parameters
     */
	public function getApiParameters()
	{
        return Mage::getSingleton('atos/api_parameters');
	}

    /**
     * Get Atos Api Files Model
     *
     * @return Mage_Atos_Model_Api_Files
     */
	public function getApiFiles()
	{
        return Mage::getSingleton('atos/api_files');
	}
	
    /**
     * Get Config model
     *
     * @return object Mage_Atos_Model_Config
     */
    public function getConfig()
    {
        return Mage::getSingleton('atos/config');
    }
	
     /**
     * Get atos session namespace
     *
     * @return Mage_Atos_Model_Session
     */
    public function getSession()
    {
        return Mage::getSingleton('atos/session');
    }

    /**
     * Get checkout session namespace
     *
     * @return object Mage_Checkout_Model_Session
     */
    public function getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Get current quote
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        return $this->getCheckout()->getQuote();
    }
}
