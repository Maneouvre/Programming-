import { addToCart, getCartQuantity } from './cart.js';
import { escapeHtml, formatPrice, productImageUrl } from './products.js';

const ORDER_STORAGE_KEY = 'ecommerceOrders';
const ordersContainer = document.querySelector('.js-orders-grid');

function getOrders() {
  try {
    const orders = JSON.parse(localStorage.getItem(ORDER_STORAGE_KEY) || '[]');
    return Array.isArray(orders) ? orders : null;
  } catch (error) {
    console.error('Could not read saved orders.', error);
    return null;
  }
}

function renderOrders() {
  const orders = getOrders();
  if (!orders) {
    ordersContainer.innerHTML = '<p role="alert">Saved orders could not be read from this browser. Please check browser storage and refresh.</p>';
    return;
  }
  if (orders.length === 0) {
    ordersContainer.innerHTML = `
      <div class="empty-orders-message">
        <p>You haven't placed any orders yet.</p>
        <a class="link-primary" href="index.html">Browse products</a>
      </div>`;
    return;
  }

  ordersContainer.innerHTML = orders.slice().reverse().map((order) => `
    <article class="order-container">
      <div class="order-header">
        <div class="order-header-left-section">
          <div class="order-date"><div class="order-header-label">Order placed:</div><div>${new Date(order.createdAt).toLocaleDateString()}</div></div>
          <div class="order-total"><div class="order-header-label">Total:</div><div>${formatPrice(order.total)}</div></div>
        </div>
        <div class="order-header-right-section">
          <div class="order-header-label">Order ID:</div>
          <div class="order-id">${escapeHtml(order.id)}</div>
        </div>
      </div>
      <div class="order-details-grid">
        ${(order.items || []).map((item) => `
          <div class="product-image-container">
            <img src="${escapeHtml(productImageUrl(item))}" alt="${escapeHtml(item.name)}">
          </div>
          <div class="product-details">
            <div class="product-name">${escapeHtml(item.name)}</div>
            <div class="product-delivery-date">Delivery estimate: ${new Date(item.estimatedDeliveryAt).toLocaleDateString()}</div>
            <div class="product-quantity">Quantity: ${Number(item.quantity)}</div>
            <button class="buy-again-button button-primary js-buy-again" data-product-id="${escapeHtml(item.productId)}" type="button">
              <img class="buy-again-icon" src="images/icons/buy-again.png" alt="">
              <span>Add to Cart</span>
            </button>
          </div>
          <div class="product-actions">
            <a class="track-package-button button-secondary" href="tracking.html?orderId=${encodeURIComponent(order.id)}&amp;productId=${encodeURIComponent(item.productId)}">Track package</a>
          </div>`).join('')}
      </div>
      <p class="order-payment-status">Status: ${escapeHtml(order.status || 'Order received')} · Demo order stored in this browser; payment and carrier delivery are not connected.</p>
    </article>`).join('');

  ordersContainer.querySelectorAll('.js-buy-again').forEach((button) => {
    button.addEventListener('click', () => {
      addToCart(button.dataset.productId);
      document.querySelectorAll('.js-cart-quantity').forEach((element) => {
        element.textContent = getCartQuantity();
      });
      button.querySelector('span').textContent = 'Added';
      window.setTimeout(() => {
        button.querySelector('span').textContent = 'Add to Cart';
      }, 1400);
    });
  });
}

renderOrders();
