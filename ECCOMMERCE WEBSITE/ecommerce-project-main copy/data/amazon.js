import { addToCart, cart } from './cart.js';
import { loadProducts } from './products.js';

export async function getProducts() {
  const productList = await loadProducts();
  
  // 1. Check the browser URL for any active search parameters
  const urlParams = new URLSearchParams(window.location.search);
  const searchTerm = urlParams.get('search');

  let productsToRender = productList;

  // 2. If a search term exists, filter the list down before showing them
  if (searchTerm) {
    const lowerQuery = searchTerm.toLowerCase().trim();
    
    productsToRender = productList.filter((product) => {
      const matchesName = product.name.toLowerCase().includes(lowerQuery);
      
      const matchesKeywords = product.keywords && product.keywords.some(keyword => 
        keyword.toLowerCase().includes(lowerQuery)
      );

      return matchesName || matchesKeywords;
    });
  }

  // 3. Render the processed products list
  displayOnPage(productsToRender);
}

getProducts();

// ADDED EXPORT: Allows search.js to force a re-render if needed
export function displayOnPage(products) {
  function updateCartQuantity() {
    let cartQuantity = 0;
    cart.forEach((item) => {
      cartQuantity += item.quantity;
    });
    document.querySelector('.js-cart-quantity').innerHTML = cartQuantity;
    console.log(cartQuantity);
    console.log(cart);
  }

  let productsHTML = '';
  products.forEach((product) => {
    productsHTML += `
            <div class="product-container">
                <div class="product-image-container">
                    <img class="product-image"
                    src="../../Admin_dashboard_php_sql/product_upload_dashboard/${product.image}"  onerror="this.src='../product_upload_dashboard/uploads/placeholder.png';"/>
                </div>

                <div class="product-name limit-text-to-2-lines">
                    ${product.name}
                </div>

                <div class="product-rating-container">
                    <img class="product-rating-stars"
                    src="${product.getRatingStars()}" />
                    <div class="product-rating-count link-primary">
                    ${product.rating_count}
                    </div>
                </div>

                <div class="product-price">
                    ${product.getPrice()}
                </div>

                <div class="product-quantity-container">
                    <select>
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4">4</option>
                    <option value="5">5</option>
                    <option value="6">6</option>
                    <option value="7">7</option>
                    <option value="8">8</option>
                    <option value="9">9</option>
                    <option value="10">10</option>
                    </select>
                </div>

                <div class="product-spacer"></div>

                <div class="added-to-cart js-added-mark">
                    <img src="images/icons/checkmark.png" />
                    Added
                </div>

                <button class="add-to-cart-button button-primary js-add-to-cart" data-product-id="${product.id}" >
                    Add to Cart
                </button>
            </div>
    `;
  });

  document.querySelector('.js-products-grid').innerHTML = productsHTML;
  
  document.querySelectorAll('.js-add-to-cart').forEach((button) => {
    button.addEventListener('click', () => {
      const productId = button.dataset.productId;
      addToCart(productId);
      updateCartQuantity();
    });
  });
}
