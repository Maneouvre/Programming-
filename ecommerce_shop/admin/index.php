<?php
declare(strict_types=1);
require __DIR__ . '/require-admin.php';

if (!isset($_SESSION['admin_csrf'])) {
    $_SESSION['admin_csrf'] = bin2hex(random_bytes(32));
}
$adminName = htmlspecialchars((string)($_SESSION['admin_name'] ?? 'Admin'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= htmlspecialchars($_SESSION['admin_csrf'], ENT_QUOTES, 'UTF-8') ?>">
    <title>Admin dashboard | Maneouver</title>
    <style>
      :root { color-scheme: light; font-family: Arial, sans-serif; color: #17231d; background: #f4f7f5; }
      * { box-sizing: border-box; }
      body { margin: 0; }
      .topbar { position: sticky; top: 0; z-index: 1000; min-height: 64px; display: flex; align-items: center; gap: 20px; padding: 12px 24px; background: #084f2d; color: #fff; }
      .brand { display: flex; align-items: center; gap: 10px; margin-right: 18px; color: #fff; text-decoration: none; font-size: 18px; font-weight: 700; }
      .brand img { width: 42px; height: 42px; object-fit: contain; background: white; border-radius: 6px; }
      .nav { display: flex; gap: 6px; }
      .nav button, .topbar a.logout { border: 0; padding: 10px 12px; border-radius: 6px; color: #fff; background: transparent; text-decoration: none; font: inherit; font-weight: 700; cursor: pointer; }
      .nav button:hover, .nav button.active, .topbar a.logout:hover { background: rgba(255,255,255,.16); }
      .admin-user { margin-left: auto; font-size: 14px; font-weight: 700; }
      main { width: min(1280px, 100%); margin: auto; padding: 28px 24px 48px; }
      .page-heading { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 24px; }
      h1 { margin: 0; font-size: 28px; }
      .muted { color: #63716a; }
      .primary-link { display: inline-block; border-radius: 7px; padding: 11px 15px; color: #fff; background: #08712f; text-decoration: none; font-weight: 700; }
      .primary-link:hover { background: #075b27; }
      .metrics { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px; margin-bottom: 22px; }
      .metric, .panel { border: 1px solid #dce5df; border-radius: 10px; background: #fff; box-shadow: 0 3px 10px rgba(20, 49, 32, .04); }
      .metric { padding: 20px; }
      .metric-label { color: #64746a; font-size: 13px; font-weight: 700; }
      .metric-value { margin-top: 10px; font-size: 27px; font-weight: 700; }
      .panel { overflow: hidden; }
      .panel-heading { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 18px 20px; border-bottom: 1px solid #e5ebe7; }
      .panel-heading h2 { margin: 0; font-size: 18px; }
      .table-wrap { overflow-x: auto; }
      table { width: 100%; border-collapse: collapse; text-align: left; }
      th, td { padding: 13px 16px; border-bottom: 1px solid #edf1ee; vertical-align: top; font-size: 14px; }
      th { color: #5c6a61; background: #f8faf8; font-size: 12px; text-transform: uppercase; letter-spacing: .04em; }
      tr:last-child td { border-bottom: 0; }
      .status-select { min-width: 142px; padding: 8px; border: 1px solid #cbd8cf; border-radius: 6px; background: white; }
      .status-note { min-height: 18px; margin-top: 4px; color: #08712f; font-size: 12px; }
      .product-actions { display: flex; flex-wrap: wrap; gap: 6px; }
      .product-actions button { padding: 7px 9px; border: 1px solid #cbd8cf; border-radius: 5px; background: #fff; color: #17231d; cursor: pointer; }
      .product-actions button:hover { background: #f1f7f3; }
      .product-actions .delete-product { color: #9a1c1c; border-color: #e7baba; }
      dialog { width: min(480px, calc(100% - 28px)); border: 0; border-radius: 10px; padding: 24px; box-shadow: 0 15px 50px rgba(0,0,0,.25); }
      dialog::backdrop { background: rgba(15, 23, 42, .45); }
      .edit-form label { display: block; margin: 14px 0 6px; font-size: 13px; font-weight: 700; }
      .edit-form input, .edit-form textarea { width: 100%; padding: 10px; border: 1px solid #cbd8cf; border-radius: 6px; font: inherit; }
      .edit-form textarea { min-height: 70px; resize: vertical; }
      .edit-actions { display: flex; justify-content: flex-end; gap: 8px; margin-top: 18px; }
      .edit-actions button { padding: 9px 13px; border: 0; border-radius: 6px; cursor: pointer; }
      .edit-actions button[type="submit"] { color: #fff; background: #08712f; }
      .product-cell { display: flex; align-items: center; gap: 12px; min-width: 220px; }
      .product-cell img { width: 48px; height: 48px; object-fit: contain; border-radius: 5px; border: 1px solid #eee; }
      .notice { margin: 0; padding: 16px 20px; color: #63716a; }
      .notice.error { color: #9a1c1c; }
      [hidden] { display: none !important; }
      @media (max-width: 820px) {
        .topbar { flex-wrap: wrap; gap: 8px; padding: 12px 14px; }
        .brand { margin-right: auto; }
        .nav { order: 3; width: 100%; overflow-x: auto; }
        .admin-user { margin-left: 0; }
        main { padding: 22px 14px 36px; }
        .metrics { grid-template-columns: repeat(2, minmax(0, 1fr)); }
      }
      @media (max-width: 480px) {
        .page-heading { align-items: flex-start; flex-direction: column; }
        .metrics { gap: 10px; }
        .metric { padding: 14px; }
        .metric-value { font-size: 22px; }
      }
    </style>
  </head>
  <body>
    <header class="topbar">
      <a class="brand" href="index.php"><img src="../images/logo.png" alt=""><span>Maneouver Admin</span></a>
      <nav class="nav" aria-label="Admin navigation">
        <button class="active" type="button" data-view="overview">Overview</button>
        <button type="button" data-view="orders">Orders</button>
        <button type="button" data-view="clients">Clients</button>
        <button type="button" data-view="products">Products</button>
      </nav>
      <span class="admin-user"><?= $adminName ?></span>
      <a class="logout" href="logout.php">Sign out</a>
    </header>
    <main>
      <div class="page-heading">
        <div><h1 id="page-title">Store overview</h1><p class="muted">Manage products, customer accounts, and orders received from your clients.</p></div>
      </div>
      <p id="global-notice" class="notice" role="status" hidden></p>
      <section id="overview-view">
        <div class="metrics">
          <article class="metric"><div class="metric-label">Products</div><div id="metric-products" class="metric-value">—</div></article>
          <article class="metric"><div class="metric-label">Orders received</div><div id="metric-orders" class="metric-value">—</div></article>
          <article class="metric"><div class="metric-label">Registered clients</div><div id="metric-clients" class="metric-value">—</div></article>
          <article class="metric"><div class="metric-label">Order value</div><div id="metric-revenue" class="metric-value">—</div></article>
        </div>
        <section class="panel">
          <div class="panel-heading"><h2>Latest client orders</h2><button type="button" data-view="orders">View all orders</button></div>
          <div id="overview-orders"></div>
        </section>
      </section>
      <section id="orders-view" hidden>
        <section class="panel"><div class="panel-heading"><h2>Orders received from all clients</h2><span id="orders-count" class="muted"></span></div><div id="orders-table"></div></section>
      </section>
      <section id="clients-view" hidden>
        <section class="panel"><div class="panel-heading"><h2>Registered clients</h2><span id="clients-count" class="muted"></span></div><div id="clients-table"></div></section>
      </section>
      <section id="products-view" hidden>
        <section class="panel"><div class="panel-heading"><h2>Store products</h2><a class="primary-link" href="add-product.php">Add product</a></div><div id="products-table"></div></section>
      </section>
    </main>
    <dialog id="edit-product-dialog">
      <h2>Edit product</h2>
      <form class="edit-form" id="edit-product-form">
        <input type="hidden" name="productId">
        <label for="edit-product-name">Product name</label>
        <input id="edit-product-name" name="productName" maxlength="255" required>
        <label for="edit-product-price">Price in cents (KES)</label>
        <input id="edit-product-price" name="priceCents" type="number" min="1" step="1" required>
        <label for="edit-product-rating">Rating (0–5)</label>
        <input id="edit-product-rating" name="ratingStars" type="number" min="0" max="5" step="0.1" required>
        <label for="edit-product-count">Rating count</label>
        <input id="edit-product-count" name="ratingCount" type="number" min="0" step="1" required>
        <label for="edit-product-keywords">Keywords (comma-separated)</label>
        <textarea id="edit-product-keywords" name="keywords"></textarea>
        <p class="notice" id="edit-product-error" role="alert" hidden></p>
        <div class="edit-actions">
          <button type="button" id="cancel-edit-product">Cancel</button>
          <button type="submit">Save changes</button>
        </div>
      </form>
    </dialog>
    <script>
      const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
      const views = ['overview', 'orders', 'clients', 'products'];
      const statusOptions = ['Order received', 'Processing', 'Shipped', 'Being delivered', 'Delivered', 'Cancelled'];
      const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
      })[char]);
      const formatMoney = (amount) => `KES ${Number(amount || 0).toLocaleString('en-KE', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
      const showNotice = (message, isError = false) => {
        const notice = document.querySelector('#global-notice');
        notice.textContent = message;
        notice.classList.toggle('error', isError);
        notice.hidden = false;
      };
      const adminGet = async (action) => {
        const response = await fetch(`api.php?action=${encodeURIComponent(action)}`, { credentials: 'same-origin' });
        const payload = await response.json();
        if (!response.ok) throw new Error(payload.error || `Request failed with HTTP ${response.status}.`);
        return payload;
      };
      const renderOrdersTable = (orders, target) => {
        if (!orders.length) {
          target.innerHTML = '<p class="notice">No client orders have been received yet.</p>';
          return;
        }
        target.innerHTML = `<div class="table-wrap"><table><thead><tr><th>Order</th><th>Client</th><th>Items</th><th>Total</th><th>Pickup</th><th>Received</th><th>Status</th></tr></thead><tbody>${orders.map((order) => {
          const itemSummary = (order.items || []).map((item) => `${escapeHtml(item.name)} × ${Number(item.quantity)}`).join('<br>');
          const options = statusOptions.map((status) => `<option value="${status}" ${status === order.order_status ? 'selected' : ''}>${status}</option>`).join('');
          return `<tr>
            <td><strong>${escapeHtml(order.order_id)}</strong><br><span class="muted">${escapeHtml(order.payment_method)}</span></td>
            <td>${escapeHtml(order.customer_name)}<br><a href="mailto:${encodeURIComponent(order.customer_email)}">${escapeHtml(order.customer_email)}</a></td>
            <td>${itemSummary || '—'}</td>
            <td><strong>${formatMoney(order.total_cents)}</strong></td>
            <td>${escapeHtml(order.county)}<br><span class="muted">${escapeHtml(order.pickup_station)}</span></td>
            <td>${escapeHtml(new Date(order.created_at).toLocaleString())}</td>
            <td><select class="status-select" data-order-id="${escapeHtml(order.order_id)}" aria-label="Update status for ${escapeHtml(order.order_id)}">${options}</select><div class="status-note" aria-live="polite"></div></td>
          </tr>`;
        }).join('')}</tbody></table></div>`;
        target.querySelectorAll('.status-select').forEach((select) => {
          select.addEventListener('change', async () => {
            const note = select.parentElement.querySelector('.status-note');
            select.disabled = true;
            note.textContent = 'Saving…';
            try {
              const response = await fetch('api.php', {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'update-order-status', orderId: select.dataset.orderId, status: select.value, csrfToken })
              });
              const result = await response.json();
              if (!response.ok) throw new Error(result.error || 'Could not update order status.');
              note.textContent = 'Status saved';
              window.setTimeout(() => { note.textContent = ''; }, 2200);
              loadDashboard();
            } catch (error) {
              note.textContent = error.message;
              showNotice(error.message, true);
            } finally {
              select.disabled = false;
            }
          });
        });
      };
      async function loadDashboard() {
        try {
          const summary = await adminGet('dashboard');
          document.querySelector('#metric-products').textContent = Number(summary.products).toLocaleString();
          document.querySelector('#metric-orders').textContent = `${Number(summary.orders).toLocaleString()} (${Number(summary.newOrders).toLocaleString()} new)`;
          document.querySelector('#metric-clients').textContent = Number(summary.clients).toLocaleString();
          document.querySelector('#metric-revenue').textContent = formatMoney(summary.revenueCents);
          renderOrdersTable(summary.latestOrders, document.querySelector('#overview-orders'));
          document.querySelector('#global-notice').hidden = true;
        } catch (error) {
          showNotice(error.message, true);
        }
      }
      async function loadOrders() {
        try {
          const { orders } = await adminGet('orders');
          document.querySelector('#orders-count').textContent = `${orders.length} orders`;
          renderOrdersTable(orders, document.querySelector('#orders-table'));
        } catch (error) {
          showNotice(error.message, true);
        }
      }
      async function loadClients() {
        try {
          const { clients } = await adminGet('clients');
          document.querySelector('#clients-count').textContent = `${clients.length} clients`;
          const target = document.querySelector('#clients-table');
          target.innerHTML = clients.length ? `<div class="table-wrap"><table><thead><tr><th>Username</th><th>Email</th><th>Registered</th><th>Orders</th><th>Total spend</th></tr></thead><tbody>${clients.map((client) => `<tr>
            <td>${escapeHtml(client.username)}</td>
            <td><a href="mailto:${encodeURIComponent(client.email)}">${escapeHtml(client.email)}</a></td>
            <td>${escapeHtml(new Date(client.created_at).toLocaleDateString())}</td>
            <td>${Number(client.orders)}</td>
            <td>${formatMoney(client.spendCents)}</td>
          </tr>`).join('')}</tbody></table></div>` : '<p class="notice">There are no registered clients yet.</p>';
        } catch (error) { showNotice(error.message, true); }
      }
      async function loadProducts() {
        try {
          const { products } = await adminGet('products');
          const target = document.querySelector('#products-table');
          target.innerHTML = products.length ? `<div class="table-wrap"><table><thead><tr><th>Product</th><th>Price</th><th>Rating</th><th>Keywords</th><th>Availability</th><th>Actions</th></tr></thead><tbody>${products.map((product) => {
            const image = `../Admin_dashboard_php_sql/product_upload_dashboard/${String(product.image_path || '').replace(/^\/+/, '')}`;
            return `<tr>
              <td><div class="product-cell"><img src="${escapeHtml(image)}" alt=""><span><strong>${escapeHtml(product.product_name)}</strong><br><span class="muted">#${escapeHtml(product.id)}</span></span></div></td>
              <td>${formatMoney(product.price_cents)}</td>
              <td>${Number(product.rating_stars || 0).toFixed(1)} / 5 (${Number(product.rating_count || 0)})</td>
              <td>${escapeHtml((product.keywords || []).join(', '))}</td>
              <td>${product.is_sold_out ? 'Sold out' : 'Available'}</td>
              <td><div class="product-actions">
                <button type="button" class="edit-product" data-product-id="${escapeHtml(product.id)}">Edit</button>
                <button type="button" class="toggle-sold-out" data-product-id="${escapeHtml(product.id)}" data-sold-out="${product.is_sold_out ? 'true' : 'false'}">${product.is_sold_out ? 'Mark available' : 'Mark sold out'}</button>
                <button type="button" class="delete-product" data-product-id="${escapeHtml(product.id)}">Delete</button>
              </div></td>
            </tr>`;
          }).join('')}</tbody></table></div>` : '<p class="notice">No products are in the catalog yet. Add one to get started.</p>';
          target.querySelectorAll('.edit-product').forEach((button) => {
            button.addEventListener('click', () => {
              const product = products.find((item) => String(item.id) === button.dataset.productId);
              if (!product) return;
              const form = document.querySelector('#edit-product-form');
              form.elements.productId.value = product.id;
              form.elements.productName.value = product.product_name;
              form.elements.priceCents.value = product.price_cents;
              form.elements.ratingStars.value = product.rating_stars;
              form.elements.ratingCount.value = product.rating_count;
              form.elements.keywords.value = (product.keywords || []).join(', ');
              document.querySelector('#edit-product-error').hidden = true;
              document.querySelector('#edit-product-dialog').showModal();
            });
          });
          target.querySelectorAll('.toggle-sold-out').forEach((button) => {
            button.addEventListener('click', async () => {
              button.disabled = true;
              try {
                const response = await fetch('api.php', {
                  method: 'POST',
                  credentials: 'same-origin',
                  headers: { 'Content-Type': 'application/json' },
                  body: JSON.stringify({
                    action: 'set-product-availability',
                    productId: button.dataset.productId,
                    soldOut: button.dataset.soldOut !== 'true',
                    csrfToken
                  })
                });
                const result = await response.json();
                if (!response.ok) throw new Error(result.error || 'Could not update product availability.');
                showNotice(result.soldOut ? 'Product marked as sold out.' : 'Product marked as available.');
                loadProducts();
              } catch (error) {
                showNotice(error.message, true);
                button.disabled = false;
              }
            });
          });
          target.querySelectorAll('.delete-product').forEach((button) => {
            button.addEventListener('click', async () => {
              if (!window.confirm('Delete this product from the catalog? Products included in past orders cannot be deleted.')) return;
              button.disabled = true;
              try {
                const response = await fetch('api.php', {
                  method: 'POST',
                  credentials: 'same-origin',
                  headers: { 'Content-Type': 'application/json' },
                  body: JSON.stringify({ action: 'delete-product', productId: button.dataset.productId, csrfToken })
                });
                const result = await response.json();
                if (!response.ok) throw new Error(result.error || 'Could not delete the product.');
                showNotice('Product deleted from the catalog.');
                loadProducts();
                loadDashboard();
              } catch (error) {
                showNotice(error.message, true);
                button.disabled = false;
              }
            });
          });
        } catch (error) { showNotice(error.message, true); }
      }
      document.querySelector('#cancel-edit-product').addEventListener('click', () => {
        document.querySelector('#edit-product-dialog').close();
      });
      document.querySelector('#edit-product-form').addEventListener('submit', async (event) => {
        event.preventDefault();
        const form = event.currentTarget;
        const error = document.querySelector('#edit-product-error');
        const submit = form.querySelector('[type="submit"]');
        submit.disabled = true;
        error.hidden = true;
        try {
          const response = await fetch('api.php', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
              action: 'update-product',
              productId: form.elements.productId.value,
              productName: form.elements.productName.value,
              priceCents: form.elements.priceCents.value,
              ratingStars: form.elements.ratingStars.value,
              ratingCount: form.elements.ratingCount.value,
              keywords: form.elements.keywords.value,
              csrfToken
            })
          });
          const result = await response.json();
          if (!response.ok) throw new Error(result.error || 'Could not save product changes.');
          document.querySelector('#edit-product-dialog').close();
          showNotice('Product updated.');
          loadProducts();
          loadDashboard();
        } catch (requestError) {
          error.textContent = requestError.message;
          error.hidden = false;
        } finally {
          submit.disabled = false;
        }
      });
      function openView(view) {
        if (!views.includes(view)) return;
        views.forEach((name) => { document.querySelector(`#${name}-view`).hidden = name !== view; });
        document.querySelectorAll('[data-view]').forEach((button) => button.classList.toggle('active', button.dataset.view === view));
        document.querySelector('#page-title').textContent = ({ overview: 'Store overview', orders: 'Client orders', clients: 'Registered clients', products: 'Store products' })[view];
        if (view === 'clients') loadClients();
        if (view === 'products') loadProducts();
        if (view === 'orders') loadOrders();
      }
      document.querySelectorAll('[data-view]').forEach((button) => button.addEventListener('click', () => openView(button.dataset.view)));
      loadDashboard();
    </script>
  </body>
</html>
