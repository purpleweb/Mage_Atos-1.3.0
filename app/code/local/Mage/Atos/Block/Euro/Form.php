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
 * @copyright  Signamarcheix Fabien - Groupe Reflect
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Atos_Block_Euro_Form extends Mage_Payment_Block_Form
{
    protected function _construct()
    {
        $this->setTemplate('payment/form/atos_euro.phtml');
        parent::_construct();
    }
    
    public function getEuroBlock()
    {
    	//$montant = Mage::getSingleton('checkout/session')->getQuote();
    	$montant = 100;
    	$html = '<script type=\'text/javascript\' src=\'http://partenaires.1euro.com/partenaires/js/popup.js\'>
    	</script>
		<div>
     		<iframe id=\'simulateur1euro\' frameborder=\'0\' scrolling=\'no\' width=\'120px\' height=\'40px\' src=\'http://www.box1euro.com/calculatrice/simulateur1euro.php5?idPartenaire=3241554&montant='.$montant.'&option=4&couleur=98C000&couleurCalculatrice=\'/>
     		</iframe>
		</div>
		<div>
     		<a href="javascript:calculette(\'http://www.box1euro.com/calculatrice/calculette1euro.php5?idPartenaire=3241554&montant=100&option=4&couleur=98C000&couleurCalculatrice=\')">
          		<img src=\'http://www.box1euro.com/images/simulateur/lienCalculatriceGenerique.gif\' border=\'0\'/>
          	</a>
        </div>\'';
    }
}