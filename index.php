<?php
// Include the database connection file
include_once("function/db.php");

$store_id = $_POST['store'];
$paymentOption = $_POST['paymentOption'];

if ($paymentOption == 'Virtual Account Payment') {
    // Query to fetch store details with a specific store_id
    $sql_list_store2 = mysqli_query($connect_android, "SELECT * FROM store WHERE store_id='$store_id'");

    while ($row = mysqli_fetch_assoc($sql_list_store2)) {
        // Retrieve the store details
        $store_name = $row['store_name'];
        $store_email = $row['paystack_email'];
        $store_dva = $row['store_dva'];
        $Store_account_name = $row['paystack_account_name'];
        $store_account_number = $row['paystack_account_number'];
        $store_bank_name = $row['paystack_bank_name'];
        $store_phone = $row['phone_number'];

        // Check if the store's DVA already exists
        if (!empty($store_dva)) {

            echo "Account Name: " . $Store_account_name . "<br>" . "Account Number: " . $store_account_number . "<br/>" . "Bank Name: " . $store_bank_name;
        } else {
            // If DVA doesn't exist, process the store details to create it

            // Explode the store name into first and last name
            $name_parts = explode(" ", $store_name);
            $first_name = isset($name_parts[0]) ? $name_parts[0] : "Unknown";
            $last_name = isset($name_parts[1]) ? $name_parts[1] : "Unknown";

            // Call Paystack API to create a customer
            $paystack_api_key = "sk_live_cf7887404952d8034ac7d143a9cdb63dbe5e7606"; // Replace with your actual Paystack API key
            $url = "https://api.paystack.co/customer";
            $data = [
                "first_name" => $first_name,
                "last_name" => $last_name,
                "email" => $store_email,
                "phone" => $store_phone
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Authorization: Bearer " . $paystack_api_key,
                "Content-Type: application/json"
            ]);

            $response = curl_exec($ch);
            curl_close($ch);

            $response_data = json_decode($response, true);

            // Check if the response contains the required data
            if (isset($response_data['data']['customer_code'])) {


                // Save the customer ID from Paystack to create a Paystack DVA
                $cust_code = $response_data['data']['customer_code'];
                $updateStoreCustCode = mysqli_query(
                    $connect_android,
                    "UPDATE store SET paystack_customer_code='$cust_code' WHERE store_id = '$store_id'"
                );


                // Create the Paystack DVA account
                $url = "https://api.paystack.co/dedicated_account";

                $fields = [
                    "customer" => $cust_code,
                    "preferred_bank" => "titan-paystack"
                ];

                $fields_string = http_build_query($fields);

                // Open cURL connection
                $ch = curl_init();

                // Set cURL options
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    "Authorization: Bearer sk_live_cf7887404952d8034ac7d143a9cdb63dbe5e7606",
                    "Cache-Control: no-cache",
                ]);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                // Execute cURL request
                $response = curl_exec($ch);
                curl_close($ch);

                // Decode the JSON response
                $result_data = json_decode($response, true);

                // Check if the data exists in the response
                if (isset($result_data['data']['account_name']) && isset($result_data['data']['account_number'])) {
                    $account_name = $result_data['data']['account_name'];
                    $account_number = $result_data['data']['account_number'];
                    $bank_name = $result_data['data']['bank']['name'];

                    // Display account name and number
                    echo "Account Name: " . $account_name . "<br>";
                    echo "Account Number: " . $account_number . "<br/>";
                    echo "Bank Name: " . $bank_name;
                } else {

                    // Save the created account details to the database
                    $updateStoreAccountDetails = mysqli_query(
                        $connect_android,
                        "UPDATE store SET paystack_account_name='$account_name', paystack_account_number='$account_number', paystack_bank_name='$bank_name', store_dva='1' WHERE store_id = '$store_id'"
                    );

                    if (!$updateStoreAccountDetails) {
                        echo "Failed to update account details: " . mysqli_error($connect_android);
                    }

                    echo "Account details not found in the response.";
                }
            } else {
                echo "Customer code not found.";
            }
        }
    }
}
