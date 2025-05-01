<?php
//  Replace with your reCAPTCHA secret key
$recaptcha_secret = '6LeMJysrAAAAAHIh-CIrd-3uq7fx_VPRbPNRCyW_';

// Get the reCAPTCHA token from the POST data
if (isset($_POST['g-recaptcha-response'])) {
   $token = $_POST['g-recaptcha-response'];
} else {
   $token = '';
}


// Get the form data
$name = isset($_POST['name']) ? strip_tags($_POST['name']) : '';
$email = isset($_POST['email']) ? filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) : '';
$message = isset($_POST['message']) ? strip_tags($_POST['message']) : '';

//  Initialize an array to store errors
$errors = array();

//  Perform basic validation
if (empty($name)) {
    $errors['name'] = 'Name is required.';
}
if (empty($email) || !$email) {
    $errors['email'] = 'Valid email is required.';
}
if (empty($message)) {
    $errors['message'] = 'Message is required.';
}


//  Verify the reCAPTCHA token with Google if token exists
 if ($token) {
   $url = 'https://www.google.com/recaptcha/api/siteverify';
   $data = array(
       'secret' => $recaptcha_secret,
       'response' => $token
   );
   $options = array(
       'http' => array(
           'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
           'method'  => 'POST',
           'content' => http_build_query($data)
       )
   );
   $context  = stream_context_create($options);
   $result = file_get_contents($url, false, $context);
   $response = json_decode($result);
 } else {
   $response = array('success' => false);
 }


if (empty($errors) && $response->success) {
    //  reCAPTCHA verification successful and no other errors
    //  Process the form data (e.g., send an email)
    $to = 'your-email@example.com';  //  Replace with your email address
    $subject = 'New Contact Form Submission';
    $body = "Name: $name\nEmail: $email\nMessage: $message";
    $headers = 'From: webmaster@example.com';  //  Replace with your domain

    if (mail($to, $subject, $body, $headers)) {
        //  Email sent successfully
        echo json_encode(array('success' => true));
    } else {
        //  Error sending email
        echo json_encode(array('success' => false, 'message' => 'Failed to send email.'));
    }
} else {
    //  reCAPTCHA verification failed or other errors
    if (!$response->success) {
       $errors['recaptcha'] = 'reCAPTCHA verification failed.';
    }
    echo json_encode(array('success' => false, 'errors' => $errors, 'message' => 'Form submission failed.'));
}
?>
