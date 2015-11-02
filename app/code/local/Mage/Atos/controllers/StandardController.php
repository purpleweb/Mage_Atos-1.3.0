<?php
/*
 * Atos Form Method Front Controller
 *
 * @category   Mage
 * @package    Mage_Atos
 * @name       Mage_Atos_StandardController
*/
class Mage_Atos_StandardController extends Mage_Core_Controller_Front_Action
{	
    /**
     * Get singleton with Atos aurore
     *
     * @return object Mage_Atos_Model_Standard
     */
    public function getStandard()
    {
        return Mage::getSingleton('atos/method_standard');
    }
	
	public function getConfig()
	{
	    return Mage::getSingleton('atos/config');
	}
	
    public function redirectAction()
    {
	    $session = Mage::getSingleton('checkout/session');

		if ($session->getQuote()->getHasError()) {
		    $this->_redirect('checkout/cart');
		} else {
	        if ($session->getQuoteId()) {
                $session->setAtosStandardQuoteId($session->getQuoteId());
		    }
		
			$this->getResponse()->setBody($this->getLayout()->createBlock('atos/standard_redirect')->toHtml());
		}
    }
	
	public function cancelAction()
	{   
	    $model = $this->getStandard();
		
		if ($response = $model->getApiResponse()->doResponse($this->getData(), array('bin_response' => $model->getBinResponse()))) {
		    $order = Mage::getModel('sales/order');
            $order->loadByIncrementId($response['order_id']);
			
            if (!$status = $model->getConfigData('order_status_payment_canceled')) {
                $status = $order->getStatus();
            }
            
			if ($order->getId()) {
    			$order->addStatusToHistory(
    			    $status,
    				$this->__('Order was canceled by customer')
    			);
    
    			if ($model->getConfigData('order_status_payment_canceled') == Mage_Sales_Model_Order::STATE_HOLDED && $order->canHold()) {
    				$order->hold();
    			}

			    $order->save();
			}
		}

        $session = Mage::getSingleton('checkout/session');
	    $session->setQuoteId($session->getAtosStandardQuoteId(true));
	
		$this->_redirect('checkout/cart');
	}
	
	public function normalAction() 
	{
	    $model = $this->getStandard();
		  $session = Mage::getSingleton('checkout/session');
		
      /* NO_RESPONSE_PAGE implies GET method for normal_return_url
      if (!$this->getRequest()->isPost('DATA')) 
	    {
          $this->_redirect('');
          return;
      }
      */
		
        $response = $model->getApiResponse()->doResponse($this->getData(), array('bin_response' => $model->getBinResponse() ) );
				
		if ($response['merchant_id'] != $model->getMerchantId()) {
		    Mage::log(sprintf('Response Merchant ID (%s) is not valid with configuration value (%s)' . "\n", $response['merchant_id'], $model->getMerchantId()), null, 'atos');
		   
		    $session->addError($this->__('We are sorry but we have an error with payment module'));
		    $this->_redirect('checkout/cart');
		    return;
		}
		
		switch ($response['response_code']) {
		    case '00':
                $session->setQuoteId($session->getAtosStandardQuoteId(true));
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
	    $model = $this->getStandard();
	
        if (!$this->getRequest()->isPost('DATA')) {
            $this->_redirect('');
            return;
        }
	
        if ($this->getConfig()->getCheckByIpAddress()) {
			if (!in_array($model->getApiParameters()->getIpAddress(), $this->getConfig()->getAuthorizedIps())) {
		        Mage::log($model->getApiParameters()->getIpAddress() . ' tries to connect to our server' . "\n", null, 'atos');
		        return;
			}
	    }
		
		$response = $model->getApiResponse()->doResponse($_POST['DATA'], array('bin_response' => $model->getBinResponse() ) );
				
		if ($response['merchant_id'] != $model->getMerchantId()) {
		    Mage::log(sprintf('Response Merchant ID (%s) is not valid with configuration value (%s)' . "\n", $response['merchant_id'], $model->getMerchantId()), null, 'atos');
		    return;
		}
		
		$order = Mage::getModel('sales/order');
        $order->loadByIncrementId($response['order_id']);
			   
		switch ($response['response_code']) {
			// Success order
		    case '00':
			   if ($order->getId()) {
                    if ($order->getState() == Mage_Sales_Model_Order::STATE_HOLDED) {
    					$order->unhold();
    				}
    				
                    if (!$status = $model->getConfigData('order_status_payment_accepted')) {
                        $status = $order->getStatus();
                    }
                    
                    $message = $this->__('Payment accepted by Atos');
                    $message .= ' - '.Mage::getSingleton('atos/api_response')->describeResponse($response);
                    
                    if ($status == Mage_Sales_Model_Order::STATE_PROCESSING) {
                        $order->setState(
                            Mage_Sales_Model_Order::STATE_PROCESSING,
                            $status,
                            $message
                        );
                    } else if ($status == Mage_Sales_Model_Order::STATE_COMPLETE) {
                        $order->setState(
                            Mage_Sales_Model_Order::STATE_COMPLETE,
                            $status,
                            $message
                        );
                    } else {
                        $order->addStatusToHistory(
        					$status,
        					$message,
        					true
        				);
    				}

					// Create invoice
                   $this->saveInvoice($order);
				   
                   $order->sendNewOrderEmail();
			   }
			   break;
			   
			default:
				// Cancel order
			    if ($order->getId()) {
			        $messageError = $this->__('Customer was rejected by Atos');
			        $messageError .= ' - '.Mage::getSingleton('atos/api_response')->describeResponse($response);
                    
			        if ($order->getState() == Mage_Sales_Model_Order::STATE_HOLDED) {
    					$order->unhold();
    				}
			        
			        if (!$status = $model->getConfigData('order_status_payment_refused')) {
                        $status = $order->getStatus();
                    }
			        
                    $order->addStatusToHistory(
    					$status,
    					$messageError
    				);
    
    				if ($status == Mage_Sales_Model_Order::STATE_HOLDED && $order->canHold()) {
    					$order->hold();
    				}
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
        if ($order->canInvoice()) {
            $convertor = Mage::getModel('sales/convert_order');
            
			$invoice = $convertor->toInvoice($order);
                       
			foreach ($order->getAllItems() as $orderItem) {
                if (!$orderItem->getQtyToInvoice()) {
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
                $order->getStatus(),
                Mage::helper('atos')->__('Invoice %s was created', $invoice->getIncrementId())
            );
        }

        return false;
    }

    protected function getData()
    {
        if ($this->getRequest()->isPost('DATA')) return $_POST['DATA'];
        elseif ($this->getRequest()->isGet('DATA')) return $_GET['DATA'];
        else return;
    }
}
