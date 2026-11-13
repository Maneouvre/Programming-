import { deliveryOptions } from './deliveryOptions.js';

const CART_STORAGE_KEY = 'cart';

export let cart = [];
loadFromStorage();

export function loadFromStorage() {
  const storedCart = localStorage.getItem(CART_STORAGE_KEY);
  let parsedCart = [];

  if (storedCart) {
    try {
      parsedCart = JSON.parse(storedCart);
    } catch (error) {
      console.error('Unable to read the saved cart.', error);
    }
  }

  cart = Array.isArray(parsedCart)
    ? parsedCart
      .filter((item) => item && item.productId !== undefined && item.productId !== null)
      .map((item) => ({
        productId: String(item.productId),
        quantity: Number.isFinite(Number(item.quantity))
          ? Math.max(1, Math.floor(Number(item.quantity) || 1))
          : 1,
        deliveryOptionId: deliveryOptions.some((option) => option.id === String(item.deliveryOptionId))
          ? String(item.deliveryOptionId)
          : deliveryOptions[0].id
      }))
    : [];
}

function saveToStorage() {
  localStorage.setItem(CART_STORAGE_KEY, JSON.stringify(cart));
}

export function getCartQuantity() {
  return cart.reduce((total, item) => total + item.quantity, 0);
}

export function addToCart(productId, quantity = 1) {
  const id = String(productId);
  const requestedAmount = Number(quantity);
  const amount = Number.isFinite(requestedAmount)
    ? Math.max(1, Math.floor(requestedAmount) || 1)
    : 1;
  const matchingItem = cart.find((item) => item.productId === id);

  if (matchingItem) {
    matchingItem.quantity += amount;
  } else {
    cart.push({
      productId: id,
      quantity: amount,
      deliveryOptionId: deliveryOptions[0].id
    });
  }

  saveToStorage();
}

export function updateQuantity(productId, quantity) {
  const id = String(productId);
  const amount = Math.floor(Number(quantity));
  const matchingItem = cart.find((item) => item.productId === id);

  if (!matchingItem || !Number.isFinite(amount) || amount < 1) {
    return;
  }

  matchingItem.quantity = amount;
  saveToStorage();
}

export function removeFromCart(productId) {
  cart = cart.filter((item) => item.productId !== String(productId));
  saveToStorage();
}

export function updateDeliveryOption(productId, deliveryOptionId) {
  const matchingItem = cart.find((item) => item.productId === String(productId));
  const validOption = deliveryOptions.find((option) => option.id === String(deliveryOptionId));

  if (!matchingItem || !validOption) {
    return;
  }

  matchingItem.deliveryOptionId = validOption.id;
  saveToStorage();
}
