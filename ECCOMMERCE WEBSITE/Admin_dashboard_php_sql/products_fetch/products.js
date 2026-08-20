/*fetch("https://pokeapi.co/api/v2/pokemon/pikachu")
.then(response=>response.json())
.then(data=>console.log(data))
.catch(error=>console.error(error))
async function fetchData() {
  try {
    const response = await fetch('https://pokeapi.co/api/v2/pokemon/pikachu');
    const data = await response.json();
    console.log(data);
  } catch (error) {
    console.error("Request failed:", error);
  }
}
fetchData();*/
/*// 1. Wait for the webpage to fully load
document.addEventListener("DOMContentLoaded", async function() {
    try {
        // 1. Fetch your data from the PHP API
        const response = await fetch("products_fetch.php");
        const products = await response.json();
        
        // 2. Target your HTML grid container and clear it
        const container = document.getElementById("products_grid");
        container.innerHTML = ""; 

        // 3. Loop through your products array using a clean mapping layout
        container.innerHTML = products.map(product => {
            const realPrice = (product.price_cents / 100).toFixed(2);
            
            return `
                <div class="product-card">
                    <img src="${product.image_path}" style="width:150px; height:auto;">
                    <h3>${product.product_name}</h3>
                    <p>Price: $${realPrice}</p>
                    <p>Rating: ⭐ ${product.rating_stars}</p>
                </div>
            `;
        }).join(""); // Merges all cards together cleanly into the container

    } catch (error) {
        console.error("Something went wrong:", error);
    }
});*/

