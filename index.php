<!DOCTYPE html>
<html>

<head>
    <title>Online Tutoring Marketplace</title>
    <!-- Add CSS and JS links here -->
</head>
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
</style>

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
        array('name' => 'Alice', 'subject' => 'Math', 'price' => 20, 'distance' => 5, 'latitude' => 37.7749, 'longitude' => -122.4194),
        array('name' => 'Bob', 'subject' => 'English', 'price' => 25, 'distance' => 10, 'latitude' => 38.9072, 'longitude' => -77.0369),
        array('name' => 'Charlie', 'subject' => 'Coding', 'price' => 30, 'distance' => 15, 'latitude' => 51.5074, 'longitude' => -0.1278),
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
    }

    // Calculate distance from user's actual location
    function calculateDistance($latitude1, $longitude1, $latitude2, $longitude2)
    {
        $earthRadius = 6371; // in kilometers

        $latDiff = deg2rad($latitude2 - $latitude1);
        $lonDiff = deg2rad($longitude2 - $longitude1);

        $a = sin($latDiff / 2) * sin($latDiff / 2) + cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * sin($lonDiff / 2) * sin($lonDiff / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        $distance = $earthRadius * $c;

        return $distance;
    }

    // User's actual location
    $userLatitude = 37.7749; // Replace with actual latitude
    $userLongitude = -122.4194; // Replace with actual longitude

    // Calculate and display the distance from user's location for each tutor
    foreach ($tutors as &$tutor) {
        $tutor['distanceFromUser'] = calculateDistance($userLatitude, $userLongitude, $tutor['latitude'], $tutor['longitude']);
        $tutor['distanceFromUser'] = round($tutor['distanceFromUser'], 2);
        echo "<div>";
        echo "<h2>" . $tutor['name'] . "</h2>";
        echo "<p>Subject: " . $tutor['subject'] . "</p>";
        echo "<p>Price: $" . $tutor['price'] . "/hour</p>";
        echo "<p>Distance from your location: " . $tutor['distanceFromUser'] . " kms</p>";
        echo "</div>";
    }
    ?>

</body>

</html>
