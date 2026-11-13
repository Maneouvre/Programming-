<?php
// Start your products session if you need to track user data later
//require_once("products_session.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Catalog</title>
    
    <style>
        /* Simple, clean grid styles to show cards side-by-side */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 20px;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 20px;
            max-width: 1200px;
            margin: 30px auto;
        }
        .product-card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 15px;
            text-align: center;
            transition: transform 0.2s;
        }
        .product-card:hover {
            transform: translateY(-5px);
        }
        .product-card img {
            width: 100%;
            height: 180px;
            object-fit: cover;
            border-radius: 6px;
        }
        .product-card h3 {
            font-size: 18px;
            margin: 12px 0 6px 0;
            color: #222;
        }
        .price {
            font-size: 16px;
            font-weight: bold;
            color: #4f46e5;
            margin: 0 0 8px 0;
        }
        .rating {
            font-size: 14px;
            color: #666;
        }
    </style>
</head>
<body>

    <h1>Product List</h1>

    <!-- Your async JS loop dynamically injects all database items right here -->
    <div id="products_grid" class="products-grid">Loading products, please wait...</div>

    <script>
    document.addEventListener("DOMContentLoaded", async function() {
        try {
            // 1. Fetch your data from the separate PHP database API script
            const response = await fetch("products_fetch.php");
            const products = await response.json();
            
            // 2. Target your grid container and empty it out
            const container = document.getElementById("products_grid");
            container.innerHTML = ""; 

            // If no data returned from database table rows
            if (products.length === 0) {
                container.innerHTML = "<p>No products found in database.</p>";
                return;
            }

            // 3. Loop through your products array using a clean mapping layout
            container.innerHTML = products.map(product => {
                // Convert price cents into a decimal format (e.g. 1000 cents -> 10.00)
                const realPrice = (product.price_cents / 100).toFixed(2);
                
                return `
                    <div class="product-card">
                        <img src="../product_upload_dashboard/${product.image_path}" onerror="this.src='../product_upload_dashboard/uploads/placeholder.png';">
                        <h3>${product.product_name}</h3>
                        <p class="price">$${realPrice}</p>
                        <p class="rating">⭐ ${product.rating_stars}</p>
                    </div>
                `;
            }).join(""); // Merges all array string cards together cleanly

        } catch (error) {
            console.error("Something went wrong:", error);
            document.getElementById("products_grid").innerHTML = "<p>Error loading product data asset streams.</p>";
        }
    });
    </script>

</body>
</html>
