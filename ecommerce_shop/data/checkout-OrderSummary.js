import { cart, removeFromCart, updateDeliveryOption, updateQuantity } from './cart.js';
import { loadProducts, products, escapeHtml, formatPrice, productImageUrl } from './products.js';
import dayjs from './dayjs.js';
import { deliveryOptions } from './deliveryOptions.js';
import { renderPaymentSummary } from './checkout-paymentSummary.js';

function deliveryOptionsHTML(product, item) {
  return deliveryOptions.map((option) => {
    const deliveryDate = dayjs().add(option.deliveryDays, 'days').format('dddd, MMMM D');
    const shippingPrice = option.priceCents === 0 ? 'FREE' : formatPrice(option.priceCents);
    return `
      <label class="delivery-option">
        <input type="radio" ${option.id === item.deliveryOptionId ? 'checked' : ''}
          class="delivery-option-input" name="delivery-option-${escapeHtml(product.id)}"
          data-product-id="${escapeHtml(product.id)}" data-delivery-option-id="${option.id}">
        <span>
          <span class="delivery-option-date">${deliveryDate}</span>
          <span class="delivery-option-price">${shippingPrice} Shipping</span>
        </span>
      </label>`;
  }).join('');
}

export function renderOrderSummary() {
  const orderSummary = document.querySelector('.js-order-summary');
  if (!orderSummary) {
    return;
  }

  const unavailableItems = cart
    .filter((item) => !products.some((product) => String(product.id) === item.productId))
    .map((item) => item.productId);
  unavailableItems.forEach(removeFromCart);

  const availableItems = cart
    .map((item) => ({ item, product: products.find((product) => String(product.id) === item.productId) }))
    .filter(({ product }) => product);

  document.querySelectorAll('.js-cart-count').forEach((element) => {
    const count = cart.reduce((total, item) => total + item.quantity, 0);
    element.textContent = `${count} ${count === 1 ? 'item' : 'items'}`;
  });

  if (availableItems.length === 0) {
    orderSummary.innerHTML = `
      <div class="empty-cart-message">
        <p>Your cart is empty.</p>
        <a class="link-primary" href="index.html">Continue shopping</a>
      </div>`;
    renderPaymentSummary();
    return;
  }

  orderSummary.innerHTML = availableItems.map(({ item, product }) => {
    const selectedDelivery = deliveryOptions.find((option) => option.id === item.deliveryOptionId) || deliveryOptions[0];
    const deliveryDate = dayjs().add(selectedDelivery.deliveryDays, 'days').format('dddd, MMMM D');
    return `
      <article class="cart-item-container js-cart-item-${escapeHtml(product.id)}">
        <div class="delivery-date">Delivery date: ${deliveryDate}</div>
        <div class="cart-item-details-grid">
          <img class="product-image" src="${escapeHtml(productImageUrl(product))}" alt="${escapeHtml(product.name)}">
          <div class="cart-item-details">
            <div class="product-name">${escapeHtml(product.name)}</div>
            <div class="product-price">${formatPrice(product.priceCents)}</div>
            <div class="product-quantity">
              <label>Quantity:
                <select class="js-quantity-select" data-product-id="${escapeHtml(product.id)}" aria-label="Quantity for ${escapeHtml(product.name)}">
                  ${Array.from({ length: Math.max(10, Math.min(item.quantity, 99)) }, (_, index) => {
                    const quantity = index + 1;
                    return `<option value="${quantity}" ${quantity === item.quantity ? 'selected' : ''}>${quantity}</option>`;
                  }).join('')}
                </select>
              </label>
              <button type="button" class="delete-quantity-link link-primary js-delete-link" data-product-id="${escapeHtml(product.id)}">Remove</button>
            </div>
          </div>
          <div class="delivery-options">
            <div class="delivery-options-title">Choose a delivery option:</div>
            ${deliveryOptionsHTML(product, item)}
          </div>
        </div>
      </article>`;
  }).join('');

  orderSummary.querySelectorAll('.js-delete-link').forEach((button) => {
    button.addEventListener('click', () => {
      removeFromCart(button.dataset.productId);
      renderOrderSummary();
    });
  });

  orderSummary.querySelectorAll('.js-quantity-select').forEach((select) => {
    select.addEventListener('change', () => {
      updateQuantity(select.dataset.productId, select.value);
      renderOrderSummary();
    });
  });

  orderSummary.querySelectorAll('.delivery-option-input').forEach((input) => {
    input.addEventListener('change', () => {
      updateDeliveryOption(input.dataset.productId, input.dataset.deliveryOptionId);
      renderOrderSummary();
    });
  });

  renderPaymentSummary();
}

if (document.querySelector('.js-order-summary')) {
  loadProducts()
    .then(renderOrderSummary)
    .catch((error) => {
      console.error('Could not load checkout products.', error);
      document.querySelector('.js-order-summary').innerHTML = '<p role="alert">Your cart could not be loaded. Please refresh the page or try again later.</p>';
    });
}
