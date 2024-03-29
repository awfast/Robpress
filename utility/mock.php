<?php

/** This class is used in testing and should not be modified */

/** DO NOT MODIFY THIS CLASS */
class Mock {

	public $f3;
	public $request = array();
	public $messages = array();
	public $session = array();
	public $params = array();
	public $reroute = false;

	public function __construct($f3) {
		$this->f3 = $f3;
	}

	//Before mocking
	public function start() {
		//Back up session
		$this->session = $this->f3->get('SESSION');
		$this->params = $this->f3->get('PARAMS');
	}

	//After mocking
	public function done() {	
		//Restore session
		$_SESSION = $this->session;
		$this->f3->set('SESSION',$this->session);
		$this->f3->set('PARAMS',$this->params);

		//Restore original login
		$auth = new AuthHelper(null);
		$auth->forceLogin($this->f3->get('SESSION.user'));
		$auth->setupSession($this->f3->get('SESSION.user'));

		//Restore session
		$_SESSION = $this->session;
		$this->f3->set('SESSION',$this->session);
		$this->f3->set('PARAMS',$this->params);
	}

	//Mock a route
	public function run($path,$data=array(),$get=array()) {

		//Remove first /
		if(substr($path,0,1) == '/') {
			$path = substr($path,1);
		}

		//Set default path
		if(empty($path)) { 
			$path = 'blog/index';
		}

		$bits = explode("/",$path);
		$prefix = '';
		$controllerClass = $controller = array_shift($bits);
		if(strtolower($controller) == 'admin') {
			$prefix = 'admin';
			$controller = array_shift($bits);
			$controllerClass = $prefix . '\\' . $controller;
		}

		if(!empty($bits)) {
			$action = array_shift($bits);
		} else {
			$action = 'index';
		}

		$parameters = $bits;

		//Before Route
		$c = new $controllerClass; 
		$c->beforeRoute($this->f3);

		//Setup data
		$c->request->data = $data;
		if(!empty($data)) {
			$c->request->type = 'POST';
			$_POST = $data;
		}
		$_GET = $get;

		//Setup route
		$old_params = $this->f3->get('PARAMS');
		$new_params = explode("/",$path);

		//Remove prefix
		if(!empty($prefix)) {
			array_shift($new_params);
		}

		//Build new path
		$new_path = strtolower($path);
		if(substr($new_path,0,1) != '/') {
			$new_path = "/$new_path";
		}

		array_unshift($new_params,$new_path);
		$new_params['controller'] = strtolower($controller);
		$new_params['action'] = strtolower($action);
		$this->f3->set('PARAMS',$new_params);

		//Run route function
		call_user_func(array($c,$action),$this);
		$messages = StatusMessage::peek();
		$this->messages = $messages;

		//Handle reroute
		if(!empty($this->reroute)) {		
			$reroute = $this->reroute;
			$this->reroute = false;	
			return $this->run($reroute);
		}

		//After Route
		ob_start();
		$c->afterRoute($this->f3);		
		$output = ob_get_contents();
		ob_end_clean();

		//Restore F3
		$this->f3->set('PARAMS',$old_params);

		$this->output = $output;
		return $output;
	}

	//Disable reroute function
	public function reroute($parameters) {
		$this->reroute = $parameters;
		return true;
	}

	//Use F3 normal functions for everything else
	public function __call($name, $arguments) {
		return call_user_func_array(array($this->f3,$name),$arguments);
	}

}
