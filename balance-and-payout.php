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
 
 
 /** Create a shortcode to be used to get the balance **/ 
 add_shortcode('external_data', 'get_balance');

/* Create a shortcode to make the bank payout request */
 add_shortcode('bank_payout_request_form', 'bank_payout_request');
 
 
//Function to create headers to be used for the transaction
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
 
 /** Get balance function **/
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

 /** Bank Payout Processing Function **/    
 function bank_payout_request(){
     
    /**Get and Sanitize the Form data**/
     
     
    /**Call the API**/
     
    /**Get the API response**/
    
    
    
    /**Display the API response **/
    
    /**Create the form**/
    echo '
    <form action="" method="POST">
        <label id="notice">Fields with * are required </label>
        <label for="accno">Bank Account Number *:</label>
        <input type="text" id="accno" name="accno" required><br><br>

        <label for="amount">Amount *:</label>
        <input type="number" id="amount" name="amount" required><br><br>

        <label for="currency">Currency *:</label>
        <select name="currency">
            <option value="">Select...</option>
            <option value="KES">KES</option>
            <option value="USD">USD</option>
        </select>
        
        <label for="reason">Reason (Optional):</label>
        <textarea id="reason" name="reason"></textarea><br><br>

        <input type="submit" value="Submit">
    </form>';
   
    echo '
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        label {
            font-size: 16px;
            margin-bottom: 8px;
            display: block;  /* Ensures labels are on separate lines */
            color: #333;
        }
        
        input[type=text], input[type=number], input[type=password], textarea, input[type=email], select {
            width: 70%;
            height: 15px;
        }
        
        #notice {
            font-size: 13px;
        }
        
  
    </style>
    
    ' 
    ;
    
   
    
    
 
 }