<?php
class GroupSummaryData{
	public $id;
	public $name;
	public $numOfUpSensors;
	public $numOfDownSensors;
	public $sensors;
	
	/**
	 * Constructs GroupSummaryData object
	 *
	 * @param string $name
	 * @param int $numOfUpSensors
	 * @param int $numOfDownSensors
	 * @param Array of SensorSummaryData $sensors
	 * @param bool true if all sensors in this group are up
	 * @return GroupSummaryData
	 */
	public function GroupSummaryData(){
	}
	
	/**
	 * Gets table row for this group...
	 *
	 * @param type String can be 'small', 'large' (default)
	 * @return unknown
	 */
	public function as_table_row($type = 'large'){		
		global $pingdom_PingdomStatus;
		
		$groupClass = "status_up";
		if($this->numOfDownSensors != 0){
			$groupClass = "status_down";
		}
		
		$groupLink = get_option('home') . '/?groupId='. $this->id;
		$groupLink = $pingdom_PingdomStatus->format_wp_link($groupLink);
		
		$tdParam = '';
		if($type == 'large'){
			$tdParam = "colspan='5' class='group'";
		}
		else {
		}
		
		return "<tr>" . 
					"<td $tdParam>" . 
						"<a href='$groupLink' class='$groupClass'>$this->name</a>".
					"</td>".
					"<td>".
						"(<span class='resolved'>$this->numOfUpSensors</span>/<span class='unresolved'>$this->numOfDownSensors</span>)".
					"</td>".
				"</tr>";
	}
}
?>