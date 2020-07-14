<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class WebServices {
	
	protected $curl;
	protected $config;
	protected $valueMap;
	
	/**
	 * WebServices Constructor
	 * Asigna constantes a la clase
	 * @params <Array> $values - Array de variables por defecto
	 */
	
	function  __construct($values=array(), $config=array()) {
		$this->valueMap = $values;
		$this->curl = curl_init();
		$this->config = array_merge([], $config);
		$this->config['userAccessKey'] = $config['accesskey'];
		$this->config['webservice'] = $config['url'].'/webservice.php';
		$this->config = array_merge($this->config, $config);
		$params = array('operation'=>'getchallenge', 'username'=>$this->config['username']);
		$response = $this->exec($params);
		if(!$response->success) {
			throw new Exception($response);
		}
		$generatedKey = md5($response->result->token.$this->config['userAccessKey']);
		$params = array('operation'=>'login', 'username'=>$this->config['username'], 'accessKey'=>$generatedKey);
		$response = $this->exec($params, true);
		if(!$response->success) {
			throw new Exception($response);
		}
		$this->set('assigned_user_id', $response->result->userId);
		$this->config['sessionName'] = $response->result->sessionName;
	}
	
	/**
	 * WebServices Destructor
	 * Limpia las variables y cierra el curl
	 */
	
	function __destruct() {
		curl_close($this->curl);
	}
	
	/**
	 * Envia los datos para ser salvado en el servidor
	 * @params <String> $key - nombre de la llave para el objeto
	 * @params <string> $value - valor de la llave
	 * @return <Object> WebService - retorna el objeto en si
 	 */
	
	public function set($key,$value) {
		$this->valueMap[$key] = $value;
		return $this;
	}
	
	/**
	 * Function to get the value for a given key
	 * @param <String> $key - nombre de la llave para el objeto
	 * @return <Object> $value - retorna el valor del objecto
	 */
	
	public function get($key){
		return $this->valueMap[$key];
	}
	
	/**
	 * Function to get all the values of the Object
	 * @return Array (key-value mapping)
	 */
	
	public function getData(){
		return $this->valueMap;
	}
	
	/**
	 * Function que ejecuta peticiones al api de webservice
	 * @param <Array> $params - parametros para enviar al webservice
	 * @return <Object> $post - retorna el valor del objecto
	 */
	
	private function exec($params=array(), $post=false) {
		$query = http_build_query($params);
		if($post) {
			curl_setopt($this->curl, CURLOPT_POST, true);
			curl_setopt($this->curl, CURLOPT_POSTFIELDS, $query);
			curl_setopt($this->curl, CURLOPT_URL, $this->config['webservice']);
		} else {
			curl_setopt($this->curl, CURLOPT_HTTPGET, true);
			curl_setopt($this->curl, CURLOPT_URL, $this->config['webservice'].'?'.$query);
		}
		curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
		return json_decode(curl_exec($this->curl));
	}
	
	
	/**
	 * Function para hacer querys segun la documentacion de Vtiger, limite de 100
	 * @return Array (key-value mapping)
	 */
        public function query($query='') {
		$params = array(
			'query'	=> $query,
			'operation' => 'query',
			'sessionName' => $this->config['sessionName']
		);
		$result = array();
		return $this->exec($params);
	}
	
		/**
		 * Function para hacer querys segun la documentacion de Vtiger, limite de 100
		 * @return Array (key-value mapping)
		 */
		public function create($module='Accounts') {
			$params = array(
				'operation' => 'create',
				'elementType' => $module,
				'element' => json_encode($this->getData()),
				'sessionName' => $this->config['sessionName']
			);
			$result = array();
			return $this->exec($params, true);
		}

		/**
		 * Function para eliminar registros segun la documentacion de Vtiger, limite de 100
		 * @return Array (key-value mapping)
		 */
		public function delete($module, $id) {	
			$params = array(
				'operation' => 'delete',
				'elementType' => $module,
				'id' => $id,
				'sessionName' => $this->config['sessionName']
			);
			$result = array();
			return $this->exec($params, true);
		}

        /**
         * Funcion para crear un leed de rdstation
         * @param <String> - Lead en json de rdstation
         */
        public function rdstation($data) {
                $params = array(
                        'element' => $data,
                        'operation' => 'rdstation',
                        'sessionName' => $this->config['sessionName']
                );
                $result = array();
                $response = $this->exec($params, true);
                if($response->success) {
                        $result = $response->result;
                } else {
                        throw new Exception($response);
                }
                return $result;
		}
		
		/**
         * Funcion para consumir webservices de panel de control
         * @param <Array> $data, <String> $operation
         */
		public function controlPanel($data,$operation) {
            $params = array(
                'element' => json_encode($data),
                'operation' => $operation,
                'sessionName' => $this->config['sessionName']
            );
            $result = array();
            $response = $this->exec($params, true);
            if($response->success) {
                $result = $response->result;
            } else {
                throw new Exception($response);
            }
            return $result;
        }

    /**
     * Funcion para conectar con woocommerce
     * @param <String> - data in json woocommerce
     */
    public function woocommerce($data) {
        $params = array(
            'element' => $data,
            'operation' => 'woocommerce',
            'sessionName' => $this->config['sessionName']
        );
        $result = array();
        $response = $this->exec($params, true);
        if($response->success) {
            $result = $response->result;
        } else {
            throw new Exception($response);
        }
        return $result;
    }

	/**
	 * Function para obterner todos los id de
	 * @param <Array> $params - parametros para enviar al webservice
	 * @return <Object> $post - retorna el valor del objecto
	 */
	public function assignedRoleUserNext($roleid) {
		$_users_ = array();
		$assigned_user_id = null;
		$database = 'assigned_user_id.db';
		$query = "select id from Users where roleid='$roleid';";
		$users = $this->query($query);
		foreach($users AS $user) {
			$_users_[] = $user->id;
		}
		$userid = explode('x', file_get_contents($database))[1];
		if(is_null($userid)) {
			$userid = '1';
		}
		foreach($users as $user) {
			$id = explode('x', $user->id)[1];
			if($userid<$id) {
				$assigned_user_id = $user->id;
				break;
			}
		}
		if(is_null($assigned_user_id)) {
			$assigned_user_id = $users[0]->id;
		}
		$this->set('assigned_user_id', $assigned_user_id);
		file_put_contents($database, $assigned_user_id);
		return $assigned_user_id;
	}
	
	/**
	 * Obtien un contacto ya existente del sistema, envia las variables
	 * $this->set('email', '@');
	 */
	public function getSettingsByEmail($email='') {
		$contact_id = null;
		$query = "select id, account_id, assigned_user_id from Contacts where email='$email';";
		$contacts = $this->query($query);
		if(count($contacts)) {
			// @note - toma el primero
			$contact = $contacts[0];
			$contact_id = $contact->id;
			$this->set('contact_id', $contact->id);
			$this->set('account_id', $contact->account_id);
			$this->set('assigned_user_id', $contact->assigned_user_id);
			$this->config['targetmodule'] = 'Potentials';
		}
		return $contact_id;
	}

	/**
     * Obtien un contacto ya existente del sistema, envia las variables
     * $this->set('email', '@');
     */
	public function getSettingsByPhone($phone='') {
		$contact_id = null;
        $query = "select id, account_id, assigned_user_id from Contacts where phone='$phone';";
        $contacts = $this->query($query);
        if(count($contacts)) {
			// @note - toma el primero
            $contact = $contacts[0];
            $contact_id = $contact->id;
            $this->set('contact_id', $contact->id);
            $this->set('account_id', $contact->account_id);
            $this->set('assigned_user_id', $contact->assigned_user_id);
			$this->config['targetmodule'] = 'Potentials';
		}
		return $contact_id;
	}

	/**
	 * process
	 * Envia las peticion para crear los modulos
	 */
	public function process() {
		$response = null;

		$targetModules = explode(' |##| ', $this->config['targetmodule']);
		$targetModules = implode(',',$targetModules);
		$this->set('actors_create', $targetModules);
		$params = array(
			'operation' => 'createAll',
			'element' => json_encode($this->getData()),
			'sessionName' => $this->config['sessionName']
		);
		$this->set('error', '');
		$this->set('response', '');
		$response = $this->exec($params, true);
		if($response->success) {
			$this->set('response', $response->result);
		} else {
			$response->error->action = 'actors_create';
			$this->set('error', $response->error);
			return $this->getData();
		}

		$this->set('redirect', $this->config['redirect']);
		return $this->getData();
	}
	
}

?>

