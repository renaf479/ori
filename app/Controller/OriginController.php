<?php

class OriginController extends AppController {
	public $helpers 	= array('Form', 'Html', 'Session', 'Js', 'Usermgmt.UserAuth', 'Minify.Minify');
	public $components 	= array('Session', 'RequestHandler', 'Usermgmt.UserAuth');
	public $uses		= array('OriginAd', 
								'OriginComponent',
								'OriginDemo',
								'OriginSite',
								'OriginTemplate',
								'OriginAdSchedule', 
								'OriginAdDesktopInitialContent', 
								'OriginAdDesktopTriggeredContent',
								'OriginAdTabetInitialContent', 
								'OriginAdTabletTriggeredContent',
								'OriginAdMobileInitialContent', 
								'OriginAdMobileTriggeredContent',
								'Usermgmt.User',
								'Usermgmt.UserGroup', 
								'Usermgmt.LoginToken');

	public function beforeFilter() {
		parent::beforeFilter();
		$this->User->userAuth = $this->UserAuth;
	}
	
/* =======================================================================
	General
========================================================================== */
	/**
	* POST data router
	*/
	public function post() {
		$this->autoRender = false;
		if($this->request->data['route']) {
			$route		= $this->request->data['route'];
			unset($this->request->data['route']);
			$response	= $this->$route($this->request->data);
			$this->set('post', $response);
		}
	}
	
	/**
	* Email demo page
	*/
	public function emailDemo($data) {
		App::uses('CakeEmail', 'Network/Email');
		
		$email 	= new CakeEmail();
		$email->template('demo');
		$email->emailFormat('html');
		$email->from(array('willie.fu@gmail.com'=>'Evolve Origin'));
		$email->to('willie.fu@gmail.com');
		$email->subject('[Evolve Origin] Demo Page (ID'+$data['ad']['id']+') - '.$data['ad']['name']);
		$email->viewVars(array('data' => $data));
		$test = $email->send();
		
		print_r($test);
	}
	
	/**
	* Email embed code
	*/
	public function emailEmbed($data) {
		App::uses('CakeEmail', 'Network/Email');
		
		$email 	= new CakeEmail();
		$email->template('embed');
		$email->emailFormat('html');
		$email->from(array('willie.fu@gmail.com'=>'Evolve Origin'));
		$email->to('willie.fu@gmail.com');
		$email->subject('[Evolve Origin] Project Details (ID'+$data['ad']['id']+') - '.$data['ad']['name']);
		$email->viewVars(array('data' => $data));
		$test = $email->send();
		
		print_r($test);
	}
	
	
	
	/**
	* System-wide AJAX file uploader
	*/
	public function upload() {
		App::import('Vendor', 'UploadHandler', array('file'=>'UploadHandler/uploadHandler.class.php'));
		
		$upload_handler = new UploadHandler();
		header('Pragma: no-cache');
		header('Cache-Control: private, no-cache');
		header('Content-Disposition: inline; filename="files.json"');
		header('X-Content-Type-Options: nosniff');
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: OPTIONS, HEAD, GET, POST, PUT, DELETE');
		header('Access-Control-Allow-Headers: X-File-Name, X-File-Type, X-File-Size');
		
		switch($_SERVER['REQUEST_METHOD']) {
			case 'POST':
				$upload_handler->post();
		        break;
		    default:
		        header('HTTP/1.1 405 Method Not Allowed');
		}
		
		exit;
	}

	public function index() {
		$this->set('title_for_layout', 'Dashboard');
/*
		$ad_units	= $this->Creator->find('all');
		//print_r($ad_units);
		$this->set(array(
			'ad_units'=>$ad_units,
			'_serialize'=>array('ad_units')
		));
*/
	}
	
	/**
	* 
	*/
	public function modal() {
		$this->layout 	= 'modal';
		$template 		= $this->request->params['template'];
		
		$this->set('title_for_layout', ucfirst($template));
		$this->set('template', $template);
	}

	/**
	* ?
	*/
	public function templateEdit($id) {
		
	}

