<?php
/**
*  mod_itcs_reservation_calendar - simple reservation calendar by it-conserv.de
*  with bootstrap datepicker script https://github.com/uxsolutions/bootstrap-datepicker
* ------------------------------------------------------------------------
* @package     itcs reservation calendar
* @author      it-conserv.de
* @copyright   2022 it-conserv.de
* @license     GNU/GPLv3 <http://www.gnu.org/licenses/gpl-3.0.de.html>
* @link        https://it-conserv.de
* ------------------------------------------------------------------------
*/

// No direct access to this file
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Log\Log;

Log::addLogger(

	// Definiert den Namen der Datei, in welche der Logger protokolliert.
	['text_file' =>  'log_itcsResCal.php'],  
	
	// Welche Typen von Meldungen soll der Logger protokollieren? (INFO, ERROR, ...)
	Log::ALL,  
	
	// Welche Kategorien von Meldungen soll der Logger protokollieren?
	['itcsResCalMsg']
);

/**
 * Script file of the mod_itcs_reservation_calendar plugin for migration to the new version
 * https://docs.joomla.org/J4.x:Creating_a_Simple_Module#Creating_script.php
 * 
 * Create Logging
 * https://wicked-software.de/debuggen-mit-dem-joomla-logging
 * https://docs.joomla.org/Using_JLog
 */
class mod_Itcs_Reservation_CalendarInstallerScript
{	

