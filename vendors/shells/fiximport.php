<?php
    class FiximportShell extends Shell {

        var $uses = array('Subscriber');

        function main(){
            $lock_filename = "/home/filmmake/public_html/cake/vendors/shells/lockfiles/fix-mgnt-order-lock.txt";
            $this->out('Initializing...');

            # -----------------------------------------------------------------------
            # Get date of last update
            $last_updated = file_get_contents($lock_filename);

            if($last_updated == '' || $last_updated == false){
                $this->out("Problem reading datefile.\n");
                exit;
            }

            $debug = true;

            if( isset($debug)){
                $this->out("Last Updated: $last_updated");
            }

            #TODO: Only run the cron once per day. Problems with magento's date limit field
            if(strtotime($last_updated) == false){
                $this->out("Problem reading datefile - Not a valid time.\n");
                exit;
            }
            $u = getdate(strtotime($last_updated));
            $timeUpdated = mktime($u['hours'],$u['minutes'],0,$u['mon'],$u['mday'],$u['year']);
            $dateDiff = time() - $timeUpdated;
            $fullDays = floor($dateDiff/(60*60*24));

            if($fullDays < 1){
                $this->out("Run less than a day ago. Stopping.");
                return;
            }

            # -----------------------------------------------------------------------
            # Initialize SOAP and API
            $client = new SoapClient('https://cordoba.site5.com/~filmmake/buy/index.php/api/?wsdl');

            // API authentification,
            // we should get session token
            $user = 'WebServicesUser';
            $apiKey = 'mgntfmmag5b7a04c8';
            $session = $client->login($user, $apiKey);


            # -----------------------------------------------------------------------
            # Get list of orders. Filter orders on the following:
            # Status = complete
            # Updated since the date of the last grab. 
            # Note: 'From' is inclusive of the date. So orders on 10-14-2009 will be selected 
            # if your say 'from 10-14-2009'
            # Todo: what if they buy and then cancel later?

            # Remember: site5 cron is on central time, magento is on eastern time!
            $results = $client->call($session, 'sales_order_invoice.list',
                array(
                    array(
                        'created_at' => array('from' => $last_updated),
                    )
                )
            );





            # For each order
            foreach($results as $r){

                # Get customer address
                $o_id = $r{'order_increment_id'};
                $order = $client->call($session, 'sales_order.info', $o_id);
                //print_r($order);
                $ad = $order{'shipping_address'};
                $items = $order{'items'};
                if(isset($debug)){
                    $this->out("-------------------------------------------");
                    //$this->out(print_r($r));
                    $this->out("Updated at: " . $order{'created_at'});
                    $this->out("Invoice Id: " . $r{'increment_id'});
                    $this->out("First Name: " . $ad{'firstname'});
                    $this->out("Last Name: " . $ad{'lastname'});
                    $this->out("Address:");
                    $this->out($ad{'street'});
                    $this->out($ad{'city'} . ", " . $ad{'region'} . " " . $ad{'postcode'});
                    //$this->out(print_r($ad));
                    $this->out("\nLooping through invoice items...");
                }

                # For each line item in the order
                foreach($items as $i){
                    # Only import subscriptions
                    if(preg_match('/^sub_(\d+?)_/',$i{'sku'},$matches)){
                        $issues = $matches[1];
                        if(isset($debug)){
                            $this->out("Found a subscription item ($issues issues)");
                        }
                        preg_match('/(\S+)\s/',$order{'updated_at'},$matches);
                        $updated = $matches[1];
                        $issue_start = $this->Subscriber->_dateToIssue($updated);
                        $issue_end = $issue_start + $issues - 1;
                        $this->out("Updated: $updated: $issue_start - $issue_end");

                        # Import into db
                        $data = Array(
                            'Subscriber' => Array
                            (
                                'firstname' => $ad{'firstname'},
                                'lastname' => $ad{'lastname'},
                                'company' => $ad{'company'},
                                'email' => $order{'customer_email'},
                                'address' => $ad{'street'},
                                'city' => $ad{'city'},
                                'state' => $ad{'region'},
                                'postcode' => $ad{'postcode'},
                                'country' => $ad{'country_id'},
                                //'order' => $r{'increment_id'},
                                'source' => 'magento_import',
                                'issue_start' => $issue_start,
                                'issue_end' => $issue_end,
								'invoice_ids' => $r{'increment_id'}
                            )
                        );

                        # If there is an existing subscription (address/name)
                        $found = $this->Subscriber->find('first', array(
                            'conditions'=>array(
                                'firstname'=>$ad{'firstname'},
                                'lastname'=>$ad{'lastname'},
                                'address'=>$ad{'street'},
                                'postcode'=>$ad{'postcode'}
                            )
                        ));

                        if($found){
                            # If exists, add $issues to it's issue_end
                            $this->out("----Subscriber exists in Subscribers Database...");
                            $this->out(print_r($found['Subscriber'],true));
                            $this->Subscriber->id = $found['Subscriber']['id'];
							$invoiceIdsStr = trim($found['Subscriber']['invoice_ids']);
							if(empty($invoiceIdsStr)){
								$data['Subscriber']['invoice_ids'] = $r{'increment_id'};
							} else {
								$invoiceIds = split(':', trim($invoiceIdsStr));
								if(in_array($r{'increment_id'}, $invoiceIds)){
									//This invoice was already processed. Skip.
									echo("Invoice " . $r{'increment_id'} . " already processed. Skipping.\n");
									//continue;
								} else {
									$invoiceIds[] = $r{'increment_id'};
									$data['Subscriber']['invoice_ids'] = implode(':',$invoiceIds);										
								}
							}
							//if the customer has not received their last issue already
							//extend the subscription, otherwise sign them up normally
							if($issue_start < $found['Subscriber']['issue_end']){
								echo("Extending subscription.\n");
								$data['Subscriber']['issue_end'] = $found['Subscriber']['issue_end'] + $issues;
							}
							
														
                        } else {
                            $this->out("Creating new subscriber...");
                            if(!$debug){
                                //$this->Subscriber->create();
                            }
                        }

                        if(true or !$debug){
							echo("Entering Subscriber:\n");
							print_r($data['Subscriber']);
                            /*
                            if (!$this->Subscriber->save($data)) {
                                $this->out("Order saved.");
                            } else {
                                $this->out("Problem saving.");
                            }
                            */
                        }
                    }
                }

            }

            $this->out("-------------------------------------------");

            // If you don't need the session anymore
            $client->endSession($session);

            # Write to lockfile
            $fp = fopen($lock_filename, "w");
            $d = getdate();
            $date_now_str = $d['year'] .'-'. $d['mon'] .'-'. $d['mday'] .' '. $d['hours'] .':'. $d['minutes'] .':'. $d['seconds'];

            if (flock($fp, LOCK_EX)) { // do an exclusive lock
                ftruncate($fp, 0);  // truncate file
                if(isset($debug)){
                    fwrite($fp, '2011-04-01 1:10:40');
                } else {
                    fwrite($fp, $date_now_str);
                    $this->out("Wrote to outfile: $date_now_str");
                }
                flock($fp, LOCK_UN); // release the lock
            } else {
                echo "Couldn't get the lock!";
            }

            fclose($fp);



        }
    }





?>
