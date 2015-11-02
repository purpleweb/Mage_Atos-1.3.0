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
 *
 * Api Response
 *
 * @category   Mage
 * @package    Mage_Atos
 * @name       Mage_Atos_Model_Api_Standard_Response
 */
class Mage_Atos_Model_Api_Response extends Mage_Atos_Model_Api_Parameters
{
    public function doResponse($data = null, $parameters)
    {
        if ( ($data === null) && isset($_POST['DATA']) && !empty($_POST['DATA']) )
	    {
		    $data = $_POST['DATA'];
		}
		
        if (!preg_match(':^[a-zA-Z0-9]+$:', $data))
		{
            $data = '';
		}
		
        $command = $parameters['bin_response'];
        $command.= ' pathfile=' . $this->getApiFiles()->getPathfileName();
        $command.= ' message=' . $data;
		
        $sips_resp = shell_exec("$command 2>&1");
        $sips_resp = explode('!', $sips_resp);
				
	    $hash = array();
        $hash['command'] = $command;
        $hash['output']  = $sips_resp;

        list (,
	        $hash['code'],
	        $hash['error'],
	        $hash['merchant_id'],
	        $hash['merchant_country'],
	        $hash['amount'],
	        $hash['transaction_id'],
	        $hash['payment_means'],
	        $hash['transmission_date'],
	        $hash['payment_time'],
	        $hash['payment_date'],
	        $hash['response_code'],
	        $hash['payment_certificate'],
	        $hash['authorisation_id'],
	        $hash['currency_code'],
	        $hash['card_number'],
	        $hash['cvv_flag'],
	        $hash['cvv_response_code'],
	        $hash['bank_response_code'],
	        $hash['complementary_code'],
	        $hash['complementary_info'],
	        $hash['return_context'],
	        $hash['caddie'], // unavailable with NO_RESPONSE_PAGE
	        $hash['receipt_complement'],
	        $hash['merchant_language'], // unavailable with NO_RESPONSE_PAGE
	        $hash['language'],
	        $hash['customer_id'], // unavailable with NO_RESPONSE_PAGE
	        $hash['order_id'],
	        $hash['customer_email'], // unavailable with NO_RESPONSE_PAGE
	        $hash['customer_ip_address'], // unavailable with NO_RESPONSE_PAGE
	        $hash['capture_day'],
	        $hash['capture_mode'],
	        $hash['data']
	    ) = $sips_resp;
		
        if (!isset($hash['code'])) 
		{
	        Mage::throwException($sips_resp);
        }
		
	    if ($hash['code'] == '-1') 
		{
	        Mage::throwException($hash['error']);
		}

        return $hash;
    }
	
