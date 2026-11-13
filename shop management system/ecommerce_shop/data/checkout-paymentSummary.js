import { cart } from './cart.js';
import { getProduct, formatPrice } from './products.js';
import { getDeliveryOption } from './deliveryOptions.js';
import { getPickupSelection, getPickupStation } from './pickupStations.js';

export async function renderPaymentSummary() {
  const element = document.querySelector('.js-payment-summary');
  if (!element) {
    return;
  }

  let productPrice = 0;
  let shippingPrice = 0;
  const pickupSelection = getPickupSelection();
  const pickupStation = pickupSelection
    ? getPickupStation(pickupSelection.countyKey, pickupSelection.stationId)
    : null;
  const pickupFee = pickupStation?.fee || 0;

  for (const item of cart) {
    const product = await getProduct(item.productId);
    if (!product) {
      continue;
    }
    productPrice += product.priceCents * item.quantity;
    shippingPrice += getDeliveryOption(item.deliveryOptionId).priceCents;
  }

  const itemCount = cart.reduce((total, item) => total + item.quantity, 0);
  const subtotal = productPrice + shippingPrice + pickupFee;
  const estimatedTax = Math.round(subtotal * 0.1);
  const total = subtotal + estimatedTax;

  element.innerHTML = `
    <div class="payment-summary-title">Payment Summary</div>
    <div class="payment-summary-row">
      <div>Items (${itemCount}):</div>
      <div class="payment-summary-money">${formatPrice(productPrice)}</div>
    </div>
    <div class="payment-summary-row">
      <div>Shipping &amp; handling:</div>
      <div class="payment-summary-money">${formatPrice(shippingPrice)}</div>
    </div>
    <div class="payment-summary-row">
      <div>Delivery station fee:</div>
      <div class="payment-summary-money">${formatPrice(pickupFee)}</div>
    </div>
    <div class="payment-summary-row subtotal-row">
      <div>Total before tax:</div>
      <div class="payment-summary-money">${formatPrice(subtotal)}</div>
    </div>
    <div class="payment-summary-row">
      <div>Estimated tax (10%):</div>
      <div class="payment-summary-money">${formatPrice(estimatedTax)}</div>
    </div>
    <div class="payment-summary-row total-row">
      <div>Order total:</div>
      <div class="payment-summary-money">${formatPrice(total)}</div>
    </div>
    <button class="place-order-button button-primary" type="button" ${itemCount === 0 || !pickupStation ? 'disabled' : ''}>
      ${pickupStation ? 'Continue to checkout' : 'Select a pickup station to continue'}
    </button>`;

  const checkoutButton = element.querySelector('.place-order-button');
  checkoutButton.addEventListener('click', () => {
    window.location.href = 'place-order.html';
  });
}

export function thousandsComma(price) {
  return Number(price).toLocaleString('en-KE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

renderPaymentSummary().catch((error) => {
  console.error('Could not calculate the payment summary.', error);
});
