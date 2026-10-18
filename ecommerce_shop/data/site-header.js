import { getCartQuantity } from './cart.js';

document.querySelectorAll('.js-cart-quantity').forEach((element) => {
  element.textContent = getCartQuantity();
});

const accountLink = document.querySelector('.js-account-link');
const accountText = document.querySelector('.js-account-text');
if (accountLink && accountText) {
  fetch('auth/check-auth.php?action=check', { credentials: 'same-origin' })
    .then((response) => {
      if (!response.ok) {
        throw new Error(`Authentication check failed with HTTP ${response.status}.`);
      }
      return response.json();
    })
    .then((auth) => {
      if (auth.logged_in) {
        accountText.textContent = auth.username ? `Hi, ${auth.username}` : 'Account';
        accountLink.href = 'auth/logout.php';
        accountLink.setAttribute('aria-label', 'Sign out');
        accountLink.title = 'Sign out';
      } else {
        accountText.textContent = 'Sign in';
        accountLink.href = 'login.php';
        accountLink.setAttribute('aria-label', 'Sign in');
        accountLink.title = 'Sign in';
      }
    })
    .catch((error) => {
      console.error('Could not load the account status.', error);
      accountText.textContent = 'Sign in';
      accountLink.href = 'login.php';
    });
}

document.querySelectorAll('.header .search-button').forEach((button) => {
  const section = button.closest('.middle-section');
  const input = section?.querySelector('.search-bar');
  if (!input) {
    return;
  }

  const submitSearch = () => {
    const query = input.value.trim();
    const target = new URL('index.html', window.location.href);
    if (query) {
      target.searchParams.set('search', query);
    }
    window.location.href = target.toString();
  };

  button.addEventListener('click', submitSearch);
});

document.querySelectorAll('.header .search-bar').forEach((input) => {
  input.addEventListener('keydown', (event) => {
    if (event.key === 'Enter') {
      const query = input.value.trim();
      const target = new URL('index.html', window.location.href);
      if (query) {
        target.searchParams.set('search', query);
      }
      window.location.href = target.toString();
    }
  });
});
