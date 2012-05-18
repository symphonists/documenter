<?php

	require_once(TOOLKIT . '/class.administrationpage.php');
	require_once(EXTENSIONS . '/documenter/lib/class.form.php');
	
	class contentExtensionDocumenterNew extends AdministrationPage {
	
		function view() {	
			DocumentationForm::render();
		}
		
		function action() {
			if(@array_key_exists('save', $_POST['action'])){
			
				$this->_errors = array();

			// Polish up some field content
				$fields = $_POST['fields'];
				
				if(isset($fields['pages'])) {
					$fields['pages'] = implode(',',$fields['pages']);
				}
				
				$fields['content_formatted'] = DocumentationForm::applyFormatting($fields['content'], true, $this->_errors);
				
				if($fields['content_formatted'] === false){
					$fields['content_formatted'] = General::sanitize(DocumentationForm::applyFormatting($fields['content']));	
				}

				if(!isset($fields['title']) || trim($fields['title']) == '') $this->_errors['title'] = __('Title is a required field');	
			
				if(!isset($fields['pages']) || trim($fields['pages']) == '') $this->_errors['pages'] = __('Page is a required field');
				
				if(!isset($fields['content']) || trim($fields['content']) == '') $this->_errors['content'] = __('Content is a required field');

				if(empty($this->_errors)){
						
					if(!Symphony::Database()->insert($fields, 'tbl_documentation')) $this->pageAlert(__('Unknown errors occurred while attempting to save. Please check your <a href="%s">activity log</a>.', array(URL.'/symphony/system/log/')), Alert::ERROR);

					else{	
						$doc_id = Symphony::Database()->getInsertID();
						redirect(URL . "/symphony/extension/documenter/edit/$doc_id/created/");
						}
				}
			}
			
			if(is_array($this->_errors) && !empty($this->_errors)) $this->pageAlert(__('An error occurred while processing this form. <a href="#error">See below for details.</a>'), Alert::ERROR);				
		}
	
	}
