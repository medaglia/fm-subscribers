<?php

require_once('Date.php');

class Subscriber extends AppModel {

	var $name = 'Subscriber';
	var $validate = array(
		'id' => array('numeric'),
		'firstname' => array( 
			'rule' => array('maxLength', 40),
			'message' => 'Please enter 40 characters or less',
			'on' => 'update'
		),
		'lastname' => array( 
			'rule' => array('maxLength', 60),
			'message' => 'Please enter 60 characters or less'
		),
		'company' => array( 
			'rule' => array('maxLength', 60),
			'message' => 'Please enter 60 characters or less'
		),
		'email' => array(
			'rule' => 'email',
			'message' => 'Please enter a valid email',
			'allowEmpty' => true
		),
		'phone' => array( 
			'rule' => array('maxLength', 40),
			'message' => 'Please enter 40 characters or less'
		),
		'address' => array(
			'rule' => array('maxLength', 250),
			'message' => 'Please enter 250 characters or less'
		),
		'city' => array(
			'rule' => array('maxLength', 60),
			'message' => 'Please enter 60 characters or less'
		),
		'state' => array(
			'rule' => array('maxLength', 60),
			'message' => 'Please enter 60 characters or less',
			'allowEmpty' => true
		),
		'country' => array(
			'rule' => array('maxLength', 80),
			'message' => 'Please enter 80 characters or less'
		),
		'postcode' => array(
			'rule' => array('maxLength', 30),
			'message' => 'Please enter 30 characters or less'
		),
		'source' => array( 
			'rule' => array('maxLength', 60),
			'message' => 'Please enter 60 characters or less'
		),
		'issue_end' => array(
            'rule' => 'greaterThanStartIssue',
            'message' => 'Start Issue must be before End Issue'
        ),
		'invoice_ids' => array(
			'rule' => array('maxLength', 350),
			'message' => 'Please enter 350 characters or less'
        )
	);

    function greaterThanStartIssue($data){
        $issue_start = $this->data['Subscriber']['issue_start'];
        if($issue_start <= $data['issue_end']){
            return true;
        }
        return false;
    }

    function lastIssueDate(){
        return $this->_issueToDate($this->field('issue_end'));
    }

    function _issueToDate($issue,$format='MM/DD/YYYY'){
        $first_year_issue = 1992;

        # Right now only deal with numeric issues
        if(is_numeric($issue)){
           (int)$issue; 
           $year = ($issue / 4) + $first_year_issue;
           $year = (int)$year;
           if($issue % 4 == 0) $month = 7; #Summer
           if($issue % 4 == 1) { $month = 10; } #Fall
           if($issue % 4 == 2) { $month = 1; $year++; } #Winter
           if($issue % 4 == 3) { $month = 4; $year++; }#Spring
           $day = 15;
           if($format=='MM/DD/YYYY'){
                return sprintf('%d/%d/%04d',$month,$day,$year);
           } elseif($format='YYYY-MM-DD'){
                return sprintf('%04d-%d-%d',$year,$month,$day);
           }
        } else {
            return false;
        }
    }

    # $date should be formatted like YYYY-MM-DD
    # returns the issue the person will receive if they subscribe on the given date
    function _dateToIssue($date){
        $dt = new Date();
        $dt->setDate($date.' 00:00:00');
        $dtm = $dt->getMonth();

        #First lets round off the day
        if($dt->getDay() > 25){
            $dtm++;
        }

        # Get the month modifier (cause issue #1 started in the fall)
        $mod = 0;
        if($dtm >=1){$mod = -1;} #2
        if($dtm >=4){$mod = 0;} #3
        if($dtm >=7){$mod = 1;} #0
        if($dtm >=10){$mod = 2;} #1

        $first_issue_dt = new Date();
        $first_issue_dt->setDate($this->_issueToDate(1,'YYYY-MM-DD'.' 00:00:00'));
        $first_issue_y = $first_issue_dt->getYear();
        $ydiff = $dt->getYear() - $first_issue_y;
        if($ydiff < 0){return false;}

        return $ydiff * 4 + $mod;
    }

    
    function afterFind ($results){
        foreach ($results as $key => $val) {
            if($val && !empty($val['Subscriber']) && !empty($val['Subscriber']['issue_end'])){
                $results[$key]['Subscriber']['last_issue_date'] = $this->_issueToDate($val['Subscriber']['issue_end']);
            }
        }
        return $results;
    }

}
?>
