<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Product Admin Dashboard</title>
  <link rel="stylesheet" href="delete_edit.css">
  <!-- FontAwesome for Dashboard Icons -->
  <link rel="stylesheet" href="https://cloudflare.com">
</head>
<body>

  <!-- SIDEBAR NAVIGATION -->
  <aside class="sidebar">
    <div class="logo">
      <i class="fa-solid fa-cubes"></i>
      <span>Product Admin</span>
    </div>
    <div class="menu-section">
      <p class="section-title">General</p>
      <nav>
        <a href="delete_edit.php" class="active"><i class="fa-solid fa-chart-pie"></i> Dashboard</a>
        <a href="delete_edit.php"><i class="fa-solid fa-box"></i> Products</a>
        <a href="../../ecommerce_shop/orders.html"><i class="fa-solid fa-cart-shopping"></i> Orders</a>
        <a href="../../ecommerce_shop/index.html"><i class="fa-solid fa-chart-line"></i> Analytics</a>
        <a href="../../ecommerce_shop/index.html"><i class="fa-solid fa-gear"></i> Settings</a>
      </nav>
    </div>
  </aside>

  <!-- MAIN APP CONTAINER -->
  <main class="main-content">
    
    <!-- HEADER BAR -->
    <header class="top-header">
      <div class="header-right">
        <button class="notif-btn"><i class="fa-regular fa-bell"></i></button>
        <div class="user-profile">
          <img src="https://placeholder.com" alt="Alex Rivera" class="avatar">
          <div class="user-info">
            <span class="user-name">Alex Rivera</span>
            <span class="user-role">Store Owner</span>
          </div>
        </div>
      </div>
    </header>

    <!-- CONTENT BODY -->
    <div class="content-body">
      <div class="page-title-row">
        <div>
          <h1>Products</h1>
          <p class="subtitle">Manage your online store catalog and settings</p>
        </div>
        
        <a href="../product_upload_dashboard/products_upload.php" class="add-product-btn"><i class="fa-solid fa-plus"></i> Add Product</a>
      </div>

      <!-- METRIC CARDS -->
      <section class="metrics-grid">
        <div class="metric-card">
          <div class="card-header">
            <span>Total Products</span>
            <i class="fa-solid fa-cubes icon-blue"></i>
          </div>
          <div class="metric-value">1,248</div>
          <div class="trend positive"><i class="fa-solid fa-arrow-up"></i> +12% <span class="trend-label">from last month</span></div>
        </div>

        <div class="metric-card">
          <div class="card-header">
            <span>Average Rating</span>
            <i class="fa-regular fa-star icon-purple"></i>
          </div>
          <div class="metric-value">4.62</div>
          <div class="trend positive"><i class="fa-solid fa-arrow-up"></i> +0.4% <span class="trend-label">from last month</span></div>
        </div>

        <div class="metric-card">
          <div class="card-header">
            <span>Active Keywords</span>
            <i class="fa-regular fa-bookmark icon-orange"></i>
          </div>
          <div class="metric-value">42</div>
          <div class="trend positive"><i class="fa-solid fa-arrow-up"></i> +3 new <span class="trend-label">from last month</span></div>
        </div>

        <div class="metric-card">
          <div class="card-header">
            <span>Revenue</span>
            <i class="fa-solid fa-dollar-sign icon-green"></i>
          </div>
          <div class="metric-value">$42,840</div>
          <div class="trend positive"><i class="fa-solid fa-arrow-up"></i> +18.2% <span class="trend-label">from last month</span></div>
        </div>
      </section>

      <!-- PRODUCTS DATA TABLE -->
      <section class="table-container">
        <table>
          <thead>
            <tr>
              <th>Preview</th>
              <th>Product details</th>
              <th>Price</th>
              <th>Keywords</th>
              <th>Rating</th>
              <th class="text-right">Actions</th>
            </tr>
          </thead>
          <tbody id="products-table-body"></tbody>
        </table>

        <!-- TABLE PAGINATION -->
        <div class="pagination-row">
          <div class="pagination-info">Showing 1-1 of 1,248 products</div>
          <div class="pagination-buttons">
            <button class="page-btn" disabled>Previous</button>
            <button class="page-btn">Next</button>
          </div>
        </div>
      </section>

    </div>
  </main>

  
    <script>
    document.addEventListener("DOMContentLoaded", async function() {
        const tableBody = document.getElementById("products-table-body");

        if (!tableBody) {
            return;
        }

        try {
            const response = await fetch("../products_fetch/products_fetch.php");
            const products = await response.json();

            if (!Array.isArray(products) || products.length === 0) {
                tableBody.innerHTML = "<tr><td colspan='6'>No products found in the database.</td></tr>";
                return;
            }

            tableBody.innerHTML = products.map((product) => {
                const realPrice = (Number(product.price_cents || 0) / 100).toFixed(2);
                const imagePath = product.image_path ? `../product_upload_dashboard/${product.image_path}` : '../product_upload_dashboard/uploads/placeholder.png';
                const keywords = Array.isArray(product.keywords) ? product.keywords : [];
                const keywordTags = keywords.slice(0, 3).map((keyword) => `<span class="tag">${keyword}</span>`).join('');
                const ratingText = product.rating_stars ? `${product.rating_stars} (${product.rating_count || 0} reviews)` : `0.0 (0 reviews)`;

                return `
                    <tr>
                        <td><img src="${imagePath}" alt="${product.product_name || 'Product'}" class="prod-img" onerror="this.src='../product_upload_dashboard/uploads/placeholder.png';"></td>
                        <td>
                            <div class="prod-title">${product.product_name || 'Unnamed Product'}</div>
                            <div class="prod-id">ID: PROD-${product.id || 'N/A'}</div>
                        </td>
                        <td class="font-medium">$${realPrice}</td>
                        <td>${keywordTags || '<span class="tag">General</span>'}</td>
                        <td>
                            <span class="star-rating">${'⭐'.repeat(Math.min(5, Math.max(0, Math.round(Number(product.rating_stars || 0)))))}${'☆'.repeat(Math.max(0, 5 - Math.min(5, Math.max(0, Math.round(Number(product.rating_stars || 0))))))}</span>
                            <span class="rating-text">${ratingText}</span>
                        </td>
                        <td class="text-right actions-cell">
                            <button class="action-btn edit" type="button"><i class="fa-regular fa-pen-to-square"></i></button>
                            <button class="action-btn delete" type="button"><i class="fa-regular fa-trash-can"></i></button>
                        </td>
                    </tr>
                `;
            }).join("");

        } catch (error) {
            console.error("Something went wrong:", error);
            tableBody.innerHTML = "<tr><td colspan='6'>Error loading product data.</td></tr>";
        }
    });
    </script>

</body>
</html>
