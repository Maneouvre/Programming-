//CLASS IN ORIENTED PROGRAMMING-OOP
//this. refers to class-name 
class Cart{
    cartItems;
    localStorageKey;
    //constructor to se up startup code-runs automatically
    constructor(localStorageKey){
                    this.localStorageKey=localStorageKey;
                    //businessCart.localStorageKey='cart-business';
                    //loads the cart stored in local storage after push
                    this.loadFromStorage();
                   // businessCart.loadFromStorage();
                }
    loadFromStorage(){ 
    this.cartItems= JSON.parse(localStorage.getItem(this.localStorageKey));
                    if(!this.cartItems){
                    this.cartItems=[{productId:"e43638ce-6aa0-4b85-b27f-e1d07eb678c6",
                    quantity:1,
                    deliveryOptionId:'1'
                    },
                    {productId:"15b6fc6f-327a-4ec4-896f-486349e85a3d",
                    quantity:2,
                    deliveryOptionId:'2'
                    }
                    ];
                    };}
    saveToStorage(){localStorage.setItem(this.localStorageKey,JSON.stringify(this.cartItems))}
    addToCart(productId){
                        let matchingItem;
                        this.cartItems.forEach((item)=>{
                        if(productId===item.productId){
                        matchingItem=item;
                        } });
                        if (matchingItem){
                        matchingItem.quantity++
                        }
                        else{
                            this.cartItems.push({
                            productId:productId,
                            quantity:1,
                            deliveryOptionId:'1'
                            });
                        }
                      saveToStorage();
                      }
removeFromCart(productId){
                        const newCart=[];
                        this.cartItems.forEach((item)=>{
                        if (item.productId!==productId){
                        newCart.push(item);} 
                        });
                        this.cartItems=newCart;
                        saveToStorage();
                        }
updateDeliveryOption(productId,deliveryOptionId){
                        let matchingItem;
                            this.cartItems.forEach((item)=>{
                            if(productId===item.productId){
                            matchingItem=item;
                            } });
                            matchingItem.deliveryOptionId=deliveryOptionId;
                        this.saveToStorage();
                        }
                        

                    


    };
   // addToCart('3ebe75dc-64d2-4137-8860-1f5a963e534b')


const cart=new Cart('cart-oop');
const businessCart=new Cart('cart-business');
//cart.localStorageKey='cart-oop';
//businessCart.localStorageKey='cart-business';
//loads the cart stored in local storage after push
//cart.loadFromStorage();
//businessCart.loadFromStorage();
console.log(cart);
console.log(businessCart);
