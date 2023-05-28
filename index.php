<!DOCTYPE html>
<html>

<head>
    <title>Online Tutoring Marketplace</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            padding: 1em;
        }

        h1 {
            color: #333;
            text-align: center;
        }

        form {
            display: flex;
            justify-content: space-around;
            margin-bottom: 2em;
            background-color: #fff;
            padding: 1em;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }

        input[type=submit] {
            background-color: #007BFF;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }

        input[type=submit]:hover {
            background-color: #0056b3;
        }

        .tutor {
            display: flex;
            border: 1px solid #ddd;
            padding: 1em;
            margin-bottom: 1em;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }

        .tutor h2 {
            margin-top: 0;
            color: #007BFF;
        }

        .map-container {
            width: 300px;
            height: 200px;
            margin-left: 1em;
        }

        .error-message {
            color: red;
            margin-bottom: 1em;
        }

        .manual-address {
            margin-top: 1em;
        }
    </style>
    <!-- Add Leaflet.js CSS link here -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
</head>

<body>
    <h1>Online Tutoring Marketplace</h1>
    <form action="index.php" method="post">
        <label for="subject">Subject:</label>
        <input type="text" id="subject" name="subject">
        <label for="price">Max Price:</label>
        <input type="number" id="price" name="price">
        <label for="distance">Max Distance:</label>
        <input type="number" id="distance" name="distance">
        <input type="submit" value="Search">
    </form>
    <!-- Clear filters form -->
    <form action="" method="post">
        <input type="submit" value="Clear filters">
    </form>
    <?php

    session_start();

    // Always display tutors by default
    $tutors = array(
        array('name' => 'p1', 'subject' => 'Math', 'price' => 20, 'distance' => 5, 'latitude' => -33.655956, 'longitude' => 151.314588),
        array('name' => 'p2', 'subject' => 'English', 'price' => 25, 'distance' => 10, 'latitude' => 39.9072, 'longitude' => -77.0369),
        array('name' => 'p3', 'subject' => 'Coding', 'price' => 30, 'distance' => 15, 'latitude' => 51.5074, 'longitude' => -0.1278),
    );

    // If form is submitted, filter tutors based on input
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Get the input data from the form
        $subject = $_POST['subject'];
        $price = $_POST['price'];
        $distance = $_POST['distance'];

        // Filter the tutors array
        $tutors = array_filter($tutors, function ($tutor) use ($subject, $price, $distance) {
            return (empty($subject) || strtolower($tutor['subject']) == strtolower($subject)) &&
                (empty($price) || $tutor['price'] <= $price) &&
                (empty($distance) || $tutor['distance'] <= $distance);
        });

        // Store the search results in a session variable and redirect
        $_SESSION['tutors'] = $tutors;
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    }

    // Display the search results from the session variable
    $tutors = $_SESSION['tutors'] ?? $tutors;
    unset($_SESSION['tutors']);

    // If no tutors matched the search criteria, display a message
    if (empty($tutors)) {
        echo "<p>No tutors matched your search criteria. Please try again.</p>";
    } else {
        // Calculate distance from user's actual location
        echo '<script>
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(successCallback, errorCallback);
            } else {
                console.log("Geolocation is not supported by this browser.");
            }

            function successCallback(position) {
                const userLatitude = position.coords.latitude;
                const userLongitude = position.coords.longitude;
                calculateDistances(userLatitude, userLongitude);
            }

            function errorCallback(error) {
                if (error.code === error.PERMISSION_DENIED) {
                    const errorMessage = document.createElement("div");
                    errorMessage.className = "error-message";
                    errorMessage.innerText = "Location access denied. Please enter your address manually.";

                    document.body.appendChild(errorMessage);

                    const manualAddressForm = document.createElement("form");
                    manualAddressForm.className = "manual-address";
                    manualAddressForm.innerHTML = "<label for=\"address\">Address:</label>" +
                        "<input type=\"text\" id=\"address\" name=\"address\">" +
                        "<input type=\"submit\" value=\"Submit\">";

                    manualAddressForm.addEventListener("submit", function (event) {
                        event.preventDefault();
                        const addressInput = document.getElementById("address");
                        const address = addressInput.value;

                        // Call a function to convert address to latitude and longitude
                        convertAddressToCoordinates(address);
                    });

                    document.body.appendChild(manualAddressForm);
                }
            }

            function calculateDistances(userLatitude, userLongitude) {
                var tutors = ' . json_encode($tutors) . ';

                for (let i = 0; i < tutors.length; i++) {
                    const tutor = tutors[i];
                    const distance = calculateDistance(userLatitude, userLongitude, tutor.latitude, tutor.longitude);
                    tutor.distanceFromUser = Math.round(distance * 100) / 100;

                    const tutorElement = document.createElement("div");
                    tutorElement.className = "tutor";
                    tutorElement.innerHTML = "<div>" +
                        "<h2>" + tutor.name + "</h2>" +
                        "<p>Subject: " + tutor.subject + "</p>" +
                        "<p>Price: $" + tutor.price + "/hour</p>" +
                        "<p>Distance from your location: " + tutor.distanceFromUser + " kms</p>" +
                        "</div>";

                    const mapContainer = document.createElement("div");
                    mapContainer.className = "map-container";
                    tutorElement.appendChild(mapContainer);

                    createMap(mapContainer, tutor.latitude, tutor.longitude);

                    document.body.appendChild(tutorElement);
                }
            }

            function calculateDistance(latitude1, longitude1, latitude2, longitude2) {
                const earthRadius = 6371; // in kilometers

                const latDiff = deg2rad(latitude2 - latitude1);
                const lonDiff = deg2rad(longitude2 - longitude1);

                const a = Math.sin(latDiff / 2) * Math.sin(latDiff / 2) + Math.cos(deg2rad(latitude1)) * Math.cos(deg2rad(latitude2)) * Math.sin(lonDiff / 2) * Math.sin(lonDiff / 2);
                const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

                const distance = earthRadius * c;

                return distance;
            }

            function deg2rad(deg) {
                return deg * (Math.PI / 180);
            }

            function createMap(container, latitude, longitude) {
                const map = L.map(container).setView([latitude, longitude], 12);

                L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
                    attribution: "Map data © <a href=\"https://openstreetmap.org\">OpenStreetMap</a> contributors",
                    maxZoom: 18,
                }).addTo(map);

                L.marker([latitude, longitude]).addTo(map);
            }

            function convertAddressToCoordinates(address) {
                // Call an API or perform the necessary operations to convert the address to latitude and longitude
                // Once you have the latitude and longitude, you can proceed with the rest of the logic
                console.log("Converting address to coordinates:", address);
            }
        </script>';
    }
    ?>

    <!-- Add Leaflet.js library script here -->
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
</body>

</html>
