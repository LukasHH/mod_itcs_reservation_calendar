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

// no direct access
defined('_JEXEC') or die;

JLoader::registerNamespace('ITCS\\Module\\ItcsReservationCalendar\\Site', __DIR__ , false, false, 'psr4');

use Joomla\CMS\Helper\ModuleHelper;
use \Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;

use ITCS\Module\ItcsReservationCalendar\Site\ItcsReservationCalendarHelper;

// load JQuery;
HTMLHelper::_('jquery.framework');

// load Params
$uniqid				= $module->id;
$title				= $module->title;
$lang = Factory::getLanguage()->getTag();
$lang = (substr($lang, 0, 2) == 'de')?'de-DE':$lang;
$lang = (substr($lang, 0, 2) == 'fr')?'fr-FR':$lang;
$lang = (substr($lang, 0, 2) == 'fi')?'fi-FI':$lang;
$lang = (substr($lang, 0, 2) == 'it')?'it-IT':$lang;
$lang = ($lang != 'de-DE' && $lang != 'fr-FR' && $lang != 'fi-FI' && $lang != 'it-IT')?'en-GB':$lang;
$day_format = ($lang != 'de-DE' && $lang != 'fr-FR' && $lang != 'fi-FI')?'d/m/Y':'d.m.Y';

// BASIC SETTINGS
$colors		= $params->get('colors');
$color_1	= $colors->color1;
$color_2	= $colors->color2;
$color_3	= $colors->color3;

// Show Legend
$show_legend	= $params->get('legend_show');
$color_text		= $params->get('color_text');
$color1_text	= $color_text->color1_text;
$color2_text	= $color_text->color2_text;
$color3_text	= $color_text->color3_text;

// Public override
$public_override	= $params->get('public_override');
$access_level		= $params->get('access');
$public_text		= $params->get('public_text');
$show_day_list		= $params->get('show_day_list');
$list_days_count	= $params->get('list_days');


// Load CSS/JS
$document = Factory::getDocument();

$document->addStylesheet(URI::base(true) . '/media/mod_itcs_reservation_calendar/css/bootstrap-datepicker3.min.css');
$document->addStylesheet(URI::base(true) . '/media/mod_itcs_reservation_calendar/css/mod_itcs_calendar_style.css');

//Scripte
$document->addScript(URI::base(true) . '/media/mod_itcs_reservation_calendar/js/bootstrap-datepicker.min.js');
$document->addScript(URI::base(true) . '/media/mod_itcs_reservation_calendar/js/locales/bootstrap-datepicker.'.$lang.'.min.js');

// Load and set colors
$re = '/rgb\(([0-9|\,|\s]*)\)/';

preg_match($re, $color_1, $match, PREG_OFFSET_CAPTURE, 0);
$color_1 = $match[1][0];

preg_match($re, $color_2, $match, PREG_OFFSET_CAPTURE, 0);
$color_2 = $match[1][0];

preg_match($re, $color_3, $match, PREG_OFFSET_CAPTURE, 0);
$color_3 = $match[1][0];

// Put styling in header
	$cal_css='
		.id'.$uniqid.' .datepicker table tr td.color1, .id'.$uniqid.' .legend p.color1::before{background-color: rgba('.$color_1.', var(--bg-opacity1));}
		.id'.$uniqid.' .datepicker table tr td.color2, .id'.$uniqid.' .legend p.color2::before{background-color: rgba('.$color_2.', var(--bg-opacity1));}
		.id'.$uniqid.' .datepicker table tr td.color3, .id'.$uniqid.' .legend p.color3::before{background-color: rgba('.$color_3.', var(--bg-opacity1));}
		
		.id'.$uniqid.' .datepicker table tr td.color1:hover, .id'.$uniqid.' .legend p.color1::before{background-color: rgba('.$color_1.', var(--bg-opacity2));}
		.id'.$uniqid.' .datepicker table tr td.color2:hover, .id'.$uniqid.' .legend p.color2::before{background-color: rgba('.$color_2.', var(--bg-opacity2));}
		.id'.$uniqid.' .datepicker table tr td.color3:hover, .id'.$uniqid.' .legend p.color3::before{background-color: rgba('.$color_3.', var(--bg-opacity2));}		
	';
	
$document->addStyleDeclaration($cal_css);

//check del_days
$chk_del_days = ($params->get('del_days') > 0 AND $params->get('auto_delete') == 1) ? 1 : 0;

// Delete Reservation Days
	if ($chk_del_days == 1){
		$new_params = ItcsReservationCalendarHelper::deleteDays($params, $uniqid);
		$days = $new_params->resdays;
	}
	else{
		$days = $params->get('resdays');
	}

// Check Public override
	$user = Factory::getUser();
	$user_level = $user->getAuthorisedViewLevels();
	$chk_access = 0; // default no access

	if ($public_override==1 and !empty($access_level)){
		foreach ($access_level as $a ){
			$chk_access = (in_array($a, $user_level) || $chk_access == 1) ? 1 : 0;
		}
	}

	if ($chk_access == 1 OR $public_override == 0){
		$public_text = '';
	}

//create Demo-Data
$demo = 0;
if ($demo == 1){
	$m = (date_format(new \DateTime(),'m')); //Monat
	$y = date_format(new \DateTime(),'Y'); //Jahr
	
	$ddays['resdays0'] = (object)array('cal_day'=>$y.'-'.$m.'-5','cal_day_count'=>'1','cal_day_color'=>'1','cal_day_info'=>'Demo Color 1');
	$ddays['resdays1'] = (object)array('cal_day'=>$y.'-'.$m.'-11','cal_day_count'=>'1','cal_day_color'=>'2','cal_day_info'=>'Demo Color 2');
	$ddays['resdays2'] = (object)array('cal_day'=>$y.'-'.$m.'-19','cal_day_count'=>'1','cal_day_color'=>'3','cal_day_info'=>'Demo Color 3');

	// Demo next month
	if ($m == "12"){
		$m = "1";
		++$y;
	}
	else{
		++$m;
	}
	$ddays['resdays4'] = (object)array('cal_day'=>$y.'-'.$m.'-3','cal_day_count'=>'1','cal_day_color'=>'2','cal_day_info'=>'Demo Color 2');

	$days = (object)$ddays;
}


// $demo 0 = off, 1 = on
$resdays = ItcsReservationCalendarHelper::getDays($days, 0, $day_format, $public_text);

$tableList = ItcsReservationCalendarHelper::getList($days, $list_days_count, $day_format);


// Load Layout
require ModuleHelper::getLayoutPath('mod_itcs_reservation_calendar', $params->get('layout', 'default'));