	/**
	* Toggles the 'status' field of a model
	*/
	private function toggleStatus($data) {
		$data['modify_date']	= date('Y-m-d H:i:s');
		$data['modify_by']		= $this->UserAuth->getUserId();
		
		if($this->{$data['model']}->save($data)) {
			return $this->{'_load'.$data['model']}();
		}
	}
	
	
	/**
	* Generic remove function for system-level ops
	*/
	private function systemRemove($data) {
		if($this->{$data['model']}->delete($data['id'])) {
			return $this->{'_load'.$data['model']}();	
		}
	}
	
	/**
	* Generic save function for system-level ops
	*/
	private function systemSave($data) {
		if(isset($data['content'])) {
			$data['content']		= json_encode($data['content']);
		}
		
		if(isset($data['config'])) {
			$data['config']			= json_encode($data['config']);
		}
		
		if(!isset($data['id'])) {
			$data['create_by']	= $this->UserAuth->getUserId();
		}
		
		$data['modify_date']	= date('Y-m-d H:i:s');
		$data['modify_by']		= $this->UserAuth->getUserId();
		
		if($this->{$data['model']}->save($data)) {
			return $this->{'_load'.$data['model']}();
		}
	}
	
	/**
	* System level folder removal
	*/
	private function _removeFolder($dir) {
	    $files = scandir($dir);
	    array_shift($files);    // remove '.' from array
	    array_shift($files);    // remove '..' from array
	    
	    foreach ($files as $file) {
	        $file = $dir . '/' . $file;
	        if (is_dir($file)) {
	            $this->_removeFolder($file);
	            rmdir($file);
	        } else {
	            unlink($file);
	        }
	    }
	    rmdir($dir);
	}
	
	
/* =======================================================================
	Public: Ad rendering
========================================================================== */
	/**
	* Displays the ad
	*/
	public function ad($originAd_state = '') {
		$this->layout 	= 'ad';
		
		$originAd_id		= $this->request->params['originAd_id'];
		$originAd_platform	= $this->request->params['originAd_platform'];
		//$originAd_state		= $originAd_state;
		
		$origin_ad		= $this->OriginAd->find('first', 
			array(
				'conditions'=>array(
					'OriginAd.id'=>$originAd_id
				),
				'contain' => array(
					'OriginAdSchedule'=>array(
						'OriginAd'.$originAd_platform.'InitialContent',
						'OriginAd'.$originAd_platform.'TriggeredContent'
					)
				)
			)
		);
		
		//If content doesn't exist, fallback to Desktop version
		if(sizeof($origin_ad['OriginAdSchedule'][0]['OriginAd'.$originAd_platform.'InitialContent']) === 0) {
			$originAd_platform	= 'Desktop';
			$origin_ad		= $this->OriginAd->find('first', 
				array(
					'conditions'=>array(
						'OriginAd.id'=>$originAd_id
					),
					'contain' => array(
						'OriginAdSchedule'=>array(
							'OriginAd'.$originAd_platform.'InitialContent',
							'OriginAd'.$originAd_platform.'TriggeredContent'
						)
					)
				)
			);
		}
		
		$this->set('origin_ad', $origin_ad);
		$this->set('originAd_platform', $originAd_platform);
		$this->set('originAd_state', $originAd_state);
		$this->set('title_for_layout', $origin_ad['OriginAd']['name']);	
	}
	
