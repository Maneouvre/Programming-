import { cart, removeFromCart, updateDeliveryOption } from './cart.js';
import { loadProducts, products } from './products.js';
import dayjs from './dayjs.js';
import { deliveryOptions } from './deliveryOptions.js';
import { renderPaymentSummary, thousandsComma } from './checkout-paymentSummary.js';

// 1. TIMING FIX: Calculate the subtotal ONLY AFTER products have successfully loaded
loadProducts().then(() => {
  calculateSubtotal(); 
  displayOrderSummary();
});

function displayOrderSummary(){
let html='';

cart.forEach((item)=>{
    const productId=item.productId;                
    let matchingProduct;
    
    products.forEach((product)=>{
        if (product.id==productId){
            matchingProduct=product;           
        }
    });
                    
    if (matchingProduct) {
       html+=`
            <div class="flex items-center py-3 space-x-3">
                <div class="h-16 w-16 bg-gray-50 rounded border border-gray-100 flex items-center justify-center p-1 flex-shrink-0">
                    <img class="h-full w-full object-contain rounded"
                    src="../Admin_dashboard_php_sql/product_upload_dashboard/${matchingProduct.image}" />
                </div>
                <div class="flex-1 min-w-0">
                    <h4 class="text-xs font-semibold text-gray-800 truncate">${matchingProduct.name}</h4>
                    <p class="text-xs text-gray-400">Qty: ${item.quantity}</p>
                    <p class="text-xs font-bold text-gray-700 mt-0.5">KES ${thousandsComma(matchingProduct.priceCents)}</p>
                </div>
            </div>
       `;
    }
});
   
document.querySelector('.js-order-summary').innerHTML = html;
};

// Dictionary dataset detailing local logistics pickup nodes 
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

// Global baseTotal tracker
let baseTotal = 0;

// Dynamic multi-tier station dropdown logic listeners
const countySelect = document.getElementById('county-select');
const stationSelect = document.getElementById('station-select');
const stationMeta = document.getElementById('station-meta');
const shippingPriceText = document.getElementById('shipping-price');
const totalPriceText = document.getElementById('total-price');
const basePriceText = document.getElementById('base-price');

// 2. CALCULATE CART ITEMS TOTAL
function calculateSubtotal() {
    baseTotal = 0; // Reset count
    
    if (!cart || !products) {
        console.error("Cart or Products data array is missing!");
        return;
    }

    cart.forEach((item) => {
        let matchingProduct;
        products.forEach((product) => {
            if (product.id.toString() === item.productId.toString()) {
                matchingProduct = product;
            }
        });
        
        if (matchingProduct) {
            baseTotal += matchingProduct.priceCents * item.quantity;
        }
    });

    // Update display right away on load
    updateTotals(0);
}

countySelect.addEventListener('change', (e) => {
    const county = e.target.value;
    stationSelect.innerHTML = '<option value="">-- Choose Station --</option>';
    if(stationMeta) stationMeta.classList.add('hidden');
    
    if (!county) {
        stationSelect.disabled = true;
        stationSelect.className = "w-full px-3 py-2 border border-gray-200 rounded-lg text-sm bg-gray-50 text-gray-400 focus:outline-none";
        updateTotals(0);
        return;
    }

    stationSelect.disabled = false;
    stationSelect.className = "w-full px-3 py-2 border border-gray-200 rounded-lg text-sm bg-white text-gray-800 focus:outline-none focus:ring-2 focus:ring-green-500";

    pickupNodes[county].forEach(station => {
        const opt = document.createElement('option');
        opt.value = station.id;
        opt.dataset.fee = station.fee;
        opt.dataset.time = station.time;
        opt.textContent = `${station.name} (+KES ${thousandsComma(station.fee)})`;
        stationSelect.appendChild(opt);
    });
});

stationSelect.addEventListener('change', (e) => {
    const selectedOpt = stationSelect.options[stationSelect.selectedIndex];
    if (!selectedOpt.value) {
        if(stationMeta) stationMeta.classList.add('hidden');
        updateTotals(0);
        return;
    }

    if(stationMeta) {
        stationMeta.innerHTML = `<strong>Collection Schedule:</strong> ${selectedOpt.dataset.time} <br> <span class="text-green-600 font-semibold">Ready for pickup within 24-48 business hours.</span>`;
        stationMeta.classList.remove('hidden');
    }
    updateTotals(parseInt(selectedOpt.dataset.fee));
});

// 3. DIRECT TOTALS ENGINE (Total Due strictly equals Subtotal cost)
function updateTotals(fee) {
    if (basePriceText) {
        basePriceText.textContent = `KES ${thousandsComma(baseTotal)}`;
    }

    if (shippingPriceText) {
        shippingPriceText.textContent = `KES ${thousandsComma(fee)}`;
    }
    
    // Total Due is set strictly to the cart calculation total with no added shipping fees
    if (totalPriceText) {
        totalPriceText.textContent = `KES ${thousandsComma(baseTotal)}`;
    }
}

// Horizontal Row Payment Interface Tab Controller Toggle Function
function switchTab(event, panelId) {
    const contents = document.querySelectorAll('.tab-content');
    contents.forEach(content => content.classList.remove('active'));

    const buttons = document.querySelectorAll('.tab-btn');
    buttons.forEach(btn => {
        btn.classList.remove('border-green-600', 'text-green-600');
        btn.classList.add('border-transparent', 'text-gray-500', 'hover:text-gray-700');
    });

    document.getElementById(panelId).classList.add('active');
    event.currentTarget.classList.remove('border-transparent', 'text-gray-500', 'hover:text-gray-700');
    event.currentTarget.classList.add('border-green-600', 'text-green-600');
}
