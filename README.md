# Drivva - Ride-Sharing Application

Drivva is a web application designed for carpooling and ride-sharing. It allows users to find trips, offer rides, and connect with other users to share their commute.

## Features

*   **User Authentication:**
    *   Sign up for a new account.
    *   Log in with existing credentials.
    *   Log out securely.
    *   Password recovery via email ("Forgot Password").
    *   "Remember me" option for persistent login.
*   **Trip Management:**
    *   Add new trips, specifying departure, destination, price, and available seats.
    *   View existing trips created by the user.
    *   Edit details of existing trips.
    *   Delete trips.
*   **Trip Types:**
    *   Option to create **Regular (recurring)** trips for specific days of the week.
    *   Option to create **One-Off** trips for a specific date.
*   **Search Functionality:**
    *   Search for available trips based on departure and destination.
*   **User Profiles:**
    *   View and update user profile information.
    *   Upload and change profile pictures.
*   **Google Maps Integration:**
    *   Display trip routes and locations using Google Maps.

## Technologies Used

*   **Backend:**
    *   PHP
    *   MySQL
*   **Frontend:**
    *   HTML
    *   CSS
    *   JavaScript
    *   jQuery
    *   Bootstrap
*   **APIs:**
    *   Google Maps API

## Installation

This project does not require specific installation steps beyond setting up the web server and database as described in the 'Setup' section. Ensure all files are placed in the web server's document root.

## Setup

To set up and run the Drivva application locally, follow these steps:

1.  **Web Server:**
    *   Ensure you have a web server installed and configured (e.g., Apache, Nginx).
2.  **PHP and MySQL:**
    *   Install PHP and MySQL.
    *   Ensure the MySQL server is running.
3.  **Database Setup:**
    *   The `connection.php` file contains the database credentials:
        *   Host: `localhost`
        *   Username: `csctheco_user`
        *   Password: `ctMk_mT+x*IU`
        *   Database Name: `csctheco_drivvr`
    *   Create a MySQL database with the name `csctheco_drivvr`.
    *   You will need to create the necessary tables. The table structure can be inferred from the PHP files that interact with the database (e.g., `users` table from `signup.php`, `login.php`, `profile.php`; `carsharetrips` table from `addtrips.php`, `getrips.php`, etc.).
        *   **Users table** (likely columns: `user_id`, `username`, `firstname`, `lastname`, `email`, `password`, `phonenumber`, `workorschool`, `gender`, `moreinformation`, `profilepicture`, etc.)
        *   **Carsharetrips table** (likely columns: `trip_id`, `user_id`, `departure`, `departureLongitude`, `departureLatitude`, `destination`, `destinationLongitude`, `destinationLatitude`, `price`, `seatsavailable`, `regular`, `monday`, `tuesday`, `wednesday`, `thursday`, `friday`, `saturday`, `sunday`, `date`, `time`, `moreinformation`, etc.)
4.  **Google Maps API Key:**
    *   You need a valid Google Maps API key.
    *   Open the `index.php` and `mainpageloggedin.php` files.
    *   Find the line where the Google Maps API script is included:
        ```html
        <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBkkioioxpUiyrpyBSyEd66VwTDEl6Ixls&libraries=places">
        </script>
        ```
    *   Replace `AIzaSyBkkioioxpUiyrpyBSyEd66VwTDEl6Ixls` with your actual Google Maps API key.
5.  **Clone the Repository:**
    *   Clone this repository to your web server's document root (e.g., `htdocs` for Apache).
6.  **Run the Application:**
    *   Open your web browser and navigate to the project's URL (e.g., `http://localhost/your-project-directory`).

## Usage

Once the application is set up on your web server:

- **To offer a ride:** Navigate to the "Offer Ride" section in the application and fill in the details.
- **To find a ride:** Use the search functionality to find available trips based on your destination and preferred time.

## Contributing

Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

## License

[MIT](https://choosealicense.com/licenses/mit/)

## Author and Copyright

*   **Author:** Jerome Smith (CSC400)
*   **Copyright:** © 2017-<?php $today = date("Y"); echo $today?>.

*Note: The copyright year will be dynamically updated by PHP when the application is run.*
