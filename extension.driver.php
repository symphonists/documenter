<?php

	class Extension_Documenter extends Extension {
	
		public function about() {
			return array(
				'name'			=> '1.0 beta',
				'release-date'	=> '2010-01-01',
				'author'		=> array(
					'name'			=> 'craig zheng',
					'email'			=> 'craig@symphony-cms.com'
				),
				'description'	=> 'Document your back end for clients or users.'
			);
		}
	
		public function fetchNavigation() {
			return array(
				array(
					'location'	=> 'System',
					'name'		=> 'Documentation',
					'link'		=> '/'
				)
			);
		}
		
		public function getSubscribedDelegates() {
			return array(
				array(
					'page' => '/system/preferences/',
					'delegate' => 'AddCustomPreferenceFieldsets',
					'callback' => 'appendPreferences'
				),
				array(
					'page' => '/system/preferences/',
					'delegate' => 'Save',
					'callback' => '__SavePreferences'
				),	
				array(
					'page' 		=> '/backend/',
					'delegate' 	=> 'InitaliseAdminPageHead',
					'callback' 	=> 'loadAssets'
				),
				array(
					'page' 		=> '/backend/',
					'delegate'	=> 'AppendElementBelowView',
					'callback'	=> 'appendDocs'
				)
			);
		}
		
		public function loadAssets($context) {
			$page = $context['parent']->Page;
			$assets_path = '/extensions/documenter/assets/';

			$page->addStylesheetToHead(URL . $assets_path . 'documenter.css', 'screen', 120);
			$page->addScriptToHead(URL . $assets_path . 'documenter.js', 110);
		}
		
		public function appendDocs($context) {
			$current_page = str_replace(URL . '/symphony', '', $context['parent']->Page->_Parent->getCurrentPageURL());
			
			if (preg_match('/edit/',$current_page)){
				$pos = strripos($current_page, '/edit/');
				$current_page = substr($current_page,0,$pos + 6);
			}
			$pages = $this->_Parent->Database->fetch("
				SELECT
					d.pages, d.id
				FROM
					`tbl_documentation` AS d
				ORDER BY
					d.pages ASC
			");
			foreach($pages as $key => $value){
				if(strstr($value['pages'],',')){
					$list = explode(',',$value['pages']);
					foreach($list as $item){
						$pages[] = array('id' => $value['id'], 'page' => $item);
					}
					unset($pages[$key]);
				}
			}
			foreach($pages as $page){
				if(in_array($current_page,$page)){
					$doc_item = $this->_Parent->Database->fetchRow(0, "
						SELECT
							d.*
						FROM
							`tbl_documentation` AS d
						WHERE
							d.id REGEXP '{$page['id']}'
						LIMIT 1
					");
					$backend_page = &$context['parent']->Page->Form;
					$link = Widget::Anchor(__('Help'), '#', __('View Documentation'), NULL, 'doc_link');
					$backend_page->appendChild($link);
					$docs = new XMLElement('div', NULL, array('id' => 'docs'));
					$title = new XMLElement('h2', $doc_item['title']);
					$docs->appendChild($title);
					$content = new XMLElement('div', $doc_item['content_formatted']);
					$docs->appendChild($content);
					$backend_page->appendChild($docs);
				}
			}			
		}
	
		public function uninstall() {
			$this->_Parent->Database->query("DROP TABLE `tbl_documentation`;");
			Administration::instance()->Configuration->remove('text-formatter', 'documentation');
			Administration::instance()->saveConfig();
		}
	
		public function install() {
			$this->_Parent->Database->query(
				"CREATE TABLE `tbl_documentation` (
					`id` int(11) unsigned NOT NULL auto_increment,
					`title` varchar(255),
					`pages` varchar(255),
					`content` text,
					`content_formatted` text,
					PRIMARY KEY (`id`)
				);");
			Administration::instance()->Configuration->set('text-formatter', 'none', 'documentation');
			Administration::instance()->saveConfig();
			return;
		}
		
		public function __SavePreferences($context){

			if(!is_array($context['settings'])) $context['settings'] = array('documentation' => array('text-formatter' => 'none'));
			
			elseif(!isset($context['settings']['documentation'])){
				$context['settings']['documentation'] = array('text-formatter' => 'none');
			}
			
		}
		
		public function appendPreferences($context){

			include_once(TOOLKIT . '/class.textformattermanager.php');

			$group = new XMLElement('fieldset');
			$group->setAttribute('class', 'settings');
			$group->appendChild(new XMLElement('legend', 'Documentation'));	
			
			$TFM = new TextformatterManager($this->_engine);
			$formatters = $TFM->listAll();
				
			$label = Widget::Label('Text Formatter');
		
			$options = array();
		
			$options[] = array('none', false, __('None'));
		
			if(!empty($formatters) && is_array($formatters)){
				foreach($formatters as $handle => $about) {
					$options[] = array($handle, ($this->_Parent->Configuration->get('text-formatter', 'documentation') == $handle), $about['name']);
				}	
			}

			$input = Widget::Select('settings[documentation][text-formatter]', $options);
			
			$label->appendChild($input);
			$group->appendChild($label);
									
			$context['wrapper']->appendChild($group);
						
		}
	
	} 
