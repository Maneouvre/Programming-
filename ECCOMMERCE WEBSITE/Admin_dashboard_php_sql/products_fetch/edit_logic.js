document.addEventListener("DOMContentLoaded", async function() {
    const tbody = document.querySelector("tbody");
    const modal = document.getElementById("edit-product-modal");
    const editForm = document.getElementById("edit-product-form");

    // ==========================================
    // 1. DATA LOADING ENGINE
    // ==========================================
    try {
        const response = await fetch("products_fetch.php");
        const products = await response.json();
        
        if (tbody) {
            tbody.innerHTML = ""; 

            if (products.length === 0) {
                tbody.innerHTML = `<tr><td colspan="6" style="text-align: center; padding: 20px;">No products found in database.</td></tr>`;
                return;
            }

            tbody.innerHTML = products.map(product => {
                const realPrice = (product.price_cents / 100).toFixed(2);
                
                // Process keywords array safely
                let tagsHTML = '';
                let rawKeywords = '';
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
                    rawKeywords = keywordArray.join(', ');
                    tagsHTML = keywordArray.map(kw => `<span class="tag">${String(kw).trim()}</span>`).join('');
                }

                const rawPath = product.image_path || '';
                const filename = rawPath.split('/').pop();
                const correctImagePath = filename ? `../product_upload_dashboard/uploads/${filename}` : '';

                const numericRating = parseFloat(product.rating_stars) || 0;
                const fileNumber = Math.round(numericRating * 10);
                const imagePath = `http://localhost/Admin_dashboard_php_sql/product_upload_dashboard/ratings/rating-${fileNumber}.png`;

                return `
                    <tr>
                      <td>
                        <img src="${correctImagePath}" onerror="this.onerror=null; this.src='../product_upload_dashboard/uploads/placeholder.png';" alt="${product.product_name}" class="prod-img" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                      </td>
                      <td>
                        <div class="prod-title">${product.product_name}</div>
                        <div class="prod-id">ID: PROD-${product.id}</div>
                      </td>
                      <td class="font-medium prod-price-cell" data-raw-price="${realPrice}">$${realPrice}</td>
                      <td class="prod-keywords-cell" data-raw-keywords="${rawKeywords}">
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
                        <!-- HOOKS ADDED HERE FOR THE EDIT SYSTEM -->
                        <button class="action-btn edit dashboard-edit-trigger" data-product-id="${product.id}">
                          Edit <i class="fa-regular fa-pen-to-square"></i>
                        </button>
                        
                        <button class="action-btn delete dashboard-delete-trigger" data-product-id="${product.id}">
                          Delete <i class="fa-regular fa-trash-can"></i>
                        </button>
                      </td>
                    </tr>
                `;
            }).join("");
        }

        if (typeof calculateDashboardMetrics === "function") {
            calculateDashboardMetrics(products);
        }

    } catch (error) {
        console.error("Dashboard table renderer failure:", error);
    }

    // ==========================================
    // 2. OPEN EDIT MODAL HANDLING
    // ==========================================
    if (tbody) {
        tbody.addEventListener("click", function(event) {
            const editButton = event.target.closest(".dashboard-edit-trigger");
            if (!editButton) return;

            const id = editButton.getAttribute("data-product-id");
            const row = editButton.closest("tr");

            // Extract existing UI textual values
            const currentName = row.querySelector(".prod-title").textContent;
            const currentPrice = row.querySelector(".prod-price-cell").getAttribute("data-raw-price");
            const currentKeywords = row.querySelector(".prod-keywords-cell").getAttribute("data-raw-keywords") || '';

            // Inject existing elements straight into the form inputs
            document.getElementById("edit-prod-id").value = id;
            document.getElementById("edit-prod-name").value = currentName;
            document.getElementById("edit-prod-price").value = currentPrice;
            document.getElementById("edit-prod-keywords").value = currentKeywords;

            // Display modal popup container window
            if (modal) modal.style.display = "flex";
        });
    }

    // ==========================================
    // 3. SUBMIT EDIT FORM HANDLING (PDO UPDATE)
    // ==========================================
    if (editForm) {
        editForm.addEventListener("submit", async function(event) {
            event.preventDefault();

            const formData = new FormData(editForm);
            // Append action flag so PHP understands this is an update query payload operation
            formData.append("action", "update"); 

                       try {
                const response = await fetch("edit_logic.php", {
                    method: "POST",
                    body: formData
                });

                // 1. Read the server response as raw text first
                const responseText = await response.text();
                
                // 2. Log it to the console so you can read any PHP crash logs instantly
                console.log("RAW PHP OUTPUT FROM SERVER:", responseText);

                // 3. Try to parse it manually now
                let result;
                try {
                    result = JSON.parse(responseText);
                } catch (parseError) {
                    console.error("Failed to parse server text as clean JSON. The server sent HTML errors instead.");
                    alert("Server Error! Check your F12 Developer Tools Console tab to see the exact PHP error line.");
                    return;
                }

                // 4. Handle clean JSON data results
                if (result.success) {
                    alert(result.message);
                    window.location.reload(); 
                } else {
                    alert("Database Update Error: " + result.message);
                }
                
            } catch (error) {
                console.error("Network communication update failure:", error);
                alert("Failed to communicate with update server API pathways.");
            }
        });

    }
});

// Global scope window methods handler to dissolve overlay frames safely
window.closeEditModal = function() {
    const modal = document.getElementById("edit-product-modal");
    if (modal) modal.style.display = "none";
};
