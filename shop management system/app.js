const state = {
    user: null,
    products: [],
    cart: new Map(),
    category: 'All items',
    paymentMethod: 'cash'
};

const $ = (selector, root = document) => root.querySelector(selector);
const $$ = (selector, root = document) => [...root.querySelectorAll(selector)];
const money = value => `KES ${Number(value || 0).toLocaleString('en-KE', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
const escapeHtml = value => String(value ?? '').replace(/[&<>"']/g, char => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
}[char]));
const localDate = (date = new Date()) => {
    const offset = date.getTimezoneOffset() * 60000;
    return new Date(date.getTime() - offset).toISOString().slice(0, 10);
};

function toast(message, error = false) {
    const element = $('#toast');
    element.textContent = message;
    element.classList.toggle('toast-error', error);
    element.classList.add('toast-visible');
    clearTimeout(toast.timer);
    toast.timer = setTimeout(() => element.classList.remove('toast-visible'), 3000);
}

async function request(action, data = {}) {
    const response = await fetch('api.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': window.SOKOFLOW_CSRF
        },
        body: JSON.stringify({ action, ...data })
    });
    const result = await response.json().catch(() => ({}));
    if (!response.ok) {
        if (response.status === 419) {
            window.location.reload();
        }
        throw new Error(result.error || 'Unable to complete that request.');
    }
    return result;
}

function showAuth(setupRequired) {
    $('#appView').hidden = true;
    const root = $('#authView');
    root.hidden = false;
    if (setupRequired) {
        root.innerHTML = `
            <section class="auth-card setup-card">
                <a class="brand auth-brand" href="#"><span class="brand-mark">s<span>.</span></span><span class="brand-name">soko<span>flow</span></span></a>
                <div class="auth-intro"><p class="eyebrow">WELCOME TO SOKOFLOW</p><h1>Your shop,<br>in good hands<span class="heading-dot">.</span></h1><p>Set up your shop by creating an owner and cashier account. Passwords must be at least 10 characters.</p></div>
                <form id="setupForm" class="auth-form">
                    <div class="setup-person"><span class="role-badge owner-badge">OWNER</span><h2>Owner account</h2>
                        <label class="field-label" for="ownerName">Full name</label><input id="ownerName" class="plain-input" required maxlength="100" autocomplete="name" placeholder="Your name">
                        <label class="field-label" for="ownerUsername">Username</label><input id="ownerUsername" class="plain-input" required minlength="3" maxlength="40" autocomplete="username" placeholder="e.g. jane.owner">
                        <label class="field-label" for="ownerPassword">Password</label><input id="ownerPassword" class="plain-input" type="password" required minlength="10" autocomplete="new-password" placeholder="At least 10 characters">
                    </div>
                    <div class="setup-person"><span class="role-badge cashier-badge">CASHIER</span><h2>Cashier account</h2>
                        <label class="field-label" for="cashierName">Full name</label><input id="cashierName" class="plain-input" required maxlength="100" autocomplete="name" placeholder="Cashier's name">
                        <label class="field-label" for="cashierUsername">Username</label><input id="cashierUsername" class="plain-input" required minlength="3" maxlength="40" autocomplete="username" placeholder="e.g. cashier">
                        <label class="field-label" for="cashierPassword">Password</label><input id="cashierPassword" class="plain-input" type="password" required minlength="10" autocomplete="new-password" placeholder="At least 10 characters">
                    </div>
                    <button class="primary-button auth-submit" type="submit">Create accounts <span>→</span></button>
                </form>
                <p class="auth-footnote"><span>◇</span> Your password is stored securely and never shown again. <a href="hosting.php">Database &amp; hosting setup</a></p>
            </section>
            <div class="auth-side"><div class="auth-art"><span class="art-sun"></span><span class="art-orbit orbit-one"></span><span class="art-orbit orbit-two"></span><div class="art-bag">s<span>.</span></div><div class="art-label label-one"><span>✦</span> All in one place</div><div class="art-label label-two"><span>↗</span> Made for your shop</div><span class="art-leaf leaf-one">⌁</span><span class="art-leaf leaf-two">⌁</span></div><p>Simple tools for a thriving shop.</p><small>SALES · STOCK · CLARITY</small></div>`;
        $('#setupForm').addEventListener('submit', async event => {
            event.preventDefault();
            const button = $('button[type="submit"]', event.currentTarget);
            button.disabled = true;
            try {
                await request('setup', {
                    owner_name: $('#ownerName').value,
                    owner_username: $('#ownerUsername').value,
                    owner_password: $('#ownerPassword').value,
                    cashier_name: $('#cashierName').value,
                    cashier_username: $('#cashierUsername').value,
                    cashier_password: $('#cashierPassword').value
                });
                toast('Accounts created. Sign in to get started.');
                showAuth(false);
            } catch (error) {
                toast(error.message, true);
                button.disabled = false;
            }
        });
        return;
    }

    root.innerHTML = `
        <section class="auth-card login-card">
            <a class="brand auth-brand" href="#"><span class="brand-mark">s<span>.</span></span><span class="brand-name">soko<span>flow</span></span></a>
            <div class="auth-intro"><p class="eyebrow">YOUR SHOP, IN GOOD HANDS</p><h1>Welcome<br>back<span class="heading-dot">.</span></h1><p>Sign in to pick up right where you left off.</p></div>
            <form id="loginForm" class="auth-form"><label class="field-label" for="loginUsername">Username</label><input id="loginUsername" class="plain-input" required autocomplete="username" placeholder="Enter your username"><label class="field-label" for="loginPassword">Password</label><input id="loginPassword" class="plain-input" type="password" required autocomplete="current-password" placeholder="Enter your password"><button class="primary-button auth-submit" type="submit">Sign in <span>→</span></button></form>
            <p class="auth-footnote"><span>◇</span> Secure sign-in for your shop team. <a href="hosting.php">Database &amp; hosting setup</a></p>
        </section>
        <div class="auth-side"><div class="auth-art"><span class="art-sun"></span><span class="art-orbit orbit-one"></span><span class="art-orbit orbit-two"></span><div class="art-bag">s<span>.</span></div><div class="art-label label-one"><span>✦</span> All in one place</div><div class="art-label label-two"><span>↗</span> Made for your shop</div><span class="art-leaf leaf-one">⌁</span><span class="art-leaf leaf-two">⌁</span></div><p>Simple tools for a thriving shop.</p><small>SALES · STOCK · CLARITY</small></div>`;
    $('#loginForm').addEventListener('submit', async event => {
        event.preventDefault();
        const button = $('button[type="submit"]', event.currentTarget);
        button.disabled = true;
        try {
            const result = await request('login', {
                username: $('#loginUsername').value,
                password: $('#loginPassword').value
            });
            window.SOKOFLOW_CSRF = result.csrf_token;
            state.user = result.user;
            showApp();
        } catch (error) {
            toast(error.message, true);
            button.disabled = false;
        }
    });
}

function showApp() {
    $('#authView').hidden = true;
    $('#appView').hidden = false;
    $('#userName').textContent = state.user.name;
    $('#userRole').textContent = state.user.role === 'owner' ? 'Shop owner' : 'Cashier';
    const initial = state.user.name.trim().charAt(0).toUpperCase() || 'S';
    $('#userAvatar').textContent = initial;
    $('#topAvatar').textContent = initial;
    $$('.owner-only').forEach(element => { element.hidden = state.user.role !== 'owner'; });
    const now = new Date();
    $('#todayLabel').textContent = now.toLocaleDateString('en-KE', { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' });
    $('#saleDate').textContent = now.toLocaleDateString('en-KE', { weekday: 'long', day: 'numeric', month: 'long' });
    navigate('sell');
    loadProducts();
    loadDebts();
}

function navigate(page) {
    if (state.user?.role !== 'owner' && ['products', 'reports'].includes(page)) {
        page = 'sell';
    }
    $$('.page').forEach(element => {
        const active = element.id === `${page}Page`;
        element.hidden = !active;
        element.classList.toggle('active-page', active);
    });
    $$('.nav-link').forEach(element => element.classList.toggle('active', element.dataset.page === page));
    const titles = { sell: 'Sell', products: 'Products', reports: 'Reports', debts: 'Credit book' };
    $('#pageTitle').textContent = titles[page];
    if (page === 'products') renderInventory();
    if (page === 'reports') loadReport();
    if (page === 'debts') loadDebts();
}

async function loadProducts() {
    try {
        const result = await request('products');
        state.products = result.products;
        renderCatalog();
        renderInventory();
    } catch (error) {
        toast(error.message, true);
    }
}

function renderCatalog() {
    const categories = ['All items', ...new Set(state.products.map(product => product.category).filter(Boolean))];
    if (!categories.includes(state.category)) state.category = 'All items';
    $('#categoryFilters').innerHTML = categories.map(category => `<button class="category-chip ${category === state.category ? 'selected' : ''}" data-category="${escapeHtml(category)}">${escapeHtml(category)}</button>`).join('');
    const search = $('#productSearch').value.trim().toLowerCase();
    const products = state.products.filter(product => {
        const matchesSearch = `${product.name} ${product.category}`.toLowerCase().includes(search);
        const matchesCategory = state.category === 'All items' || product.category === state.category;
        return matchesSearch && matchesCategory;
    });
    $('#productGrid').innerHTML = products.length ? products.map((product, index) => {
        const out = Number(product.stock) === 0;
        const low = !out && Number(product.stock) <= Number(product.low_stock);
        const qty = state.cart.get(Number(product.id))?.quantity || 0;
        return `<button class="product-card ${out ? 'out-of-stock' : ''}" data-add-product="${Number(product.id)}" ${out ? 'disabled' : ''}><span class="product-card-top"><span class="product-emoji">${productEmoji(product.category, index)}</span>${low ? '<span class="stock-alert">LOW</span>' : ''}</span><span class="product-name">${escapeHtml(product.name)}</span><span class="product-category">${escapeHtml(product.category || 'General')}</span><span class="product-card-bottom"><b>${money(product.price)}</b><span class="product-stock ${low ? 'low' : ''}">${out ? 'Out of stock' : `${Number(product.stock)} left`}</span></span>${qty ? `<span class="product-in-cart">${qty}</span>` : ''}</button>`;
    }).join('') : `<div class="catalog-empty"><span>⌕</span><b>${state.products.length ? 'No matching products' : 'Your shelf is waiting'}</b><p>${state.products.length ? 'Try another search or category.' : 'Add products in Inventory to start selling.'}</p>${state.user.role === 'owner' && !state.products.length ? '<button class="primary-button" data-go-products>Add your first product →</button>' : ''}</div>`;
}

function productEmoji(category, index) {
    const icons = {
        grocery: ['🥫', '🥛', '🍚', '🍞', '🫘'],
        food: ['🥫', '🥛', '🍚', '🍞', '🫘'],
        drinks: ['🥤', '🧃', '☕', '🧋'],
        household: ['🧹', '🧼', '🧽', '🧻'],
        beauty: ['🧴', '🪥', '🧼', '🪮'],
        stationery: ['✏️', '📒', '🖊️', '📎']
    };
    const key = String(category || '').toLowerCase();
    const group = Object.keys(icons).find(name => key.includes(name)) || 'grocery';
    return icons[group][index % icons[group].length];
}

function addToCart(id) {
    const product = state.products.find(item => Number(item.id) === id);
    if (!product) return;
    const current = state.cart.get(id)?.quantity || 0;
    if (current >= Number(product.stock)) {
        toast('You have reached the available stock for this item.', true);
        return;
    }
    state.cart.set(id, { product, quantity: current + 1 });
    renderCatalog();
    renderCart();
}

function renderCart() {
    const items = [...state.cart.values()];
    const count = items.reduce((sum, item) => sum + item.quantity, 0);
    const total = items.reduce((sum, item) => sum + Number(item.product.price) * item.quantity, 0);
    $('#cartCount').textContent = count ? `${count} ${count === 1 ? 'item' : 'items'}` : 'No items yet';
    $('#cartSubtotal').textContent = money(total);
    $('#cartTotal').textContent = money(total);
    $('#payButtonTotal').textContent = `${money(total)}  →`;
    $('#cartItems').innerHTML = items.length ? items.map(item => `<div class="cart-item"><span class="cart-item-icon">${productEmoji(item.product.category, Number(item.product.id))}</span><span class="cart-item-meta"><b>${escapeHtml(item.product.name)}</b><small>${money(item.product.price)} each</small></span><span class="quantity-control"><button data-quantity="${Number(item.product.id)}" data-change="-1" aria-label="Decrease quantity">−</button><b>${item.quantity}</b><button data-quantity="${Number(item.product.id)}" data-change="1" aria-label="Increase quantity">＋</button></span><b class="cart-item-total">${money(Number(item.product.price) * item.quantity)}</b></div>`).join('') : '<div class="empty-cart"><span>＋</span><b>Your sale starts here</b><p>Add items from the product list.</p></div>';
    $('#completeSale').disabled = count === 0;
    updateChange();
}

function updateChange() {
    const total = [...state.cart.values()].reduce((sum, item) => sum + Number(item.product.price) * item.quantity, 0);
    const received = Number($('#amountReceived').value || 0);
    $('#changeDue').textContent = money(Math.max(0, received - total));
}

function renderInventory() {
    const products = state.products;
    $('#productStatCount').textContent = products.length;
    $('#productStatLow').textContent = products.filter(product => Number(product.stock) <= Number(product.low_stock)).length;
    $('#productStatValue').textContent = money(products.reduce((sum, product) => sum + Number(product.cost) * Number(product.stock), 0));
    const query = ($('#inventorySearch')?.value || '').trim().toLowerCase();
    const filtered = products.filter(product => `${product.name} ${product.category}`.toLowerCase().includes(query));
    $('#inventoryRows').innerHTML = filtered.map(product => {
        const low = Number(product.stock) <= Number(product.low_stock);
        return `<tr><td><span class="table-product"><span class="table-product-icon">${productEmoji(product.category, Number(product.id))}</span><span><b>${escapeHtml(product.name)}</b><small>${escapeHtml(product.category || 'General')}</small></span></span></td><td>${money(product.price)}</td><td class="muted-cell">${money(product.cost)}</td><td><b>${Number(product.stock)}</b> <span class="unit-label">units</span></td><td><span class="status-pill ${low ? 'status-low' : 'status-good'}"><span></span>${low ? 'Low stock' : 'In stock'}</span></td><td><div class="row-actions"><button class="row-action" data-edit-product="${Number(product.id)}">Edit</button><button class="row-action delete-action" data-delete-product="${Number(product.id)}" aria-label="Delete ${escapeHtml(product.name)}">×</button></div></td></tr>`;
    }).join('');
    $('#inventoryEmpty').hidden = filtered.length !== 0;
}

function openProduct(product = null) {
    $('#productForm').reset();
    $('#productId').value = product?.id || '';
    $('#productDialogTitle').textContent = product ? 'Edit product' : 'Add product';
    $('#productName').value = product?.name || '';
    $('#productCategory').value = product?.category || '';
    $('#productPrice').value = product?.price ?? '';
    $('#productCost').value = product?.cost ?? '';
    $('#productStock').value = product?.stock ?? 0;
    $('#productLowStock').value = product?.low_stock ?? 5;
    $('#productDialog').showModal();
}

async function loadReport() {
    const date = $('#reportDate').value || localDate();
    try {
        const report = await request('reports', { date });
        const totals = report.totals;
        $('#reportTotal').textContent = money(totals.total_sales);
        $('#reportSaleCount').textContent = totals.sale_count;
        $('#reportCash').textContent = money(totals.cash_sales);
        $('#reportMpesa').textContent = money(totals.mpesa_sales);
        $('#reportProfit').textContent = money(totals.estimated_profit);
        $('#latestSales').innerHTML = report.latest_sales.length ? report.latest_sales.map(sale => `<div class="recent-sale"><span class="sale-avatar">${escapeHtml(sale.cashier.trim().charAt(0).toUpperCase())}</span><span class="recent-sale-meta"><b>Sale #${Number(sale.id)}</b><small>${escapeHtml(sale.cashier)} · ${new Date(sale.created_at.replace(' ', 'T')).toLocaleTimeString('en-KE', { hour: 'numeric', minute: '2-digit' })}</small></span><span class="recent-sale-method">${sale.payment_method === 'mpesa' ? '<span class="mpesa-mark">M</span> M-Pesa' : sale.payment_method === 'credit' ? 'Credit' : '<span class="cash-mini">K</span> Cash'}</span><b>${money(sale.total)}</b></div>`).join('') : '<div class="list-empty">No sales recorded for this day yet.</div>';
        $('#lowStockList').innerHTML = report.low_stock.length ? report.low_stock.map(product => `<div class="low-stock-row"><span class="low-stock-product">${productEmoji('', Number(product.id))}</span><span><b>${escapeHtml(product.name)}</b><small>Restock when convenient</small></span><span class="stock-remaining">${Number(product.stock)} <small>left</small></span></div>`).join('') : '<div class="list-empty">Everything is looking well stocked.</div>';
    } catch (error) {
        toast(error.message, true);
    }
}

