import { cart,removeFromCart,updateDeliveryOption} from './cart.js';
import { loadProducts,products,convertCentsToDollar } from './products.js';
import { addToCart } from './cart.js';
import dayjs from './dayjs.js';
import { deliveryOptions } from './deliveryOptions.js';
import { renderPaymentSummary,thousandsComma} from './checkout-paymentSummary.js';
loadProducts(displayOnPage)
  .then(() => {
     displayOnPage();
  })
  .catch((err) => {
    console.error("3.  Promise rejected! Error caught in main chain:", err);
  });
 
function displayOnPage(){
function updateCartQuantity(){
                        let cartQuantity=0;
                        cart.forEach((item)=>{
                        cartQuantity+=item.quantity;
                        });
                        document.querySelector('.js-cart-quantity').innerHTML=cartQuantity;
                        }

let productsHTML='';
products.forEach((product) => {
    productsHTML+=`
            <div class="product-container">
                <div class="product-image-container">
                    <img class="product-image"
                    src="../Admin_dashboard_php_sql/product_upload_dashboard/${product.image}" />
                </div>

                <div class="product-name limit-text-to-2-lines">
                    ${product.name}
                </div>

                <div class="product-rating-container">
                    <img class="product-rating-stars"
                    src="images/ratings/rating-${(product.rating)*10}.png" />
                    <div class="product-rating-count link-primary">
                    ${product.rating_count}
                    </div>
                </div>

                <div class="product-price">
                    KES ${thousandsComma(product.priceCents)}
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
`;});

document.querySelector('.js-products-grid').innerHTML=productsHTML;
document.querySelectorAll('.js-add-to-cart').forEach((button)=>
{
    
      
    button.addEventListener('click',() => 
                {
                const productId = button.dataset.productId;
                addToCart(productId);
                updateCartQuantity();})
});
}