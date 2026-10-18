import { cart } from './cart.js';
import { products,getProduct} from './products.js';
import { getDeliveryOption } from './deliveryOptions.js';


export async function renderPaymentSummary() {
    let productPriceCents = 0;
    let shippingPriceCents=0;
    let totalBeforeTaxation=0;
    let estimatedTax=0;
    let orderTotal=0;

    for (const item of cart) {
        const product = await getProduct(item.productId);
       
        if (product) {
            productPriceCents += (product.priceCents * item.quantity);
            const deliveryOption=getDeliveryOption(item.deliveryOptionId);
            if (deliveryOption){shippingPriceCents+=deliveryOption.priceCents;}
        }};
        //<!--end of for each loop-->
            totalBeforeTaxation=productPriceCents+shippingPriceCents;
            estimatedTax=Math.round(totalBeforeTaxation*0.1);
            orderTotal=totalBeforeTaxation+estimatedTax;
        

        const paymentSummaryHTML=`
            <div class="payment-summary-title">
              Payment Summary
            </div>

            <div class="payment-summary-row">
              <div>Items (3):</div>
              <div class="payment-summary-money">KES ${thousandsComma(productPriceCents)}</div>
            </div>

            <div class="payment-summary-row">
              <div>Shipping &amp; handling:</div>
            <div class="payment-summary-money">KES ${thousandsComma(shippingPriceCents)}</div>
            </div>

            <div class="payment-summary-row subtotal-row">
              <div>Total before tax:</div>
              <div class="payment-summary-money">KES ${thousandsComma(totalBeforeTaxation)}</div>
            </div>

            <div class="payment-summary-row">
              <div>Estimated tax (10%):</div>
              <div class="payment-summary-money">KES ${thousandsComma(estimatedTax)}</div>
            </div>

            <div class="payment-summary-row total-row">
              <div>Order total:</div>
              <div class="payment-summary-money">KES ${thousandsComma(orderTotal)}</div>
            </div>

            <button onclick="window.location.href='place-order.html'" class="place-order-button button-primary">
              Place your order
            </button>
        
        
        `;
       // document.querySelector('.js-payment-summary').innerHTML=paymentSummaryHTML;

const element = document.querySelector('.js-payment-summary');
if (element) {
    element.innerHTML = paymentSummaryHTML;
}} 
renderPaymentSummary(); 

export function thousandsComma(priceCents){
  return priceCents.toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2})
};