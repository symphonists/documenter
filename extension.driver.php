<?php

	class Extension_Documenter extends Extension {
	
		public function about() {
			return array(
				'name'			=> 'Documenter',
				'version'		=> '0.1',
				'release-date'	=> '2009-12-21',
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

			// load styles
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
					d.pages
				FROM
					`tbl_documentation` AS d
				ORDER BY
					d.pages ASC
			");
			foreach($pages as $key => $value){
				if(strstr($value['pages'],',')){
					$list = explode(',',$value['pages']);
					foreach($list as $item){
						$pages[] = $item;
					}
					unset($pages[$key]);
				} else {
					$pages[$key] = $value['pages'];
				}
			}
			if(in_array($current_page,$pages)){
				$doc_item = $this->_Parent->Database->fetchRow(0, "
					SELECT
						d.*
					FROM
						`tbl_documentation` AS d
					WHERE
						d.pages REGEXP '{$current_page}'
					LIMIT 1
				");
				$backend_page = &$context['parent']->Page->Form;
				$link = Widget::Anchor(__('Help'), '#', __('View Documentation'), NULL, 'doc_link');
				$backend_page->appendChild($link);
				$docs = new XMLElement('div', NULL, array('id' => 'docs'));
				$title = new XMLElement('h2', $doc_item['title']);
				$docs->appendChild($title);
				$content = new XMLElement('div', $doc_item['content']);
				$docs->appendChild($content);
				$backend_page->appendChild($docs);
			}			
		}
	
		public function uninstall() {
			$this->_Parent->Database->query("DROP TABLE `tbl_documentation`;");
		}
	
		public function install() {
			return $this->_Parent->Database->query(
				"CREATE TABLE `tbl_documentation` (
					`id` int(11) unsigned NOT NULL auto_increment,
					`title` varchar(255),
					`pages` varchar(255),
					`content` text,
					PRIMARY KEY (`id`)
				);");
		}
	
	} 
