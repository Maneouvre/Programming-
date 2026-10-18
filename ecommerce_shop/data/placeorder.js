import { cart, getCartQuantity, removeFromCart } from './cart.js';
import { loadProducts, products, escapeHtml, formatPrice, productImageUrl } from './products.js';
import { deliveryOptions } from './deliveryOptions.js';

const pickupNodes = {
  nairobi: [
    { id: 'nbi-cbd', name: 'Nairobi CBD - Tom Mboya St Hub (G4S)', fee: 200, time: 'Mon-Sat 8AM - 6PM' },
    { id: 'nbi-west', name: 'Westlands - Sarit Centre Agent Desk', fee: 350, time: 'Mon-Sun 9AM - 8PM' }
  ],
  mombasa: [
    { id: 'msa-digo', name: 'Mombasa CBD - Digo Road Agent Node', fee: 400, time: 'Mon-Sat 8AM - 5PM' }
  ],
  kisumu: [
    { id: 'ksm-mega', name: 'Kisumu - Mega Plaza Main Hub (Wells Fargo)', fee: 450, time: 'Mon-Sat 8AM - 5PM' }
  ],
  uasin: [
    { id: 'eld-cbd', name: 'Eldoret CBD - Zion Mall Ground Station', fee: 450, time: 'Mon-Sat 8AM - 6PM' }
  ]
};

const countySelect = document.querySelector('#county-select');
const stationSelect = document.querySelector('#station-select');
const stationMeta = document.querySelector('#station-meta');
const itemsContainer = document.querySelector('.js-order-summary');
const message = document.querySelector('#order-message');
const placeOrderButton = document.querySelector('.js-place-demo-order');
const basePriceText = document.querySelector('#base-price');
const shippingPriceText = document.querySelector('#shipping-price');
const pickupPriceText = document.querySelector('#pickup-price');
const taxPriceText = document.querySelector('#tax-price');
const totalPriceText = document.querySelector('#total-price');
const customerNameInput = document.querySelector('#customer-name');
const customerEmailInput = document.querySelector('#customer-email');
let activePaymentMethod = 'M-Pesa';
let itemsSubtotal = 0;
let deliveryTotal = 0;
let pickupFee = 0;
let authenticated = false;

async function checkAuthentication() {
  const response = await fetch('auth/check-auth.php?action=check', { credentials: 'same-origin' });
  if (!response.ok) {
    throw new Error(`Authentication check failed with HTTP ${response.status}.`);
  }
  return response.json();
}

function redirectToSignIn() {
  window.location.replace('login.php?return_to=place-order.html');
}

function setMessage(text, isError = false) {
  message.textContent = text;
  message.classList.toggle('text-red-700', isError);
  message.classList.toggle('text-green-700', !isError);
}

function renderItems() {
  itemsSubtotal = 0;
  deliveryTotal = 0;
  if (cart.length === 0) {
    itemsContainer.innerHTML = '<p>Your cart is empty. <a class="text-green-700 underline" href="index.html">Shop now</a>.</p>';
    placeOrderButton.disabled = true;
    updateTotals();
    return;
  }

  const cartLines = cart.map((item) => {
    const product = products.find((candidate) => String(candidate.id) === item.productId);
    if (!product) {
      return '';
    }
    const option = deliveryOptions.find((candidate) => candidate.id === item.deliveryOptionId) || deliveryOptions[0];
    itemsSubtotal += product.priceCents * item.quantity;
    deliveryTotal += option.priceCents;
    return `
      <div class="flex items-center py-3 space-x-3">
        <div class="h-16 w-16 bg-gray-50 rounded border border-gray-100 flex items-center justify-center p-1 flex-shrink-0">
          <img class="h-full w-full object-contain rounded" src="${escapeHtml(productImageUrl(product))}" alt="${escapeHtml(product.name)}">
        </div>
        <div class="flex-1 min-w-0">
          <h4 class="text-xs font-semibold text-gray-800">${escapeHtml(product.name)}</h4>
          <p class="text-xs text-gray-500">Quantity: ${item.quantity}</p>
          <p class="text-xs font-bold text-gray-700 mt-0.5">${formatPrice(product.priceCents * item.quantity)}</p>
        </div>
      </div>`;
  }).filter(Boolean);

  if (cartLines.length === 0) {
    itemsContainer.innerHTML = '<p role="alert">Products in your cart are no longer available. Please return to the shop.</p>';
    placeOrderButton.disabled = true;
    updateTotals();
    return;
  }
  if (cartLines.length !== cart.length) {
    itemsContainer.innerHTML = '<p role="alert">Some products in your cart are no longer available. Please review your cart before continuing.</p>';
    placeOrderButton.disabled = true;
    updateTotals();
    return;
  }

  itemsContainer.innerHTML = cartLines.join('');
  placeOrderButton.disabled = !authenticated;
  updateTotals();
}

function updateTotals() {
  const beforeTax = itemsSubtotal + deliveryTotal + pickupFee;
  const tax = Math.round(beforeTax * 0.1);
  basePriceText.textContent = formatPrice(itemsSubtotal);
  shippingPriceText.textContent = formatPrice(deliveryTotal);
  pickupPriceText.textContent = formatPrice(pickupFee);
  taxPriceText.textContent = formatPrice(tax);
  totalPriceText.textContent = formatPrice(beforeTax + tax);
}

