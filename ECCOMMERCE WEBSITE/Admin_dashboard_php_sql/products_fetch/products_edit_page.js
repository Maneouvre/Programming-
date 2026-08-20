    // 1. Core metric logic layer
    function calculateDashboardMetrics(products) {
        if (!products || products.length === 0) return;

        const totalProducts = products.length;
        let totalStars = 0;
        let productsWithRatings = 0;
        const uniqueKeywords = new Set();
        let totalCents = 0;

        products.forEach(product => {
            // Process Average Rating calculations
            const rating = parseFloat(product.rating_stars);
            if (!isNaN(rating)) {
                totalStars += rating;
                productsWithRatings++;
            }

            // Process Total Revenue accumulations
            const cents = parseInt(product.price_cents);
            if (!isNaN(cents)) {
                totalCents += cents;
            }

            // Process Keywords normalization lists
            if (product.keywords) {
                let kwArray = [];
                if (Array.isArray(product.keywords)) {
                    kwArray = product.keywords;
                } else if (typeof product.keywords === 'string') {
                    try {
                        kwArray = JSON.parse(product.keywords);
                        if (!Array.isArray(kwArray)) kwArray = [kwArray];
                    } catch(e) {
                        kwArray = product.keywords.split(',');
                    }
                }
                kwArray.forEach(kw => {
                    const trimmed = String(kw).trim().toLowerCase();
                    if (trimmed) uniqueKeywords.add(trimmed);
                });
            }
        });

        const avgRating = productsWithRatings > 0 ? (totalStars / productsWithRatings).toFixed(2) : "0.00";
        const totalRevenue = (totalCents / 100).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        // Safely update specific DOM metric cards wrappers
        const totalEl = document.getElementById("metric-total-products");
        const avgEl = document.getElementById("metric-avg-rating");
        const kwEl = document.getElementById("metric-active-keywords");
        const revEl = document.getElementById("metric-total-revenue");

        if (totalEl) totalEl.textContent = totalProducts.toLocaleString();
        if (avgEl) avgEl.textContent = avgRating;
        if (kwEl) kwEl.textContent = uniqueKeywords.size;
        if (revEl) revEl.textContent = `$${totalRevenue}`;

        const infoText = document.querySelector(".pagination-info");
        if (infoText) {
            infoText.textContent = `Showing 1-${totalProducts} of ${totalProducts.toLocaleString()} products`;
        }
    }

    // 2. Dashboard asynchronous data orchestration
    document.addEventListener("DOMContentLoaded", async function() {
        try {
            const response = await fetch("products_fetch.php");
            const products = await response.json();
            
            // Build metrics display profiles
            calculateDashboardMetrics(products);
            
            const tbody = document.querySelector("tbody");
            tbody.innerHTML = ""; 

            if (products.length === 0) {
                tbody.innerHTML = `<tr><td colspan="6" style="text-align: center; padding: 20px;">No products found in database.</td></tr>`;
                return;
            }

            // Map each product to a table row
            tbody.innerHTML = products.map(product => {
                const realPrice = (product.price_cents / 100).toFixed(2);
                
                // Parse keyword nodes
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

                // Repair paths for uploaded product files 
                const rawPath = product.image_path || '';
                const filename = rawPath.split('/').pop();
                const correctImagePath = filename ? `../product_upload_dashboard/uploads/${filename}` : '';

                // Rating file mappings configuration profile
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
                        <button class="action-btn delete">Delete <i class="fa-regular fa-trash-can"></i></button>
                      </td>
                    </tr>
                `;
            }).join("");

        } catch (error) {
            console.error("Something went wrong:", error);
            const tbody = document.querySelector("tbody");
            if (tbody) {
                tbody.innerHTML = `<tr><td colspan="6" style="text-align: center; color: red; padding: 20px;">Error loading product data rows.</td></tr>`;
            }
        }
    });
