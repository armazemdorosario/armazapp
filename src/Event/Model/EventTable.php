<?php

namespace Event\Model;

use armazemapp\PDOAdapter;
/**
 *
 * @author Paulo H. (Jimmy) Andrade Mota C.
 *        
 */
class EventTable {
	
	protected $tableGateway;
	
	public function __construct() {
		$this->tableGateway = new PDOAdapter();
	}
	
	public function fetchAll() {
		$resultSet = $this->tableGateway->query('SELECT * FROM `events` WHERE 1;');
		return $resultSet;
	}
	
	public function getEvent( $event_fbid ) {
		$rowset = $this->tableGateway->query("SELECT * FROM `events` WHERE `event_fbid` = '$event_fbid';");
		$row = $rowset->fetch();
		if ( !$row ) {
			throw new \Exception("Could not find event with ID $event_fbid");
		}
		return $row;
	}
	
	public function saveEvent( Event $event ) {
		
		$data = array(
				'event_fbname' => $event->event_fbname,
				'start_date' => $event->start_date,			
		);
		
		$event_fbid = $event->event_fbid;
		
		if ( is_null( $event_fbid ) || empty( $event_fbid ) || !$event_fbid || $event_fbid == 0 ) {
			throw new \Exception( "Event id $event_fbid is not valid" );
		}
		else {
			if( $this->getEvent( $event_fbid ) ) {
				$this->tableGateway->exec( "
						UPDATE `events`
						SET `event_fbname` = '{$data['event_fbname']}',
							`start_date` = '{$data['start_date']}', 
						WHERE `event_fbid` = $event_fbid;" );
			}
			else {
				$this->tableGateway->exec( "
						INSERT INTO `events` (`event_fbid`, `event_fbname`, `start_date`)
						VALUES ('{$data['event_fbname']}', '{$data['start_date']}', '$event_fbid');
				" );
			}
		}
		
	}
	
	public function deleteEvent( $event_fbid ) {
		$this->tableGateway->exec( "DELETE FROM `events` WHERE `event_fbid` = '$event_fbid' LIMIT 1;" );
	}
	
}

?>