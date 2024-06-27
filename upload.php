<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "csv-mysql";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$insertedCount = 0;
$duplicateCount = 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file']['tmp_name'];

    if (($handle = fopen($file, "r")) !== FALSE) {
        $header = fgetcsv($handle, 1000, ",");
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $row = array_combine($header, $data);

            $country = getId($conn, 'countries', 'Country', $row['Country']);
            list($city_name, $state_name) = explode(', ', $row['Search Area']);
            $city = getId($conn, 'cities', 'City', $city_name);
            $state = getId($conn, 'states', 'State', $state_name);

            $company_name = $conn->real_escape_string($row['Business Name']);
            $address = $conn->real_escape_string($row['Address']);
            $contact_email = isset($row['Email']) ? $conn->real_escape_string($row['Email']) : '';
            $website = isset($row['Website']) ? $conn->real_escape_string($row['Website']) : '';
            $category = isset($row['Categories']) ? $conn->real_escape_string($row['Categories']) : '';
            $phone = isset($row['Phone']) ? $conn->real_escape_string($row['Phone']) : '';

            $sql = "SELECT id FROM businesses WHERE company_name='$company_name' AND address='$address' AND city_id='$city' AND state_id='$state' AND country_id='$country' AND contact_email='$contact_email'";
            $result = $conn->query($sql);

            if ($result->num_rows == 0) {
                $sql = "INSERT INTO businesses (company_name, address, city_id, state_id, country_id, contact_email, website, services, phone)
                        VALUES ('$company_name', '$address', '$city', '$state', '$country', '$contact_email', '$website', '$category', '$phone')";

                if ($conn->query($sql) === TRUE) {
                    $insertedCount++;
                } else {
                    echo "Error: " . $sql . "<br>" . $conn->error;
                }
            } else {
                $duplicateCount++;
            }
        }
        fclose($handle);
    }
}

$conn->close();

header("Location: index.php?inserted=$insertedCount&duplicates=$duplicateCount");
exit();

function getId($conn, $table, $column, $value)
{
    $value = $conn->real_escape_string($value);
    $sql = "SELECT id FROM $table WHERE name='$value'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['id'];
    } else {
        $sql = "INSERT INTO $table (name) VALUES ('$value')";
        if ($conn->query($sql) === TRUE) {
            return $conn->insert_id;
        } else {
            die("Error: " . $sql . "<br>" . $conn->error);
        }
    }
}
