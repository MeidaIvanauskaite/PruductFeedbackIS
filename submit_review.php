<?php
    session_start();
    include "db_conn.php";

    if (isset($_POST['client_name']) && isset($_POST['client_lastname']) && isset($_POST['client_email']) && isset($_POST['review_text']) && isset($_POST['review_rating'])) {
        function validate($data) {
            $data = trim($data);
            $data = stripslashes($data);
            $data = htmlspecialchars($data);
            return $data;
        }

        $product_id = $_GET['product_id'];
        $client_name = validate($_POST['client_name']);
        $client_lastname = validate($_POST['client_lastname']);
        $client_email = validate($_POST['client_email']);
        $review_text = validate($_POST['review_text']);
        $review_rating = validate($_POST['review_rating']);
        $review_date = date("Y-m-d H:i:s");

        $sql_client = "SELECT * FROM klientas WHERE Kliento_vardas='$client_name' AND Kliento_pavarde='$client_lastname' AND Kliento_el_pastas='$client_email'";
        $result_client = mysqli_query($conn, $sql_client);

        if (mysqli_num_rows($result_client) > 0) {
            $row = mysqli_fetch_assoc($result_client);
            $client_id = $row['Kliento_id'];
        } else {
            $sql_insert_client = "INSERT INTO klientas(Kliento_vardas, Kliento_pavarde, Kliento_el_pastas) VALUES('$client_name','$client_lastname','$client_email')";
            $result_insert_client = mysqli_query($conn, $sql_insert_client);
            $sql_client = "SELECT * FROM klientas WHERE Kliento_vardas='$client_name' AND Kliento_pavarde='$client_lastname' AND Kliento_el_pastas='$client_email'";
            $result_client = mysqli_query($conn, $sql_client);
            $row = mysqli_fetch_assoc($result_client);
            $client_id = $row['Kliento_id'];
        }
        
        $sql_insert_review = "INSERT INTO atsiliepimas(Atsiliepimo_data, Atsiliepimo_tekstas, Reitingas, Kliento_id, Prekes_id) VALUES('$review_date', '$review_text', '$review_rating', '$client_id', '$product_id')";
        $result_review = mysqli_query($conn, $sql_insert_review);

        header("Location: index.php?success=Atsiliepimas pridėtas!");
        exit();
    } else {
        header("Location: index.php?error=Pasitaikė nežinoma klaida");
        exit();
    }
?>