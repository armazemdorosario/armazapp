<?php

namespace Event\Model;

/**
 *
 * @author Paulo H. (Jimmy) Andrade Mota C.
 *        
 */
class Event {
	
	public $event_fbid;
	
	public $event_fbname;
	
	public $start_date;
	
	public function exchangeArray($data) {
		$this->event_fbid = (!empty($data['event_fbid'])) ? $data['event_fbid'] : null;
		$this->event_fbname = (!empty($data['event_fbname'])) ? $data['event_fbname'] : null;
		$this->start_date = (!empty($data['start_date'])) ? $data['start_date'] : null;
	}
	
}

?>