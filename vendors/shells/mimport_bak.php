<?php
    class MimportShell extends Shell {

        var $uses = array('Subscriber');

        function main(){
            $lock_filename = "/tmp/mgnt-order-lock.txt";
            $this->out('Initializing...');

            # -----------------------------------------------------------------------
            # Get date of last update
            $last_updated = file_get_contents($lock_filename);

            if($last_updated != '' && ! $last_updated){
                $this->out("Problem reading datefile.\n");
                exit;
            }


            $debug = true;

            if( isset($debug)){
                $this->out("Last Updated: $last_updated<br/>");
            }



            # -----------------------------------------------------------------------
            # Initialize SOAP and API
            $client = new SoapClient('http://filmmakermagazine.net/buy/api/?wsdl');

            // API authentification,
            // we should get session token
            $user = 'WebServicesUser';
            $apiKey = 'mgntfmmag5b7a04c8';
            $session = $client->login($user, $apiKey);

            if( isset($debug)){
                $this->out("Soap Initialized.");
            }


            # -----------------------------------------------------------------------
            # Get list of orders. Filter orders on the following:
            # Status = complete
            # Updated since the date of the last grab. 
            # Note: 'From' is inclusive of the date. So orders on 10-14-2009 will be selected 
            # if your say 'from 10-14-2009'
            # Todo: what if they buy and then cancel later?

            # Remember: site5 cron is on central time, magento is on eastern time!
            $results = $client->call($session, 'sales_order.list',
                array(
                    array(
                        'updated_at' => array('from' => $last_updated),
                        'status' => array('eq' => 'complete')
                    )
                )
            );

            # For each order
            foreach($results as $r){

                # Get customer address
                $o_id = $r{'increment_id'};
                $order = $client->call($session, 'sales_order.info', $o_id);
                $ad = $order{'shipping_address'};
                $items = $order{'items'};
                if(isset($debug)){
                    $this->out("-------------------------------------------");
                    $this->out("Updated: " . $order{'updated_at'});
                    $this->out("First: " . $ad{'firstname'});
                    $this->out("Last: " . $ad{'lastname'});
                    $this->out("Company: " . $ad{'company'});
                    $this->out("Email " . $r{'customer_email'});
                    $this->out("Order Id " . $r{'increment_id'});
                    $this->out("Address:");
                    $this->out("'" . $ad{'street'} . "'");
                    $this->out($ad{'city'});
                    $this->out($ad{'region'});
                    $this->out($ad{'postcode'});
                    $this->out(print_r($ad));
                    $this->out("-------------------------------------------");
                }

                # For each line item in the order
                foreach($items as $i){
                    # Only import subscriptions
                    if(preg_match('/^sub_(\d+?)_/',$i{'sku'},$matches)){
                        $issues = $matches[1];
                        if(isset($debug)){
                            $this->out("Found a subscription item( $issues issues )");
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
                                'email' => $r{'customer_email'},
                                'address' => $ad{'street'},
                                'city' => $ad{'city'},
                                'state' => $ad{'region'},
                                'postcode' => $ad{'postcode'},
                                'order' => $r{'increment_id'},
                                'source' => 'magento_import',
                                'issue_start' => $issue_start,
                                'issue_end' => $issue_end
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
                            $this->out("Found Existing Subscriber");
                            $this->out(print_r($found));
                            $this->Subscriber->id = $found['Subscriber']['id'];
                            $data['Subscriber']['issue_end'] = $found['Subscriber']['issue_end'] + $issues;
                        } else {
                            $this->out("Creating new subscriber.");
                            $this->Subscriber->create();
                        }

                        if (! $this->Subscriber->save($data)) {
                            $this->out("Problem saving.");
                        } else {
                            $this->out("Order saved.");
                        }
                    }
                }

            }


            // If you don't need the session anymore
            $client->endSession($session);

            # Write to lockfile
            $fp = fopen($lock_filename, "w");
            $date_now_str = date('Y-m-d h:m:s', time());

            if (flock($fp, LOCK_EX)) { // do an exclusive lock
                ftruncate($fp, 0);  // truncate file
                if(0 && isset($debug)){
                    fwrite($fp, '2009-11-10');
                } else {
                    fwrite($fp, $date_now_str);
                }
                flock($fp, LOCK_UN); // release the lock
            } else {
                echo "Couldn't get the lock!";
            }

            fclose($fp);



        }
    }





?>
