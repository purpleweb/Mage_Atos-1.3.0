<?php
/**
 * Form Block
 *
 * @category   Mage
 * @package    Mage_Atos
 * @name       Mage_Atos_Block_Aurore_Form
 */
class Mage_Atos_Block_Aurore_Form extends Mage_Payment_Block_Form
{
    protected function _construct()
    {
        $this->setTemplate('payment/form/atos_aurore.phtml');
        parent::_construct();
    }
}