<?php
/**
 * Form Block
 *
 * @category   Mage
 * @package    Mage_Atos
 * @name       Mage_Atos_Block_Several_Form
 */
class Mage_Atos_Block_Several_Form extends Mage_Payment_Block_Form
{
    protected function _construct()
    {
        $this->setTemplate('payment/form/atos_several.phtml');
        parent::_construct();
    }
	
	public function getCreditCardsAccepted()
	{
		$cards = Mage::getSingleton('atos/config')->getCreditCardTypes();
		
		$array = array();
	    foreach (explode(',', Mage::getSingleton('atos/method_several')->getCctypes()) as $value)
		{
		    $array[] = $cards[$value];
		}
		
	    return implode(', ', $array);
	}
}