async function loadDebts() {
    try {
        const result = await request('debts');
        $('#debtNavCount').textContent = result.debts.length;
        $('#debtCount').textContent = result.debts.length;
        $('#debtTotal').textContent = money(result.debts.reduce((sum, debt) => sum + Number(debt.balance), 0));
        $('#debtRows').innerHTML = result.debts.map(debt => `<tr><td><span class="table-product"><span class="debt-customer-avatar">${escapeHtml(debt.customer_name.trim().charAt(0).toUpperCase())}</span><b>${escapeHtml(debt.customer_name)}</b></span></td><td class="muted-cell">#${Number(debt.sale_id)}</td><td>${new Date(debt.created_at.replace(' ', 'T')).toLocaleDateString('en-KE', { day: 'numeric', month: 'short', year: 'numeric' })}</td><td>${money(debt.amount_due)}</td><td><b class="balance-amount">${money(debt.balance)}</b></td><td><button class="small-primary" data-pay-debt="${Number(debt.id)}" data-balance="${Number(debt.balance)}" data-customer="${escapeHtml(debt.customer_name)}">Record payment</button></td></tr>`).join('');
        $('#debtEmpty').hidden = result.debts.length !== 0;
    } catch (error) {
        toast(error.message, true);
    }
}

function renderReceipt(receipt) {
    const lines = receipt.items.map(item => `<div class="receipt-line"><span>${escapeHtml(item.name)} × ${Number(item.quantity)}</span><span>${money(item.line_total)}</span></div>`).join('');
    const content = `<div class="receipt-brand"><span class="brand-mark">s<span>.</span></span><b>sokoflow</b></div><p class="receipt-thanks">A little shop with a big heart.</p><div class="receipt-rule"></div><div class="receipt-meta"><span>RECEIPT #${Number(receipt.id)}</span><span>${new Date(receipt.date.replace(' ', 'T')).toLocaleString('en-KE')}</span></div>${receipt.customer ? `<p class="receipt-customer">Customer: ${escapeHtml(receipt.customer)}</p>` : ''}<div class="receipt-lines">${lines}</div><div class="receipt-rule"></div><div class="receipt-total"><span>TOTAL</span><b>${money(receipt.total)}</b></div><div class="receipt-payment"><span>${receipt.payment_method === 'mpesa' ? 'M-Pesa' : receipt.payment_method === 'credit' ? 'Credit sale' : 'Cash'}</span><span>${receipt.payment_method === 'credit' ? `Due: ${money(receipt.total)}` : `Received: ${money(receipt.received)}`}</span></div>${receipt.payment_method !== 'credit' ? `<div class="receipt-payment"><span>Change</span><span>${money(receipt.change)}</span></div>` : ''}<p class="receipt-footer">Served by ${escapeHtml(receipt.cashier)}<br>Thank you for shopping with us!</p>`;
    $('#receiptContent').innerHTML = content;
    $('#receiptDialog').showModal();
}

