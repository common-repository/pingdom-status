<?php
class SensorSummaryData{
	public $id;
	public $name;
	public $type;
	public $uptime;
	public $downtime;
	public $average_responsetime;
	public $outages;
	public $isUp;
	
	public function SensorSummaryData(){
		$this->name = "";
		$this->type = "";
		$this->uptime = "";
		$this->downtime = "";
		$this->outages = "";	
		$this->average_responsetime = "";	
	}
	
	public function as_table_row($long, $l2){
		global $pingdom_PingdomStatus;
		
		// Class for td
		$tdClass = "";
		if($l2){
			$tdClass = "td_l2";
		}
		
		// Determine status class
		$statusClass = "status_down";
		if($this->isUp){
			$statusClass = "status_up";
		}
		
		// Link
		$sensorLink = add_query_arg('sensorId', $this->id);
		
		// Format percentage
		$uptimePercentage = $this->getUptime();
		
		// Format downtime time span
		$downtimeSpan = $this->getDowntime();
		
		$toReturn = "<tr><td class='$tdClass'><a href='$sensorLink' class='$statusClass'>$this->name</a></td>";
		if($long){
			$toReturn .= "<td>$this->type</td>";
			$toReturn .= "<td>$uptimePercentage%</td>";
			$toReturn .= "<td>$downtimeSpan</td>";
			$toReturn .= "<td>$this->outages</td>";
			$toReturn .= "<td>&nbsp;</td>";
		}
		else{
			$toReturn .= "<td>&nbsp;</td>";
		}
		
		$toReturn .= "</tr>";
		return $toReturn;
	}

	public function getUptime(){
		return sprintf("%.2f", $this->uptime);
	}
	
	public function getDowntime(){
		return PingdomStatus_Date::getTimeSpanString($this->downtime);
	}

	public function getAverageResponseTime(){
		return sprintf("%sms", floor($this->average_responsetime));
	}
}
?>
