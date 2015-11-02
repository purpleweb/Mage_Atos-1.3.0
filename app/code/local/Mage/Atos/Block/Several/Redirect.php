<?php
/**
 * Redirect to Atos
 *
 * @category   Mage
 * @package    Mage_Atos
 * @name       Mage_Atos_Block_Several_Redirect
 */
class Mage_Atos_Block_Several_Redirect extends Mage_Core_Block_Abstract
{
    protected function _toHtml()
    {
        $several = Mage::getModel('atos/method_several');
		$several->callRequest();
		
        if ($several->getSystemError()) {
		    return $several->getSystemMessage();
		}
		
		$html = '';

		$html.= '<style type="text/css">'."\n";
		$html.= '  @import url("'.$this->getSkinUrl('css/stylesheet.css').'");'."\n";
		$html.= '  @import url("'.$this->getSkinUrl('css/checkout.css').'");'."\n";
		$html.= '</style>'."\n";

		$html.= '<div id="atosButtons">'."\n";
		$html.= '  <p class="center">'.$this->__('You have to pay to validate your order').'</p>'."\n";
		$html.= '  <form action="'.$several->getSystemUrl().'" method="post">'."\n";
		$html.= $several->getSystemMessage()."\n";
		$html.= '  </form>'."\n";
		$html.= '</div>'."\n";

        return $html;
    }
}