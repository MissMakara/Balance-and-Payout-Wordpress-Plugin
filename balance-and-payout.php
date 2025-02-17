<?php
/**
 * Plugin Name: Balance and Payout
 * Author: Isabel
 * Description: Check your balance and make payouts from Cashia Payments
 * 
 * 
 * 
 * 
 * 
 * 
 ***/


//Basic security
 defined ('ABSPATH') or die('Unauthorized Access');
 
 
 //create a shortcode
 add_shortcode('external_data', 'get_balance');
 
 
//Function to create headers to be used for the transaction
//  
 function create_headers($url, $data){
     error_log('DEBUG: In header function');
     
     $key_id = '01949863-f22a-7201-a86b-31f67f4e7f29';
     $secret = 'pETwoTp4yfwScHmbIfO3yxgt-2IH-gDpXORAdum5Mg0=';
     $host = 'core-backend.stg.cashia.com';
     $nonce =uniqid();
     error_log('DEBUG: Nonce=>'.print_r($nonce, true));
     $timestamp = time();
     error_log('DEBUG: timestamp=>'.print_r($timestamp, true));
     $method = 'GET';
     $signature_raw = $host . $method . $timestamp . $nonce . $key_id;
     $signature = hash_hmac('sha256', $signature_raw, $secret);
     error_log('DEBUG: signature=>'.print_r($signature, true));
     
     $body_hash = hash_hmac('sha256', $signature, $secret);
     error_log('DEBUG: body hash =>'.print_r( $signature, true));
     
     $headers_object = [
         'X-Cashia-Key-ID'=> $key_id,
         'X-Cashia-Timestamp'=> $timestamp,
         'X-Cashia-Signature'=> $signature,
         'X-Cashia-Nonce'=> $nonce,
         'X-Cashia-Hash'=> $body_hash,
     ];
    
    return $headers_object;
 }
 
 
 function get_balance(){
     error_log('Admin area loaded');
     
     $url = 'http://core-backend.stg.cashia.com/api/v1/wallet/balance';
     $data = array(
    'currency' => 'KES'
    );
     
     //call create headers function and pass request object
     error_log('DEBUG: Calling headers function');
     
     //pass url and data to create headers function
     $headers = create_headers($url, $data);
     
     error_log('DEBUG:Received headers:'. print_r($headers, true));
     
     
     $response = wp_remote_get($url,
         $args = array(
             'method' => 'GET',
             'headers' => $headers,
             'body' => $data,
             
             )
         );
    

// 	function wp_remote_retrieve_body( $response ) {
// 	    error_log('DEBUG: Retrieving the response');
	    
// 	    if ( is_wp_error( $response ) || ! isset( $response['body'] ) ) {
// 	        $error_message = $response->get_error_message();
	 
	        
// 	        error_log("DEBUG: An error was received from the GET response");
// 	        error_log(print_r($error_message, true));
// 	        return [''];
	        
// 	    }
// 	    else{
// 	        return $response['body'];
// 	    }
	    
//     }
    error_log('DEBUG: Retrieving the response');
    
    if (is_wp_error($response)) {
        error_log("DEBUG: Received an error");
        $errors = $response->get_error_message();
        
        foreach ($errors as $error){
            error_log('ERROR'.print_r($error, true));
        }
        
        return ['Sorry, received an Error. Contact your administrator'];
    }
    else {
        error_log("DEBUG: Received an response");
        
        $response_body = wp_remote_retrieve_body($response);
        
        error_log('DEBUG: Response Body'.print_r($response_body, true));
        
        //return $response_body;
        
        $results = json_decode($response_body, true);
        //var_dump($results);
        // $count = 0;
        // if (is_array($results)) {
        //     foreach ($results as $wallet) {
        //         // Now $wallet is an individual JSON object (associative array
        //         echo 'Wallet ID: ' . $wallet['walletId'] . "<br>";
        //         echo 'Available: ' . $wallet['available'] . "<br>";
        //         echo 'Hold: ' . $wallet['hold'] . "\n";
        //         echo 'Total: ' . $wallet['total'] . "\n";
        //         echo 'Currency: ' . $wallet['currency'] . "\n";
        //         echo 'Message: ' . $wallet['message'] . "\n";
        //         echo "--------------------\n"; // Separator for clarity
        //         echo $count++;
        //         }
            
        // } 
        // else {
        //     echo "Invalid JSON response";
            
        // }
        // error_log("Results:". print_r($results, true));
        
        /** Display the balance data **/
        $html = '';
        $html .= '<table>';
        $html .= '<tr>';
        $html .= '<th> Balance Type </th>';
        $html .= '<th> Amount</th>';
        $html .= '</tr>';
        
        foreach ($results as $wallet){
            $html .= '<tr>';
            $html .= '<th> Available:</th>';
            $html .= '<td>'.$wallet['available'] .'</td>';
            $html .= '</tr>'; 
            
            $html .= '<tr>'; 
            $html .= '<th> Hold:</th>';
            $html .= '<td>'.$wallet['hold'] .'</td>';
            $html .= '</tr>';
            
            $html .= '<tr>'; 
            $html .= '<th> Total:</th>';
            $html .= '<td>'.$wallet['total'] .'</td>';
            $html .= '</tr>';
           
        }
        
        $html .= '</table>';
        
        return $html;
            
        
    
        
        
    }
    
    
}

     
 