import { addToCart, getCartQuantity } from './cart.js';
import { loadProducts, products, escapeHtml, formatPrice, productImageUrl } from './products.js';

const grid = document.querySelector('.js-products-grid');
const searchInput = document.querySelector('.search-bar');

function updateCartQuantity() {
  document.querySelectorAll('.js-cart-quantity').forEach((element) => {
    element.textContent = getCartQuantity();
  });
}

function renderProducts(searchTerm = '') {
  const query = searchTerm.trim().toLocaleLowerCase();
  const visibleProducts = products.filter((product) => {
    const searchableText = [
      product.name,
      ...product.keywords
    ].join(' ').toLocaleLowerCase();
    return searchableText.includes(query);
  });

  if (visibleProducts.length === 0) {
    grid.innerHTML = `<p class="products-empty-state">No products found. Try a different search.</p>`;
    return;
  }

  grid.innerHTML = visibleProducts.map((product) => `
    <article class="product-container">
      <div class="product-image-container">
        <img class="product-image" src="${escapeHtml(productImageUrl(product))}" alt="${escapeHtml(product.name)}">
      </div>
      <div class="product-name limit-text-to-2-lines">${escapeHtml(product.name)}</div>
      <div class="product-rating-container">
        <img class="product-rating-stars" src="images/ratings/rating-${Math.min(50, Math.max(0, Math.round(product.rating * 2) * 5))}.png" alt="${product.rating} out of 5 stars">
        <div class="product-rating-count">${product.rating_count.toLocaleString()}</div>
      </div>
      <div class="product-price">${formatPrice(product.priceCents)}</div>
      <label class="product-quantity-container">
        <span class="visually-hidden">Quantity</span>
        <select aria-label="Quantity for ${escapeHtml(product.name)}">
          ${Array.from({ length: 10 }, (_, index) => `<option value="${index + 1}">${index + 1}</option>`).join('')}
        </select>
      </label>
      <div class="product-spacer"></div>
      <div class="added-to-cart js-added-mark" aria-live="polite">
        <img src="images/icons/checkmark.png" alt=""> Added to cart
      </div>
      <button class="add-to-cart-button button-primary js-add-to-cart" data-product-id="${escapeHtml(product.id)}">Add to Cart</button>
    </article>
  `).join('');

  grid.querySelectorAll('.js-add-to-cart').forEach((button) => {
    button.addEventListener('click', () => {
      const container = button.closest('.product-container');
      const quantity = Number(container.querySelector('select').value);
      addToCart(button.dataset.productId, quantity);
      updateCartQuantity();
      const confirmation = container.querySelector('.js-added-mark');
      confirmation.classList.add('is-visible');
      window.setTimeout(() => confirmation.classList.remove('is-visible'), 1600);
    });
  });
}

searchInput.value = new URLSearchParams(window.location.search).get('search') || '';
updateCartQuantity();

loadProducts()
  .then(() => renderProducts(searchInput.value))
  .catch((error) => {
    console.error('Could not load products.', error);
    grid.innerHTML = '<p class="products-empty-state" role="alert">Products are temporarily unavailable. Please check that the product database is running and refresh this page.</p>';
  });
