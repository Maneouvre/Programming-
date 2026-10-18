import { escapeHtml, productImageUrl } from './products.js';

const container = document.querySelector('.js-tracking-content');

function renderTracking() {
  const params = new URLSearchParams(window.location.search);
  const orderId = params.get('orderId');
  const productId = params.get('productId');

  let orders;
  try {
    orders = JSON.parse(localStorage.getItem('ecommerceOrders') || '[]');
    if (!Array.isArray(orders)) {
      throw new Error('Saved orders have an invalid format.');
    }
  } catch (error) {
    console.error('Could not read saved tracking information.', error);
    container.innerHTML = '<p role="alert">Tracking information could not be read from this browser. Please check browser storage and refresh.</p>';
    return;
  }

  const order = orders.find((savedOrder) => savedOrder.id === orderId);
  const item = order?.items?.find((orderItem) => String(orderItem.productId) === String(productId)) || order?.items?.[0];

  if (!order || !item) {
    container.innerHTML = `
      <a class="back-to-orders-link link-primary" href="orders.html">View all orders</a>
      <p class="tracking-empty-state">We couldn't find that order on this device. View your saved orders or start shopping.</p>
      <a class="link-primary" href="index.html">Browse products</a>`;
    return;
  }

  const image = productImageUrl(item);
  const stage = order.status === 'Delivered' ? 2 : order.status === 'Shipped' ? 1 : 0;
  const progress = stage === 2 ? '100%' : stage === 1 ? '65%' : '20%';

  container.innerHTML = `
    <a class="back-to-orders-link link-primary" href="orders.html">View all orders</a>
    <div class="delivery-date">Estimated delivery: ${new Date(item.estimatedDeliveryAt).toLocaleDateString()}</div>
    <div class="product-info">${escapeHtml(item.name)}</div>
    <div class="product-info">Quantity: ${Number(item.quantity)}</div>
    <div class="product-info">Order: ${escapeHtml(order.id)}</div>
    <div class="product-info">Status: ${escapeHtml(order.status || 'Order received')} (demo order)</div>
    <img class="product-image" src="${escapeHtml(image)}" alt="${escapeHtml(item.name)}">
    <div class="progress-labels-container">
      <div class="progress-label ${stage === 0 ? 'current-status' : ''}">Preparing</div>
      <div class="progress-label ${stage === 1 ? 'current-status' : ''}">Shipped</div>
      <div class="progress-label ${stage === 2 ? 'current-status' : ''}">Delivered</div>
    </div>
    <div class="progress-bar-container" role="progressbar" aria-label="Package progress" aria-valuemin="0" aria-valuemax="100" aria-valuenow="${parseInt(progress, 10)}">
      <div class="progress-bar" style="width: ${progress}"></div>
    </div>
    <p class="tracking-demo-note">This is a locally saved demo order. Live carrier tracking is not connected.</p>`;
}

renderTracking();
