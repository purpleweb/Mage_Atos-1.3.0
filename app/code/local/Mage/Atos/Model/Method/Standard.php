<?php
/**
 * Magento Atos
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Mage
 * @package    Mage_Atos
 * @copyright  Quadra Informatique - Nicolas FISCHER (nico5)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Atos_Model_Method_Standard extends Mage_Atos_Model_Abstract
{
	private $_url = null;
	private $_message = null;
	private $_error = false;

    protected $_code  = 'atos_standard';
	
    protected $_formBlockType = 'atos/standard_form';
	protected $_infoBlockType = 'atos/standard_info';

    /**
     * Availability options
     */
    protected $_isGateway               = false;
    protected $_canAuthorize            = false;
    protected $_canCapture              = false;
    protected $_canCapturePartial       = false;
    protected $_canRefund               = false;
    protected $_canVoid                 = false;
    protected $_canUseInternal          = false;
    protected $_canUseCheckout          = true;
    protected $_canUseForMultishipping  = false;
	
    public function getCode()
	{
	    return $this->_code;
	}
	
	public function isAvailable($quote = null)
	{
	   	if (Mage::getSingleton('checkout/session')->getQuote()->getIsMultiShipping()) {
            return false;
		} else {
		    return parent::isAvailable($quote);
		}
	}
		
    /**
     *  Form block description
     *
     *  @return	 object
     */
    public function createFormBlock($name)
    {
        $block = $this->getLayout()->createBlock($this->_formBlockType, $name);
        $block->setMethod($this->_code);
        $block->setPayment($this->getPayment());
        return $block;
    }
	
    /**
     *  @return	  string Return cancel URL
     */
    public function getCancelReturnUrl()
    {
        return Mage::getUrl('atos/standard/cancel');
    }
	
    /**
     *  Return URL for customer response
     *
     *  @return	  string Return customer URL
     */
    public function getNormalReturnUrl()
    {
        return Mage::getUrl('atos/standard/normal');
    }
	
    /**
     *  Return URL for automatic response
     *
     *  @return	  string Return automatic URL
     */
    public function getAutomaticReturnUrl()
    {
        return Mage::getUrl('atos/standard/automatic');
    }
	
    /**
     *  Return Order Place Redirect URL
     *
     *  @return	  string Order Redirect URL
     */
    public function getOrderPlaceRedirectUrl()
    {
        return Mage::getUrl('atos/standard/redirect');
	}
	
    public function callRequest()
    {
		$payment = Mage::getSingleton('checkout/session')->getQuote()->getPayment();

        if (!$card = $payment->getData('cc_type')) {
            $order = Mage::getModel('sales/order')->loadByIncrementId(Mage::getSingleton('checkout/session')->getLastRealOrderId());
            $payment = $order->getPayment();
            $card = $payment->getData('cc_type');
		}
		
		$data = str_replace(',', '\;', $this->getDataFieldKeys());

      	$command = ' data=' . $data;
      	    
        $parameters = array(
            'command'       => $command,
            'bin_request'   => $this->getBinRequest(),
        	'templatefile'   => $this->getTemplatefile(),
            'merchant_id'   => $this->getMerchantId(),
            //'payment_means' => $this->getPaymentMeans(),
            'payment_means' => $card.',1',
            'url' => array(
                'cancel' => $this->getCancelReturnUrl(),
                'normal' => $this->getNormalReturnUrl(),
                'automatic' => $this->getAutomaticReturnUrl()
            )
		);

		$sips = $this->getApiRequest()->doRequest($parameters);
		
        if ($sips['error']) {
            $this->_error = true;
        } else {
	        $regs = array();
			
	        if (eregi('<form [^>]*action="([^"]*)"[^>]*>(.*)</form>', $sips['message'], $regs)) {
/*echo "EURO<br>";
Zend_Debug::dump($sips);
Zend_Debug::dump($regs);
exit;*/
	            $this->_url = $regs[1];
                $this->_message = $regs[2];
	        } else {
                $this->_error = true;
                $this->_message = Mage::helper('atos')->__('Call Bin Request Error - Check path to the file or command line for debug');
	        }
        }
    }
	
	public function getSystemUrl() 
	{	
	    return $this->_url;
	}
	
	public function getSystemMessage() 
	{
	    return $this->_message;
	}
	
    public function getSystemError() 
	{
	    return $this->_error;
	}
	
    /**
     * Return merchant ID
     *
     * @return string
     */
    public function getMerchantId()
    {
	    return $this->getConfigData('merchant_id');
    }
	
    public function getPathfile()
    {
	    return $this->getConfigData('pathfile');
    }
	
    /**
     *  Return Atos bin file for request
     *
     *  @return	  string
     */
    public function getBinRequest()
    {
	    return $this->getConfigData('bin_request');
    }
	
    /**
     *  Return Atos bin file for response
     *
     *  @return	  string
     */
    public function getBinResponse()
    {
	    return $this->getConfigData('bin_response');
    }


    public function getCheckByIpAddress()
	{
	    return $this->getConfigData('check_ip_address');
	}
	
	public function getCctypes()
	{
	    return $this->getConfigData('cctypes');
	}
	
    /**
     *  Return credit card type accepted
     *
     *  @return	  string
     */
    protected function getPaymentMeans()
	{
	    $cc = $this->getCctypes();
	
	    if ( !empty($cc) )
		{
	        if ( strstr($cc, ',') ) 
		    {
		        $return = '';
		        foreach(explode(',', $cc) as $card) 
			    {
		            $return .= $card.',1,';
		        }
		  
		        return substr($return, 0, -1);
		    } else {
		        return $cc.',1';
		    }
		} else {
		    return 'CB,1,VISA,1,MASTERCARD,1';
		}
	}
	
	public function getCaptureMode()
	{
	    return $this->getConfigData('capture_mode');
	}
	
	public function getDataFieldKeys()
	{
	    return $this->getConfigData('data_field');
	}

    /**
     *  Return new order status
     *
     *  @return	  string New order status
     */
    public function getNewOrderStatus()
    {
        return $this->getConfigData('order_status');
    }
    
	/**
     *  Return template file name (used only in prod env to display payment pages with a template chosen by user)
     *
     *  @return      string templatefile
     */    
    public function getTemplatefile(){
        return $this->getConfigData('templatefile');
    }
}
