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

namespace ITCS\Module\ItcsReservationCalendar\Site;

// no direct access
defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Uri\Uri;
use \Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

class ItcsReservationCalendarHelper
{
    /**
     * Delete Days
     *
     * @param 	string	$params as json String
     * @param 	int		$id as uniq id as integer
	 * @return	object	new params
     */    
    public static function deleteDays($params, $id)
    {
		$p = json_decode($params);
		
		$del_days = $params->get('del_days')*-1; //change count days to negative integer
		$today = date_create(date('d.m.Y'));
		
		$i=0;
		foreach($p AS $item=>$value){
			if($item <> 'resdays'){
				//Daten belassen
				$new_params[$item] = $value;
			}
			else{
				//--> old Day Params				
				foreach($value AS $cal_day){
					$day = date_create($cal_day->cal_day);
					$diff = date_diff($today,$day);
					
					if ($diff->format("%R%a") >= $del_days){
						//Daten behalten
						//$d[$item.$i] = (object)array('cal_day'=>$cal_day->cal_day,'cal_res_color'=>$cal_day->cal_res_color,'cal_info'=>$cal_day->cal_info);
						foreach($cal_day AS $index=>$v){
							$new_day[$index]=$v;
						}
						$d[$item.$i] = (object)$new_day;
						
						$i++;
					}
				}
				$new_params[$item]= (object)$d;
			}
		}
		
		
		//Update Data
			$new = json_encode($new_params);
			$new_params['json']=$new;
		
			$db = Factory::getDbo();
			$query = $db->getQuery(true);
			
			// Fields to update.
			$fields = array(
				$db->quoteName('params') . ' = ' . $db->quote($new)
			);
			
			// Conditions for which records should be updated.
			$conditions = array(
				$db->quoteName('id') . ' = ' . $db->quote($id)
			);
			
			$query->update($db->quoteName('#__modules'))->set($fields)->where($conditions);
			$db->setQuery($query);
			$result = $db->execute();			
			
        return (object)$new_params;
		
    }

    /**
     * get Day - Preparing the days
     *
     * @param	object	$days containing cal_day, cal_day_count, cal_day_color, cal_day_info
     * @param	int		$demo contains yes = 1 or no = 0
	 * @param	string	$format contains the php date format e.g. 'd.m.Y'
	 * @param	string	$override the public info text
	 * @return	array	reservation days with informations
     */  	
	public static function getDays($days, $demo, $format, $override)
	{
		$resdays = array();
		foreach($days as $item){

			if(!empty($item->cal_day)){
		
				$cal_day_count = (!empty($item->cal_day_count))?intval($item->cal_day_count):1;
				for($i=0; $i < $cal_day_count; $i++) {
					
					//load date as utc
					$cal_day = new \DateTime($item->cal_day, new \DateTimeZone('UTC'));
					$cal_day->setTime(0, 0, 0);
					$cal_day->modify('+'.$i.' day');

					$item_color = 'color' . $item->cal_day_color;

					$resdays[] = (object)array(
						't' => $cal_day->getTimestamp(),
						'cal_day' => $cal_day->format($format),
						'cal_day_info' => (!empty($override)) ? $override : $item->cal_day_info,
						'cal_day_color' => $item_color
					);
				}
			}
		}

		sort($resdays);
				
		return $resdays;
	}

    /**
     * getList - Create table List
     *
     * @param	object	$days containing cal_day, cal_day_count, cal_day_color, cal_day_info
	 * @param	int		$list_days_count Number of days to be displayed
	 * @param	string	$format contains the php date format e.g. 'd.m.Y'
	 * @return	string	html list next days
     */  	
	public static function getList($days, $list_days_count, $format)
	{
		$counter = 1;
		$today = new \DateTime();
		$list = '';

		foreach($days as $item){

			if(!empty($item->cal_day)){
		
				$cal_day_count = (!empty($item->cal_day_count))?intval($item->cal_day_count):1;

				$day_from = new \DateTime($item->cal_day);
				$day_from->setTime(0, 0, 0);
				$date_to = new \DateTime($item->cal_day);
				$date_to->setTime(0, 0, 0);

				if($cal_day_count > 1){				
					date_add($date_to, date_interval_create_from_date_string(($cal_day_count - 1).' days'));
					$to = ' - ' . $date_to->format($format);
				}
				else{
					$to = '';
				}

				if ($list_days_count == 0 OR ($day_from > $today AND $counter <= $list_days_count)){
					$list .=  '<p style="line-height: 1em; font-size: 0.8em;"><strong>'.$day_from->format($format) . $to.'</strong><br />'.$item->cal_day_info.'</p>';
					$counter ++;
				}
			}

		}

		return $list;
	}	

}