    /**
     * Function called before extension installation/update/removal procedure commences
     *
     * @param   string            $type    The type of change (install, update or discover_install, not uninstall)
     * @param   InstallerAdapter  $parent  The class calling this method
     *
     * @return  boolean  True on success
     */
    function preflight($type, $parent) {

		Log::add('Start Preflight', LOG::INFO, 'itcsResCalMsg');

		$db = Factory::getDbo();
		$msg = ''; // Message

		// check old version
		$query = $db->getQuery(true);
		$query->select($db->quoteName('manifest_cache'));
		$query->from($db->quoteName('#__extensions'));
		$query->where($db->quoteName('element') . ' = ' . $db->quote('mod_itcs_reservation_calendar'));
		$db->setQuery($query);


		if($result = $db->loadResult()){

			$res = json_decode($result);
			
			// get old version params
			if (version_compare($res->version, '4.0.0', '<')){

				//Request used modules
				$query = $db->getQuery(true);
				$query->select($db->quoteName(array('id','params')));
				$query->from($db->quoteName('#__modules'));
				$query->where($db->quoteName('module') . ' = ' . $db->quote('mod_itcs_reservation_calendar'));
			
				$db->setQuery($query);

				// Update Parameters
				if($row = $db->loadAssocList()){
					foreach($row AS $i){
						$p = json_decode($i['params']);

						Log::add('Old Params (id: '.$i['id'].'): ', LOG::INFO, 'itcsResCalMsg');
						Log::add(print_r($p, true), LOG::INFO, 'itcsResCalMsg');

						$new_params = new stdClass();
						$new_params->colors = new stdClass();
						$new_params->color_text = new stdClass();

						foreach($p AS $item=>$value){
	
							switch ($item) {
								case 'color1':
								case 'color2':
								case 'color3':
									$v = $this->hex2RGB($value, true, ',');
									$new_params->colors->{$item} = 'rgb('.$v.')';
									//$colors[$item] = $value;
									break;
								case 'color1_text':
								case 'color2_text':
								case 'color3_text':
									$new_params->color_text->{$item} = $value;
									//$ctext[$item] = $value;
									break;
								default:
								   $new_params->{$item} = $value;
							}
						
						}

						Log::add('New Params (id: '.$i['id'].'): ', LOG::INFO, 'itcsResCalMsg');
						Log::add(print_r($new_params, true), LOG::INFO, 'itcsResCalMsg');						

						// Update the params for new version
						$object = new stdClass();
						$object->id = $i['id'];
						$object->params = json_encode($new_params);
						$result = Factory::getDbo()->updateObject('#__modules', $object, 'id');
						
						$msg .= '<br><i class="icon icon-ok"></i>Parameters for Modul with ID: '.$i['id'].' was updated';

						Log::add('Module with id: '.$i['id'].' was updated ', LOG::INFO, 'itcsResCalMsg');
					}
				}

				// Delete old files and folder
					if(Folder::exists(JPATH_ROOT.'/modules/mod_itcs_reservation_calendar/helper')){
						if(Folder::delete(JPATH_ROOT.'/modules/mod_itcs_reservation_calendar/helper')){
							Log::add('Delete /helper Folder OK', LOG::INFO, 'itcsResCalMsg');
						}
						else{
							Log::add('Delete /helper Folder Error', LOG::ERROR, 'itcsResCalMsg');
						}
					}

					if(Folder::exists(JPATH_ROOT.'/modules/mod_itcs_reservation_calendar/assets/php')){
						if(Folder::delete(JPATH_ROOT.'/modules/mod_itcs_reservation_calendar/assets/php')){
							Log::add('Delete /assets/php Folder OK', LOG::INFO, 'itcsResCalMsg');
						}
						else{
							Log::add('Delete /assets/php Folder Error', LOG::ERROR, 'itcsResCalMsg');
						}
					}

					if(Folder::exists(JPATH_ROOT.'/modules/mod_itcs_reservation_calendar/assets/images')){
						if(Folder::delete(JPATH_ROOT.'/modules/mod_itcs_reservation_calendar/assets/images')){
							Log::add('Delete /assets/images Folder OK', LOG::INFO, 'itcsResCalMsg');
						}
						else{
							Log::add('Delete /assets/images Folder Error', LOG::ERROR, 'itcsResCalMsg');
						}
					}

					if(Folder::exists(JPATH_ROOT.'/modules/mod_itcs_reservation_calendar/language')){
						if(Folder::delete(JPATH_ROOT.'/modules/mod_itcs_reservation_calendar/language')){
							Log::add('Delete /language Folder OK', LOG::INFO, 'itcsResCalMsg');
						}
						else{
							Log::add('Delete /language Folder Error', LOG::ERROR, 'itcsResCalMsg');
						}
					}

					// Files
					if(File::exists(JPATH_ROOT.'/modules/mod_itcs_reservation_calendar/assets/elements/admin.css')){			
						if(File::delete(JPATH_ROOT.'/modules/mod_itcs_reservation_calendar/assets/elements/admin.css')){
							Log::add('Delete /assets/elements/admin.css File OK', LOG::INFO, 'itcsResCalMsg');
						}
						else{
							Log::add('Delete /assets/elements/admin.css File Error', LOG::ERROR, 'itcsResCalMsg');
						}
					}

					if(File::exists(JPATH_ROOT.'/language/de-DE/de-DE.mod_itcs_reservation_calendar.ini')){			
						if(File::delete(JPATH_ROOT.'/language/de-DE/de-DE.mod_itcs_reservation_calendar.ini')){
							Log::add('Delete DE-language file OK', LOG::INFO, 'itcsResCalMsg');
						}
						else{
							Log::add('Delete DE-language file Error', LOG::ERROR, 'itcsResCalMsg');
						}
					}

					if(File::exists(JPATH_ROOT.'/language/en-GB/en-GB.mod_itcs_reservation_calendar.ini')){			
						if(File::delete(JPATH_ROOT.'/language/en-GB/en-GB.mod_itcs_reservation_calendar.ini')){
							Log::add('Delete EN-language file OK', LOG::INFO, 'itcsResCalMsg');
						}
						else{
							Log::add('Delete EN-language file Error', LOG::ERROR, 'itcsResCalMsg');
						}
					}					

					if(File::exists(JPATH_ROOT.'/language/fi-FI/fi-FI.mod_itcs_reservation_calendar.ini')){			
						if(File::delete(JPATH_ROOT.'/language/fi-FI/fi-FI.mod_itcs_reservation_calendar.ini')){
							Log::add('Delete FI-language file OK', LOG::INFO, 'itcsResCalMsg');
						}
						else{
							Log::add('Delete FI-language file Error', LOG::ERROR, 'itcsResCalMsg');
						}
					}

					if(File::exists(JPATH_ROOT.'/language/fr-FR/fr-FR.mod_itcs_reservation_calendar.ini')){			
						if(File::delete(JPATH_ROOT.'/language/fr-FR/fr-FR.mod_itcs_reservation_calendar.ini')){
							Log::add('Delete FR-language file OK', LOG::INFO, 'itcsResCalMsg');
						}
						else{
							Log::add('Delete FR-language file path Error', LOG::ERROR, 'itcsResCalMsg');
						}
					}

				// Output Message
				if(!empty($msg)){
					echo '<div class="alert alert-info span6 offset3">';
					echo '<p><strong>'.$type.'</strong></p>';
					echo '<p>The module with parameters were migrated from '.$res->version.' to the new version.</p>';
					echo '<p>'.$msg.'</p>';
					echo '</div>';
				}

				Log::add('END Preflight with update', LOG::INFO, 'itcsResCalMsg');
				return true;
			}

			// Update to 4.0.4
			if (version_compare($res->version, '4.0.4', '<')){

				$re = '/(\d{2}:\d{2}:\d{2})/';
				$tz = Factory::getConfig()->get('offset');

				//Request used modules
				$query = $db->getQuery(true);
				$query->select($db->quoteName(array('id','params')));
				$query->from($db->quoteName('#__modules'));
				$query->where($db->quoteName('module') . ' = ' . $db->quote('mod_itcs_reservation_calendar'));
			
				$db->setQuery($query);

				// get Update Parameters
				if($row = $db->loadAssocList()){
					foreach($row AS $i){

						$params = json_decode($i['params']);

						// Prüfe ob Tage vorhanden sind
						if(!empty($params->resdays)){

							Log::add('Old Params (id: '.$i['id'].'): ', LOG::INFO, 'itcsResCalMsg');
							Log::add(print_r($params->resdays, true), LOG::INFO, 'itcsResCalMsg');

							//Update durchführen
							foreach($params->resdays AS $item=>$value){
																						
								preg_match($re, $value->cal_day, $matches, PREG_OFFSET_CAPTURE, 0);
								$cal_day = new \DateTime($value->cal_day, new \DateTimeZone('UTC'));
								if($matches){
									$cal_day->setTimezone(new \DateTimeZone($tz));
								}
								$cal_day->setTime(0, 0, 0);
								$params->resdays->{$item}->cal_day = $cal_day->format('Y-m-d');

							}
	
							Log::add('New Params (id: '.$i['id'].'): ', LOG::INFO, 'itcsResCalMsg');
							Log::add(print_r($params->resdays, true), LOG::INFO, 'itcsResCalMsg');
	
							// Update the params for new version
							$object = new stdClass();
							$object->id = $i['id'];
							$object->params = json_encode($params);
							$result = Factory::getDbo()->updateObject('#__modules', $object, 'id');
							
							$msg .= '<br><i class="icon icon-ok"></i>Parameters for Modul with ID: '.$i['id'].' was updated';
							Log::add('Module with id: '.$i['id'].' was updated ', LOG::INFO, 'itcsResCalMsg');						
						}					
					}
				}

				// Output Message
				if(!empty($msg)){
					echo '<div class="alert alert-info span6 offset3">';
					echo '<p><strong>'.$type.'</strong></p>';
					echo '<p>The module with parameters were migrated from '.$res->version.' to the new version.</p>';
					echo '<p>'.$msg.'</p>';
					echo '</div>';
				
					Log::add('END Preflight with update', LOG::INFO, 'itcsResCalMsg');
					return true;				
				}
			}

			Log::add('END Preflight without update', LOG::INFO, 'itcsResCalMsg');
			return true;
		}

		Log::add('END Preflight with DB ERROR', LOG::ERROR, 'itcsResCalMsg');
		return true;
	}

