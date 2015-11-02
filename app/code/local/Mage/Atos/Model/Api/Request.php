<?php
/**
 * Api Request
 *
 * @category   Mage
 * @package    Mage_Atos
 * @copyright  Ilan Parmentier (Ryu007) Artbambou
 * @name       Mage_Atos_Model_Api_Request
 */
class Mage_Atos_Model_Api_Request extends Mage_Atos_Model_Api_Parameters
{
    public function doRequest($parameters = array())
    {
        $command = $parameters['bin_request'];
		$command.= ' pathfile=' . $this->getApiFiles()->getPathfileName($parameters['merchant_id']);
        $command.= ' language=' . $this->getLanguageCode();
        $command.= ' merchant_id=' . $parameters['merchant_id'];
		$command.= ' merchant_country=' . $this->getMerchantCountry();
        $command.= ' amount=' . $this->getGrandTotal();
        $command.= ' currency_code=' . $this->getCurrencyCode();
  	    $command.= ' payment_means=' . $parameters['payment_means'];
        $command.= ' normal_return_url=' . $parameters['url']['normal'];
        $command.= ' cancel_return_url=' . $parameters['url']['cancel'];
        $command.= ' automatic_response_url=' . $parameters['url']['automatic'];
        $command.= ' customer_id=' . $this->getQuote()->getBillingAddress()->getId();
        
        if (!$customerEmail = $this->getQuote()->getBillingAddress()->getEmail()) {
            $customerEmail = $this->getQuote()->getData('customer_email');
        }
		$command.= ' customer_email=' . $customerEmail;
        $command.= ' customer_ip_address=' . $this->getIpAddress();
  	    $command.= ' order_id=' . $this->getCheckout()->getLastRealOrderId();
  	    
        if (array_key_exists('templatefile', $parameters) && $parameters['templatefile'] != '') {
            $command.= ' templatefile=' . $parameters['templatefile'];
        }

		if (isset($parameters['command'])) {		
		    $command.= $parameters['command'];
		} else {
		    switch ($this->getCaptureMode()) {
		        case 'author':
		        case 'validation':
		            if ( ($this->getCaptureDays() >= 0) && ($this->getCaptureDays() <= 99) ) {
		                $command.= ' capture_mode=' . $this->getCaptureMode();
			            $command.= ' capture_days=' . $this->getCaptureDays();
			        }
		            break;
		    }
		}

        $sips_result = shell_exec("$command &2>1");

		if (!empty($sips_result)) {
            $sips_values = explode('!', $sips_result);
            $sips = array(
		        'code'    => $sips_values[1],
			    'error'   => $sips_values[2],
			    'message' => $sips_values[3],
			    'command' => $command,
			    'output'  => $sips_result
		    );

            if (!isset($sips['code'])) 
			{
			    Mage::throwException($sips_result);
            }
		
		    if ($sips['code'] == '-1') 
		    {
		        Mage::throwException($sips['error']);
		    }

		    return $sips;
		} else {
			if (file_exists($parameters['bin_request']) === false) 
			{
		        Mage::throwException( Mage::helper('atos')->__('Impossible to launch binary file - Path to binary file seem to be not correct (%s)<br />Command line : %s', $parameters['bin_request'], $command) );
			}
			
			if (is_executable($parameters['bin_request']) === false)
			{
			    $perms = substr(sprintf('%o', fileperms($parameters['bin_request'])), -4);
			    Mage::throwException( Mage::helper('atos')->__('Impossible to execute binary file - Set correct chmod (current chmod %s)<br />Command line : %s', $perms, $command) );
			}
			
            return false;
		}
    }
}
