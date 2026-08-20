export let cart;
loadFromStorage();
export function loadFromStorage() { 
  cart= JSON.parse(localStorage.getItem('cart'));
        if(!cart){
        cart=[
        ];
        };
};


function saveToStorage(){
  localStorage.setItem('cart',JSON.stringify(cart))
};

export function addToCart(productId){
                        let matchingItem;
                        cart.forEach((item)=>{
                        if(productId===item.productId){
                        matchingItem=item;
                        } });
                        if (matchingItem){
                        matchingItem.quantity++
                        }
                        else{
                            cart.push({
                            productId:productId,
                            quantity:1,
                            deliveryOptionId:'1'
                            });
                        }
                      saveToStorage();
                      };

export function removeFromCart(productId){
  const newCart=[];
  cart.forEach((item)=>{
    if (item.productId!==productId){
      newCart.push(item);} 
  });
cart=newCart;
saveToStorage();
};

export function updateDeliveryOption(productId,deliveryOptionId){
 let matchingItem;
                        cart.forEach((item)=>{
                        if(productId===item.productId){
                        matchingItem=item;
                        } });
                        matchingItem.deliveryOptionId=deliveryOptionId;
  saveToStorage();
};