<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category   Mage
 * @package    Mage_Atos
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
**/
 
class Mage_Atos_Model_Api_Files extends Mage_Atos_Model_Abstract
{
    public function getPathfileName($merchand_id = null)
	{
	  $path = Mage::getBaseDir('base').DS.'lib'.DS.'atos'.DS;
	  
	  if ( is_dir($path) ) 
	  {
	      if (isset($merchand_id))
		  {
		      $pathfile = $path.'pathfile.'.$merchand_id;
		  } else {
	          $parmcom = self::getInstalledParmcom();
              $pathfile = $path.'pathfile.'.$parmcom[0]['value'];
		  }
	  
		  
		  if (!file_exists($pathfile)) 
		  {
		      $content = '#########################################################################' . "\n";
		      $content.= '#' . "\n";
		      $content.= '# Pathfile' . "\n";
		      $content.= '#' . "\n";
		      $content.= '# Liste fichiers parametres utilisés par le module de paiement' . "\n";
		      $content.= '#' . "\n";
		      $content.= '#########################################################################' . "\n";
		      $content.= "\n";
		      $content.= '# ------------------------------------------------------------------------'. "\n";
		      $content.= '# Chemin vers le répertoire des logos depuis le web alias' . "\n";
		      $content.= '# Exemple pour le répertoire www.merchant.com/cyberplus/payment/logo/' . "\n";
		      $content.= '# indiquer:' . "\n";
		      $content.= '# ------------------------------------------------------------------------' . "\n";
		      $content.= '#' . "\n";
			  $content.= 'D_LOGO!' . Mage::getUrl('media/atos/') . '!'. "\n"; 
	          $content.= '#' . "\n";
	          $content.= '#------------------------------------------------------------------------' . "\n";
	          $content.= '#  Fichiers parametres lies a l\'api cyberplus paiement' . "\n";
	          $content.= '#------------------------------------------------------------------------' . "\n";
	          $content.= '#' . "\n";
	          $content.= '# Certificat du commercant' . "\n";
	          $content.= '#' . "\n";
	          $content.= 'F_CERTIFICATE!' . $path . 'certif!' . "\n";
	          $content.= '#' . "\n";
	          $content.= '# Fichier paramètre commercant' . "\n";
	          $content.= '#' . "\n";
	          $content.= 'F_PARAM!' . $path . 'parmcom!' . "\n";
	          $content.= '#' . "\n";
	          $content.= '# Fichier des paramètres communs' . "\n";
	          $content.= '#' . "\n";
	          $content.= 'F_DEFAULT!' . $pathfile . '!' . "\n";
	          $content.= '#' . "\n";
	          $content.= '# --------------------------------------------------------------------------' . "\n";
	          $content.= '# End of file' . "\n";
	          $content.= '# --------------------------------------------------------------------------' . "\n";

	          if ($fp = fopen($pathfile, 'w')) 
		      {
	              fputs($fp, $content);
	              fclose($fp);
	          }
          }
      }

      return $pathfile;
    }

	public function getCertificate() 
	{
        $certificate = null;
      
	    foreach (self::getInstalledCertificates() as $current) 
		{
	        if (!isset($current['test'])) 
		    {
	            return $current;
	        }
		  
	        if (!isset($certificate)) 
		    {
		        $certificate = $current;
		    }
        }
	  
        return $certificate;
	}
	
    public function getInstalledCertificates() 
	{
	    $certificates = array();
		$path = Mage::getBaseDir('base').DS.'lib'.DS.'atos';
		
		if ( is_dir($path) ) 
		{
            $dir = dir($path);
	        while ( $file = $dir->read() ) 
			{
			    $data = split('\.', $file);
	            $n = sizeof($data) - 1;
			
	            if ($data[0] == 'certif') 
			    {
	                $certificates[] = self::getCertificateInfo($data[$n]);
			    }
	        }
	        
			sort($certificates);
	        $dir->close();
		}
       
	    return $certificates;
    }
	
    public function getCertificateInfo($id) {
	    $certificates = self::getPredefinedCertificates();

        if (isset($certificates[$id])) 
		{
		    return $certificates[$id];
	    }

        return array
		(
		    'value'  => $id,
		    'label'  => $id,
		);
    }
	
    public function getPredefinedCertificates() 
	{
		$predefined = array
		(
		    '013044876511111' => array
			(
		        'value' => '013044876511111',
		        'label' => 'Compte test eTransaction'
		    ),
			
			'014213245611111' => array
			(
			    'value' => '014213245611111',
				'label' => 'Compte test Sogenactif'
			),
			
			'038862749811111' => array
			(
		        'value' => '038862749811111',
		        'label' => 'Compte test CyberPlus'
			),
			
			'082584341411111' => array
			(
		        'value' => '082584341411111',
		        'label' => 'Compte test Mercanet'
			),
			
			'014141675911111' => array
			(
		       'value' => '014141675911111',
		       'label' => 'Compte test Scelluis'
			),
			
			'014295303911111' => array
			(
		       'value' => '014295303911111',
		       'label' => 'Compte test Sherlocks'
			),
			
			'000000014005555' => array 
			(
			   'value' => '000000014005555',
			   'label' => 'Compte test Aurore Cetelem'
			)
		);

        return $predefined;
    }
	
    public function getInstalledParmcom() 
	{
        $parmcom = array();
		$path = Mage::getBaseDir('base').DS.'lib'.DS.'atos';
        
		if ( is_dir($path) ) 
		{
		    $dir = dir($path);
			
	        while ( $file = $dir->read() ) 
		    {
			    
				$data = split('\.', $file);
				
				if ( ($data[0] == 'parmcom') && !file_exists($path . 'certif.fr.' . $data[1])) 
				{
	                $parmcom[] = array
					(
				        'value' => $file,
				        'label' => $file
				    );
				}
	        }
			
	        sort($parmcom);
			$dir->close();
		}
		
		if (empty($parmcom))
		{
		    Mage::throwException('Parcom files doesn\'t exist !');
		}
		
        return $parmcom;
    }
}