	/**
	* Displays the embed content wrapped inside an iframe
	*/
	public function adIframe() {
		$this->layout	= 'adIframe';
		///adIframe/:originAd_model/:originAd_contentId
		$originAd_model		= $this->request->params['originAd_model'];
		$originAd_contentId	= $this->request->params['originAd_contentId'];
		
		$origin_ad		= $this->{$originAd_model}->find('first', 
			array(
				'conditions'=>array(
					$originAd_model.'.id'=>$originAd_contentId
				),
				'recursive'=>-1
			)
		);
		$origin_ad 		= json_decode($origin_ad[$originAd_model]['content']);
		
		$this->set('origin_ad', $origin_ad);
	}
	
/* =======================================================================
	Public: Spec sheets/guidelines
========================================================================== */
	/**
	* Displays ad template guidelines
	*/
	public function guidelines() {
		$specsheet	= $this->OriginTemplate->find('first', 
			array(
				'conditions'=>array(
					'OriginTemplate.alias'=>$this->request->params['specsheet_alias']
				)
			)
		);
		$specsheet				= $specsheet['OriginTemplate'];
		$specsheet['content']	= json_decode($specsheet['content']);
		$specsheet['config']	= json_decode($specsheet['config']);
		
		$this->set('specsheet', $specsheet);
		$this->set('title_for_layout', $specsheet['name'].' Guidelines');
	}

/* =======================================================================
	Ad components
========================================================================== */
	/**
	* Loads the component model
	*/
	private function _loadOriginComponent() {
		$this->layout	= 'ajax';
		$origin_components	= $this->OriginComponent->find('all', 
			array('order'=>array('OriginComponent.name ASC'))
		);
		$this->set('origin_components', $origin_components);
		return $this->render('/Origin/json/json_component');
	}

	/**
	* Origin ad component manager
	*/
	public function componentList() {
		$this->set('title_for_layout', 'Ad Components');
	}
	
	/**
	* Loads a specified ad component
	*/
	public function loadComponent() {
		$this->layout 	= 'components';
		$component 		= $this->request->params['component'];
		$this->set('component', $component);
	}

/* =======================================================================
	Ad Templates
========================================================================== */
	/**
	* Loads the template model
	*/
	private function _loadOriginTemplate() {
		$this->layout	= 'ajax';
		$origin_templates	= $this->OriginTemplate->find('all', 
			array('order'=>array('OriginTemplate.name ASC'))
		);
		$this->set('origin_templates', $origin_templates);
		return $this->render('/Origin/json/json_template');
	}
	
	/**
	* Origin ad template manager
	*/
	public function templateList() {
		$this->set('title_for_layout', 'Ad Templates');
	}
	
/* =======================================================================
	Demo page of Origin units (both administrator and public)
========================================================================== */
	/**
	* Loads the site demo list
	*/
	private function _loadOriginSite() {
		$this->layout	= 'ajax';
		$origin_sites	= $this->OriginSite->find('all', 
			array('order'=>array('OriginSite.name ASC'))
		);
		$this->set('origin_sites', $origin_sites);
		return $this->render('/Origin/json/json_site');
	}

	/**
	* Loads the model data
	*/
	private function _loadDemos() {
		$this->layout	= 'ajax';
		$origin_demos	= $this->OriginDemo->find('all',
		array(
			'order'=>array('OriginDemo.name ASC')
		));
		$this->set('origin_demos', $origin_demos);
		return $this->render('/Origin/json/json_demo');
	}
	
	/**
	* Public demo page viewer
	*/
	public function demo() {
		$this->layout 	= 'demo_public';
		
		$demo = $this->OriginDemo->find('first', 
			array(
				'conditions'=>array(
					'OriginDemo.alias'=>$this->request->params['alias']
				)
			)
		);
		
		if($demo['OriginDemo']['status'] !== '1') {
			throw new NotFoundException();
		}
		
		$demo['OriginDemo']['config']	= json_decode($demo['OriginDemo']['config']);
		$this->set('demo', json_encode($demo));
		$this->set('title_for_layout', $demo['OriginDemo']['name']);
	}
	
	/**
	* Listing of all saved demo pages
	*/
	public function demoList() {
		$this->set('title_for_layout', 'Demo Listing');
	}
	
	/**
	* Create a demo page
	*/
	public function demoCreate() {
		$this->layout 	= 'demo';
		$origin_ad		= $this->OriginAd->find('first', 
			array(
				'conditions'=>array(
					'OriginAd.id'=>$this->request->params['originAd_id']
				),
				'recursive'=>-1
			)
		);
		
		$origin_ad['OriginAd']['config']	= json_decode($origin_ad['OriginAd']['config']);
		$origin_ad['OriginAd']['content']	= json_decode($origin_ad['OriginAd']['content']);
		
		$this->set('demoEdit', false);
		$this->set('origin_ad', json_encode($origin_ad));
		$this->set('title_for_layout', $origin_ad['OriginAd']['name'].' Demo');
		
		
		//$this->jsonAdUnit($this->request->params['originAd_id']);
		//$this->render('/Origin/json/json_ad_unit');	
	}
	
