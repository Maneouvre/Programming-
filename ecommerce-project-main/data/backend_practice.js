//create new http msg to send to backend(request)
const xhr = new XMLHttpRequest();
//asynchronous code solved,load waits for event to come back
//.response gives us the information linked to the backend url
xhr.addEventListener('load',()=>{
    console.log(xhr.response)});
//http set up
xhr.open('GET','http://localhost/forms_php_mysql/form.php');
xhr.send();