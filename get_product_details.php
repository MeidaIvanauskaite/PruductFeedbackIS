<?php
    session_start();
    include "db_conn.php";

    if (isset($_GET['product_id']) && is_numeric($_GET['product_id'])) {
        $productId = intval($_GET['product_id']);
        $productQuery = "SELECT Prekes_pavadinimas, Prekes_tipas, Prekes_aprasymas, Kaina FROM preke WHERE Prekes_id = ?";
        $stmt = $conn->prepare($productQuery);
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $productResult = $stmt->get_result();

        if ($productResult->num_rows > 0) {
            $product = $productResult->fetch_assoc();
            $reviewsQuery = "SELECT a.Atsiliepimo_id, a.Atsiliepimo_data, a.Atsiliepimo_tekstas, a.Reitingas, k.Kliento_vardas, k.Kliento_pavarde, k.Kliento_el_pastas 
                            FROM atsiliepimas a 
                            JOIN klientas k ON a.Kliento_id = k.Kliento_id 
                            WHERE a.Prekes_id = ?";
            
            $stmt = $conn->prepare($reviewsQuery);
            $stmt->bind_param("i", $productId);
            $stmt->execute();
            $reviewsResult = $stmt->get_result();

            $reviews = [];
            $totalRating = 0;
            $reviewCount = 0;
            while ($review = $reviewsResult->fetch_assoc()) {
                $reviews[] = [
                    "id" => $review['Atsiliepimo_id'],
                    "date" => $review['Atsiliepimo_data'],
                    "text" => $review['Atsiliepimo_tekstas'],
                    "rating" => (float)$review['Reitingas'],
                    "client_name" => $review['Kliento_vardas'] . " " . $review['Kliento_pavarde'],
                    "client_email" => $review['Kliento_el_pastas']
                ];
                $totalRating += $review['Reitingas'];
                $reviewCount++;
            }

            $averageRating = $reviewCount > 0 ? round($totalRating / $reviewCount, 2) : null;

            echo json_encode([
                "status" => "success",
                "product_title" => $product['Prekes_pavadinimas'],
                "product_type" => $product['Prekes_tipas'],
                "product_description" => $product['Prekes_aprasymas'],
                "product_price" => number_format($product['Kaina'], 2),
                "reviews" => $reviews,
                "average_rating" => $averageRating
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Product not found"
            ]);
        }

        $stmt->close();
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Invalid product ID"
        ]);
    }
    
    $conn->close();
?>
