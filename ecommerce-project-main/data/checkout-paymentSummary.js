import { cart } from './cart.js';
import { products, getProduct } from './products.js';
import { getDeliveryOption } from './deliveryOptions.js';
export function renderPaymentSummary() {
    let productPriceCents = 0;
    let shippingPriceCents=0;
    let totalBeforeTaxation=0;
    let estimatedTax=0;
    let orderTotal=0;

    cart.forEach((item) => {
        const product = getProduct(item.productId);
        
       
        if (product) {
            productPriceCents += (product.priceCents * item.quantity);};
            
            const deliveryOption=getDeliveryOption(item.deliveryOptionId);
            shippingPriceCents+=deliveryOption.priceCents;
            totalBeforeTaxation=productPriceCents+shippingPriceCents;
            estimatedTax=totalBeforeTaxation*0.1;
            orderTotal=totalBeforeTaxation+estimatedTax;

        const paymentSummaryHTML=`
            <div class="payment-summary-title">
              Payment Summary
            </div>

            <div class="payment-summary-row">
              <div>Items (3):</div>
              <div class="payment-summary-money">$ ${convertCentsToDollar(productPriceCents)}</div>
            </div>

            <div class="payment-summary-row">
              <div>Shipping &amp; handling:</div>
            <div class="payment-summary-money">$ ${convertCentsToDollar(shippingPriceCents)}</div>
            </div>

            <div class="payment-summary-row subtotal-row">
              <div>Total before tax:</div>
              <div class="payment-summary-money">$ ${convertCentsToDollar(totalBeforeTaxation)}</div>
            </div>

            <div class="payment-summary-row">
              <div>Estimated tax (10%):</div>
              <div class="payment-summary-money">$ ${convertCentsToDollar(estimatedTax)}</div>
            </div>

            <div class="payment-summary-row total-row">
              <div>Order total:</div>
              <div class="payment-summary-money">$ ${convertCentsToDollar(orderTotal)}</div>
            </div>

            <button class="place-order-button button-primary">
              Place your order
            </button>
        
        
        `;
        document.querySelector('.js-payment-summary').innerHTML=paymentSummaryHTML;


            
    });
    
};
renderPaymentSummary(); 
//function to convert price in cents to dollars
export function convertCentsToDollar(priceCents){
    return(Math.round(priceCents/100)).toFixed(2);

};