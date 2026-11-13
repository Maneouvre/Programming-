export function convertCentsToDollar(priceCents) {
  return (Math.round(Number(priceCents)) / 100).toFixed(2);
}

export function escapeHtml(value) {
  return String(value ?? '').replace(/[&<>"']/g, (character) => ({
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#39;'
  })[character]);
}

export function productImageUrl(product) {
  const image = String(product.image ?? '');
  if (/^(https?:|data:|blob:)/i.test(image) || image.startsWith('images/')) {
    return image;
  }
  return `Admin_dashboard_php_sql/product_upload_dashboard/${image.replace(/^\/+/, '')}`;
}

export function formatPrice(price) {
  return `KES ${Number(price).toLocaleString('en-KE', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  })}`;
}

class Product {
  constructor(details) {
    this.id = details.id;
    this.image = details.image_path ?? details.image;
    this.name = details.product_name ?? details.name;
    this.rating = Number(details.rating_stars ?? details.rating?.stars ?? details.rating);
    this.rating_count = Number(details.rating_count ?? details.rating?.count ?? 0);
    this.priceCents = Number(details.price_cents ?? details.priceCents);
    this.keywords = Array.isArray(details.keywords) ? details.keywords : [];
    this.isSoldOut = Boolean(Number(details.is_sold_out ?? details.isSoldOut ?? 0));
  }
}

export let products = [];
let productsPromise;

export async function loadProducts() {
  if (products.length > 0) {
    return products;
  }
  if (!productsPromise) {
    productsPromise = fetch('Admin_dashboard_php_sql/products_fetch/products_fetch.php')
      .then((response) => {
        if (!response.ok) {
          throw new Error(`Product request failed with HTTP ${response.status}.`);
        }
        return response.json();
      })
      .then((data) => {
        if (!Array.isArray(data)) {
          throw new Error(data?.error || 'The product service returned an invalid response.');
        }
        products = data.map((details) => new Product(details));
        return products;
      })
      .catch((error) => {
        productsPromise = undefined;
        throw error;
      });
  }
  return productsPromise;
}

export async function getProduct(productId) {
  if (products.length === 0) {
    await loadProducts();
  }
  return products.find((product) => String(product.id) === String(productId));
}
