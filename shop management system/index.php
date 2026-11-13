<?php
declare(strict_types=1);
session_start([
    'cookie_httponly' => true,
    'cookie_secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'cookie_samesite' => 'Lax',
    'use_strict_mode' => true,
]);
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f5f7f4">
    <title>SokoFlow — Shop POS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div id="toast" class="toast" role="status" aria-live="polite"></div>
<main id="authView" class="auth-shell" hidden></main>
<div id="appView" class="app-shell" hidden>
    <aside class="sidebar">
        <a class="brand" href="#" aria-label="SokoFlow home">
            <span class="brand-mark">s<span>.</span></span>
            <span class="brand-name">soko<span>flow</span></span>
        </a>
        <div class="shop-switcher"><span class="shop-avatar">M</span><span><b>My shop</b><small>Single location</small></span><span class="switch-chevron">⌄</span></div>
        <div class="nav-caption">WORKSPACE</div>
        <nav class="main-nav" aria-label="Main navigation">
            <button class="nav-link active" data-page="sell"><span class="nav-icon">▦</span>Sell <span class="nav-shortcut">1</span></button>
            <button class="nav-link owner-only" data-page="products"><span class="nav-icon">◫</span>Products <span class="nav-shortcut">2</span></button>
            <button class="nav-link owner-only" data-page="reports"><span class="nav-icon">▥</span>Reports <span class="nav-shortcut">3</span></button>
            <button class="nav-link" data-page="debts"><span class="nav-icon">◷</span>Credit book <span id="debtNavCount" class="nav-count">0</span></button>
        </nav>
        <div class="sidebar-bottom">
            <div class="help-card"><span class="help-spark">✳</span><b>Good business<br>starts here.</b><p>Keep track of every sale, every day.</p><span class="help-decoration">↗</span></div>
            <div class="user-card"><span id="userAvatar" class="user-avatar">S</span><span class="user-meta"><b id="userName">Shop owner</b><small id="userRole">Owner</small></span><button id="logoutButton" class="icon-button logout-button" title="Sign out" aria-label="Sign out">↗</button></div>
        </div>
    </aside>
    <section class="workspace">
        <header class="topbar"><div><span class="breadcrumb">Workspace</span><span class="breadcrumb-separator">/</span><b id="pageTitle">Sell</b></div><div class="topbar-right"><span id="todayLabel" class="today-label"></span><span class="online-dot"></span><span class="online-label">All changes saved</span><span class="topbar-avatar" id="topAvatar">S</span></div></header>
        <section id="sellPage" class="page active-page">
            <div class="page-heading sell-heading"><div><p class="eyebrow">POINT OF SALE</p><h1>Make a sale<span class="heading-dot">.</span></h1><p class="page-subtitle">A good day starts with a great customer.</p></div><div class="sale-date"><span class="date-icon">◷</span><span><small>Today</small><b id="saleDate"></b></span></div></div>
            <div class="sell-layout">
                <section class="catalog-panel">
                    <div class="catalog-toolbar"><label class="search-box"><span class="search-icon">⌕</span><input id="productSearch" type="search" placeholder="Search products..." autocomplete="off"><kbd>/</kbd></label><div id="categoryFilters" class="category-filters"></div></div>
                    <div id="productGrid" class="product-grid"></div>
                </section>
                <aside class="cart-panel">
                    <div class="cart-header"><div><h2>Current sale</h2><p id="cartCount">No items yet</p></div><button id="clearCart" class="text-button">Clear all</button></div>
                    <div id="cartItems" class="cart-items"><div class="empty-cart"><span>＋</span><b>Your sale starts here</b><p>Add items from the product list.</p></div></div>
                    <div class="cart-bottom">
                        <div class="summary-row"><span>Subtotal</span><b id="cartSubtotal">KES 0.00</b></div>
                        <div class="summary-row total-row"><span>Total due</span><b id="cartTotal">KES 0.00</b></div>
                        <div class="payment-label">PAYMENT METHOD</div>
                        <div class="payment-options"><button class="payment-option selected" data-method="cash"><span class="payment-symbol">K</span><span>Cash</span></button><button class="payment-option" data-method="mpesa"><span class="mpesa-mark">M</span><span>M-Pesa</span></button><button class="payment-option" data-method="credit"><span class="credit-mark">↗</span><span>Credit</span></button></div>
                        <div id="cashFields" class="payment-fields"><label class="field-label" for="amountReceived">Amount received</label><div class="money-input"><span>KES</span><input id="amountReceived" inputmode="decimal" type="number" min="0" step="0.01" placeholder="0.00"></div><div class="change-line"><span>Change due</span><b id="changeDue">KES 0.00</b></div></div>
                        <div id="creditFields" class="payment-fields" hidden><label class="field-label" for="creditCustomer">Customer name</label><input id="creditCustomer" class="plain-input" type="text" maxlength="120" placeholder="Who is taking this on credit?"><p class="credit-note">The balance will be added to your credit book.</p></div>
                        <button id="completeSale" class="pay-button" disabled><span>Complete sale</span><span id="payButtonTotal">KES 0.00&nbsp; →</span></button>
                        <p class="secure-note"><span>●</span> Sale is recorded securely</p>
                    </div>
                </aside>
            </div>
        </section>
        <section id="productsPage" class="page" hidden>
            <div class="page-heading"><div><p class="eyebrow">YOUR INVENTORY</p><h1>Products<span class="heading-dot">.</span></h1><p class="page-subtitle">Manage the items you have in store.</p></div><button id="addProduct" class="primary-button">＋ <span>Add product</span></button></div>
            <div class="inventory-stats"><div><span class="stat-icon">◫</span><span><small>Total products</small><b id="productStatCount">0</b></span></div><div><span class="stat-icon amber-icon">△</span><span><small>Running low</small><b id="productStatLow">0</b></span></div><div><span class="stat-icon green-icon">◉</span><span><small>Inventory value</small><b id="productStatValue">KES 0</b></span></div></div>
            <div class="table-card"><div class="table-toolbar"><div><h2>All products</h2><p>Every item in your shop, at a glance.</p></div><label class="search-box table-search"><span class="search-icon">⌕</span><input id="inventorySearch" type="search" placeholder="Find a product..."></label></div><div class="table-scroll"><table><thead><tr><th>PRODUCT</th><th>SELLING PRICE</th><th>BUYING PRICE</th><th>IN STOCK</th><th>STATUS</th><th></th></tr></thead><tbody id="inventoryRows"></tbody></table><div id="inventoryEmpty" class="table-empty" hidden>No products yet. Add your first product to get started.</div></div></div>
        </section>
        <section id="reportsPage" class="page" hidden>
            <div class="page-heading"><div><p class="eyebrow">THE BIG PICTURE</p><h1>Daily report<span class="heading-dot">.</span></h1><p class="page-subtitle">A little clarity goes a long way.</p></div><label class="date-picker"><span>◷</span><input id="reportDate" type="date"></label></div>
            <div class="report-cards"><article class="report-card report-sales"><div class="report-card-top"><span>Total sales</span><span class="report-icon">↗</span></div><b id="reportTotal">KES 0.00</b><small><span id="reportSaleCount">0</span> transactions</small><span class="report-card-shape"></span></article><article class="report-card"><div class="report-card-top"><span>Cash received</span><span class="report-icon cash-report-icon">K</span></div><b id="reportCash">KES 0.00</b><small>Cash payments</small></article><article class="report-card"><div class="report-card-top"><span>M-Pesa received</span><span class="report-icon mpesa-report-icon">M</span></div><b id="reportMpesa">KES 0.00</b><small>M-Pesa payments</small></article><article class="report-card report-profit"><div class="report-card-top"><span>Estimated profit</span><span class="report-icon">↗</span></div><b id="reportProfit">KES 0.00</b><small>Based on buying prices</small></article></div>
            <div class="report-columns"><div class="table-card"><div class="table-toolbar compact-toolbar"><div><h2>Recent sales</h2><p>Transactions for the selected day.</p></div></div><div id="latestSales" class="recent-sales"></div></div><div class="table-card low-stock-card"><div class="table-toolbar compact-toolbar"><div><h2>Low stock</h2><p>Items ready for a restock.</p></div><span class="low-stock-badge">ATTENTION</span></div><div id="lowStockList" class="low-stock-list"></div></div></div>
        </section>
        <section id="debtsPage" class="page" hidden>
            <div class="page-heading"><div><p class="eyebrow">CUSTOMER CREDIT</p><h1>Credit book<span class="heading-dot">.</span></h1><p class="page-subtitle">Keep track of what your customers owe.</p></div></div>
            <div class="debt-summary"><span class="debt-summary-icon">◷</span><span><small>Outstanding balance</small><b id="debtTotal">KES 0.00</b></span><span class="debt-summary-count"><b id="debtCount">0</b> open balances</span></div>
            <div class="table-card"><div class="table-toolbar"><div><h2>Open balances</h2><p>Record a payment when your customer settles up.</p></div></div><div class="table-scroll"><table><thead><tr><th>CUSTOMER</th><th>SALE #</th><th>DATE</th><th>ORIGINAL</th><th>REMAINING</th><th></th></tr></thead><tbody id="debtRows"></tbody></table><div id="debtEmpty" class="table-empty" hidden>No open balances. Nice work!</div></div></div>
        </section>
    </section>