	/**
	* Edit a demo page
	*/
	public function demoEdit() {
		$this->layout 	= 'demo';
		$origin_demo 	= $this->OriginDemo->find('first', 
			array(
				'conditions'=>array(
					'OriginDemo.alias'=>$this->request->params['alias']
				),
				'recursive'=>-1
			)
		);
		
		$origin_demo['OriginDemo']['config']	= json_decode($origin_demo['OriginDemo']['config']);
		
		$this->set('origin_ad', json_encode($origin_demo));
		$this->set('demoEdit', true);
		$this->set('title_for_layout', $origin_demo['OriginDemo']['name'].' Demo');
		
		$this->render('/Origin/demo_create');
	}
	
	/**
	* Delete a demo page
	*/
	public function demoDelete($data) {
		if($this->OriginDemo->delete($data['id'])) {
			$this->layout	= 'ajax';
			$this->jsonDemo($data);
			return $this->render('/Origin/json/json_demo');
		}
	}
	
	/**
	* Default Origin Demo page
	*/
	public function demoOrigin() {
		$this->layout 	= 'demo_default';
		$origin_ad		= $this->OriginAd->find('first', 
			array(
				'recursive'=>-1,
				'conditions'=>array(
					'OriginAd.id'=>$this->request->params['originAd_id']
				),
				'fields'=>array(
					'OriginAd.id',
					'OriginAd.name',
					'OriginAd.type'
				)
			)
		);
		
		$this->set('title_for_layout', $origin_ad['OriginAd']['name']);
		$this->set('origin_ad', json_encode($origin_ad));
	}

	/**
	* Loads a specified demo page template
	*/
	public function demoLoadTemplate() {
		$this->layout 	= 'templates';
		$template 		= $this->request->params['template'];
		$this->set('template', $template);
	}
	
	/**
	* Origin demo manager
	*/
	public function demoManager() {
		$this->set('title_for_layout', 'Demo Manager');
	}
	
	/**
	* Save/update an Origin site demo page
	*/
	private function demoSave($data) {
		$data['config']			= json_encode($data['config']);
		
		if(!isset($data['id'])) {
			$data['create_by']	= $this->UserAuth->getUserId();
		}
		
		$data['modify_date']	= date('Y-m-d H:i:s');
		$data['modify_by']		= $this->UserAuth->getUserId();
		
		if($this->OriginDemo->save($data)) {
			if(empty($data['alias'])) {
				App::import('Vendor', 'pseudocrypt');
				$aliasData['id']	= $this->OriginDemo->id;
				$aliasData['alias'] = $data['alias'] = PseudoCrypt::hash($aliasData['id'], 6);	
				$this->OriginDemo->save($aliasData);

			}
			echo $data['alias'];
		}
	}
	
	/**
	* Toggle an Origin site demo status
	*/
	private function demoStatus($data) {
		$data['modify_date']	= date('Y-m-d H:i:s');
		$data['modify_by']		= $this->UserAuth->getUserId();
		
		if($this->OriginDemo->save($data)) {
			return $this->_loadDemos();
		}
	}
	
/* =======================================================================
	Site Demo Template
========================================================================== */
	/**
	* Origin site manager
	*/
	public function siteList() {
		$this->set('title_for_layout', 'Demo Manager');
	}
	
/* =======================================================================
	Settings
========================================================================== */		
	/**
	* Settings page
	*/
	public function settings() {
		$this->set('title_for_layout', 'Settings');
	}
	
	/**
	* Origin system permissions page //UNUSED?
	*/
/*
	public function dashboardAccess() {
		$this->set('title_for_layout', 'System Settings');
	}
*/
	
	/**
	* Adds a new user permissions group
	*/
	private function dashboardGroupAdd($data) {
		$this->UserGroup->set($data);
		if($this->UserGroup->addValidate()) {
			$this->UserGroup->save($data, false);
		}
	}
	
