// data/search.js

function performSearch() {
  const searchBar = document.querySelector('.js-search-bar');
  const query = searchBar.value.trim();

  if (query) {
    // Navigates to index.html while pushing the search parameters into the URL
    window.location.href = `index.html?search=${encodeURIComponent(query)}`;
  } else {
    // If empty, redirect back to homepage showing everything
    window.location.href = 'index.html';
  }
}

// Safely attach event listeners once elements exist
const searchButton = document.querySelector('.js-search-button');
const searchBar = document.querySelector('.js-search-bar');

if (searchButton && searchBar) {
  // Listen for click event on looking-glass icon
  searchButton.addEventListener('click', () => {
    performSearch();
  });

  // Listen for user hitting 'Enter' key inside text field
  searchBar.addEventListener('keydown', (event) => {
    if (event.key === 'Enter') {
      performSearch();
    }
  });

  // Keep search text populated inside input box after the page reloads
  const urlParams = new URLSearchParams(window.location.search);
  const currentSearch = urlParams.get('search');
  if (currentSearch) {
    searchBar.value = currentSearch;
  }
}