	/**
	 * Convert a hexa decimal color code to its RGB equivalent
	 *
	 * License - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
	 * Code base: http://www.php.net/manual/en/function.hexdec.php#99478
	 *
	 * @param	string	$hexStr (hexadecimal color value)
	 * @param	boolean	$returnAsString (if set true, returns the value separated by the separator character. Otherwise returns associative array)
	 * @param	string	$seperator (to separate RGB values. Applicable only if second parameter is true.)
	 * @return	array	or string (depending on second parameter. Returns False if invalid hex color value)
	 *
	 */
	private function hex2RGB($hexStr, $returnAsString = false, $seperator = ',') {
		$hexStr = preg_replace('/[^0-9a-f]/i', '', $hexStr); // Gets a proper hex string
		//if shorthand notation, need some string manipulations
		if(strlen($hexStr) == 3)
			$hexStr = preg_replace('/([0-9A-F]{1})/i','$1$1',$hexStr);
		if ( strlen($hexStr) != 6) {//Invalid hex color code
			return false;
		}
		$rgbArray = array();
		$colorVal = hexdec($hexStr);
		$rgbArray['red'] = 0xFF & ($colorVal >> 0x10);
		$rgbArray['green'] = 0xFF & ($colorVal >> 0x8);
		$rgbArray['blue'] = 0xFF & $colorVal;
		// returns the rgb string or the associative array
		return $returnAsString ? implode($seperator, $rgbArray) : $rgbArray;
	}

}
