<?php
    session_start();
    include "db_conn.php";

    function fetchCompanies($conn) {
        $sql = "SELECT Imones_id, Imones_pavadinimas FROM imone";
        return $conn->query($sql);
    }

    function fetchProductsByCompanyId($conn, $companyId) {
        $sql = "SELECT Prekes_id, Prekes_pavadinimas, Prekes_tipas, Prekes_aprasymas, Kaina FROM preke WHERE Imones_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $companyId);
        $stmt->execute();
        return $stmt->get_result();
    }
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="icon" href="./icon.png" type="image/x-icon">
        <title>Prekių atsiliepimų informacinė sistema</title>
        <script>
            function loadProductDetails(productId) {
                fetch(`get_product_details.php?product_id=${productId}`)
                    .then(response => response.json())
                    .then(data => {
                        const detailsContainer = document.getElementById('product-details');
                        detailsContainer.innerHTML = `
                            <h2>${data.product_title}</h2>
                            <p><b>Tipas:</b> ${data.product_type}</p>
                            <p><b>Kaina:</b> €${data.product_price}</p>
                            <p><b>Aprašymas (Anglų kalba):</b> ${data.product_description}</p>
                            <h3>Atsiliepimai</h3>
                            ${data.reviews.length > 0 
                                ? data.reviews.map(review => `
                                    <div class="review">
                                        <p><strong>${review.client_name}</strong> (${review.client_email}, ${review.date})</p>
                                        <p>${review.text}</p>
                                        <p><b>Įvertinimas:</b> ${review.rating}</p>
                                    </div>
                                `).join('')
                                : '<p>Dar nėra atsiliepimų.</p>'
                            }
                            <p><strong>Vidutinis įvertinimas:</strong> ${data.average_rating || 'N/A'}</p>
                            <div id='add-review'>
                                <h3>Pateikite atsiliepimą</h3>
                                <form id='review-form' action='submit_review.php?product_id=${productId}' method='post'>
                                    <div class='form-group'>
                                        <label for='client-name'>Vardas:</label>
                                        <input type='text' id='client-name' name='client_name' required>
                                    </div>
                                    <div class='form-group'>
                                        <label for='client-lastname'>Pavardė:</label>
                                        <input type='text' id='client-lastname' name='client_lastname' required>
                                    </div>
                                    <div class='form-group'>
                                        <label for='client-email'>El. paštas:</label>
                                        <input type='email' id='client-email' name='client_email' required>
                                    </div>
                                    <div class='form-group'>
                                        <label for='review-text'>Atsiliepimas:</label>
                                        <textarea id='review-text' name='review_text' rows='3' required></textarea>
                                    </div>
                                    <div class='form-group'>
                                        <label for='review-rating'>Įvertinimas (1-10):</label>
                                        <input type='number' id='review-rating' name='review_rating' min='1' max='10' required>
                                    </div>
                                    <button type='submit'>Pateikti atsiliepimą</button>
                                </form>
                            </div>
                        `;
                    })
                    .catch(error => {
                        console.error('Error fetching product details:', error);
                    });
            }
        </script>
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            body {
                font-family: Arial, sans-serif;
                background-color: #f4f4f9;
                color: #333;
                display: flex;
                flex-direction: column;
                height: 100vh;
            }

            header {
                background-color: #2c3e50;
                color: #ecf0f1;
                padding: 20px;
                text-align: center;
                flex-shrink: 0;
            }

            header h1 {
                font-size: 2rem;
                margin-bottom: 10px;
            }

            header .logout-btn {
                background-color: #e74c3c;
                color: #fff;
                text-decoration: none;
                padding: 10px 20px;
                border-radius: 5px;
                font-weight: bold;
                transition: background-color 0.3s;
            }

            header .logout-btn:hover {
                background-color: #c0392b;
            }

            .container {
                display: flex;
                flex: 1;
                overflow: hidden;
            }

            .sidebar {
                width: 25%;
                background: #ecf0f1;
                padding: 20px;
                overflow-y: auto;
                border-right: 2px solid #bdc3c7;
            }

            .sidebar h2 {
                margin-bottom: 20px;
                color: #2c3e50;
            }

            .company {
                margin-bottom: 20px;
            }

            .company h3 {
                font-size: 1.2rem;
                margin-bottom: 10px;
                color: #2980b9;
            }

            .product-list {
                list-style: none;
                padding-left: 0;
            }

            .product-list li {
                margin-bottom: 10px;
            }

            .product-list button {
                background-color: #3498db;
                color: #fff;
                border: none;
                padding: 8px 12px;
                border-radius: 5px;
                cursor: pointer;
                transition: background-color 0.3s;
            }

            .product-list button:hover {
                background-color: #2980b9;
            }

            .content {
                flex: 1;
                padding: 20px;
                overflow-y: auto;
                background-color: #fff;
            }

            #product-details h2 {
                font-size: 1.5rem;
                margin-bottom: 10px;
                color: #2c3e50;
            }

            #product-details h3 {
                margin-top: 20px;
                color: #e67e22;
            }

            .review {
                margin-top: 10px;
                padding: 10px;
                border: 1px solid #bdc3c7;
                border-radius: 5px;
                background: #f9f9f9;
            }

            #add-review {
                margin-top: 20px;
                padding: 15px;
                border: 1px solid #ccc;
                background: #f9f9f9;
                border-radius: 5px;
            }

            #add-review h3 {
                margin-bottom: 15px;
            }

            #add-review .form-group {
                margin-bottom: 15px;
            }

            #add-review label {
                display: block;
                font-weight: bold;
                margin-bottom: 5px;
            }

            #add-review input, #add-review textarea, #add-review button {
                width: 100%;
                padding: 10px;
                border: 1px solid #ccc;
                border-radius: 5px;
            }

            #add-review button {
                background: #007bff;
                color: white;
                cursor: pointer;
            }

            #add-review button:hover {
                background: #0056b3;
            }
        </style>
    </head>
    <body>
        <header>
            <h1>Prekių atsiliepimų informacinė sistema</h1>
        </header>

        <div class="container">
            <aside class="sidebar">
                <h2>Įmonės</h2>
                <?php
                    $companies = fetchCompanies($conn);
                    if ($companies->num_rows > 0) {
                        while ($company = $companies->fetch_assoc()) {
                            echo "<div class='company'>";
                            echo "<h3>{$company['Imones_pavadinimas']}</h3>";
                            $products = fetchProductsByCompanyId($conn, $company['Imones_id']);
                            if ($products->num_rows > 0) {
                                echo "<ul class='product-list'>";
                                while ($product = $products->fetch_assoc()) {
                                    echo "<li>
                                            <button onclick='loadProductDetails({$product['Prekes_id']})'>
                                                {$product['Prekes_pavadinimas']}
                                            </button>
                                        </li>";
                                }
                                echo "</ul>";
                            } else {
                                echo "<p>Produktų nėra.</p>";
                            }
                            echo "</div>";
                        }
                    } else {
                        echo "<p>Nerasta įmonių.</p>";
                    }
                ?>
            </aside>
            <main class="content">
                <div id="product-details">
                    <p>Norėdami pamatyti išsamią informaciją, pasirinkite produktą.</p>
                    <?php if (isset($_GET['error'])) { ?>
                        <p style="color: red; margin: 5px 0"><b><?php echo $_GET['error'];?></b></p>
                    <?php }?>

                    <?php if (isset($_GET['success'])) { ?>
                        <p style="color: green; margin: 5px 0"><b><?php echo $_GET['success'];?></b></p>
                    <?php }?>
                </div>
            </main>
        </div>
    </body>
</html>