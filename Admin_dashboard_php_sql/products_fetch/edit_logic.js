// ==========================================
// DYNAMIC DASHBOARD ROW INJECTION ENGINE
// ==========================================
document.addEventListener("DOMContentLoaded", async function() {
    try {
        // 1. Fetch your dynamic product array stream from your PHP file
        const response = await fetch("products_fetch.php");
        const products = await response.json();
        
        // 2. TARGET THE DOM: Look up and define tbody cleanly
        const tbody = document.querySelector("tbody");
        if (!tbody) {
            console.error("DOM Error: Could not locate table <tbody> container element.");
            return;
        }
        
        // Clear out any loading placeholders safely
        tbody.innerHTML = ""; 

        // Handle case where database holds zero items
        if (products.length === 0) {
            tbody.innerHTML = `<tr><td colspan="6" style="text-align: center; padding: 20px;">No products found in database.</td></tr>`;
            return;
        }

        // 3. MAP THE ARRAY: Build table rows and assign the dashboard-delete-trigger hooks
        tbody.innerHTML = products.map(product => {
            const realPrice = (product.price_cents / 100).toFixed(2);
            
            // Clean and process keywords string lists or tags array configurations
            let tagsHTML = '';
            if (product.keywords) {
                let keywordArray = [];
                if (Array.isArray(product.keywords)) {
                    keywordArray = product.keywords;
                } else if (typeof product.keywords === 'string') {
                    try {
                        keywordArray = JSON.parse(product.keywords);
                        if (!Array.isArray(keywordArray)) keywordArray = [keywordArray];
                    } catch(e) {
                        keywordArray = product.keywords.split(',');
                    }
                } else {
                    keywordArray = [String(product.keywords)];
                }
                tagsHTML = keywordArray.map(kw => `<span class="tag">${String(kw).trim()}</span>`).join('');
            }

            // Isolate filenames to repair server folder path discrepancies
            const rawPath = product.image_path || '';
            const filename = rawPath.split('/').pop();
            const correctImagePath = filename ? `../product_upload_dashboard/uploads/${filename}` : '';

            // Map decimal star values directly to whole integer path identifiers
            const numericRating = parseFloat(product.rating_stars) || 0;
            const fileNumber = Math.round(numericRating * 10);
            const imagePath = `http://localhost/Admin_dashboard_php_sql/product_upload_dashboard/ratings/rating-${fileNumber}.png`;

            return `
                <tr>
                  <td>
                    <img src="${correctImagePath}" 
                         onerror="this.onerror=null; this.src='../product_upload_dashboard/uploads/placeholder.png';" 
                         alt="${product.product_name}" 
                         class="prod-img"
                         style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                  </td>
                  <td>
                    <div class="prod-title">${product.product_name}</div>
                    <div class="prod-id">ID: PROD-${product.id}</div>
                  </td>
                  <td class="font-medium">$${realPrice}</td>
                  <td>
                    ${tagsHTML || '<span class="tag">General</span>'}
                  </td>
                  <td>
                    <div style="display: flex; align-items: center; gap: 6px; white-space: nowrap;">
                      <img src="${imagePath}" alt="${numericRating} stars" style="height: 16px; object-fit: contain; vertical-align: middle;">
                      <span class="rating-text" style="font-size: 13px; color: #4a5568;">
                        ${numericRating} (${product.rating_count || 0} reviews)
                      </span>
                    </div>
                  </td>
                  <td class="text-right actions-cell">
                    <button class="action-btn edit">Edit <i class="fa-regular fa-pen-to-square"></i></button>
                    
                    <!-- THE LINK HOOK FOR YOUR PRODUCTS.PHP EVENT DELEGATION LISTENER -->
                    <button class="action-btn delete dashboard-delete-trigger" data-product-id="${product.id}">
                      Delete <i class="fa-regular fa-trash-can"></i>
                    </button>
                  </td>
                </tr>
            `;
        }).join("");

        // 4. METRICS INITIALIZER: Pass array data over to metrics updater card nodes if configured
        if (typeof calculateDashboardMetrics === "function") {
            calculateDashboardMetrics(products);
        }

    } catch (error) {
        console.error("Dashboard table renderer failure exception loop:", error);
    }
});
