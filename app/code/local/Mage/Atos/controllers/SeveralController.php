<?php
/*
 * Atos Form Method Front Controller
 *
 * @category   Mage
 * @package    Mage_Atos
 * @name       Mage_Atos_SeveralController
*/
class Mage_Atos_SeveralController extends Mage_Core_Controller_Front_Action
{	
    /**
     * Get singleton with Atos aurore
     *
     * @return object Mage_Atos_Model_Several
     */
    public function getSeveral()
    {
        return Mage::getSingleton('atos/method_several');
    }
	
	public function getConfig()
	{
	    return Mage::getSingleton('atos/config');
	}
		
    public function redirectAction()
    {
	    $session = Mage::getSingleton('checkout/session');
        
		if ($session->getQuote()->getHasError())
		{
		    $this->_redirect('checkout/cart');
		} else {
	        if ($session->getQuoteId()) 
		    {
                $session->setAtosSeveralQuoteId($session->getQuoteId());
		    }
		
			$this->getResponse()->setBody($this->getLayout()->createBlock('atos/several_redirect')->toHtml());
		}
    }
	
	public function cancelAction()
	{   
	    $model = $this->getSeveral();
		
		if ( $response = $model->getApiResponse()->doResponse($_POST['DATA'], array('bin_response' => $model->getBinResponse() ) ) ) 
		{
		    $order = Mage::getModel('sales/order');
            $order->loadByIncrementId($response['order_id']);
			
			if ($order->getId()) 
			{
                $order->cancel();
			    $order->save();
			}
		}

        $session = Mage::getSingleton('checkout/session');
	    $session->setQuoteId($session->getAtosSeveralQuoteId(true));
	
		$this->_redirect('checkout/cart');
	}
	
	public function normalAction() 
	{
	    $model = $this->getSeveral();
		$session = Mage::getSingleton('checkout/session');
		
        if (!$this->getRequest()->isPost('DATA')) 
	    {
            $this->_redirect('');
            return;
        }
		
		$response = $model->getApiResponse()->doResponse($_POST['DATA'], array('bin_response' => $model->getBinResponse() ) );
				
		if ($response['merchant_id'] != $model->getMerchantId()) 
		{
		    Mage::log(sprintf('Response Merchant ID (%s) is not valid with configuration value (%s)' . "\n", $response['merchant_id'], $model->getMerchantId()), null, 'atos');
		   
		    $session->addError($this->__('We are sorry but we have an error with payment module'));
		    $this->_redirect('checkout/cart');
		    return;
		}
		
		switch ($response['response_code'])
		{
		    case '00':
                $session->setQuoteId($session->getAtosSeveralQuoteId(true));
		     	$session->getQuote()->setIsActive(false)->save();
				
				$this->_redirect('checkout/onepage/success', array('_secure'=>true));
			    break;
				
			default:
		        $session->addError($this->__('(Response Code %s) Error with payment module', $response['response_code']));
		        $this->_redirect('checkout/cart');
			    break;
		}
	}


	public function automaticAction() 
	{
	    $model = $this->getSeveral();
	
        if (!$this->getRequest()->isPost('DATA')) 
	    {
            $this->_redirect('');
            return;
        }
	
        if ($this->getConfig()->getCheckByIpAddress()) 
		{
			if (!in_array($model->getApiParameters()->getIpAddress(), $this->getConfig()->getAuthorizedIps())) 
			{
		        Mage::log($model->getApiParameters()->getIpAddress() . ' tries to connect to our server' . "\n", null, 'atos');
		        return;
			}
	    }
		
		$response = $model->getApiResponse()->doResponse($_POST['DATA'], array('bin_response' => $model->getBinResponse() ) );
				
		if ($response['merchant_id'] != $model->getMerchantId()) 
		{
		    Mage::log(sprintf('Response Merchant ID (%s) is not valid with configuration value (%s)' . "\n", $response['merchant_id'], $model->getMerchantId()), null, 'atos');
		    return;
		}
		
		$order = Mage::getModel('sales/order');
        $order->loadByIncrementId($response['order_id']);
			   
		switch ($response['response_code'])
		{
// Success order
		    case '00':
			   if ($order->getId()) 
			   {
                   $order->addStatusToHistory(
                       Mage_Sales_Model_Order::STATE_PROCESSING,
                       Mage::getSingleton('atos/api_response')->describeResponse($response)
				   );

// Create invoice
                   $this->saveInvoice($order);
				   
                   $order->sendNewOrderEmail();
			   }
			   break;
			   
			default:
// Cancel order
			    if ($order->getId()) 
				{
                    $order->addStatusToHistory(
                       Mage_Sales_Model_Order::STATE_CANCELED,
                       Mage::getSingleton('atos/api_response')->describeResponse($response)
				    );
				
			        $order->cancel();
				}
			    break;
		}
		
		$order->save();
	}
	
    /**
     *  Save invoice for order
     *
     *  @param    Mage_Sales_Model_Order $order
     *  @return	  boolean Can save invoice or not
     */
    protected function saveInvoice(Mage_Sales_Model_Order $order)
    {
        if ($order->canInvoice()) 
	    {
            $convertor = Mage::getModel('sales/convert_order');
            
			$invoice = $convertor->toInvoice($order);
                       
			foreach ($order->getAllItems() as $orderItem) 
			{
                if (!$orderItem->getQtyToInvoice()) 
			    {
                    continue;
                }
						   
                $item = $convertor->itemToInvoiceItem($orderItem);
                $item->setQty($orderItem->getQtyToInvoice());
                $invoice->addItem($item);
            }
					   
            $invoice->collectTotals();
            $invoice->register();
                      
		    Mage::getModel('core/resource_transaction')
              ->addObject($invoice)
              ->addObject($invoice->getOrder())
              ->save();
						 
            $order->addStatusToHistory(
                Mage_Sales_Model_Order::STATE_PROCESSING,
                Mage::helper('atos')->__('Invoice %s was created', $invoice->getIncrementId())
            );
        }

        return false;
    }
}