<?php
App::import('Sanitize');

class SubscribersController extends AppController {
	
	var $name = 'Subscribers';
	var $helpers = array('Html', 'Form', 'Ajax', 'Javascript', 'Csv');
    var $components = array('RequestHandler');

    var $paginate = array(
        'limit' => 25,
        'order' => array(
            'Subscriber.id' => 'desc'
            ),
    );

    

	function index() {
		$this->Subscriber->recursive = 0;
        $searchterm = '';

        $filters = array();
        if( isset($this->passedArgs['key'])) {
            $searchterm = trim($this->passedArgs['key']);
        }
        if( isset($this->params['url']['key'])) {
            $searchterm = trim($this->params['url']['key']);
        }
        if( $searchterm != '') {
            $filters = array(
                    "or"=>array(
                    "lower(Subscriber.lastname) like '%". low($searchterm) . "%'",
                    "lower(Subscriber.firstname) like '%". low($searchterm) . "%'",
                    "lower(Subscriber.company) like '%". low($searchterm) . "%'",
                    "lower(Subscriber.email) like '%". low($searchterm) . "%'",
                    "lower(Subscriber.source) like '%". low($searchterm) . "%'",
                    "lower(Subscriber.address) like '%". low($searchterm) . "%'",
                    "lower(Subscriber.city) like '%". low($searchterm) . "%'",
                    "lower(Subscriber.country) like '%". low($searchterm) . "%'"
                    )
                );
        }

        if ( !isset( $this->passedArgs['key']) && isset($this->params['url']['key']) ) 
              $this->passedArgs['key'] = $this->params['url']['key'];

        $this->set('subscribers', $this->paginate('Subscriber', $filters));
        $this->set('searchTerm', $searchterm);

	}

	function view($id = null) {
		if (!$id) {
			$this->Session->setFlash(__('Invalid Subscriber.', true));
			$this->redirect(array('action'=>'index'));
		}
        $s = $this->Subscriber->read(null, $id);
		$this->set('subscriber', $s);
		$this->set('dateToIssue', $this->Subscriber->_dateToIssue($s['Subscriber']['issue_start']));
	}

    # Takes an array and converts to data array
    function _csvtodata($line,$mode='fmoffice'){
        $rowData = a();
        $rowData['firstname'] = $line[0];
        $rowData['lastname'] = $line[1];
        $rowData['company'] = $line[2];
        $rowData['email'] = $line[3];
        $rowData['phone'] = $line[4];
        $rowData['address'] = $line[5];
        $rowData['city'] = $line[6];
        $rowData['state'] = $line[7];
        $rowData['postcode'] = $line[8];
        $rowData['country'] = $line[9];
        $rowData['source'] = $line[10];
        $rowData['quantity'] = $line[11];

        if(empty($mode) || $mode == 'fmoffice'){
            $rowData['issue_start'] = $line[12];
            $rowData['issue_end'] = $line[13];
        } elseif($mode == 'cart'){
            $order_date = $line[12];
            $rowData['issue_start'] = $this->Subscriber->_dateToIssue($order_date);
            $sub_issues = $line[13];
            $rowData['issue_end'] = $rowData['issue_start'] + $sub_issues - 1;
            $rowData['order_id'] = $line[14];
        }
        return $rowData;

        /*
            TODO:
            convert years + order_date into issue_end/issue_start
        */
    }

    function export(){
        $is = range(70,120);
        $issues = array();  
        foreach ($is as $v) $issues[$v] = $v;  

		$this->set('issues', $issues);

        if($this->RequestHandler->isPost()){
            $conds = array("issue_end >=" => $this->params['data']['Subscriber']['issue_end']);
            $subs = $this->Subscriber->find('all', 
                array(
                    'conditions'=>$conds,
                    'order' => array('country', 'lastname'),
                )
            );

            $filename = 'Subscribers.csv';
            $filepath = 'exports/' . $filename;
            $fh = fopen(WWW_ROOT.'/exports/Subscribers.csv','w') or die("Can't open export file");

            fputcsv($fh, array_keys($subs[0]['Subscriber'])); 
            foreach($subs as $s){
                fputcsv($fh,$s['Subscriber']); 
            }
            fclose($fh);

		    $this->set('filepath', $filepath);
		    $this->set('subscribers', $subs);
		    $this->set('data', $this->params['data']['Subscriber']['issue_end']);
//            $this->layout = null;
//            $this->autoLayout = false;
        }
    }

