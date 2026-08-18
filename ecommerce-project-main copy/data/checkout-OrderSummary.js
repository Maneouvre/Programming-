import { cart,removeFromCart,updateDeliveryOption} from './cart.js';
import { loadProducts,convertCentsToDollar } from './products.js';
import dayjs from './dayjs.js';
import { deliveryOptions } from './deliveryOptions.js';
import { renderPaymentSummary } from './checkout-paymentSummary.js';

let products = [];

async function getProducts() {
  const productList = await loadProducts();
  console.log("Loaded products successfully:", productList);
  products = productList; 
  renderOrderSummary(products);
}

getProducts();

export function renderOrderSummary(productsList){
  if (!productsList) {
    productsList = products;
  }

  let html='';

  cart.forEach((item)=>{
      const productId=item.productId;
      let matchingProduct;

      productsList.forEach((product)=>{
          if (product.id===productId){
              matchingProduct=product;
          }
      });

      if (!matchingProduct) {
        return;
      }

      const deliveryOptionid= item.deliveryOptionId;
      let deliveryOpt;
      deliveryOptions.forEach((option)=>{
          if(option.id===deliveryOptionid){
              deliveryOpt=option;
          }
      });

      const today=dayjs();
      const deliveryDate=today.add(deliveryOpt.deliveryDays,'days');
      const dateString=deliveryDate.format('dddd, MMMM D');  
          
      html+=`
          <div class="cart-item-container js-cart-item-${matchingProduct.id}">
              <div class="delivery-date">
                Delivery date: ${dateString}
              </div>

              <div class="cart-item-details-grid">
                <img class="product-image"
                  src="http://localhost/Admin_dashboard_php_sql/product_upload_dashboard/${matchingProduct.image}" />

                <div class="cart-item-details">
                  <div class="product-name">
                    ${matchingProduct.name}
                  </div>
                  <div class="product-price">
                    ${matchingProduct.getPrice()}
                  </div>
                  <div class="product-quantity">
                    <span>
                      Quantity: <span class="quantity-label">${item.quantity}</span>
                    </span>
                    <span class="update-quantity-link link-primary">
                      Update
                    </span>
                    <span class="delete-quantity-link link-primary js-delete-link" data-product-id="${matchingProduct.id}">
                      Delete
                    </span>
                  </div>
                </div>

                <div class="delivery-options">
                  <div class="delivery-options-title">
                    Choose a delivery option:
                  </div>
                  ${deliveryOptionsHTML(matchingProduct,item)}
                </div>
                </div>
              </div>
            </div> 
          `
  });
            
  document.querySelector('.js-order-summary').innerHTML = html;

  document.querySelectorAll('.js-delete-link').forEach((link) => {
    link.addEventListener('click', () => {
      const productId = link.dataset.productId;
      removeFromCart(productId);
      const cartItem= document.querySelector(`.js-cart-item-${productId}`);
      if (cartItem) cartItem.remove();
      renderPaymentSummary();
    });
  });

  function deliveryOptionsHTML(matchingProduct,item){
    let html='';
    deliveryOptions.forEach((deliveryOption)=>{
      const today=dayjs();
      const deliveryDate=today.add(deliveryOption.deliveryDays,'days');
      const dateString=deliveryDate.format('dddd, MMMM D');
      const priceString = deliveryOption.deliveryDays === 7  ? 'FREE-' : `$${(deliveryOption.priceCents / 100).toFixed(2)}-`;
      const isChecked = deliveryOption.id === item.deliveryOptionId;

      html+=`
        <div class="delivery-option js-delivery-option" data-product-id="${matchingProduct.id}" data-delivery-option-id="${deliveryOption.id}">
            <input type="radio" ${isChecked ? 'checked' : ''} class="delivery-option-input" name="delivery-option-${matchingProduct.id}" />
              <div>
                <div class="delivery-option-date">
                  ${dateString}
                </div>
                <div class="delivery-option-price">
                  ${priceString} Shipping
                </div>
              </div>
        </div>`
    });
    return html;
  };

  document.querySelectorAll('.js-delivery-option').forEach((element) => {
    element.addEventListener('click',()=>{
      const productId=element.dataset.productId;
      const deliveryOptionId=element.dataset.deliveryOptionId;
      updateDeliveryOption(productId,deliveryOptionId);
      renderOrderSummary();
      renderPaymentSummary();
    });
  });
} 

renderOrderSummary(); 