	public function describeResponse($response, $return='string') 
	{
	    $array = array();
		
		$string = Mage::helper('atos')->__('Numero de transaction : %s', $response['transaction_id']) . "\n";
		$string.= Mage::helper('atos')->__('Mode de capture : %s', $response['capture_mode']) . "\n";
		
		if (isset($response['capture_day']) && is_numeric($response['capture_day']))
		{
		    if ($response['capture_day'] == 0) {
			  $string.= Mage::helper('atos')->__('Jour avant la capture : capture immediate') . "\n";
			} else {
		      $string.= Mage::helper('atos')->__('Jour avant la capture : %s', $response['capture_day']) . "\n";
			}
		}
		
		$string.= Mage::helper('atos')->__('Type de carte de credit : %s', $response['payment_means']) . "\n";

// Credit card number
        if (isset($response['card_number']) && !empty($response['card_number'])) 
		{
	        $cc = split('\.', $response['card_number']);
			$array['card_number'] = $cc[0] . ' #### #### ##' . $cc[1];
			
		    $string.= Mage::helper('atos')->__('Numero de carte bancaire : %s', $array['card_number']) . "\n";
		}
		
		if (isset($response['cvv_flag'])) 
		{
		    switch ($response['cvv_flag']) 
			{
			    case '1':
				    switch ($response['cvv_response_code']) 
					{
					    case '4E':
						    $array['cvv_response_code'] = "Numero de controle incorrect";
						    break;
						case '4D':
						    $array['cvv_response_code'] = "Numero de controle correct";
						    break;
					    case '50':
						    $array['cvv_response_code'] = "Numero de controle non traite";
						    break;
						case '53':
						    $array['cvv_response_code'] = "Le numero de controle est absent de la demande d'autorisation";
						    break;
						case '55':
						    $array['cvv_response_code'] = "La banque de l'internaute n�est pas certifiee, le controle n�a pu etre effectue";
						    break;
						case 'NO':
						    $array['cvv_response_code'] = "Pas de cryptogramme sur la carte";
						    break;	
					    default:
						    $array['cvv_response_code'] = "Aucune information sur le cryptogramme de la carte";
						    break;								
					}
					
				    $string .= Mage::helper('atos')->__('A propos du cryptogramme de la carte : %s', $array['cvv_response_code']) . "\n";
					
					if (isset($response['cvv_key']))
					{
					    $array['cvv_key'] = $response['cvv_key'];
				        $string .= Mage::helper('atos')->__('Cryptogramme de la carte de credit : %s', $response['cvv_key']) . "\n";
					}
				    break;
			}
		}
		
		if (isset($response['response_code']))
		{
		    switch($response['response_code'])
			{
	            case '00': 
				    $array['response_code'] = "Autorisation acceptee";
					break;
                case '02':  
		            $array['response_code'] = "Demande d'autorisation par telephone a la banque a cause d'un depassement de plafond d'autorisation sur la carte";
		            break;
                case '03':
		            $array['response_code'] = "Champ merchant_id invalide, verifier la valeur renseignee dans la requete ou contrat de vente a distance inexistant, contacter votre banque.";
		            break;
		        case '05':
		            $array['response_code'] = "Autorisation refusee";
		            break;
		        case '12':
		            $array['response_code'] = "Transaction invalide, verifier les param�tres transferes dans la requete.";
		            break;
				case '17':
		            $array['response_code'] = "Annulation de l'internaute";
					break;
				case '30':
		            $array['response_code'] = "Erreur de format";
					break;
				case '34':
				    $array['response_code'] = "Suspicion de fraude";
				    break;
				case '75':
		            $array['response_code'] = "Nombre de tentatives de saisie du numero de carte depasse";
					break;
				case '90':
		            $array['response_code'] = "Service temporairement indisponible";
					break;
			    case '94':
		            $array['response_code'] = "Transaction deja enregistree";
					break;
				default:
		            $array['response_code'] = "ATOS Transaction rejetee - code invalide " . $response['response_code'];
			}
			
		    $string .= Mage::helper('atos')->__('Code reponse de la banque : %s', $array['response_code']) . "\n";
		}
		
	    if (isset($response['bank_response_code'])) 
		{
		    if (in_array($response['payment_means'], array('CB', 'VISA', 'MASTERCARD')))
		    {
	            switch ($response['bank_response_code']) 
		        {
		            case '00':
			            $array['bank_response_code'] = "Transaction approuvee ou traitee avec succes";
			            break;
				    case '02':
				        $array['bank_response_code'] = "Contacter l'emetteur de carte";
				        break;
				    case '03':
				        $array['bank_response_code'] = "Accepteur invalide";
				        break;
				    case '04':
				        $array['bank_response_code'] = "Conserver la carte";
				        break;
				    case '05':
				        $array['bank_response_code'] = "Ne pas honorer";
				        break;
				    case '07':
				        $array['bank_response_code'] = "Conserver la carte, conditions speciales";
				        break;
				    case '08':
				        $array['bank_response_code'] = "Approuver apres identification";
				        break;
				    case '12':
				        $array['bank_response_code'] = "Transaction invalide";
				        break;
			        case '13':
				        $array['bank_response_code'] = "Montant invalide";
				        break;
			        case '14':
				        $array['bank_response_code'] = "Numero de porteur invalide";
				        break;
				    case '15':
				        $array['bank_response_code'] = "Emetteur de carte inconnu";
				        break;
				    case '30':
				        $array['bank_response_code'] = "Erreur de format";
				        break;
				    case '31':
				        $array['bank_response_code'] = "Identifiant de l'organisme acquereur inconnu";
				        break;
				    case '33':
				        $array['bank_response_code'] = "Date de validite de la carte depassee";
				        break;
				    case '34':
				        $array['bank_response_code'] = "Suspicion de fraude";
				        break;
				    case '41':
				        $array['bank_response_code'] = "Carte perdue";
				        break;
				    case '43':
				        $array['bank_response_code'] = "Carte volee";
				        break;
				    case '51':
				        $array['bank_response_code'] = "Provision insuffisante ou credit depasse";
				        break;
				    case '54':
				        $array['bank_response_code'] = "Date de validite de la carte depassee";
				        break;
				    case '56':
				        $array['bank_response_code'] = "Carte absente du fichier";
				        break;
				    case '57':
			            $array['bank_response_code'] = "Transaction non permise a ce porteur";
				        break;
				    case '58':
			            $array['bank_response_code'] = "Transaction interdite au terminal";
				        break;
				    case '59':
				        $array['bank_response_code'] = "Suspicion de fraude";
				        break;
				    case '60':
				        $array['bank_response_code'] = "L'accepteur de carte doit contacter l'acquereur";
				        break;
				    case '61':
				        $array['bank_response_code'] = "Depasse la limite du montant de retrait";
				        break;	
				    case '63':
				        $array['bank_response_code'] = "Regles de securite non respectees";
				        break;	
				    case '68':
				        $array['bank_response_code'] = "Reponse non parvenue ou recue trop tard";
				        break;	
				    case '90':
				        $array['bank_response_code'] = "Arret momentane du systeme";
				        break;		
				    case '91':
				        $array['bank_response_code'] = "Emetteur de cartes inaccessible";
				        break;
				    case '96':
				        $array['bank_response_code'] = "Mauvais fonctionnement du systeme";
				        break;
				    case '97':
				        $array['bank_response_code'] = "Echeance de la temporisation de surveillance globale";
				        break;
				    case '98':
				        $array['bank_response_code'] = "Serveur indisponible routage reseau demande a nouveau";
				        break;		
				    case '99':
				        $array['bank_response_code'] = "Incident domaine initiateur";
				        break;
		        }
			
			    if (isset($array['bank_response_code']))
				{
		            $string .= Mage::helper('atos')->__('Code reponse de la Banque : %s', $array['bank_response_code']) . "\n";
				}
			}
		}

		if (isset($response['complementary_code']))
		{
	        switch ($response['complementary_code']) 
			{
		        case '00':
			        $array['complementary_code'] = "Tous les controles auxquels vous avez adheres se sont effectues avec succes";
			        break;
			    case '02':
			        $array['complementary_code'] = "La carte utilisee a depasse l'encours autorise";
			        break;
		       case '03':
			        $array['complementary_code'] = "La carte utilisee appartient a la�liste grise du commercant";
			        break;
			   case '05':
			        $array['complementary_code'] = "Le BIN de la carte utilisee appartient a une plage non referencee dans la table des BIN de la plate-forme MERCANET";
			        break;
		       case '06':
			        $array['complementary_code'] = "Le numero de carte n'est pas dans une plage de meme nationalite que celle du commercant";
			        break;
			   case '99':
			        $array['complementary_code'] = "Le serveur MERCANET a un rencontre un probleme lors du traitement d�un des controles locaux complementaires";
			        break;
		    }
		
		    if (isset($array['complementary_code']))
			{
		        $string .= Mage::helper('atos')->__('Controle supplementaire : %s', $array['complementary_code']) . "\n";
		    }
		}
		
		if (isset($response['data']))
		{
		    $array['data'] = $response['data'];
		    $string .= $response['data'] . "\n";
		}
		
		if ($return == 'string')
		{
		    return $string;
		} else {
		    return $array;
		}
	}
}
