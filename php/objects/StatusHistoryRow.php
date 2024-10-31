<?php
require_once(ABSPATH . 'wp-content/plugins/pingdom-status/php/PingdomStatus_Date.php');
class StatusHistoryRow{
	public $dateFrom;
	public $dateTo;
	public $spanDowntime;
	public $messageHeading;
	public $messageId;
	
	public function StatusHistoryRow(){
		
	}
	
	/**
	 * Gets this object to be displayed as table row.
	 *
	 */
	public function as_table_row(){
		global $pingdom_PingdomStatus;
		
		$messageLink = get_option('home') . '/?p='. $this->messageId;
		$messageLink = $pingdom_PingdomStatus->format_wp_link($messageLink);
		
		$timeSpan = PingdomStatus_Date::getTimeSpanString($this->spanDowntime);
		return "
		<tr>
            <td>$this->dateFrom</td>
            <td>$this->dateTo</td>
            <td>$timeSpan</td>
            <td><a href='$messageLink'>$this->messageHeading</a></td>
         </tr>";
	}
}
?>