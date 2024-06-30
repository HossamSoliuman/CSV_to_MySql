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
$invalidCategoryCount = 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file']['tmp_name'];

    if (($handle = fopen($file, "r")) !== FALSE) {
        $header = fgetcsv($handle, 1000, ",");
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $row = array_combine($header, $data);

            $category = isset($row['Categories']) ? $conn->real_escape_string($row['Categories']) : '';

            if (in_array($category, ['Business Broker', 'Business Brokers', 'Real Estate Agents'])) {
                $country = getId($conn, 'countries', 'name', $row['Country']);
                list($city_name, $state_name) = explode(', ', $row['Search Area']);
                $state = getId($conn, 'state', 'name', $state_name, $country);
                $city = getId($conn, 'city', 'name', $city_name, $state);

                $company = $conn->real_escape_string($row['Business Name']);
                $slug = strtolower(str_replace(' ', '-', $company));
                $zipPostalCode = substr($row['Address'], -7);
                $address = preg_replace('/,.*$/', '', $conn->real_escape_string($row['Address']));
                $contact_email = isset($row['Email']) ? $conn->real_escape_string($row['Email']) : '';
                $website = isset($row['Website']) ? $conn->real_escape_string($row['Website']) : '';
                $phone_number = isset($row['Phone']) ? $conn->real_escape_string($row['Phone']) : '';

                $sql = "SELECT broker_id FROM agencies WHERE company='$company' AND address='$address' AND city='$city' AND state='$state' AND country='$country' AND contact_email='$contact_email'";
                $result = $conn->query($sql);

                if ($result->num_rows == 0) {
                    $sql = "INSERT INTO agencies (userid, row_id, company, type, slug, tagline, address, city, state, country, website, contact_email, phone_number, fax, facebook_page_link, twitter_page_link, instagram_page_link, pininterest_page_link, youtuble_page_link, linkedin_page_link, contact_name, image, description, services, about, use_registered_email, is_featured, view_count, IsClaimed, ZipPostalCode)
                            VALUES (1, 'dummy_row_id', '$company', 1, '$slug', '', '$address', '$city', '$state', '$country', '$website', '$contact_email', '$phone_number', 0, '', '', '', '', '', '', '', '', '', '', '', 0, 0, '', b'0', '$zipPostalCode')";

                    if ($conn->query($sql) === TRUE) {
                        $insertedCount++;
                    } else {
                        echo "Error: " . $sql . "<br>" . $conn->error;
                    }
                } else {
                    $duplicateCount++;
                }
            } else {
                $invalidCategoryCount++;
            }
        }
        fclose($handle);
    }
}

$conn->close();

header("Location: index.php?inserted=$insertedCount&duplicates=$duplicateCount&invalid=$invalidCategoryCount");
exit();

function getId($conn, $table, $column, $value, $parentId = null)
{
    $value = $conn->real_escape_string($value);

    switch ($table) {
        case 'countries':
            $primaryKey = 'id';
            $column = 'name';
            break;
        case 'state':
            $primaryKey = 'state_id';
            $parentColumn = 'country_id';
            $column = 'name';
            break;
        case 'city':
            $primaryKey = 'city_id';
            $parentColumn = 'state_id';
            $column = 'name';
            break;
        default:
            die("Error: Unknown table '$table'");
    }

    $condition = "$column='$value'";
    if ($parentId !== null) {
        $condition .= " AND $parentColumn='$parentId'";
    }

    $sql = "SELECT $primaryKey FROM $table WHERE $condition";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row[$primaryKey];
    } else {
        if ($parentId !== null) {
            $sql = "INSERT INTO $table ($column, $parentColumn) VALUES ('$value', '$parentId')";
        } else {
            $sql = "INSERT INTO $table ($column) VALUES ('$value')";
        }

        if ($conn->query($sql) === TRUE) {
            return $conn->insert_id;
        } else {
            die("Error: " . $sql . "<br>" . $conn->error);
        }
    }
}

$conn = new mysqli("localhost", "username", "password", "csv-mysql");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$countryId = getId($conn, 'countries', 'name', 'Canada');
$stateId = getId($conn, 'state', 'name', 'Ontario', $countryId);
$cityId = getId($conn, 'city', 'name', 'Toronto', $stateId);

echo "Country ID: $countryId, State ID: $stateId, City ID: $cityId";

$conn->close();
