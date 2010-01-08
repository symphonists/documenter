<?php

	class DocumentationForm {
	
		function render() {	
		
			$this->setPageType('form');
			$fields = array();
			
		// If we're editing, make sure the item exists
			if ($this->_context[0]) {
				if (!$doc_id = $this->_context[0]) redirect(URL . '/symphony/extension/documenter/manage');
				
				$existing = $this->_Parent->Database->fetchRow(0, "
					SELECT
						d.*
					FROM
						`tbl_documentation` AS d
					WHERE
						d.id = '{$doc_id}'
					LIMIT 1
				");
				
				if (!$existing) {
					$this->_Parent->customError(
						E_USER_ERROR, __('Documentation Item not found'),
						__('The documentation item you requested to edit does not exist.'),
						false, true, 'error', array(
							'header'	=> 'HTTP/1.0 404 Not Found'
						)
					);
				}
			}
			
		// Build the status message
			if (isset($this->_context[1])) {
				$this->pageAlert(
					__(
						'%s %s at %s. <a href="%s">Create another?</a> <a href="%s">View all %s</a>',
						array(
							__('Documentation Item'),
							($this->_context[1] == 'saved' ? 'updated' : 'created'),
							DateTimeObj::getTimeAgo(__SYM_TIME_FORMAT__),
							URL . '/symphony/extension/documenter/new/',
							URL . '/symphony/extension/documenter/',
							__('Documentation')
						)
					),
					Alert::SUCCESS
				);
			}
			
		// Find values
			if (isset($_POST['fields'])) {
				$fields = $_POST['fields'];
				
			} else if ($this->_context[0]) {
				$fields = $existing;
				$fields['content'] = General::sanitize($fields['content']);
			}
			
			$title = $fields['title'];
			if (trim($title) == '') $title = $existing['title'];
		
		// Start building the page
			$this->setTitle(__(
				($title ? '%1$s &ndash; %2$s &ndash; %3$s' : '%1$s &ndash; %2$s'),
				array(
					__('Symphony'),
					__('Documentation'),
					$title
				)
			));
			$this->appendSubheading(($title ? $title : __('Untitled')));
			
		// Start building the fieldsets
			$div = new XMLElement('div');
			$div->setAttribute('class', 'group');
		
			$fieldset = new XMLElement('fieldset');
			$fieldset->setAttribute('class', 'primary');

		// Title text input
			$label = Widget::Label(__('Title'));		
			$label->appendChild(Widget::Input(
				'fields[title]', General::sanitize($fields['title'])
			));
			
			if (isset($this->_Parent->_errors['title'])) {
				$label = $this->wrapFormElementWithError($label, $this->_Parent->_errors['title']);
			}
			$fieldset->appendChild($label);
			
		// Content textarea
			$label = Widget::Label(__('Content'));
			
			$content = Widget::Textarea('fields[content]', 30, 80, $fields['content']);
			if(Administration::instance()->Configuration->get('text-formatter', 'documentation') != 'none') $content->setAttribute('class', Administration::instance()->Configuration->get('text-formatter', 'documentation'));
			
			$label->appendChild($content);
			$fieldset->appendChild((isset($this->_errors['content']) ? $this->wrapFormElementWithError($label, $this->_errors['content']) : $label));
			
			$div->appendChild($fieldset);
			
		// Pages multi-select
			$fieldset = new XMLElement('fieldset');
			$label = Widget::Label(__('Pages'));
			$pages_array = explode(',', $fields['pages']);
			$options = array();
			
			// Build the options list using the navigation array
			foreach($this->_Parent->Page->_navigation as $menu){
				$items = array();
				foreach($menu['children'] as $item){
					$items[] = array($item['link'], (in_array($item['link'], $pages_array)), $menu['name'] . " > " . $item['name']);
					
					// If it's a section, add New and Edit pages
					// NOTE: This will likely break when extensions add custom nav groups
					if($menu['name'] != 'Blueprints' and $menu['name'] != 'System'){
						$items[] = array($item['link'] . 'new/', (in_array($item['link'] . 'new/', $pages_array)), $menu['name'] . " > " . $item['name'] . " New");
						$items[] = array($item['link'] . 'edit/', (in_array($item['link'] . 'edit/', $pages_array)), $menu['name'] . " > " . $item['name'] . " Edit");
					}
				}
				$options[] = array('label' => $menu['name'], 'options' => $items);
			}
			
			$label->appendChild(Widget::Select('fields[pages][]', $options, array('multiple' => 'multiple', 'id' => 'pagelist')));
			
			if (isset($this->_Parent->_errors['page'])) {
				$label = $this->wrapFormElementWithError($label, $this->_Parent->_errors['page']);
			}
			
			$fieldset->appendChild($label);
			$div->appendChild($fieldset);
			$this->Form->appendChild($div);
			
		// Form actions
			
			$div = new XMLElement('div');
			$div->setAttribute('class', 'actions');
			$div->appendChild(Widget::Input(
				'action[save]', ($this->_context[0] ? __('Save Changes') : __('Document It')),
				'submit', array('accesskey' => 's')
			));
			
			if($this->_context[0]){
				$button = new XMLElement('button', __('Delete'));
				$button->setAttributeArray(array('name' => 'action[delete]', 'class' => 'confirm delete', 'title' => __('Delete this template')));
				$div->appendChild($button);
			}
			
			$this->Form->appendChild($div);
		}
		
		function applyFormatting($data, $validate=false, &$errors=NULL){
		
			include_once(TOOLKIT . '/class.textformattermanager.php');
		
			$text_formatter = Administration::instance()->Configuration->get('text-formatter', 'documentation');
	
			if($text_formatter != 'none'){
			print_r($this->_engine);
				$tfm = new TextformatterManager($this->_Parent);
				$formatter = $tfm->create($text_formatter);
				$result = $formatter->run($data);
			}
			else {
				$result = $data;
			}

			if($validate === true){

				include_once(TOOLKIT . '/class.xsltprocess.php');

				if(!General::validateXML($result, $errors, false, new XsltProcess)){
					$result = html_entity_decode($result, ENT_QUOTES, 'UTF-8');
					$result = $this->__replaceAmpersands($result);

					if(!General::validateXML($result, $errors, false, new XsltProcess)){

						$result = $formatter->run(General::sanitize($data));
					
						if(!General::validateXML($result, $errors, false, new XsltProcess)){
							return false;
						}
					}
				}
			}

			return $result;		
		}
		
		private function __replaceAmpersands($value) {
			return preg_replace('/&(?!(#[0-9]+|#x[0-9a-f]+|amp|lt|gt);)/i', '&amp;', trim($value));
		}
	
	}