countySelect.addEventListener('change', () => {
  const stations = pickupNodes[countySelect.value] || [];
  stationSelect.replaceChildren(new Option('-- Choose Station --', ''));
  stationMeta.classList.add('hidden');
  pickupFee = 0;
  stations.forEach((station) => {
    stationSelect.add(new Option(`${station.name} (+${formatPrice(station.fee)})`, station.id));
  });
  stationSelect.disabled = stations.length === 0;
  updateTotals();
});

stationSelect.addEventListener('change', () => {
  const countyStations = pickupNodes[countySelect.value] || [];
  const station = countyStations.find((candidate) => candidate.id === stationSelect.value);
  pickupFee = station?.fee || 0;
  if (station) {
    stationMeta.textContent = `Collection schedule: ${station.time}. Ready for pickup within 24-48 business hours.`;
    stationMeta.classList.remove('hidden');
  } else {
    stationMeta.classList.add('hidden');
  }
  updateTotals();
});

document.querySelectorAll('.tab-btn[data-panel]').forEach((button) => {
  button.addEventListener('click', () => {
    document.querySelectorAll('.tab-btn[data-panel]').forEach((tab) => {
      tab.classList.remove('border-green-600', 'text-green-600');
      tab.classList.add('border-transparent', 'text-gray-500');
      tab.setAttribute('aria-selected', 'false');
    });
    document.querySelectorAll('.tab-content').forEach((panel) => panel.classList.remove('active'));
    button.classList.add('border-green-600', 'text-green-600');
    button.classList.remove('border-transparent', 'text-gray-500');
    button.setAttribute('aria-selected', 'true');
    document.querySelector(`#${button.dataset.panel}`).classList.add('active');
    activePaymentMethod = button.dataset.paymentMethod;
  });
});

placeOrderButton.addEventListener('click', async () => {
  let auth;
  try {
    auth = await checkAuthentication();
  } catch (error) {
    console.error('Could not verify your sign-in before checkout.', error);
    setMessage('We could not verify your sign-in. Refresh the page or try again.', true);
    return;
  }
  if (!auth.logged_in) {
    redirectToSignIn();
    return;
  }

  if (!customerNameInput.reportValidity() || !customerEmailInput.reportValidity()) {
    return;
  }
  if (!countySelect.value || !stationSelect.value) {
    setMessage('Please choose your county and pickup station.', true);
    countySelect.focus();
    return;
  }
  if (getCartQuantity() === 0 || !cart.length) {
    setMessage('Your cart is empty. Add products before checkout.', true);
    return;
  }

  const originalButtonText = placeOrderButton.textContent;
  placeOrderButton.disabled = true;
  placeOrderButton.textContent = 'Submitting order…';
  try {
    const tokenResponse = await fetch('api/orders.php', { credentials: 'same-origin' });
    const tokenPayload = await tokenResponse.json();
    if (!tokenResponse.ok) {
      throw new Error(tokenPayload.error || 'Could not start the secure order request.');
    }
    const response = await fetch('api/orders.php', {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        csrf_token: tokenPayload.csrf_token,
        customer: { name: customerNameInput.value.trim() },
        pickup: { countyKey: countySelect.value, stationId: stationSelect.value },
        paymentMethod: activePaymentMethod,
        items: cart.map((item) => ({
          productId: item.productId,
          quantity: item.quantity,
          deliveryOptionId: item.deliveryOptionId
        }))
      })
    });
    const payload = await response.json();
    if (!response.ok) {
      throw new Error(payload.error || 'The order could not be submitted.');
    }
    const order = payload.order;

    cart.slice().forEach((item) => removeFromCart(item.productId));
    pickupFee = 0;
    countySelect.value = '';
    stationSelect.replaceChildren(new Option('Select a county first', ''));
    stationSelect.disabled = true;
    stationMeta.classList.add('hidden');
    placeOrderButton.disabled = true;
    message.classList.remove('text-red-700');
    message.classList.add('text-green-700');
    message.innerHTML = `Order <strong>${escapeHtml(order.id)}</strong> received. No payment was processed. <a class="underline font-semibold" href="orders.html">View your orders</a>.`;
    renderItems();
  } catch (error) {
    console.error('Could not submit the order.', error);
    setMessage(error.message || 'We could not submit your order. Please try again.', true);
    placeOrderButton.disabled = false;
  } finally {
    placeOrderButton.textContent = originalButtonText;
  }
});

Promise.all([loadProducts(), checkAuthentication()])
  .then(([, auth]) => {
    if (!auth.logged_in) {
      redirectToSignIn();
      return;
    }
    authenticated = true;
    customerEmailInput.value = auth.email || '';
    customerEmailInput.readOnly = true;
    renderItems();
  })
  .catch((error) => {
    console.error('Could not initialize the authenticated checkout.', error);
    itemsContainer.innerHTML = '<p role="alert">Checkout could not load your products or verify your sign-in. Please refresh and try again.</p>';
    placeOrderButton.disabled = true;
    setMessage('Checkout is currently unavailable. Please try again.', true);
  });
