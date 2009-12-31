<?php

	class DocumentationForm {
	
		function render() {	
		
			$this->setPageType('form');
			$fields = array();
			
			// Verify doc exists:
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
			
			// Status message:
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
			
			// Find values:
			if (isset($_POST['fields'])) {
				$fields = $_POST['fields'];
				
			} else if ($this->_context[0]) {
				$fields = $existing;
				$fields['content'] = General::sanitize($fields['content']);
			}
			
			$title = $fields['title'];
			if (trim($title) == '') $title = $existing['title'];
			
			$this->setTitle(__(
				($title ? '%1$s &ndash; %2$s &ndash; %3$s' : '%1$s &ndash; %2$s'),
				array(
					__('Symphony'),
					__('Documentation'),
					$title
				)
			));
			$this->appendSubheading(($title ? $title : __('Untitled')));
			
		// Start Fields
			$div = new XMLElement('div');
			$div->setAttribute('class', 'group');
		
			$fieldset = new XMLElement('fieldset');
			$fieldset->setAttribute('class', 'primary');

		// Title
			
			$label = Widget::Label(__('Title'));		
			$label->appendChild(Widget::Input(
				'fields[title]', General::sanitize($fields['title'])
			));
			
			if (isset($this->_Parent->_errors['title'])) {
				$label = $this->wrapFormElementWithError($label, $this->_Parent->_errors['title']);
			}
			
			$fieldset->appendChild($label);
			
		// Content
		
			$label = Widget::Label(__('Content'));
			$label->appendChild(Widget::Textarea('fields[content]', 30, 80, $fields['content'], array('class' => 'code')));
			$fieldset->appendChild((isset($this->_errors['content']) ? $this->wrapFormElementWithError($label, $this->_errors['content']) : $label));
			
			$div->appendChild($fieldset);
			
		// Page --------------------------------------------------------------
		
			$fieldset = new XMLElement('fieldset');
			$label = Widget::Label(__('Pages'));
			$pages_array = explode(',', $fields['pages']);
			$options = array();
			
			foreach($this->_Parent->Page->_navigation as $menu){
				$items = array();
				foreach($menu['children'] as $item){
					$items[] = array($item['link'], (in_array($item['link'], $pages_array)), $menu['name'] . " > " . $item['name']);
					if($menu['name'] == 'Content'){
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
			
		// Controls -----------------------------------------------------------
			
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
	
	}
