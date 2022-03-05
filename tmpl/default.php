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
defined( '_JEXEC' ) or die;
use Joomla\CMS\Language\Text;
use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Date\Date;

?>

<div class="itcs_calendar <?php echo 'id'.$uniqid ?>">
	<div class="itcs_calendar_cal"></div>
  	<?php if ($show_legend==1): ?>
	<!-- Legende -->
    <div class="legend">
		<p class="color0"><?php echo Text::_('CAL_TODAY'); ?></p>
  		<?php if ($color1_text != ""): ?><p class="color1"><?php echo $color1_text; ?></p><?php endif; ?>
  		<?php if ($color2_text != ""): ?><p class="color2"><?php echo $color2_text; ?></p><?php endif; ?>
  		<?php if ($color3_text != ""): ?><p class="color3"><?php echo $color3_text; ?></p><?php endif; ?>
  	</div>
  	<?php endif; ?>
	
	<!-- Liste -->
	<?php if ($chk_access == 1 AND $show_day_list == 1): ?>
	<div style="margin-top:10px;">
		<?php echo $tableList; ?>
	</div>
	<?php endif; ?>
</div>

<!-- Create jQuery for reservation calendar -->
<?php $start = date_format(new DateTime(), 'd.m.Y'); //Tag  ?>

<script type="text/javascript">

	var disableDays_<?php echo 'id'.$uniqid; ?> = <?php echo json_encode($resdays ); ?>;

	jQuery(".itcs_calendar.<?php echo 'id'.$uniqid; ?> .itcs_calendar_cal").datepicker({
		weekStart: 1,
		startDate: "<?php echo $start; ?>",
		todayBtn: "linked",
		language: "<?php echo $lang; ?>",
		forceParse: false,
		todayHighlight: true,
		beforeShowDay: function (date){
			date.setMinutes(date.getMinutes() - date.getTimezoneOffset()); // fix offset to utc
			var chkDate = Math.round(date.getTime()/1000); // get timestamp
			var r ='';
      		var c ='';

			disableDays_<?php echo 'id'.$uniqid; ?>.forEach(disableDay => {
				if(disableDay.t == chkDate){
				r = disableDay.cal_day_info;
				c = 'reserved tt ' + disableDay.cal_day_color;
				}
			});
  			return {tooltip: r, classes: c};
		},
		toggleActive: true
	});   
</script>