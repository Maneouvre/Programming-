<?php
// ==========================================
// 1. PDO DATABASE API DELETE HANDLER LAYER
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    // Clear out buffers to make sure only clean JSON data prints
    ob_clean();
    header('Content-Type: application/json');

    $host   = "localhost";
    $dbname = "ecommerce_db";
    $user   = "root";
    $pass   = "";

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);

        $productId = intval($_POST['delete_id']);

        // Securely prepare statement to protect from injections
        $stmt = $pdo->prepare("DELETE FROM `products` WHERE `id` = :id");
        $stmt->execute(['id' => $productId]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Product removed from database catalog!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Product ID not located inside tables.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit; // Terminate execution immediately so no HTML leaks into the delete request stream
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Product Admin Dashboard</title>
  <link rel="stylesheet" href="products.css">
  <!-- Valid FontAwesome Mirror for rendering sidebar buttons icons -->
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
        <a href="#" class="nav-item active">
                    <span class="nav-icon">📊</span> Dashboard
                </a>
                <a href="../products_fetch/products.php" class="nav-item">
                    <span class="nav-icon">📦</span> Products
                </a>
                <a href="#" class="nav-item">
                    <span class="nav-icon">📋</span> Orders
                </a>
                <a href="#" class="nav-item">
                    <span class="nav-icon">📈</span> Analytics
                </a>
                <a href="#" class="nav-item">
                    <span class="nav-icon">⚙️</span> Settings
                </a>

    </div>
  </aside>

  <!-- MAIN APP CONTAINER -->
  <main class="main-content">
    
    <!-- HEADER BAR -->
    <header class="top-header">
      <div class="header-right">
        <button class="notif-btn"><i class="fa-regular fa-bell"></i></button>
        <div class="user-profile">
          <img src="https://placehold.co" alt="Manoeuvre" class="avatar" style="border-radius: 50%;">
          <div class="user-info">
            <span class="user-name">Manoeuvre</span>
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
        <a href="http://localhost/ECCOMMERCE%20WEBSITE/Admin_dashboard_php_sql/product_upload_dashboard/products_upload.php" class="product-link">
          <button class="add-product-btn"><i class="fa-solid fa-plus"></i> Add Product</button>
        </a>
      </div>

      <!-- METRIC CARDS -->
      <section class="metrics-grid">
        <div class="metric-card">
          <div class="card-header">
            <span>Total Products</span>
            <i class="fa-solid fa-cubes icon-blue"></i>
          </div>
          <div class="metric-value" id="metric-total-products">0</div>
          <div class="trend positive"><i class="fa-solid fa-arrow-up"></i> +12% <span class="trend-label">from last month</span></div>
        </div>

        <div class="metric-card">
          <div class="card-header">
            <span>Average Rating</span>
            <i class="fa-regular fa-star icon-purple"></i>
          </div>
          <div class="metric-value" id="metric-avg-rating">0.00</div>
          <div class="trend positive"><i class="fa-solid fa-arrow-up"></i> +0.4% <span class="trend-label">from last month</span></div>
        </div>

        <div class="metric-card">
          <div class="card-header">
            <span>Active Keywords</span>
            <i class="fa-regular fa-bookmark icon-orange"></i>
          </div>
          <div class="metric-value" id="metric-active-keywords">0</div>
          <div class="trend positive"><i class="fa-solid fa-arrow-up"></i> +3 new <span class="trend-label">from last month</span></div>
        </div>

        <div class="metric-card">
          <div class="card-header">
            <span>Revenue</span>
            <i class="fa-solid fa-dollar-sign icon-green"></i>
          </div>
          <div class="metric-value" id="metric-total-revenue">$0.00</div>
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
          <tbody>
            <tr>
              <td colspan="6" style="text-align: center; padding: 20px; color: #718096;">
                Loading product profiles...
              </td>
            </tr>
          </tbody>
        </table>

        <!-- TABLE PAGINATION -->
        <div class="pagination-row">
          <div class="pagination-info">Showing 0-0 of 0 products</div>
          <div class="pagination-buttons">
            <button class="page-btn" disabled>Previous</button>
            <button class="page-btn">Next</button>
          </div>
        </div>
      </section>

    </div>
  </main>

  <!-- External Script Assets Logic Modules -->
  <script src="products_edit_page.js"></script>
  <script src="edit_logic.js"></script>
  <!-- Load your dynamic JavaScript file -->
  <script src="edit_logic.js"></script>

  <!-- THE INTERACTIVE EVENT LISTENER -->
  <script>
    document.addEventListener("DOMContentLoaded", function() {
        const tbody = document.querySelector("tbody");
        if (!tbody) return;

        // Catch clicks on the table body dynamically
        tbody.addEventListener("click", async function(event) {
            const deleteButton = event.target.closest(".dashboard-delete-trigger");
            if (!deleteButton) return; // Exit if they clicked somewhere else

            event.preventDefault();

            const id = deleteButton.getAttribute("data-product-id");
            const row = deleteButton.closest("tr");
            const productName = row.querySelector(".prod-title") ? row.querySelector(".prod-title").textContent : "this item";

            if (!confirm(`Are you sure you want to delete "${productName}" from the database permanently?`)) {
                return;
            }

            try {
                const formData = new FormData();
                formData.append("delete_id", id);

                // Send request over to your dedicated php processor file
                const response = await fetch("edit_logic.php", {
                    method: "POST",
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    row.remove(); // Instantly wipe it off the web browser screen
                    alert(result.message);
                } else {
                    alert("Database Error: " + result.message);
                }
            } catch (error) {
                console.error("Network error:", error);
                alert("Failed to communicate with your server backend. Check network connections.");
            }
        });
    });
  </script>

<!-- EDIT PRODUCT MODAL OVERLAY -->
<div id="edit-product-modal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
  <div class="modal-content" style="background: #fff; padding: 25px; border-radius: 8px; width: 450px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); position: relative;">
    <h2 style="margin-top: 0; margin-bottom: 20px; font-size: 1.5rem; color: #2d3748;">Edit Product Details</h2>
    
    <form id="edit-product-form">
      <input type="hidden" id="edit-prod-id" name="edit_id">
      
      <div style="margin-bottom: 15px;">
        <label style="display: block; font-weight: 600; margin-bottom: 5px; color: #4a5568;">Product Name</label>
        <input type="text" id="edit-prod-name" name="product_name" required style="width: 100%; padding: 8px; border: 1px solid #cbd5e0; border-radius: 4px; box-sizing: border-box;">
      </div>

      <div style="margin-bottom: 15px;">
        <label style="display: block; font-weight: 600; margin-bottom: 5px; color: #4a5568;">Price ($)</label>
        <input type="number" id="edit-prod-price" name="price" step="0.01" required style="width: 100%; padding: 8px; border: 1px solid #cbd5e0; border-radius: 4px; box-sizing: border-box;">
      </div>

      <div style="margin-bottom: 20px;">
        <label style="display: block; font-weight: 600; margin-bottom: 5px; color: #4a5568;">Keywords (comma separated)</label>
        <input type="text" id="edit-prod-keywords" name="keywords" style="width: 100%; padding: 8px; border: 1px solid #cbd5e0; border-radius: 4px; box-sizing: border-box;">
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 10px;">
        <button type="button" onclick="closeEditModal()" style="background: #edf2f7; color: #4a5568; border: none; padding: 10px 15px; border-radius: 4px; cursor: pointer; font-weight: 600;">Cancel</button>
        <button type="submit" style="background: #3182ce; color: #fff; border: none; padding: 10px 15px; border-radius: 4px; cursor: pointer; font-weight: 600;">Save Changes</button>
      </div>
    </form>
  </div>
</div>


</body>
</html>

 
</body>
</html>