document.addEventListener('click', async event => {
    const nav = event.target.closest('[data-page]');
    if (nav) navigate(nav.dataset.page);

    const category = event.target.closest('[data-category]');
    if (category) {
        state.category = category.dataset.category;
        renderCatalog();
    }

    const productCard = event.target.closest('[data-add-product]');
    if (productCard) addToCart(Number(productCard.dataset.addProduct));

    const quantity = event.target.closest('[data-quantity]');
    if (quantity) {
        const id = Number(quantity.dataset.quantity);
        const item = state.cart.get(id);
        if (!item) return;
        const next = item.quantity + Number(quantity.dataset.change);
        if (next < 1) state.cart.delete(id);
        else if (next <= Number(item.product.stock)) item.quantity = next;
        else toast('You have reached the available stock for this item.', true);
        renderCart();
        renderCatalog();
    }

    if (event.target.closest('#clearCart')) {
        state.cart.clear();
        renderCart();
        renderCatalog();
    }
    if (event.target.closest('[data-go-products]')) navigate('products');
    if (event.target.closest('#addProduct')) openProduct();

    const edit = event.target.closest('[data-edit-product]');
    if (edit) openProduct(state.products.find(product => Number(product.id) === Number(edit.dataset.editProduct)));

    const remove = event.target.closest('[data-delete-product]');
    if (remove && window.confirm('Delete this product? Sales history is kept, so products already sold cannot be deleted.')) {
        try {
            await request('delete_product', { id: Number(remove.dataset.deleteProduct) });
            toast('Product deleted.');
            await loadProducts();
        } catch (error) {
            toast(error.message, true);
        }
    }

    const payDebt = event.target.closest('[data-pay-debt]');
    if (payDebt) {
        $('#debtId').value = payDebt.dataset.payDebt;
        $('#debtBalanceHint').textContent = `${payDebt.dataset.customer} owes ${money(payDebt.dataset.balance)}`;
        $('#debtAmount').max = payDebt.dataset.balance;
        $('#debtAmount').value = payDebt.dataset.balance;
        $('#debtDialog').showModal();
    }

    const close = event.target.closest('[data-close-dialog]');
    if (close) $(`#${close.dataset.closeDialog}`).close();
    if (event.target.closest('#logoutButton')) {
        try {
            await request('logout');
            state.user = null;
            state.products = [];
            state.cart.clear();
            window.location.reload();
        } catch (error) {
            toast(error.message, true);
        }
    }
});

