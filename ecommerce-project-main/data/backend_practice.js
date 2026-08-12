//http resquest
const xhr = new XMLHttpRequest();
xhr.addEventListener('load',()=>{xhr.response});
xhr.open('GET','http://127.0.0.1:5500/ecommerce-project-main/checkout.html');
xhr.send();