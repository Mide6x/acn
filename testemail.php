<?php
// Initialize cURL session
$curl = curl_init();

// Data to be sent in the POST request
$data = [
    'title' => 'John Doe',
    'emailsubject' => 'johndoe@example.com',
    'emailbody' => 'John Doe',
    'createdby' => 'johndoe@example.com'
];
// Set options for the GET request
curl_setopt_array($curl, [
    CURLOPT_URL => "http://localhost/emailnotificationsystem/api/post/createnotification.php", // Replace with your API URL
    CURLOPT_RETURNTRANSFER => true,  // Return the response as a string
    CURLOPT_POST => true,  // Send as POST request
    CURLOPT_POSTFIELDS => json_encode($data),  // Convert data to JSON
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json",
        "Authorization: Bearer YOUR_API_KEY" // Optional: Include API token if needed
    ]
]);

// Execute the request and store the response
$response = curl_exec($curl);
// Check for errors
if (curl_errno($curl)) {
    echo 'Error: ' . curl_error($curl);
} else {
    // Output the response
    echo $response;
}

// Close cURL session
curl_close($curl);
?>
