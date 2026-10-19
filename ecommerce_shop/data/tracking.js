import { escapeHtml, productImageUrl } from './products.js';

const container = document.querySelector('.js-tracking-content');
const stages = ['Order received', 'Processing', 'Shipped', 'Being delivered', 'Delivered'];
let isRefreshing = false;

async function fetchOrders() {
  const response = await fetch('api/orders.php', {
    credentials: 'same-origin',
    cache: 'no-store'
  });
  if (response.status === 401) {
    window.location.replace('login.php?return_to=orders.html');
    return null;
  }
  const payload = await response.json();
  if (!response.ok) {
    throw new Error(payload.error || `Tracking request failed with HTTP ${response.status}.`);
  }
  return payload.orders;
}

function updateTrackingStatus(status) {
  const isCancelled = status === 'Cancelled';
  const stage = isCancelled ? -1 : Math.max(0, stages.indexOf(status));
  const progress = container.querySelector('.tracking-steps');

  container.querySelector('.tracking-status').textContent = `Status: ${status}`;
  progress.setAttribute('aria-label', isCancelled ? 'Order cancelled' : 'Order progress');
  progress.style.setProperty('--progress-width', `${Math.max(0, stage) * 18.75}%`);
  progress.querySelectorAll('.tracking-step').forEach((step, index) => {
    step.classList.toggle('is-complete', !isCancelled && index < stage);
    step.classList.toggle('is-current', !isCancelled && index === stage);
    if (!isCancelled && index === stage) {
      step.setAttribute('aria-current', 'step');
    } else {
      step.removeAttribute('aria-current');
    }
  });
  container.querySelector('.tracking-cancelled').hidden = !isCancelled;
}

async function renderTracking() {
  const params = new URLSearchParams(window.location.search);
  const orderId = params.get('orderId');
  const productId = params.get('productId');

  try {
    const orders = await fetchOrders();
    if (!orders) {
      return;
    }
    const order = orders.find((savedOrder) => savedOrder.id === orderId);
    const item = order?.items?.find((orderItem) => String(orderItem.productId) === String(productId)) || order?.items?.[0];

    if (!order || !item) {
      container.innerHTML = `
        <a class="back-to-orders-link link-primary" href="orders.html">View all orders</a>
        <p class="tracking-empty-state">We couldn't find that order in your account. View your orders or start shopping.</p>
        <a class="link-primary" href="index.html">Browse products</a>`;
      return;
    }

    const image = productImageUrl(item);
    const status = order.status || 'Order received';
    const steps = stages.map((label, index) => `
      <li class="tracking-step">
        <span class="tracking-step-marker" aria-hidden="true"></span>
        <span class="tracking-step-label">${label}</span>
      </li>`).join('');

    container.innerHTML = `
      <a class="back-to-orders-link link-primary" href="orders.html">View all orders</a>
      <div class="delivery-date">Estimated delivery: ${new Date(item.estimatedDeliveryAt).toLocaleDateString()}</div>
      <div class="product-info">${escapeHtml(item.name)}</div>
      <div class="product-info">Quantity: ${Number(item.quantity)}</div>
      <div class="product-info">Order: ${escapeHtml(order.id)}</div>
      <div class="product-info tracking-status" aria-live="polite"></div>
      <img class="product-image" src="${escapeHtml(image)}" alt="${escapeHtml(item.name)}">
      <ol class="tracking-steps" aria-label="Order progress">
        ${steps}
      </ol>
      <p class="tracking-cancelled" hidden>This order was cancelled.</p>
      <p class="tracking-refresh-error" role="status" hidden></p>
      <p class="tracking-demo-note">Order status is updated by the store administrator. Live carrier tracking is not connected.</p>`;
    updateTrackingStatus(status);

    window.setInterval(async () => {
      if (document.hidden || isRefreshing) {
        return;
      }
      isRefreshing = true;
      try {
        const latestOrders = await fetchOrders();
        if (!latestOrders) {
          return;
        }
        const latestOrder = latestOrders.find((savedOrder) => savedOrder.id === orderId);
        if (latestOrder) {
          updateTrackingStatus(latestOrder.status || 'Order received');
        }
        container.querySelector('.tracking-refresh-error').hidden = true;
      } catch (error) {
        console.error('Could not refresh order tracking.', error);
        const refreshError = container.querySelector('.tracking-refresh-error');
        refreshError.textContent = 'Could not refresh the order status. Showing the last available update.';
        refreshError.hidden = false;
      } finally {
        isRefreshing = false;
      }
    }, 10000);
  } catch (error) {
    console.error('Could not load order tracking.', error);
    container.innerHTML = `<p role="alert">${escapeHtml(error.message || 'Tracking information is temporarily unavailable. Please refresh and try again.')}</p>`;
  }
}

renderTracking();
