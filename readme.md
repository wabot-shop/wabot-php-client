# Wabot API Client for PHP

This is a PHP client library for interacting with the Wabot API. It handles authentication, token management, and provides methods to interact with the Wabot API endpoints, such as sending messages and retrieving templates.

## Table of Contents

- [Prerequisites](#prerequisites)
- [Installation](#installation)
- [Usage](#usage)
  - [Initialization](#initialization)
  - [Authentication](#authentication)
  - [Getting Templates](#getting-templates)
  - [Sending Messages](#sending-messages)
  - [Logout](#logout)
- [Example](#example)
- [Notes](#notes)
- [License](#license)

## Prerequisites

- PHP 7.x or higher
- cURL extension enabled
- JSON extension enabled

## Installation

1. **Clone or Download**

   Clone this repository or download the `WabotApiClient.php` file and include it in your project.

2. **Include the Client**

   ```php
   require_once 'path/to/WabotApiClient.php';

    $clientId = 'YOUR_CLIENT_ID';
    $clientSecret = 'YOUR_CLIENT_SECRET';

    $wabot = new WabotApiClient($clientId, $clientSecret);