	/**
	* ?
	*/
/*
	public function dashboardUser() {
		
	}
*/
	
	/**
	* Creates a new user
	*/
	private function dashboardUserAdd($data) {
		if($this->User->RegisterValidate()) {
			$data['email_verified']		= 1;
			$data['active']				= 1;
			$salt						= $this->UserAuth->makeSalt();
			$data['salt'] 				= $salt;
			$data['password'] 			= $this->UserAuth->makePassword($data['password'], $salt);
			$this->User->save($data,false);
		} else {
			return json_encode($this->User->invalidFields());
		}
	}
	
	/**
	* Updates an user's password
	*/
	private function dashboardUserPasswordUpdate($data) {
		$userId = $this->UserAuth->getUserId();		
		$this->User->set($data);
		
		if($this->User->RegisterValidate()) {
			$user	= array();
			$user['User']['id']=$userId;
			$salt=$this->UserAuth->makeSalt();
			$user['User']['salt'] = $salt;
			$user['User']['password'] = $this->UserAuth->makePassword($data['password'], $salt);
			$this->User->save($user,false);
			$this->LoginToken->deleteAll(array('LoginToken.user_id'=>$userId), false);
		} else {
			return json_encode($this->User->invalidFields());
		}
	}
	
	/**
	* Toggles an user's status
	*/
	private function dashboardUserStatus($data) {
		$userId			= $data['id'];
		$active			= $data['status'];
		
		if (!empty($userId)) {
			$user=array();
			$user['User']['id']=$userId;
			$user['User']['active']=($active) ? 1 : 0;
			$this->User->save($user,false);
		}	
	}
	
