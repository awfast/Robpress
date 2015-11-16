<?php

	namespace Admin;

	class Settings extends AdminController {

		public function index($f3) {
			$settings = $this->Model->Settings->fetchAll();
			if($this->request->is('post')) {
				foreach($settings as $setting) {
					if(isset($this->request->data[$setting->setting])) {
						$setting->value = $this->request->data[$setting->setting];
						$setting->save();
					} else {
						$setting->value = 0;
						$setting->save();
					}
				}
				\StatusMessage::add('Settings updated','success');
			}
			$f3->set('settings',$settings);
		}

	}

?>
