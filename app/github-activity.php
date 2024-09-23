<?php

// Include the Composer autoload file
require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

// Load .env file
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

if ($argc !== 2) {
    echo "Usage: php github-activity.php <github-username>\n";
    exit(1);
}

$username = $argv[1];
$githubUserApiEndpoint = $_ENV["GITHUB_USER_ACTIVITY_ENDPOINT"] . $username . "/events";


// Set the HTTP headers for the request
$options = [
    'http' => [
        'header' => 'User-Agent: PHP CLI Script\r\n'
    ]
];

$context = stream_context_create($options);
$response = @file_get_contents($githubUserApiEndpoint, false, $context);

if ($response === false) {
    echo "Error fetching github user";
    exit(1);
}

$data = json_decode($response, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo "Error: Unable to parse the GitHub API response.\n";
    exit(1);
}

if (empty($data)) {
    echo "No recent activity found for user '$username'.\n";
    exit(0);
}



# ==============================================================================
# Output:
# Pushed 3 commits to [REPO_NAME]
# Opened a new issue in REPO_NAME
# Starred [REPO_NAME]  
# ==============================================================================

foreach ($data as $event) {
    $type = $event['type'];
    $repo = $event['repo']['name'];

    switch ($type) {
        case 'CreateEvent':
            echo "Created a new repository ". $repo . "\n";
            break;
        case 'PushEvent':
            echo "Pushed ". count($event['payload']['commits']). " commits to $repo\n";
            break;
        case 'IssuesEvent':
            echo "Opened a new issue in $repo\n";
            break;
        case 'StarEvent':
            echo "Starred $repo\n";
            break;
        default:
            echo "Unknown event type: $type\n";
    }
}