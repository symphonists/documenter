<?php

	require_once(CONTENT . '/content.blueprintspages.php');

	class contentExtensionDocumenterIndex extends contentBlueprintsPages {
	
		public function view() {
		
		// Start building the page
			$this->setPageType('table');
			$this->setTitle(__('%1$s &ndash; %2$s', array(__('Symphony'), __('Documentation'))));
			
			$this->appendSubheading(
				__('Documentation'),
				Widget::Anchor(
					__('Create New'), URL . '/symphony/extension/documenter/new/',
					__('Create a new documentation item'), 'create button'
				)
			);
		
		// Grab all the documentation items
			$docs = Symphony::Database()->fetch("
				SELECT
					d.*
				FROM
					`tbl_documentation` AS d
				ORDER BY
					d.pages ASC
			");
			
		// Build the table
			$thead = array(
				array(__('Title'), 'col'),
				array(__('Pages'), 'col')
			);
			
			$tbody = array();

		// If there are no records, display default message
			if (!is_array($docs) or empty($docs)) {
				$tbody = array(Widget::TableRow(array(
					Widget::TableData(__('None found.'), 'inactive', null, count($thead))
				), 'odd'));
				
			}
		
		// Otherwise, build table rows
			else{
				$bOdd = true;
				
				foreach ($docs as $doc) {
					$doc_edit_url = URL . '/symphony/extension/documenter/edit/' . $doc['id'] . '/';
					
					$col_title = Widget::TableData(Widget::Anchor(
						$doc['title'], $doc_edit_url
					));
					$col_title->appendChild(Widget::Input("items[{$doc['id']}]", null, 'checkbox'));
					
					$pages = $doc['pages'];
					$pages = explode(',', $pages);
					$pages = join(', ', $pages);
					$col_pages = Widget::TableData($pages);
					
					$tbody[] = Widget::TableRow(array($col_title, $col_pages), ($bOdd ? 'odd' : NULL));
					
					$bOdd = !$bOdd;
				}
			}
			
			$table = Widget::Table(
				Widget::TableHead($thead), null,
				Widget::TableBody($tbody), null
			);
			$table->setAttribute('class','selectable');
			
			$this->Form->appendChild($table);
			
			$actions = new XMLElement('div');
			$actions->setAttribute('class', 'actions');
			
			$options = array(
				array(null, false, __('With Selected...')),
				array('delete', false, __('Delete'))							
			);
			
			$actions->appendChild(Widget::Apply($options));
			
			$this->Form->appendChild($actions);
		
		}
		
		function __actionIndex(){

			$checked = @array_keys($_POST['items']);

			if(is_array($checked) && !empty($checked)){
				switch($_POST['with-selected']) {

					case 'delete':

						$doc_items = $checked;

						Symphony::Database()->delete('tbl_documentation', " `id` IN('".implode("','",$checked)."')");
						redirect(Administration::instance()->getCurrentPageURL());	
						break;  	
				}
			}
		}	
	
	} 
