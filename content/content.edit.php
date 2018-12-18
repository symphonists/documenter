<?php

	require_once(TOOLKIT . '/class.administrationpage.php');
	require_once(EXTENSIONS . '/documenter/lib/class.form.php');

	class contentExtensionDocumenterEdit extends AdministrationPage {

		private $form;

		public function __construct() {
			parent::__construct();
			$this->form = new DocumentationForm($this);
		}

		public function view() {
			$this->form->render();
		}

		public function action() {
			$doc_id = $this->_context[0];

			// Delete action
			if (@array_key_exists('delete', $_POST['action'])) {
				$page = Symphony::Database()
					->select(['*'])
					->from('tbl_documentation')
					->where(['id' => $doc_id])
					->execute()
					->next();

				Symphony::Database()
					->delete('tbl_documentation')
					->where(['id' => $doc_id])
					->execute()
					->success();

				redirect(URL . '/symphony/extension/documenter/');
			}

			// Save action
			if(@array_key_exists('save', $_POST['action'])){

				$this->_errors = array();

				// Polish up some field content
				$fields = $_POST['fields'];

				if(isset($fields['pages'])) {
					$fields['pages'] = implode(',',$fields['pages']);
				}

				$fields['content_formatted'] = $this->form->applyFormatting($fields['content'], true, $this->_errors);

				if($fields['content_formatted'] === false){
					$fields['content_formatted'] = General::sanitize($this->form->applyFormatting($fields['content']));
				}

				if(!isset($fields['content']) || trim($fields['content']) == '') $this->_errors['content'] = __('Content is a required field');

				if(!isset($fields['pages']) || trim($fields['pages']) == '') $this->_errors['pages'] = __('Pages is a required field');

				if(empty($this->_errors)){
					if (!Symphony::Database()
						->update('tbl_documentation')
						->set($fields)
						->where(['id' => $doc_id])
						->execute()
						->success()
					) {
						$this->pageAlert(__('Unknown errors occurred while attempting to save. Please check your <a href="%s">activity log</a>.', array(URL.'/symphony/system/log/')), Alert::ERROR);
					}
					else {
						redirect(URL . "/symphony/extension/documenter/edit/$doc_id/saved/");
					}
				}
			}

			if(is_array($this->_errors) && !empty($this->_errors)) $this->pageAlert(__('An error occurred while processing this form. <a href="#error">See below for details.</a>'), Alert::ERROR);
		}

	}
