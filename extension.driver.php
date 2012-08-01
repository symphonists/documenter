<?php

	class Extension_Documenter extends Extension {
		
		public function fetchNavigation() {
			return array(
				array(
					'location'	=> __('System'),
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
					'delegate'	=> 'InitaliseAdminPageHead',
					'callback'	=> 'appendDocs'
				)
			);
		}

		public function loadAssets($context) {
			Administration::instance()->Page->addStylesheetToHead(URL . '/extensions/documenter/assets/documenter.admin.css', 'screen', 100);
			Administration::instance()->Page->addScriptToHead(URL . '/extensions/documenter/assets/documenter.admin.js', 101, false);
		}

		public function appendDocs($context) {
			$current_page_url = str_replace(SYMPHONY_URL, '', Administration::instance()->getCurrentPageURL());

			if(preg_match('/edit/',$current_page_url)) {
				$pos = strripos($current_page_url, '/edit/');
				$current_page_url = substr($current_page_url, 0, $pos + 6);
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
			Symphony::ExtensionManager()->notifyMembers('appendDocsPre',
				'/backend/', array(
					'pages' => &$pages
				)
			);

			// Fetch documentation items 
			$items = array();
			foreach($pages as $page) {
				if(in_array($current_page_url, $page)) {
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
						# Description: Allows other extensions to insert documentation for the $current_page_url
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
				
				$button = Symphony::Configuration()->get('button-text', 'Documentation');
				$drawer = Widget::Drawer(
					'documenter',
					($button != '' ? $button : __('Documentation')),
					$docs,
					'closed'
				);
				Administration::instance()->Page->insertDrawer($drawer, 'vertical-right');
				
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
					`pages` text,
					`content` text,
					`content_formatted` text,
					PRIMARY KEY (`id`)
				) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;"
      );
			Symphony::Configuration()->set('text-formatter', 'none', 'documentation');
			Symphony::Configuration()->set('button-text', __('Documentation'), 'documentation');
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
				Symphony::Configuration()->get('button-text', 'documentation'),
				'text'
			);

			$label->appendChild($input);
			$div->appendChild($label);

			$formatters = TextformatterManager::listAll();

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
