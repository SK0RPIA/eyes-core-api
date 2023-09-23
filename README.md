# eyes-core-api

**The backend API service for eyes-core, providing real-time system performance metrics. Delivers data seamlessly to the eyes-core dashboard.**

![API Endpoint Screenshot](futur_screen.png)

## Table of Contents
1. [Features](#features)
2. [Requirements](#requirements)
3. [Installation](#installation)
4. [Configuration](#configuration)
5. [Endpoints](#endpoints)
6. [Usage](#usage)
7. [Contribution](#contribution)
8. [License](#license)

## Features
- **High Performance:** Efficiently gathers and delivers system performance metrics.
- **Optimized for eyes-core:** Tailored to provide data in the best format for the eyes-core dashboard.
- **Secure:** Implements best practices to ensure data integrity and security.

## Requirements
- PHP 7.4 or newer
- Composer
- A server (like nginx or Apache)

## Installation
1. Clone the repository:
   ```bash
   git clone https://github.com/yourusername/eyes-core-api.git
   ```
   
2. Navigate to the project directory:
   ```bash
   cd eyes-core-api
   ```

4. Install dependencies with Composer:
   ```bash
   composer install
   ```
   
## Configuration

- Rename the .env.example file to .env.
- Update the database and other configuration details in the .env file as necessary.

## Endpoints
/disk: Fetches disk and partition metrics.
/cpu: Provides CPU usage details.
/ram: Delivers RAM utilization metrics.

## Usage
Start the API server. (The method will depend on your server software.)
Test the API using a tool like Postman or by navigating to http://api.yourdomain.com/disk in a web browser.

## Contribution
Contributions are heartily welcome! Please go through the contribution guidelines before making any changes.

## License
This project is under the MIT License. Refer to the LICENCE document for more specifics.
