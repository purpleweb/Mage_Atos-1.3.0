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

class Mage_Atos_Block_Standard_Form extends Mage_Payment_Block_Form
{
    protected function _construct()
    {
        $this->setTemplate('payment/form/atos.phtml');
        parent::_construct();
    }
	
	public function getCreditCardsAccepted()
	{
		$cards = Mage::getSingleton('atos/config')->getCreditCardTypes();
		
		$array = array();
	    foreach (explode(',', Mage::getSingleton('atos/method_standard')->getCctypes()) as $key => $value)
		{
		    if (array_key_exists($value, $cards)) {
				$array[$value] = $cards[$value];
			}
		}
		
		return $array;
	}
	
	public function getAtosLogoSrc()
	{
		return $this->getUrl() . Mage::getStoreConfig('logo/atos_standard');
	}

}
