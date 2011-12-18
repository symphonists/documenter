<?php

	class Extension_Documenter extends Extension {

		public function about() {
			return array(
				'name'			=> 'Documenter',
				'version'		=> '1.0RC2',
				'release-date'	=> '2011-05-03',
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
					'name'		=> __('Documentation'),
					'link'		=> '/',
					'limit'		=> 'developer'
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

			$page->addStylesheetToHead(URL . $assets_path . 'documenter.admin.css', 'screen', 120);
			$page->addScriptToHead(URL . $assets_path . 'documenter.admin.js', 130);
		}

		public function appendDocs($context) {
			$current_page = str_replace(URL . '/symphony', '', $context['parent']->Page->_Parent->getCurrentPageURL());

			if(preg_match('/edit/',$current_page)) {
				$pos = strripos($current_page, '/edit/');
				$current_page = substr($current_page, 0, $pos + 6);
			}
			$pages = Symphony::Database()->fetch("
				SELECT
					d.pages, d.id
				FROM
					`tbl_documentation` AS d
				ORDER BY
					d.pages ASC
			");

			foreach($pages as $key => $value) {
				if(strstr($value['pages'],',')) {
					$list = explode(',',$value['pages']);
					foreach($list as $item){
						$pages[] = array('id' => $value['id'], 'page' => $item);
					}
					unset($pages[$key]);
				}
			}

			###
			# Delegate: appendDocsPre
			# Description: Allow other extensions to add their own documentation page
			Administration::instance()->ExtensionManager->notifyMembers('appendDocsPre',
				'/backend/', array(
					'pages' => &$pages
				)
			);

			// Fetch documentation items 
			$items = array();
			foreach($pages as $page) {
				if(in_array($current_page,$page)) {
					if(isset($page['id'])) {
						$items[] = Symphony::Database()->fetchRow(0, "
							SELECT
								d.title, d.content_formatted
							FROM
								`tbl_documentation` AS d
  							WHERE
								 d.id = '{$page['id']}'
							LIMIT 1
						 ");
					} 
					else {
						###
						# Delegate: appendDocsPost
						# Description: Allows other extensions to insert documentation for the $current_page
						Administration::instance()->ExtensionManager->notifyMembers('appendDocsPost',
							'/backend/', array(
								'doc_item' => &$doc_items
							)
						);
					}
				}
			}

			// Allows a page to have more then one documentation source
			if(!empty($items)) {

				// Append help item
				$help = new XMLElement('a', Symphony::Configuration()->get('button-text', 'documentation'), array('class' => 'documenter button', 'title' => __('View Documentation')));
				$context['parent']->Page->Body->appendChild($help);

				// Generate documentation panel
				$docs = new XMLElement('div', NULL, array('id' => 'documenter-drawer'));
				foreach($items as $item) {
				
					// Add title
					if(isset($item['title'])) {
						$docs->appendChild(
							new XMLElement('h2', $item['title'], array('id' => 'documenter-title'))
						);
					}

					// Add formatted help text
					$docs->appendChild(
						new XMLElement('div', $item['content_formatted'], array('class' => 'documenter-content'))
					);

				}
				// Append documentation
				$context['parent']->Page->Body->appendChild($docs);
			}
		}

		public function uninstall() {
			Symphony::Database()->query("DROP TABLE `tbl_documentation`;");
			Symphony::Configuration()->remove('text-formatter', 'documentation');
			Symphony::Configuration()->remove('button-text', 'documentation');
			Administration::instance()->saveConfig();
		}

		public function install() {
			Symphony::Database()->query(
				"CREATE TABLE `tbl_documentation` (
					`id` int(11) unsigned NOT NULL auto_increment,
					`title` varchar(255),
					`pages` varchar(255),
					`content` text,
					`content_formatted` text,
					PRIMARY KEY (`id`)
				);");
			Symphony::Configuration()->set('text-formatter', 'none', 'documentation');
			Symphony::Configuration()->set('button-text', __('Need help?'), 'documentation');
			Administration::instance()->saveConfig();
			return;
		}

		public function __SavePreferences($context) {

			if(!is_array($context['settings'])) $context['settings'] = array('documentation' => array('text-formatter' => 'none'));

			elseif(!isset($context['settings']['documentation'])) {
				$context['settings']['documentation'] = array('text-formatter' => 'none');
			}

		}

		public function appendPreferences($context) {

			include_once(TOOLKIT . '/class.textformattermanager.php');

			$group = new XMLElement('fieldset');
			$group->setAttribute('class', 'settings');
			$group->appendChild(new XMLElement('legend', __('Documentation')));

			$div = new XMLElement('div');
			$div->setAttribute('class', 'group');

		// Input for button text
			$label = Widget::Label(__('Button Text'));
			$input = Widget::Input(
				'settings[documentation][button-text]',
				__($this->_Parent->Configuration->get('button-text', 'documentation')),
				'text'
			);

			$label->appendChild($input);
			$div->appendChild($label);

			$TFM = new TextformatterManager($this->_Parent);
			$formatters = $TFM->listAll();

		// Text formatter select
			$label = Widget::Label(__('Text Formatter'));

			$options = array();

			$options[] = array('none', false, __('None'));

			if(!empty($formatters) && is_array($formatters)) {
				foreach($formatters as $handle => $about) {
					$options[] = array(
						$handle,
						(Symphony::Configuration()->get('text-formatter', 'documentation') == $handle),
						$about['name']);
				}
			}

			$input = Widget::Select('settings[documentation][text-formatter]', $options);

			$label->appendChild($input);
			$div->appendChild($label);

			$group->appendChild($div);
			$context['wrapper']->appendChild($group);
		}
		
	}
