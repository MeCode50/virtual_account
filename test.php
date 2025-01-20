<?php
session_start();
include_once("config.php");
require("sendgrid-php/sendgrid-php.php");

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

$mytime = gmdate("Y-m-d H:i:s");
$mytime = date('Y-m-d H:i:s', strtotime('+1 hour', strtotime($mytime)));

$mydate = gmdate("Y-m-d");
$mydate = date('Y-m-d', strtotime('+1 hour', strtotime($mydate)));

$userId = $_SESSION["user_id"];


if (isset($_POST["act_now"])) {
    $reg_channel = $_POST["reg_channel"];
    $otpCode = $_POST["otpCode"];
    $activationType = $_POST["activation_type"];
    $paymentOption = $_POST["payment_option"];
    $duration = $_POST["duration"];
    $itemSubCat_s = $_POST["item_sub_cat"];
    $fname = strtolower($_POST["fname"]);
    $fname = ucwords($fname);
    $lname = strtolower($_POST["lname"]);
    $lname = ucfirst($lname);
    $email = strtolower($_POST["email"]);
    $phone = $_POST["phone"];
    $address = htmlspecialchars($_POST["address"]);
    $deviceIMEI = $_POST["imei"];
    $salesSentinelCost = $_POST["sentinel_cost"];
    $deviceCost = $_POST["device_cost"];
    $pincode = $_POST["pin"];
    $sentinelPackage = $_POST["sentinel_package"];
    $deviceBrand = $_POST["deviceBrand"];
    $deviceId = $_POST["device_id"];
    $country = $_POST["country"];
    $mbeID = $userId;

    $salesStore = $_POST["store"];
    $sql_store = mysqli_query($connect_android, "SELECT * FROM store WHERE store_id='$salesStore' ");
    while ($row_store = mysqli_fetch_array($sql_store)) {
        $storeName = $row_store["store_name"];
        $companyId = $row_store["company_id"];
        $store_id = $row['store_id'];
        $store_email = $row['store_email'];
        $store_dva = $row['store_dva'];
        $Store_account_name = $row['paystack_account_name'];
        $store_account_number = $row['paystack_account_number'];
        $store_bank_name = $row['paystack_bank_name'];
    }

    //Remove space character and special character
    $fname = trim($fname);
    $lname = trim($lname);
    $fname = str_replace("'", "", $fname);
    $lname = str_replace("'", "", $lname);
    $address = str_replace("'", "", $address);
    $email = preg_replace("/\s+/", "", $email);
    $deviceIMEI = preg_replace("/\s+/", "", $deviceIMEI);
    $salesSentinelCost = preg_replace("/\s+/", "", $salesSentinelCost);
    $salesSentinelCost = str_replace(",", "", $salesSentinelCost);
    $deviceCost = preg_replace("/\s+/", "", $deviceCost);
    $deviceCost = str_replace(",", "", $deviceCost);

    //hold data in session
    $_SESSION["fname"] = $fname;
    $_SESSION["lname"] = $lname;
    $_SESSION["phone"] = $phone;
    $_SESSION["email"] = $email;
    $_SESSION["address"] = $address;
    $_SESSION["country"] = $country;
    $_SESSION["pincode"] = $pincode;
    $_SESSION["deviceCost"] = $deviceCost;
    $_SESSION["sentinelCost"] = $salesSentinelCost;
    $_SESSION["deviceId"] = $deviceId;
    $_SESSION["deviceBrand"] = $deviceBrand;
    $_SESSION["deviceIMEI"] = $deviceIMEI;
    $_SESSION["otpCode"] = $otpCode;
    $_SESSION['salesStoreId'] = $salesStore;
    $_SESSION['salesStoreName'] = $storeName;

    //check if OTP is valid
    $sql_chk_otp = mysqli_query($connect_android, "SELECT * FROM activation_otp WHERE otp='$otpCode' AND status='0' ORDER BY sn DESC LIMIT 1");
    while ($row_chk_otp = mysqli_fetch_array($sql_chk_otp)) {
        $otpRegDate = $row_chk_otp["created_at"];

        //limit time to 1hr (60*60)
        $otpDate = strtotime($otpRegDate);
        $otpExpDate = $otpDate + (60 * 60);
        $otpExpDate = date('Y-m-d H:i:s', $otpExpDate);
    }

    //Match device name and sentinel price from device name
    $sql_sentinel_price = mysqli_query($connect_android, "SELECT * FROM device_prices WHERE id='$deviceId' LIMIT 1");
    while ($row_sentinel_price = mysqli_fetch_array($sql_sentinel_price)) {
        $productName = $row_sentinel_price["device_name"];
        $deviceModel = $row_sentinel_price["device_model_number"];
        $deviceManufacturer = strtolower($row_sentinel_price["device_manufacturer"]);
        $pin_type = $row_sentinel_price["device_type"];
        $insureDeviceCost = $row_sentinel_price["price"];
        $devfinDevicePrice = $row_sentinel_price["devfin_price"];
        $sentinelSLD = $row_sentinel_price["SLD"];
        $sentinelSLDA = $row_sentinel_price["SLDA"];
        $sentinelSAP = $row_sentinel_price["SAP"];
        if ($deviceCost == "") {
            $deviceCost = $insureDeviceCost;
        }
    }
    $_SESSION["deviceName"] = $productName;

    //calculate sentinel price and check if sentiflex activation is selected
    if ($pin_type == "android") {
        //check type
        //if($activationType==1){
        if ($sentinelPackage == "SLD") {
            $sentinelPrice = $sentinelSLD;
        }
        if ($sentinelPackage == "Sentinel Comfy 3 Months SLD") {
            $sentinelPrice = $sentinelSLD * 0.25;
        }
        if ($sentinelPackage == "Sentinel Comfy 6 Months SLD") {
            $sentinelPrice = $sentinelSLD * 0.5;
        }
        if ($sentinelPackage == "SLDA") {
            $sentinelPrice = $sentinelSLDA;
        }
        if ($sentinelPackage == "SAP") {
            $sentinelPrice = $sentinelSAP;
        }
        if ($sentinelPackage == "RaaS Basic") {
            $sentinelPrice = 2000;
        }
        if ($sentinelPackage == "RaaS Premium") {
            $sentinelPrice = 5000;
        }
        if ($sentinelPackage == "SENTICARE") {
            $sentinelPrice = 5000;
            $paymentOption = "Device Loan";
        }
        /*}else{
      if ($devfinDevicePrice < 100000){
        $sentinelPrice=7000;
        $sentinelPackage="SENTICARE";
        $paymentOption="Device Loan";
      }else{
        $sentinelPrice=0.08*$devfinDevicePrice;
        $sentinelPackage="SENTIPLUS"; 
        $paymentOption="Device Loan";
      } 
    }*/
    } elseif ($pin_type == "ios") {
        //check type
        //if($activationType==1){
        if ($sentinelPackage == "SLD") {
            $sentinelPrice = $sentinelSLD;
        }
        if ($sentinelPackage == "Sentinel Comfy 3 Months SLD") {
            $sentinelPrice = $sentinelSLD * 0.25;
        }
        if ($sentinelPackage == "Sentinel Comfy 6 Months SLD") {
            $sentinelPrice = $sentinelSLD * 0.5;
        }

        if ($sentinelPackage == "RaaS Basic") {
            $sentinelPrice = 2000;
        }
        if ($sentinelPackage == "RaaS Premium") {
            $sentinelPrice = 5000;
        }
        /*}else{
      $sentinelPrice=0.05*$devfinDevicePrice;
      $sentinelPackage="SLD"; 
      $paymentOption="Device Loan"; 
    }*/
    }

    if ($sql_chk_otp->num_rows > 0) {
        //check if OTP time is valid
        if ($mytime <= $otpExpDate) {
            //Check if IMEI is in use on sentinel server
            $sql_chk_imeiused = mysqli_query($connect_android, "SELECT * FROM device_tag_register WHERE reg_imei='$deviceIMEI' LIMIT 1");
            if (!$sql_chk_imeiused->num_rows > 0) {
                mysqli_free_result($sql_chk_imeiused);
                //Upload image file
                $target_dir = "../../manager/admin/android/activation_images/";
                $target_dir2 = "../../manager/admin/android/activation_images/";
                $target_dir3 = "../../manager/admin/android/activation_images/";
                $target_file = $target_dir . basename($_FILES["activationImage"]["name"]);
                $target_file2 = $target_dir2 . basename($_FILES["activationImage2"]["name"]);
                $target_file3 = $target_dir3 . basename($_FILES["activationImage3"]["name"]);
                // Change the picture name to a randomly generated name before uploading  
                $filenamex = basename($_FILES['activationImage']['name']);
                $filenamex2 = basename($_FILES['activationImage2']['name']);
                $filenamex3 = basename($_FILES['activationImage3']['name']);
                $extensionx = pathinfo($filenamex, PATHINFO_EXTENSION);
                $extensionx2 = pathinfo($filenamex2, PATHINFO_EXTENSION);
                $extensionx3 = pathinfo($filenamex3, PATHINFO_EXTENSION);
                $permitted_chars = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                $newx = substr(str_shuffle($permitted_chars), 0, 32) . '.' . $extensionx;
                $newx2 = substr(str_shuffle($permitted_chars), 0, 32) . '.' . $extensionx2;
                $newx3 = substr(str_shuffle($permitted_chars), 0, 32) . '.' . $extensionx3;
                //upload image
                move_uploaded_file($_FILES["activationImage"]["tmp_name"], "../../manager/admin/android/activation_images/$newx");
                move_uploaded_file($_FILES["activationImage2"]["tmp_name"], "../../manager/admin/android/activation_images/$newx2");
                move_uploaded_file($_FILES["activationImage3"]["tmp_name"], "../../manager/admin/android/activation_images/$newx3");

                $permitted_chars = 'ABCDEFGHIJKLMNPQRSTUVWXYZ1234567890';
                $refId = substr(str_shuffle($permitted_chars), 0, 10);
                $refId = "RELAY" . $refId;

                //Store in open payment
                mysqli_query($connect_android, "INSERT INTO open_payment (company_id,store_id,pin_used,otp_code,reg_channel,first_name,last_name,email,phone_num,address,country,devfin_ref,mbe_id,device_manufacturer,reg_device,device_id,device_mod,device_price,device_type,activation_type,reg_imei,subscription,package,sentinel_price,sales_sentinel_price,payment_type,activation_form,sentinel_block_form,sentinel_scratch_card,duration,sentiflex_subcategory,created_at) VALUES ('$companyId','$salesStore','No','$otpCode','$reg_channel','$fname','$lname','$email','$phone','$address','$country','$refId','$mbeID','$deviceManufacturer','$productName','$deviceId','$deviceModel','$deviceCost','$pin_type','$activationType','$deviceIMEI','Annually','$sentinelPackage','$sentinelPrice','$salesSentinelCost','$paymentOption','$newx','$newx2','$newx3','$duration','$itemSubCat_s','$mytime')");

                //check payment option
                if ($paymentOption == "Pay Small Small") {
                    //post to sentiflex
                    mysqli_query($connect_sentiflex, "INSERT INTO sentinel_application (reference_id,last_name,first_name,phone_number_1,email,imei,residential_address,store,source,device_name,device_id,sentinel,sentinel_price,duration,created_at) VALUES ('$refId','$lname','$fname','$phone','$email','$deviceIMEI','$address','$storeName','Relay','$productName','$deviceId','$sentinelPackage','$sentinelPrice','$duration','$mytime')");

                    //go to loan managament page
                    if (!headers_sent()) {
                        header("Location: https://www.sentiflex.com/sentinel/index2.php?ref_id=" . $refId);
                        exit;
                    }
                } else {
                    if ($paymentOption == "Virtual Account Payment") {
                        // Check if the store's DVA already exists
                        if (!empty($store_dva)) {


                            $_SESSION["response2"] =
                                "Account Name: " . $Store_account_name . "<br>" . "Account Number: " . $store_account_number . "<br/>" . "Bank Name: " . $store_bank_name;

                            if (!headers_sent()) {
                                header("Location: test2.php");
                                exit;
                            }
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
                                "email" => $store_email
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
                                    "preferred_bank" => "titan-paystack",
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

                                    $_SESSION["response2"] =
                                        "Account Name: " . $account_name . "<br>" . "Account Number: " . $account_number . "<br/>" . "Bank Name: " . $bank_name;


                                    if (!headers_sent()) {
                                        header("Location: test2.php");
                                        exit;
                                    }

                                    // Save the created account details to the database
                                    $updateStoreAccountDetails = mysqli_query(
                                        $connect_android,
                                        "UPDATE store SET paystack_account_name='$account_name', paystack_account_number='$account_number', paystack_bank_name='$bank_name', store_dva='1' WHERE store_id = '$store_id'"
                                    );

                                    if ($updateStoreAccountDetails) {
                                        echo "Account details updated successfully.";
                                    } else {
                                        echo "Failed to update account details: " . mysqli_error($connect_android);
                                    }
                                } else {
                                    echo "Account details not found in the response.";
                                }
                            } else {
                                echo "Customer code not found.";
                            }
                        }
                    } else {
                        if ($sentinelPrice > 0) {
                            //check if bundle is SAP and try enrollment first, continue only when device is enrolled or exist
                            if (($deviceManufacturer == "samsung" or $deviceManufacturer == "samsung tab") and ($sentinelPackage == "SLDA" or $sentinelPackage == "SAP" or $sentinelPackage == "SENTICARE" or $sentinelPackage == "SENTIPLUS")) {
                                //update
                                mysqli_query($connect_android, "UPDATE open_payment SET package='$sentinelPackage',sentinel_price='$sentinelPrice',pin_used='No',pin_code='$pincode',lock_provider='knox',lock_state='unlock' WHERE devfin_ref='$refId'");

                                //enroll using knox
                                $curl = curl_init();
                                curl_setopt_array($curl, array(
                                    CURLOPT_URL => "https://devfinapi.sentinelock.com/v1/prod/post/",
                                    CURLOPT_RETURNTRANSFER => true,
                                    CURLOPT_ENCODING => "",
                                    CURLOPT_MAXREDIRS => 10,
                                    CURLOPT_TIMEOUT => 0,
                                    CURLOPT_FOLLOWLOCATION => true,
                                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                                    CURLOPT_CUSTOMREQUEST => "POST",
                                    CURLOPT_POSTFIELDS => array('command' => 'enrollDevice', 'requestApikey' => 'lGwQ4BUjeTgpc&kx5b6NHunFML38', 'deviceIMEI' => $deviceIMEI),
                                ));
                                $response = curl_exec($curl);
                                curl_close($curl);
                                $character = json_decode($response);
                                $checkStatus = $character->statusCode;

                                if ($checkStatus == 2000000) {
                                    //Check if device enrolled
                                    $curl = curl_init();
                                    curl_setopt_array($curl, array(
                                        CURLOPT_URL => "https://devfinapi.sentinelock.com/v1/prod/post/",
                                        CURLOPT_RETURNTRANSFER => true,
                                        CURLOPT_ENCODING => "",
                                        CURLOPT_MAXREDIRS => 10,
                                        CURLOPT_TIMEOUT => 0,
                                        CURLOPT_FOLLOWLOCATION => true,
                                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                                        CURLOPT_CUSTOMREQUEST => "POST",
                                        CURLOPT_POSTFIELDS => array('command' => 'triggerDevice', 'trigger' => 'log', 'requestApikey' => 'lGwQ4BUjeTgpc&kx5b6NHunFML38', 'deviceIMEI' => $deviceIMEI),
                                    ));
                                    $response = curl_exec($curl);
                                    curl_close($curl);
                                    $character = json_decode($response);
                                    $action = $character->deviceLogs[0]->action;
                                    $deviceStatus = $character->deviceLogs[0]->deviceStatus;
                                    $details = $character->deviceLogs[0]->details;

                                    //log knox report
                                    mysqli_query($connect_android, "INSERT INTO samsung_knox_activation_log (device_imei,status_code,action,device_status,details,created_at) VALUES ('$deviceIMEI','$checkStatus','$action','$deviceStatus','$details','$mytime')");
                                    //Go to activation page
                                    if (!headers_sent()) {
                                        header("Location: test2_action.php?paymentReference=" . $refId . "&paymentStatus=1");
                                        exit;
                                    }
                                } else {
                                    /*$_SESSION["response2"]="Device enrollment failed... Please try again!";
                if (!headers_sent()){
                  header("Location: test2.php");
                  exit;
                } */

                                    //update
                                    mysqli_query($connect_android, "UPDATE open_payment SET package='$sentinelPackage',sentinel_price='$sentinelPrice',pin_used='No',pin_code='$pincode',lock_provider='datacultr',lock_state='unlock' WHERE devfin_ref='$refId'");

                                    //Go to QR Enrollment Page
                                    if (!headers_sent()) {
                                        header("Location: ../../enrollment/v2/index.php?paymentReference=" . $refId . "&paymentStatus=1&dc=yes");
                                        exit;
                                    }
                                }
                            } elseif ($deviceManufacturer != "samsung" and $pin_type = "android" and ($sentinelPackage == "SLDA" or $sentinelPackage == "SAP" or $sentinelPackage == "SENTICARE" or $sentinelPackage == "SENTIPLUS")) {
                                //update
                                mysqli_query($connect_android, "UPDATE open_payment SET package='$sentinelPackage',sentinel_price='$sentinelPrice',pin_used='No',pin_code='$pincode',lock_provider='datacultr',lock_state='unlock' WHERE devfin_ref='$refId'");

                                //Go to QR Enrollment Page
                                if (!headers_sent()) {
                                    header("Location: ../../enrollment/v2/index.php?paymentReference=" . $refId . "&paymentStatus=1");
                                    exit;
                                }
                            } else {
                                //update
                                mysqli_query($connect_android, "UPDATE open_payment SET package='$sentinelPackage',sentinel_price='$sentinelPrice',pin_used='No',pin_code='$pincode',lock_state='none' WHERE devfin_ref='$refId'");

                                //Go to activation page
                                if (!headers_sent()) {
                                    header("Location: action_2.php?paymentReference=" . $refId . "&paymentStatus=1");
                                    exit;
                                }
                            }
                        } else {
                            $_SESSION["response2"] = "No Package Price Found. This device cannot be activated on $sentinelPackage.";
                            if (!headers_sent()) {
                                header("Location: test2.php");
                                exit;
                            }
                        }
                    }
                }
            } else {
                $_SESSION["response2"] = "Device IMEI has been used. Please provide a new device IMEI.";
                if (!headers_sent()) {
                    header("Location: test2.php");
                    exit;
                }
            }
        } else {
            $_SESSION["response2"] = "OTP Code expired. Please generate another activation OTP code.";
            if (!headers_sent()) {
                header("Location: test2.php");
                exit;
            }
        }
    } else {
        $_SESSION["response2"] = "Invalid OTP Code. Please generate another activation OTP code.";
        if (!headers_sent()) {
            header("Location: test2.php");
            exit;
        }
    }
}