	/**
	* Updates an user's account
	*/
	private function dashboardUserUpdate($data) {		
		if(isset($data['cpassword'])) {
			$this->User->set($data);
			
			if($data['password'] === $data['cpassword'] && !empty($data['password'])) {
				$salt				= $this->UserAuth->makeSalt();
				$data['salt'] 		= $salt;
				$data['password'] 	= $this->UserAuth->makePassword($data['password'], $salt);
				if($this->User->RegisterValidate()) {
					$this->User->save($data, false);
				} else {
					return json_encode($this->User->invalidFields());
				}
			} else {
				unset($data['password']);
			}
		} else {
			unset($data['salt']);
			unset($data['password']);
			unset($data['cpassword']);
			unset($data['email_verified']);
			unset($data['active']);
			unset($data['ip_address']);
			unset($data['created']);
			unset($data['modified']);
			$this->User->set($data);
			
			if($this->User->RegisterValidate()) {
				$this->User->save($data, false);
			} else {
				return json_encode($this->User->invalidFields());
			}
		}	
	}
	
/* =======================================================================
	JSON feeds
========================================================================== */	
	/**
	* JSON feed of user activity
	*/
	public function jsonActivity() {
		
		$activities = $this->OriginAd->query('
						SELECT id, name, modify_by as userid, date, action 
						FROM (
							SELECT id, name, create_by, modify_by, date, action 
							FROM (
								SELECT id, name, create_by, modify_by, create_date as "date", "created" as "action"
								FROM origin_ads ORDER BY create_date DESC LIMIT 30
								) AS A
							UNION ALL (
								SELECT id, name, create_by, modify_by, modify_date as "date", "updated" as "action"
								FROM origin_ads ORDER BY modify_date DESC LIMIT 30
								)
							) AS activity
						ORDER BY date DESC LIMIT 10');
		
		$users		= $this->User->find('all');
		$this->set('activities', $activities);
		$this->set('users', $users);
	}



	/**
	* JSON feed of the specified Origin ad template
	*/
	public function jsonAdTemplate() {
		$template_id		= $this->request->params['template_id'];
		$origin_template	= $this->OriginTemplate->find('first', 
			array(
				'conditions'=>array('OriginTemplate.id'=>$template_id)
			)
		);
		$this->set('origin_template', $origin_template);
	}
	
	/**
	* JSON feed of a specified Origin ad unit
	*/
	public function jsonAdUnit($originAd_id = '') {
		$originAd_id 	= ($originAd_id)? $originAd_id: $this->request->params['originAd_id'];
		$origin_ad		= $this->OriginAd->find('first', 
			array(
				'recursive'=>2,
				'conditions'=>array(
					'OriginAd.id'=>$originAd_id
				)
			)
		);
		$this->set('origin_ad', $origin_ad);
		return $origin_ad;
	}
	
	/**
	* JSON feed of a demos for a specific ad unit
	*/
	public function jsonDemo($data = '') {
		$originAd_id	= ($data)? $data['origin_ad_id']: $this->request->params['originAd_id'];
		$origin_demo 	= $this->OriginDemo->find('all', 
			array(
				'conditions'=>array(
					'OriginDemo.origin_ad_id'=>$originAd_id
				)
			)
		);
		$this->set('origin_demo', $origin_demo);
	}
	
	/**
	* JSON feed of all demos
	*/
	public function jsonDemoList() {
		
	}
	
	/**
	* JSON feed of a specific Origin ad unit's library
	*/
	public function jsonLibrary() {
		$this->set('originAd_id', $this->request->params['originAd_id']);	
	}
	
	/**
	* JSON feed of all Origin ad units
	*/
	public function jsonList() {
		$origin_ads		= $this->OriginAd->find('all', 
			array(
				'order'=>array('OriginAd.id DESC'),
				'recursive'=>-1
			));
		$users			= $this->User->find('all');
		
		$this->set('origin_ads', $origin_ads);
		$this->set('users', $users);
	}
	
	/**
	* JSON feed of all showcase Origin ad units
	* UNUSED
	*/
	public function jsonListShowcase() {
		
		$origin_ads		= $this->OriginAd->find('all', 
			array(
				'conditions'=>array(
					'OriginAd.status'=>1,
					'OriginAd.showcase'=>1,
					'OriginAd.type'=>$this->request->params['type']
				),
				'fields'=>array(
					'OriginAd.id',
					'OriginAd.name',
					'OriginAd.config',
					'OriginAd.content',
					'OriginAd.status',
					'OriginAd.showcase'),
				'order'=>array('OriginAd.id DESC'),
				'recursive'=>2
			));
		$this->set('origin_ads', $origin_ads);
	}
	
	/**
	* JSON feed of all Origin ad components
	*/
	public function jsonComponent() {
		$origin_components	= $this->OriginComponent->find('all',
			array(
				'order'=>array('OriginComponent.name ASC')
			)
		);
		$this->set('origin_components', $origin_components);
	}
	
	/**
	* JSON feed of all Origin demo sites
	*/
	public function jsonSite() {
		$origin_sites	= $this->OriginSite->find('all', 
			array(
				'order'=>array('OriginSite.name ASC')
			)
		);
		$this->set('origin_sites', $origin_sites);
	}
	
	/**
	* JSON feed of all Origin ad templates
	*/
	public function jsonTemplate() {
		$origin_templates	= $this->OriginTemplate->find('all',
			array(
				'order'=>array('OriginTemplate.name ASC')
			)
		);
		$this->set('origin_templates', $origin_templates);
	}
	
	/**
	* JSON feed of unique Origin Ad Templates for the homepage
	*/
	public function jsonTemplateHome() {
		$origin_templates	= $this->OriginTemplate->find('all',
			array(
				'conditions'=>array(
					'OriginTemplate.status'=>1,
					'OriginAds.showcase'=>1
				),
				'fields'=>array(
					'OriginTemplate.*',
					'OriginAds.*'
				),
				'joins'=>array(
					array(
						'table'=>'origin_ads',
						'alias'=>'OriginAds',
						'type'=>'LEFT',
						'conditions'=>array(
							'OriginAds.type_id = OriginTemplate.id'
						)
					)
				),
				'order'=>array('OriginTemplate.name ASC')
			)
		);
		$this->set('origin_templates', $origin_templates);
	}
	
	/**
	* RSS parser
	*/
	public function rssFeed() {
		App::import('Vendor', 'xmlToArray');
		$this->layout	= 'xml/default';
		
		$xml = simplexml_load_file('http://'.$this->request->params['url']);
		$this->set('xml', xmlToArray::convert($xml));
	}
/* =======================================================================
	Origin Ad Creator
========================================================================== */
	/**
	* 
	*/
	private function _adModifyUpdate($originAd_id) {
		$data['id']				= $originAd_id;
		$data['modify_by']		= $this->UserAuth->getUserId();
		$data['modify_date']	= date('Y-m-d H:i:s');
		$this->OriginAd->save($data);
	}
	
	/**
	* Displays a listing of all Origin ad units
	*/
	public function ad_list() {
		$this->set('title_for_layout', 'Origin Ads');
		$this->render('list');
	}
	
	/**
	* Opens Origin's ad creator
	*/
	public function edit() {
		$origin_ad		= $this->OriginAd->find('first', 
			array(
				'recursive'=>-1,
				'conditions'=>array(
					'OriginAd.id'=>$this->request->params['originAd_id']
				)
			)
		);
		$this->set('origin_ad', $origin_ad);
		$this->set('title_for_layout', $origin_ad['OriginAd']['name'].' - Ad Creator');
	}
	
	/**
	* Loads the current ad unit in JSON format
	*/
	private function _creatorAdLoad($data) {
		$this->layout	= 'ajax';
		$this->jsonAdUnit($data['originAd_id']);
		return $this->render('/Origin/json/json_ad_unit');
	}
	
	/**
	* Deletes an Origin ad unit
	*/
	private function adDelete($data) {
		if($this->OriginAd->delete($data['id'], true)) {
			//Remove folder
			$this->_removeFolder('../webroot/assets/creator/'.$data['id']);
		
			$this->layout	= 'ajax';
			$this->jsonList();
			return $this->render('/Origin/json/json_list');
		}
	}
	
	/**
	* Creates a new Origin ad unit
	*/
	private function adCreate($data) {
		$tempContent			= $data['content'];
		$data['content']		= json_encode($data['content']);
		$data['config']			= json_encode($data['config']);
		$data['create_by']		= $this->UserAuth->getUserId();
		$data['modify_by']		= $this->UserAuth->getUserId();
		$data['modify_date']	= date('Y-m-d H:i:s');
		
		if($this->OriginAd->save($data)) {
			$schedule['origin_ad_id']	= $this->OriginAd->id;
			$this->OriginAdSchedule->save($schedule);
			$assets		= '../webroot/assets/creator/'.$this->OriginAd->id;
			if(!is_dir($assets)) {
				mkdir($assets, 0777, true);
			}
			
			//Move optional temporary image into new location			
			if(isset($tempContent['img_thumbnail']) && $tempContent['img_thumbnail'] !== '') {
				$newLocation 	= '/assets/creator/'.$this->OriginAd->id.'/'.basename($tempContent['img_thumbnail']);
			
				rename('../webroot'.$tempContent['img_thumbnail'], '../webroot'.$newLocation);
				
				$updateData['id']						= $this->OriginAd->id;
				$updateData['content']['img_thumbnail']	= $newLocation;
				$updateData['content']['ga_id']			= $tempContent['ga_id'];
				$updateData['content']					= json_encode($updateData['content']);
				
				$this->OriginAd->save($updateData);
			}
			echo $this->OriginAd->id;
		}
	}
	
	/**
	* Toggle an ad unit's showcase status
	*/
	private function adShowcase($data) {
		$showcase['id']			= $data['id'];
		$showcase['showcase']	= $data['showcase'];
	
		if($this->OriginAd->save($showcase)) {
			$this->layout	= 'ajax';
			$this->jsonList();
			return $this->render('/Origin/json/json_list');
		}
	}
	
	/**
	* Save CSS
	*/
	private function cssUpdate($data) {
		$css['id'] = $css['originAd_id'] = $data['id'];
		$css['content_css']	= $data['content'];
	
		if($this->OriginAd->save($css)) {
			$this->_adModifyUpdate($css['originAd_id']);
			
			return $this->_creatorAdLoad($css);
		}
	}

	/**
	* Save settings
	*/
	private function creatorSettingsUpdate($data) {
		unset($data['statusSwitch']);
		
		$data['config']			= json_encode($data['config']);
		$data['content']		= json_encode($data['content']);
		$data['modify_date']	= date('Y-m-d H:i:s');
		$data['modify_by']		= $this->UserAuth->getUserId();
		$data['status']			= empty($data['status'])? 0: 1;
		$data['originAd_id']	= $data['id'];
		
		if($this->OriginAd->save($data)) {
			return $this->_creatorAdLoad($data);
		}
	}

	/**
	* Creates an Origin ad unit's content record
	*/
	private function creatorContentSave($data) {
		//Save workspace updates first...
		$this->creatorWorkspaceUpdate($data);
		
		if(!isset($data['id'])) {
			$order	= $this->{'OriginAd'.$data['model'].'Content'}->find('first',
				array(
					'conditions'=>array('OriginAd'.$data['model'].'Content.origin_ad_schedule_id'=>$data['origin_ad_schedule_id']),
					'fields'=>array('MAX(`order`) as `order`')
				)
			);
			//$order	= (int)$order[0]['order'] + 1;
			$data['order']			= (int)$order[0]['order'] + 1;
		}
		
		$embedIframe			= isset($data['content']['iframe'])? true: false;
		$data['origin_ad_id']	= $data['originAd_id'];
		$data['content']		= json_encode($data['content']);
		$data['config']			= json_encode($data['config']);
		
		if($this->{'OriginAd'.$data['model'].'Content'}->save($data)) {
			
			if($embedIframe) {
				/**
				* Special case for iframe-able embed content
				*/
				$updateData['id']		= $this->{'OriginAd'.$data['model'].'Content'}->id;
				$updateData['render'] 	= str_replace(array('%model%', '%id%'), array('OriginAd'.$data['model'].'Content', $updateData['id']), $data['render']);
				$this->{'OriginAd'.$data['model'].'Content'}->save($updateData);
			}
			
			return $this->_creatorAdLoad($data);
		}
	}
	
	/**
	* Removes the content from the ad unit
	*/
	private function creatorContentRemove($data) {
		if($this->{'OriginAd'.$data['model'].'Content'}->delete($data['id'])) {
			$this->creatorWorkspaceUpdate($data);
			return $this->_creatorAdLoad($data);	
		}
	}
	
	/**
	* User action to save a workspace's content items (size and position)
	*/
	private function creatorWorkspaceUpdate($data) {
		//Array of relevant models
		$modelArray		= array('OriginAdDesktopInitialContent', 
								'OriginAdDesktopTriggeredContent',
								'OriginAdTabletInitialContent', 
								'OriginAdTabletTriggeredContent',
								'OriginAdMobileInitialContent', 
								'OriginAdMobileTriggeredContent');
	
		//print_r($data);
		foreach($data['data'] as $schedule) {
			foreach($modelArray as $modelName) {
				$dataSave	= $schedule[$modelName];
				
				foreach($dataSave as $key=>$content) {
					unset($dataSave[$key]['origin_ad_schedule_id']);
					//unset($dataSave[$key]['content']);
					unset($dataSave[$key]['render']);
					//unset($dataSave[$key]['order']);
					$dataSave[$key]['content']= json_encode($content['content']);
					$dataSave[$key]['config'] = json_encode($content['config']);
				}
				
				if($dataSave) {
					$this->$modelName->saveAll($dataSave);
					//Wipe previous ID
					$this->$modelName->create();
				}
			}
		}
		$this->_adModifyUpdate($data['originAd_id']);
		return $this->_creatorAdLoad($data);
	}
	
	/**
	* Updates the order of all content layers
	*/
	private function creatorLayerUpdate($data) {
		if($this->{'OriginAd'.$data['model'].'Content'}->saveAll($data['data'])) {
			return $this->_creatorAdLoad($data);	
		}
		$this->_adModifyUpdate($data['originAd_id']);
	}
}
