<?php

	require_once(CONTENT . '/content.blueprintspages.php');

	class contentExtensionDocumenterIndex extends contentBlueprintsPages {
	
		public function view() {
			$this->setPageType('table');
			$this->setTitle(__('%1$s &ndash; %2$s', array(__('Symphony'), __('Documentation'))));
			
			$this->appendSubheading(__('Documentation'), Widget::Anchor(
				__('Create New'), URL . '/symphony/extension/documenter/new/',
				__('Create a new documentation item'), 'create button'
			));
			
			$docs = $this->_Parent->Database->fetch("
				SELECT
					d.*
				FROM
					`tbl_documentation` AS d
				ORDER BY
					d.pages ASC
			");
			
			$thead = array(
				array(__('Title'), 'col'),
				array(__('Pages'), 'col')
			);
			
			$tbody = array();
			
			if (!is_array($docs) or empty($docs)) {
				$tbody = array(Widget::TableRow(array(
					Widget::TableData(__('None found.'), 'inactive', null, count($thead))
				), 'odd'));
				
			}
			
			else{
				
				$bOdd = true;
				
				foreach ($docs as $doc) {
					$doc_edit_url = URL . '/symphony/extension/documenter/edit/' . $doc['id'] . '/';
					
					$col_title = Widget::TableData(Widget::Anchor(
						$doc['title'], $doc_edit_url
					));
					$col_title->appendChild(Widget::Input("items[{$doc['id']}]", null, 'checkbox'));
					
					$col_pages = Widget::TableData($doc['pages']);
					
					$tbody[] = Widget::TableRow(array($col_title, $col_pages), ($bOdd ? 'odd' : NULL));
					
					$bOdd = !$bOdd;
				}
			}
			
			$table = Widget::Table(
				Widget::TableHead($thead), null,
				Widget::TableBody($tbody), null
			);
			
			
			$this->Form->appendChild($table);
			
			$tableActions = new XMLElement('div');
			$tableActions->setAttribute('class', 'actions');
			
			$options = array(
				array(null, false, __('With Selected...')),
				array('delete', false, __('Delete'))							
			);
			
			$tableActions->appendChild(Widget::Select('with-selected', $options));
			$tableActions->appendChild(Widget::Input('action[apply]', __('Apply'), 'submit'));
			
			$this->Form->appendChild($tableActions);
		
		}
		
		function __actionIndex(){

			$checked = @array_keys($_POST['items']);

			if(is_array($checked) && !empty($checked)){
				switch($_POST['with-selected']) {

					case 'delete':

						$doc_items = $checked;

						$this->_Parent->Database->delete('tbl_documentation', " `id` IN('".implode("','",$checked)."')");

						redirect($this->_Parent->getCurrentPageURL());	
						break;  	
				}
			}
		}	
	
	} 