$('#productForm').addEventListener('submit', async event => {
    event.preventDefault();
    const button = $('button[type="submit"]', event.currentTarget);
    button.disabled = true;
    try {
        await request('save_product', {
            id: $('#productId').value || undefined,
            name: $('#productName').value,
            category: $('#productCategory').value,
            price: $('#productPrice').value,
            cost: $('#productCost').value,
            stock: $('#productStock').value,
            low_stock: $('#productLowStock').value
        });
        $('#productDialog').close();
        toast('Product saved.');
        await loadProducts();
    } catch (error) {
        toast(error.message, true);
    } finally {
        button.disabled = false;
    }
});

$('#debtForm').addEventListener('submit', async event => {
    event.preventDefault();
    const button = $('button[type="submit"]', event.currentTarget);
    button.disabled = true;
    try {
        await request('pay_debt', {
            id: Number($('#debtId').value),
            amount: $('#debtAmount').value,
            method: $('#debtMethod').value
        });
        $('#debtDialog').close();
        toast('Payment recorded.');
        await loadDebts();
    } catch (error) {
        toast(error.message, true);
    } finally {
        button.disabled = false;
    }
});

$('#completeSale').addEventListener('click', async event => {
    if (!state.cart.size) return;
    const button = event.currentTarget;
    button.disabled = true;
    try {
        const result = await request('create_sale', {
            items: [...state.cart.values()].map(item => ({ id: item.product.id, quantity: item.quantity })),
            payment_method: state.paymentMethod,
            amount_received: state.paymentMethod === 'credit' ? '0' : $('#amountReceived').value,
            customer: $('#creditCustomer').value
        });
        state.cart.clear();
        $('#amountReceived').value = '';
        $('#creditCustomer').value = '';
        renderCart();
        await loadProducts();
        await loadDebts();
        renderReceipt(result.receipt);
        toast('Sale completed successfully.');
    } catch (error) {
        toast(error.message, true);
    } finally {
        button.disabled = state.cart.size === 0;
    }
});

