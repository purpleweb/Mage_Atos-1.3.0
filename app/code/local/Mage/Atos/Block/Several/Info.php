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
 * @copyright  Ilan Parmentier - Artbambou SARL
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Atos_Block_Several_Info extends Mage_Payment_Block_Info
{
    protected function _construct()
	{
        parent::_construct();
        $this->setTemplate('payment/info/atos_several.phtml');
    }
}