<?php

	require_once(TOOLKIT . '/class.administrationpage.php');
	require_once(EXTENSIONS . '/documenter/lib/class.form.php');
	
	class contentExtensionDocumenterNew extends AdministrationPage {
	
		function view() {	
			DocumentationForm::render();
		}
		
		function action() {
			if(@array_key_exists('save', $_POST['action'])){

				$fields = $_POST['fields'];
				$fields['pages'] = implode(',',$fields['pages']);
				
				$this->_errors = array();

				if(!isset($fields['title']) || trim($fields['title']) == '') $this->_errors['title'] = __('Title is a required field');	
			
				if(!isset($fields['pages']) || trim($fields['pages']) == '') $this->_errors['pages'] = __('Page is a required field');
				
				if(!isset($fields['content']) || trim($fields['content']) == '') $this->_errors['content'] = __('Content is a required field');

				if(empty($this->_errors)){
					
					// Is this a duplicate?
					if($this->_Parent->Database->fetchRow(0, "SELECT * FROM `tbl_documentation` 
						WHERE `pages` = '" . $fields['pages'] . "'  
						LIMIT 1")){	
							$this->_errors['title'] = __('Documentation already exists for this page');
						}
						
					// If not, save it	
					else{	
						if(!$this->_Parent->Database->insert($fields, 'tbl_documentation')) $this->pageAlert(__('Unknown errors occurred while attempting to save. Please check your <a href="%s">activity log</a>.', array(URL.'/symphony/system/log/')), Alert::ERROR);

						else{	
							$doc_id = $this->_Parent->Database->getInsertID();
			                redirect(URL . "/symphony/extension/documenter/edit/$doc_id/created/");
						}
					}
				}
			}
			
			if(is_array($this->_errors) && !empty($this->_errors)) $this->pageAlert(__('An error occurred while processing this form. <a href="#error">See below for details.</a>'), Alert::ERROR);				
		}
	
	}