$$('.payment-option').forEach(button => button.addEventListener('click', () => {
    state.paymentMethod = button.dataset.method;
    $$('.payment-option').forEach(option => option.classList.toggle('selected', option === button));
    $('#cashFields').hidden = state.paymentMethod === 'credit';
    $('#creditFields').hidden = state.paymentMethod !== 'credit';
    $('#amountReceived').required = state.paymentMethod !== 'credit';
}));

$('#amountReceived').addEventListener('input', updateChange);
$('#productSearch').addEventListener('input', renderCatalog);
$('#inventorySearch').addEventListener('input', renderInventory);
$('#reportDate').value = localDate();
$('#reportDate').addEventListener('change', loadReport);
$('#printReceipt').addEventListener('click', () => window.print());
$('#shareReceipt').addEventListener('click', async () => {
    const text = $('#receiptContent').innerText;
    try {
        if (navigator.share) await navigator.share({ title: 'SokoFlow receipt', text });
        else {
            await navigator.clipboard.writeText(text);
            toast('Receipt copied. You can paste it into a message.');
        }
    } catch (error) {
        if (error.name !== 'AbortError') toast('Unable to share the receipt.', true);
    }
});

document.addEventListener('keydown', event => {
    if (event.key === '/' && !['INPUT', 'TEXTAREA'].includes(document.activeElement.tagName)) {
        event.preventDefault();
        $('#productSearch').focus();
    }
    if (!event.metaKey && !event.ctrlKey && !event.altKey && ['1', '2', '3'].includes(event.key)) {
        const page = { '1': 'sell', '2': 'products', '3': 'reports' }[event.key];
        if (state.user?.role === 'owner' || page === 'sell') navigate(page);
    }
});

(async function initialize() {
    try {
        const result = await request('status');
        window.SOKOFLOW_CSRF = result.csrf_token;
        if (result.user) {
            state.user = result.user;
            showApp();
        } else {
            showAuth(result.setup_required);
        }
    } catch (error) {
        showAuth(false);
        toast(error.message, true);
    }
})();
