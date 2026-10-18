// 1. DYNAMIC HEADER AND ACCOUNT ICON LAYOUT RENDERER
function updateHeaderAuthStatus() {
    fetch('auth/check-auth.php?action=check')
        .then(response => response.json())
        .then(auth => {
            const labelText = document.getElementById('account-display-text');
            const guestMenu = document.getElementById('guest-menu-items');
            const userMenu = document.getElementById('user-menu-items');

            if (auth.logged_in) {
                if(labelText) labelText.textContent = "Hi, Member";
                if(guestMenu) guestMenu.classList.add('hidden');
                if(userMenu) userMenu.classList.remove('hidden');
            } else {
                if(labelText) labelText.textContent = "Account";
                if(guestMenu) guestMenu.classList.remove('hidden');
                if(userMenu) userMenu.classList.add('hidden');
            }
        })
        .catch(err => console.error("Error confirming header authentication state:", err));
}

// 2. THE CONDITIONAL REDIRECTION VALIDATOR ON INTERACTION
function handlePlaceOrderSecurityRoute() {
    fetch('auth/check-auth.php?action=check')
        .then(response => response.json())
        .then(auth => {
            if (auth.logged_in) {
                console.log("Access Granted. Proceeding to order processing...");
                // Place your M-Pesa STK push call trigger here or route them to place-order.html
                // window.location.href = 'place-order.html';
            } else {
                alert("Authentication required! Please sign in to securely confirm your order.");
                window.location.href = 'signin.html?redirect=checkout';
            }
        })
        .catch(err => {
            console.error("Authentication handshake breakdown:", err);
            alert("Security validation timeout. Please try again.");
        });
}

// 3. EVENT BINDING HOOK LISTENER ASSIGNMENT
document.addEventListener('DOMContentLoaded', () => {
    updateHeaderAuthStatus(); // Run instantly to update navigation layout elements

    const orderSubmitBtn = document.querySelector('.place-order-button');
    if (orderSubmitBtn) {
        orderSubmitBtn.addEventListener('click', (e) => {
            e.preventDefault(); // Intercept default button submissions
            handlePlaceOrderSecurityRoute();
        });
    }
});