    function _getFields(){
	    return a('First Name','Last Name','Company','Email','Phone','Address',
			'City','State','Zip','Country','Chapter','Quantity','Begin Issue','End Issue');
    }

	function import() {
        #MAX ROWS
        $maxRows = 850;
        $saveDelim = ',';
        $tmpFilePath = WWW_ROOT.'/uploads/tmp_subscribers.csv';

        $mode = '';
        if(($this->params['form'] && $this->params['form']['mode'] == 'cart') ||
            ($this->params['pass'] && $this->params['pass'][0] == 'cart')){ 
            $mode = 'cart'; 
        }

        # Display field names
	    $default_fields = $this->_getFields();
        $cart_fields = a('First Name','Last Name','Company','Email','Phone','Address',
            'City','State','Zip','Country','Chapter','Quantity','Order Date',
            'Num Issues','Order Id');
        $fields = $default_fields;
       
        if($mode == 'cart'){ $fields = $cart_fields; }
        
		$usr_msg = "";

		// just show instructions
		if(!empty($this->data) && !empty($this->data['Subscriber']['TheFile']) &&
			is_uploaded_file($this->data['Subscriber']['TheFile']['tmp_name'])) {
			$handle = fopen($this->data['Subscriber']['TheFile']['tmp_name'], "r");

			$tmpfile = new File($tmpFilePath, true);
			if(!$tmpfile){
				$usr_msg = "Error: Could not open temp file for writing.";
			}
			$tmpfile->write('');  # Clear the file
		
			$lines = a();
			$badlines = a();
			$cdel = $this->data['Subscriber']['Delim'];

			while(($data = fgetcsv($handle, $maxRows, ',')) != FALSE){
				$line = array_slice($data,0,count($fields));
                $csvData = $this->_csvtodata($line, $mode);
				$this->Subscriber->set( $csvData );

                $tmpData = a();
				foreach($csvData as $l){
					$cvsData[] = trim($l);
				}

				if( !$this->Subscriber->validates()){
					$line[] = join('. ',array_values($this->Subscriber->invalidFields()));
					$badlines[] = $line;
				} else {
					$lines[] = $csvData;
                    $delStr = '"' . $saveDelim . '"';
					$tmpline = '"' . join($delStr, $csvData) . '"' . "\n";
					$this->log("Appended " . $tmpline);
					$tmpfile->append($tmpline);
				}

			}
			fclose($handle);
			$tmpfile->close();

            #At this piont, we've converted, so mode not needed.
            $mode = '';
            $fields = $fields;
		}

        # Import from the temp CSV
		if(isset($this->data['Subscriber']['ingest'])){
			$this->log("Ingesting");
			$handle = fopen($tmpFilePath, "r");
			$tmpfile = new File(WWW_ROOT.'/uploads/tmp_subscribers.csv', true);
			if(!$tmpfile){
				$usr_msg = "Error: Could not open temp file for writing.";
			}
            $success = true;
            $subscribed_count = 0;
            $existing_count = 0;
			while(($data = fgetcsv($handle, $maxRows, $saveDelim)) != FALSE){
                $csvData = $this->_csvtodata($data);

                # Don't overwrite newer existing subscriptions
                if($this->existing_subs($csvData)){
                    $existing_count++;
                    continue;
                }

                # Delete any existing subscriber
                $this->delete_old_subs($csvData);
    
                $s = new $this->Subscriber;
                $subscribed_count++;
                if(!$s->save($csvData, true, $this->Subscriber->whitelist)){
				    $usr_msg .= "Error on save: " . join(',', $data);
                    $success = false;
                }
            }
            if($success){
                $usr_msg = "Your import is complete. Thank you. $subscribed_count people were subscribed. $existing_count people had pre-existing subscriptions.";
            }
            


            // imoport
            # TODO: Add expire-date logic to show/index/import pages
            # TODO: Better data-validation?
            
            # TODO: Check for dupes (same firstname, lastname, address, city, zip)
            # if dupe found, compare last issue, only update if new last issue is later.
            # calculate last issue on list & show pages?
		}


		$delims = aa(',','Comma ,',';','Semicolon ;');
		$this->set(compact('lines','badlines','delims','fields','maxRows','mode'));
		$this->set('usr_msg',$usr_msg);



	}

