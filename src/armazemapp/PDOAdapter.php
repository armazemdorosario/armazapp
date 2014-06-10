<?php

namespace armazemapp;

use \PDO;
use \PDOException;

/**
 *
 * @author Jimmy Andrade
 *        
 */
class PDOAdapter extends PDO {

	private $config = array(
		'PDOURI' => 'mysql:host=localhost;dbname=gagito_armazemapp;charset=utf8',
		'databaseUser' => 'gagito_armazapp',
		'databasePassword' => '~PPGtivZc8f~',
	);
	
	public function __construct () {
		try {
			parent::__construct ( $this->config['PDOURI'], $this->config['databaseUser'], $this->config['databasePassword'] );
			parent::setAttribute (PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}
		catch (PDOException $e) {
			error_log('Failed to connect to database. ' . $e->getMessage());
			return NULL;
		}
	}
	
	public function query($statement, $returnFetch = true) {
		$returnValue = false;
		try {
			$query = parent::prepare($statement);
			$query->execute();
			$returnValue = $returnFetch ? $query->fetchAll(PDO::FETCH_ASSOC) : $query;			
			if(!is_object($query)) {				$error_message = 'Objeto nulo retornado ao tentar executar consulta ' . $statement;				mail('webmaster@armazemdorosario.com.br', 'Erro', $error_message); error_log($error_message, E_USER_ERROR);
				$returnValue = null;
			}
				}
		catch(PDOException $e) {			$error_message = 'Falha ao realizar consulta ao banco de dados. ' . $e->getMessage();			mail('webmaster@armazemdorosario.com.br', 'Erro', $error_message); error_log($error_message, E_USER_ERROR);				$returnValue = json_encode($e);
		}
		return $returnValue;		
	}
	
	public function rowCount($statement) {
		$query = $this->query($statement, false);
		
		if(is_object($query)) {
			return $query->rowCount();
		} else {
			return -1;
		}
	} 
	
}

?>