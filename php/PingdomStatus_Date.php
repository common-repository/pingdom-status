<?php
class PingdomStatus_Date{
	/**
	 * Converts to time span string.
	 *
	 * @param unknown_type $seconds
	 */
	public static function getTimeSpanString($secs){
		if($secs == 0){
			return "0s";
		}
		$vals = array('w' => (int) ($secs / 86400 / 7),
						'd' => $secs / 86400 % 7,
						'h' => $secs / 3600 % 24,
						'm' => $secs / 60 % 60,
						's' => $secs % 60);



		$ret = array();
		foreach ($vals as $k => $v) {
			if ($v > 0) {
				$ret[] = $v . $k;
			}
		}
		
		return join(' ', $ret);
	}
}
?>