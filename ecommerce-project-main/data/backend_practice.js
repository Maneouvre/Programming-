<<<<<<< HEAD
//http resquest
const xhr = new XMLHttpRequest();
xhr.addEventListener('load',()=>{xhr.response});
xhr.open('GET','http://127.0.0.1:5500/ecommerce-project-main/checkout.html');
=======
//create new http msg to send to backend(request)
const xhr = new XMLHttpRequest();
//asynchronous code solved,load waits for event to come back
//.response gives us the information linked to the backend url
xhr.addEventListener('load',()=>{
    console.log(xhr.response)});
//http set up
xhr.open('GET','http://localhost/forms_php_mysql/form.php');
>>>>>>> 576101d2466c9927d7de9ada1803733e8be45433
xhr.send();