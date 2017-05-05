<?php

	class Extension_Documenter extends Extension {

		public function fetchNavigation() {
			return array(
				array(
					'location'	=> __('System'),
					'name'		=> __('Documentation'),
					'link'		=> '/',
					'limit'		=> 'manager',
				)
			);
		}

		public function getSubscribedDelegates() {
			return array(
				array(
					'page' =>     '/system/preferences/',
					'delegate' => 'AddCustomPreferenceFieldsets',
					'callback' => 'appendPreferences'
				),
				array(
					'page' =>     '/system/preferences/',
					'delegate' => 'Save',
					'callback' => 'savePreferences'
				),
				array(
					'page'     => '/backend/',
					'delegate' => 'InitaliseAdminPageHead',
					'callback' => 'loadAssets'
				),
				array(
					'page'     => '/backend/',
					'delegate' => 'InitaliseAdminPageHead',
					'callback' => 'appendDocs'
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
							new XMLElement('h2', $item['title'])
						);
					}

					// Add formatted help text
					$docs->appendChild(
						new XMLElement('div', $item['content_formatted'], array('class' => 'documenter-content'))
					);

				}

				$button = General::sanitize(Symphony::Configuration()->get('button-text', 'Documentation'));
				$drawer = Widget::Drawer(
					'documenter',
					($button != '' ? $button : __('Documentation')),
					$docs,
					'closed'
				);

				Widget::registerSVGIcon(
					'help',
					'<svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="26px" height="26px" viewBox="0 0 26 26"><circle fill="currentColor" cx="13" cy="20.1" r="1.3"/><path fill="currentColor" d="M14,17.1h-2v-3.2c0-0.6,0.4-1,1-1c1.7,0,3.1-1.3,3.1-3s-1.4-3-3.1-3c-1.7,0-3.1,1.3-3.1,3.3h-2c0-3,2.3-5.3,5.1-5.3c2.9,0,5.1,2.2,5.1,5c0,2.5-1.7,4.5-4.1,4.9V17.1z"/><path fill="currentColor" d="M13,26C5.8,26,0,20.2,0,13S5.8,0,13,0s13,5.8,13,13S20.2,26,13,26z M13,2C6.9,2,2,6.9,2,13s4.9,11,11,11s11-4.9,11-11S19.1,2,13,2z"/></svg>'
				);
				Administration::instance()->Page->insertDrawer(
					$drawer,
					'vertical-right',
					'append',
					Widget::SVGIcon('help')
				);
			}
		}

		public function uninstall() {
			Symphony::Database()->query("DROP TABLE `tbl_documentation`;");
			Symphony::Configuration()->remove('text-formatter', 'documentation');
			Symphony::Configuration()->remove('button-text', 'documentation');
			Symphony::Configuration()->write();
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
			Symphony::Configuration()->write();
			return;
		}

		public function savePreferences($context) {

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
				General::sanitize(Symphony::Configuration()->get('button-text', 'documentation')),
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
