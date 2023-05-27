<!DOCTYPE html>
<html>

<head>
    <title>Online Tutoring Marketplace</title>
    <!-- Add CSS and JS links here -->
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

    <?php
    // Always display tutors by default
    $tutors = array(
        array('name' => 'person1', 'subject' => 'Math', 'price' => 20, 'distance' => 5),
        array('name' => 'person2', 'subject' => 'English', 'price' => 25, 'distance' => 10),
        array('name' => 'person3', 'subject' => 'Coding', 'price' => 30, 'distance' => 15),
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
        if (count($tutors) === 0) {
            echo "<p>No tutors matched your search criteria. Please try again.</p>";
        }
    }

    // Loop through the tutors and display them
    foreach ($tutors as $tutor) {
        echo "<div>";
        echo "<h2>" . $tutor['name'] . "</h2>";
        echo "<p>Subject: " . $tutor['subject'] . "</p>";
        echo "<p>Price: $" . $tutor['price'] . "/hour</p>";
        echo "<p>Distance: " . $tutor['distance'] . " kilometres</p>";
        echo "</div>";
    }

    ?>
</body>

</html>