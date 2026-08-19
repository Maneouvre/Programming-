import { cart } from './cart.js';
import { products, getProduct,convertCentsToDollar } from './products.js';
import { getDeliveryOption } from './deliveryOptions.js';



export async function renderPaymentSummary() {
    let productPriceCents = 0;
    let shippingPriceCents = 0;
    let totalBeforeTaxation=0;

    
    for (const item of cart) {
        const product = await getProduct(item.productId);
        console.log(product);
        if (product) {
            productPriceCents += (product.priceCents * item.quantity);
            
            const deliveryOption = getDeliveryOption(item.deliveryOptionId);
            shippingPriceCents += deliveryOption.priceCents;
        }
    }

    // Calculations must happen AFTER all items have been processed
    totalBeforeTaxation = productPriceCents + shippingPriceCents;
    const estimatedTax = totalBeforeTaxation * 0.1;
    const orderTotal = totalBeforeTaxation + estimatedTax;

    // Generate HTML only ONCE with the final total values
    const paymentSummaryHTML = `
        <div class="payment-summary-title">
          Payment Summary
        </div>

        <div class="payment-summary-row">
          <div>Items (${cart.length}):</div>
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

    const element = document.querySelector('.js-payment-summary');
    if (element) {
        element.innerHTML = paymentSummaryHTML;
    }
}
 renderPaymentSummary();
 console.log('This is the local storage',localStorage);