</div>
<dialog id="productDialog" class="modal">
    <form id="productForm" method="dialog">
        <div class="modal-heading"><div><p class="eyebrow">INVENTORY</p><h2 id="productDialogTitle">Add product</h2></div><button type="button" class="icon-button modal-close" data-close-dialog="productDialog" aria-label="Close">×</button></div>
        <input id="productId" type="hidden">
        <label class="field-label" for="productName">Product name</label><input id="productName" class="plain-input" maxlength="120" required placeholder="e.g. Maize flour 2kg">
        <div class="form-grid"><div><label class="field-label" for="productCategory">Category</label><input id="productCategory" class="plain-input" maxlength="80" placeholder="e.g. Groceries"></div><div><label class="field-label" for="productStock">Stock count</label><input id="productStock" class="plain-input" type="number" min="0" step="1" required></div></div>
        <div class="form-grid"><div><label class="field-label" for="productPrice">Selling price (KES)</label><input id="productPrice" class="plain-input" type="number" min="0.01" step="0.01" required></div><div><label class="field-label" for="productCost">Buying price (KES)</label><input id="productCost" class="plain-input" type="number" min="0" step="0.01" required></div></div>
        <label class="field-label" for="productLowStock">Low-stock alert at</label><input id="productLowStock" class="plain-input" type="number" min="0" step="1" value="5" required>
        <div class="modal-actions"><button type="button" class="secondary-button" data-close-dialog="productDialog">Cancel</button><button type="submit" class="primary-button">Save product <span>→</span></button></div>
    </form>