    function delete_old_subs($new_data){
        $this->Subscriber->deleteAll(array(
                'firstname'=>$new_data['firstname'],
                'lastname'=>$new_data['lastname'],
                'address'=>$new_data['address'],
                'postcode'=>$new_data['postcode'],
                'issue_end <'=>$new_data['issue_end']
            )
        );
    }

    # Return true if the subscriber exists with a later last_issue
    function existing_subs($new_data){
        if($this->Subscriber->find('all', array(
                'conditions'=>array(
                    'firstname'=>$new_data['firstname'],
                    'lastname'=>$new_data['lastname'],
                    'address'=>$new_data['address'],
                    'postcode'=>$new_data['postcode'],
                    'issue_end >='=>$new_data['issue_end']
                ), 
                'recursive'=>0
            )
        )){
            return true;
        }
        return false;
    }


	function add() {
		if (!empty($this->data)) {
			$this->Subscriber->create();
			if ($this->Subscriber->save($this->data)) {
				$this->Session->setFlash(__('The Subscriber has been saved', true));
				$this->redirect(array('action'=>'index'));
			} else {
				$this->Session->setFlash(__('The Subscriber could not be saved. Please, try again.', true));
			}
		}
	}

	function edit($id = null) {
		if (!$id && empty($this->data)) {
			$this->Session->setFlash(__('Invalid Subscriber', true));
			$this->redirect(array('action'=>'index'));
		}
		if (!empty($this->data)) {
			if ($this->Subscriber->save($this->data)) {
				$this->Session->setFlash(__('The Subscriber has been saved', true));
				$this->redirect(array('action'=>'index'));
			} else {
				$this->Session->setFlash(__('The Subscriber could not be saved. Please, try again.', true));
			}
		}
		if (empty($this->data)) {
			$this->data = $this->Subscriber->read(null, $id);
		}
	}



	function delete($id = null) {
		if (!$id) {
			$this->Session->setFlash(__('Invalid id for Subscriber', true));
			$this->redirect(array('action'=>'index'));
		}
		if ($this->Subscriber->del($id)) {
			$this->Session->setFlash(__('Subscriber deleted', true));
			$this->redirect(array('action'=>'index'));
		}
	}


    function magento_import() {
        // Check the action is being invoked by the cron dispatcher
        if (!defined('CRON_DISPATCHER')) { $this->redirect('/'); exit(); }

        $this->layout = null; // turn off the layout
        
        // do something here
    }


	function admin_index() {
		$this->Subscriber->recursive = 0;
		$this->set('subscribers', $this->paginate());
	}

	function admin_view($id = null) {
		if (!$id) {
			$this->Session->setFlash(__('Invalid Subscriber.', true));
			$this->redirect(array('action'=>'index'));
		}
		$this->set('subscriber', $this->Subscriber->read(null, $id));
	}

	function admin_add() {
		if (!empty($this->data)) {
			$this->Subscriber->create();
			if ($this->Subscriber->save($this->data)) {
				$this->Session->setFlash(__('The Subscriber has been saved', true));
				$this->redirect(array('action'=>'index'));
			} else {
				$this->Session->setFlash(__('The Subscriber could not be saved. Please, try again.', true));
			}
		}
	}

	function admin_edit($id = null) {
		if (!$id && empty($this->data)) {
			$this->Session->setFlash(__('Invalid Subscriber', true));
			$this->redirect(array('action'=>'index'));
		}
		if (!empty($this->data)) {
			if ($this->Subscriber->save($this->data)) {
				$this->Session->setFlash(__('The Subscriber has been saved', true));
				$this->redirect(array('action'=>'index'));
			} else {
				$this->Session->setFlash(__('The Subscriber could not be saved. Please, try again.', true));
			}
		}
		if (empty($this->data)) {
			$this->data = $this->Subscriber->read(null, $id);
		}
	}

	function admin_delete($id = null) {
		if (!$id) {
			$this->Session->setFlash(__('Invalid id for Subscriber', true));
			$this->redirect(array('action'=>'index'));
		}
		if ($this->Subscriber->del($id)) {
			$this->Session->setFlash(__('Subscriber deleted', true));
			$this->redirect(array('action'=>'index'));
		}
	}

}
?>
