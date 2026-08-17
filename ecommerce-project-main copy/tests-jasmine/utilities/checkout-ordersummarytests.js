import { renderOrderSummary } from "../../data/checkout-OrderSummary.js";
import { loadFromStorage } from "../../data/cart.js";
//BACKEND INTRODUCED
//import { loadProducts } from "../../data/products.js";
describe('Test-suite:renderOrderSummary',()=>{
    //tests how the page looks
    it('displays the cart',()=>{

        document.querySelector('.js-test-container').innerHTML=`<div class="js-order-summary"></div>`;
/*beforeAll((done)=>{
    loadProducts();
    done();
    }) 
    
    beforeEach(()=>{ spyOn(localStorage,'setItem');
            spyOn(localStorage,'getItem').and.callFake(()=>{return JSON.stringify(
                    [{productId:"e43638ce-6aa0-4b85-b27f-e1d07eb678c6",
                    quantity:1,
                    deliveryOptionId:'1'
                    },
                    {productId:"15b6fc6f-327a-4ec4-896f-486349e85a3d",
                    quantity:2,
                    deliveryOptionId:'2'
                    }]
    )});
            loadFromStorage();

            renderOrderSummary();
    });
});})*/
            spyOn(localStorage,'setItem');
            spyOn(localStorage,'getItem').and.callFake(()=>{return JSON.stringify(
                    [{productId:"e43638ce-6aa0-4b85-b27f-e1d07eb678c6",
                    quantity:1,
                    deliveryOptionId:'1'
                    },
                    {productId:"15b6fc6f-327a-4ec4-896f-486349e85a3d",
                    quantity:2,
                    deliveryOptionId:'2'
                    }]
    )});
            loadFromStorage();

            renderOrderSummary();
    });
});