</dialog>
<dialog id="debtDialog" class="modal debt-modal">
    <form id="debtForm" method="dialog">
        <div class="modal-heading"><div><p class="eyebrow">CREDIT BOOK</p><h2>Record payment</h2></div><button type="button" class="icon-button modal-close" data-close-dialog="debtDialog" aria-label="Close">×</button></div>
        <input id="debtId" type="hidden"><p id="debtBalanceHint" class="debt-balance-hint"></p>
        <label class="field-label" for="debtAmount">Amount received (KES)</label><input id="debtAmount" class="plain-input" type="number" min="0.01" step="0.01" required>
        <label class="field-label" for="debtMethod">Payment method</label><select id="debtMethod" class="plain-input"><option value="cash">Cash</option><option value="mpesa">M-Pesa</option></select>
        <div class="modal-actions"><button type="button" class="secondary-button" data-close-dialog="debtDialog">Cancel</button><button type="submit" class="primary-button">Record payment <span>→</span></button></div>
    </form>
</dialog>
<dialog id="receiptDialog" class="modal receipt-modal"><div class="receipt-content" id="receiptContent"></div><div class="receipt-actions"><button id="shareReceipt" class="secondary-button">Share</button><button id="printReceipt" class="primary-button">Print receipt</button><button type="button" class="icon-button modal-close" data-close-dialog="receiptDialog" aria-label="Close">×</button></div></dialog>
<script>window.SOKOFLOW_CSRF = <?= json_encode($_SESSION['csrf_token'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;</script>
<script src="app.js" defer></script>
</body>
</html>
