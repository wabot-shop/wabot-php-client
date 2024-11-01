<?php

require_once 'WabotApiClient.php';

try {
    $clientId     = 'YOUR_CLIENT_ID';
    $clientSecret = 'YOUR_CLIENT_SECRET';

    $wabot = new WabotApiClient($clientId, $clientSecret);

    // Authenticate
    $wabot->authenticate();

    // Get Templates
    $templates = $wabot->getTemplates();

    foreach ($templates as $template) {
        echo "Template ID: {$template['template_id']}, Name: {$template['name']}\n";
    }

    // Send a message
    $to          = '+1234567890';
    $templateId  = '339'; // Replace with your template ID
    $templateParams = ['John', 'your email address'];

    $response = $wabot->sendMessage($to, $templateId, $templateParams);

    echo "Message sent successfully.\n";

    // Logout
    $wabot->logout();

} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
