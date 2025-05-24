let cart = JSON.parse(localStorage.getItem("cart")) || [];
updateCart();
let currentUserId = null;
let slideIndex = 1;
showSlides(slideIndex);

// Fetch current user info on page load
fetch('backend/check_session.php')
    .then(response => response.json())
    .then(data => {
        if (data.loggedin) {
            currentUserId = data.user_id;
            // Optionally, display user name somewhere
        } else {
            // Optionally, redirect to login or show a message
        }
    });

function addToCart(productName, price, productId) {
    // Use backend for persistent cart
    fetch('backend/add_to_cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'product_id=' + encodeURIComponent(productId)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadCart();
        } else {
            alert('Error adding to cart: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        alert('Error adding to cart: ' + error);
    });
}

function updateCart() {
    let cartItems = document.getElementById("cart-items");
    cartItems.innerHTML = ""; 
    let total = 0;

    cart.forEach((item, index) => {
        total += item.price * item.quantity;

        let li = document.createElement("div");
        li.classList.add("cart-item");
        li.innerHTML = `
            <span>${item.product} - ₱${item.price} (${item.quantity}x)</span>
            <div class="quantity-controls">
                <button onclick="decreaseQuantity(${index})">-</button>
                <span>${item.quantity}</span>
                <button onclick="increaseQuantity(${index})">+</button>
            </div>
            <button class="remove-btn" onclick="removeFromCart(${index})">Remove</button>
        `;
        cartItems.appendChild(li);
    });
    document.getElementById("checkout-total").textContent = total.toFixed(2);
    localStorage.setItem("cart", JSON.stringify(cart));
}

function increaseQuantity(index) {
    cart[index].quantity++;
    updateCart();
}

function decreaseQuantity(index) {
    if (cart[index].quantity > 1) {
        cart[index].quantity--;
    } else {
        cart.splice(index, 1);
    }
    updateCart();
}

function removeFromCart(productId) {
    fetch('backend/remove_from_cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'product_id=' + encodeURIComponent(productId)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadCart();
        } else {
            alert('Error removing from cart: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        alert('Error removing from cart: ' + error);
    });
}

function addProduct() {
    const name = document.getElementById('sell-name').value;
    const price = document.getElementById('sell-price').value;
    const image = document.getElementById('sell-image').files[0];

    if (!name || !price) {
        alert('Please fill in all required fields');
        return;
    }

    const formData = new FormData();
    formData.append('name', name);
    formData.append('price', price);
    if (image) {
        formData.append('image', image);
    }

    fetch('backend/add_product.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Product added successfully!');
            // Clear form
            document.getElementById('sell-name').value = '';
            document.getElementById('sell-price').value = '';
            document.getElementById('sell-image').value = '';
            // Refresh product lists
            loadUserProducts();
            loadAllProducts(); // Refresh main product list
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while adding the product');
    });
}

function loadUserProducts() {
    fetch('backend/get_user_products.php')
        .then(response => response.json())
        .then(data => {
            const userProductsDiv = document.getElementById('user-products');
            userProductsDiv.innerHTML = '';
            
            if (data.success && data.products.length > 0) {
                data.products.forEach(product => {
                    const productDiv = document.createElement('div');
                    productDiv.className = 'product';
                    productDiv.setAttribute('data-product-id', product.id);
                    productDiv.innerHTML = `
                        <img src="${product.image_path || 'placeholder.jpg'}" alt="${product.name}">
                        <div class="product-info">
                            <h3>${product.name}</h3>
                            <p>Price: ₱${product.price}</p>
                            <div class="product-actions">
                                <button onclick="editProduct(${product.id}, '${product.name}', ${product.price})" class="edit-btn">Edit</button>
                                <button onclick="deleteProduct(${product.id})" class="delete-btn">Delete</button>
                            </div>
                        </div>
                    `;
                    userProductsDiv.appendChild(productDiv);
                });
            } else {
                userProductsDiv.innerHTML = '<p>No products listed yet.</p>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('user-products').innerHTML = '<p>Error loading products</p>';
        });
}

function searchProducts() {
    let query = document.getElementById("search-bar").value.toLowerCase();
    let products = document.querySelectorAll(".product");

    products.forEach(product => {
        let name = product.querySelector("h3").textContent.toLowerCase();
        product.style.display = name.includes(query) ? "block" : "none";
    });
}

function showTab(tabId) {
    document.querySelectorAll(".tab-content").forEach(tab => tab.classList.add("hidden"));
    document.getElementById(tabId).classList.remove("hidden");

    document.querySelectorAll(".tab").forEach(btn => btn.classList.remove("active"));
    document.querySelector(`[onclick="showTab('${tabId}')"]`).classList.add("active");

    if (tabId === 'user-listings') {
        loadUserProducts();
    }
}

// Function to validate Philippine phone number
function validatePhilippinePhone(phone) {
    const phoneRegex = /^09\d{9}$/;  // Format: 09 followed by 9 digits
    return phoneRegex.test(phone);
}

// Add input event listener to phone field for real-time validation
document.getElementById("phone").addEventListener("input", function(e) {
    const phone = e.target.value.trim();
    if (phone && !validatePhilippinePhone(phone)) {
        this.setCustomValidity("Please enter a valid phone number starting with '09' followed by 9 digits");
    } else {
        this.setCustomValidity("");
    }
});

document.getElementById("checkout-form").addEventListener("submit", function(event) {
    event.preventDefault();

    let phone = document.getElementById("phone").value.trim();
    let email = document.getElementById("email").value.trim();
    let campus = document.getElementById("campus").value;
    let totalAmount = document.getElementById("checkout-total").textContent;

    if (phone === "" || email === "" || campus === "") {
        alert("Please fill in all details.");
        return;
    }

    // Validate phone number format
    if (!validatePhilippinePhone(phone)) {
        alert("Please enter a valid Philippine phone number starting with '09' followed by 9 digits");
        return;
    }

    // Proceed with order if validation passes
    placeOrder();
    document.getElementById("checkout-form").reset();
});

// Fetch and display products from backend
function loadAllProducts() {
    fetch('backend/get_products.php')
        .then(response => response.json())
        .then(products => {
            console.log('Fetched products:', products); // DEBUG LOG
            const productList = document.getElementById('product-list');
            productList.innerHTML = '';
            if (products.length === 0) {
                productList.innerHTML = '<p>No products available.</p>';
                return;
            }
            products.forEach(product => {
                const productDiv = document.createElement('div');
                productDiv.className = 'product';
                productDiv.innerHTML = `
                    <img src="${product.image_path ? product.image_path : 'book1.jpg'}" alt="${product.name}" onerror="this.onerror=null;this.src='book1.jpg';">
                    <h3>${product.name}</h3>
                    <p>Price: ₱${product.price}</p>
                    <button onclick="addToCart('${product.name}', ${product.price}, ${product.id})">Add to Cart</button>
                `;
                productList.appendChild(productDiv);
            });
        })
        .catch(error => {
            console.error('Error loading products:', error);
            document.getElementById('product-list').innerHTML = '<p>Error loading products: ' + error + '</p>';
        });
}

// Call loadAllProducts on page load
window.addEventListener('DOMContentLoaded', function() {
    loadAllProducts();
    loadUserProducts(); // Always load user products on page load
    loadCart();
    loadOrders();
});

function deleteProduct(id) {
    console.log('deleteProduct called with id:', id);
    if (!confirm('Are you sure you want to delete this product?')) return;
    fetch('backend/delete_product.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id=' + encodeURIComponent(id)
    })
    .then(response => response.json())
    .then(data => {
        console.log('delete_product.php response:', data);
        if (data.success) {
            loadUserProducts();
            loadAllProducts();
        } else {
            alert('Error deleting product: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        alert('Error deleting product: ' + error);
    });
}

function loadCart() {
    fetch('backend/get_cart.php')
        .then(response => response.json())
        .then(data => {
            const cartItems = document.getElementById('cart-items');
            cartItems.innerHTML = '';
            let total = 0;
            if (data.success && data.cart.length > 0) {
                data.cart.forEach(item => {
                    total += item.price * item.quantity;
                    let li = document.createElement('div');
                    li.classList.add('cart-item');
                    li.innerHTML = `
                        <span><img src="${item.image_path || 'book1.jpg'}" alt="${item.name}" style="width:30px;height:30px;vertical-align:middle;"> ${item.name} - ₱${item.price} (${item.quantity}x)</span>
                        <button class="remove-btn" onclick="removeFromCart(${item.product_id})">Remove</button>
                    `;
                    cartItems.appendChild(li);
                });
                // Update checkout summary
                document.getElementById('subtotal').textContent = total.toFixed(2);
                document.getElementById('checkout-total').textContent = total.toFixed(2);
            } else {
                cartItems.innerHTML = '<p>Your cart is empty.</p>';
                // Reset checkout summary when cart is empty
                document.getElementById('subtotal').textContent = '0.00';
                document.getElementById('checkout-total').textContent = '0.00';
            }
        })
        .catch(error => {
            document.getElementById('cart-items').innerHTML = '<p>Error loading cart</p>';
            // Reset checkout summary on error
            document.getElementById('subtotal').textContent = '0.00';
            document.getElementById('checkout-total').textContent = '0.00';
        });
}

function placeOrder() {
    fetch('backend/place_order.php', {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Order placed successfully!');
            loadCart();
            loadOrders();
        } else {
            alert('Error placing order: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        alert('Error placing order: ' + error);
    });
}

function loadOrders() {
    fetch('backend/get_orders.php')
        .then(response => response.json())
        .then(data => {
            const ordersDiv = document.getElementById('order-history');
            ordersDiv.innerHTML = '';
            if (data.success && data.orders.length > 0) {
                data.orders.forEach(order => {
                    let orderDiv = document.createElement('div');
                    orderDiv.className = 'order';
                    let itemsHtml = order.items.map(item => `
                        <div class="order-item">
                            <img src="${item.image_path || 'book1.jpg'}" alt="${item.name}" style="width:30px;height:30px;vertical-align:middle;"> ${item.name} x${item.quantity} (₱${item.price})
                        </div>
                    `).join('');
                    orderDiv.innerHTML = `
                        <h4>Order #${order.id} - ${order.status}</h4>
                        <div>${itemsHtml}</div>
                        <div>Total: ₱${order.total}</div>
                        <div>Placed: ${order.created_at}</div>
                        ${order.status !== 'Cancelled' ? `<button onclick="cancelOrder(${order.id})">Cancel Order</button>` : ''}
                        <hr>
                    `;
                    ordersDiv.appendChild(orderDiv);
                });
            } else {
                ordersDiv.innerHTML = '<p>No orders yet.</p>';
            }
        })
        .catch(error => {
            document.getElementById('order-history').innerHTML = '<p>Error loading orders</p>';
        });
}

function cancelOrder(orderId) {
    fetch('backend/cancel_order.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'order_id=' + encodeURIComponent(orderId)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadOrders();
        } else {
            alert('Error cancelling order: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        alert('Error cancelling order: ' + error);
    });
}

// Function to open edit mode for a product
function editProduct(productId, currentName, currentPrice) {
    const productDiv = document.querySelector(`[data-product-id="${productId}"]`);
    const editForm = document.createElement('div');
    editForm.className = 'edit-form';
    editForm.innerHTML = `
        <input type="text" class="edit-name" value="${currentName}" maxlength="40" placeholder="Product Name">
        <input type="number" class="edit-price" value="${currentPrice}" min="0" max="999999" placeholder="Price">
        <div class="edit-buttons">
            <button onclick="saveProductChanges(${productId})" class="save-btn">Save</button>
            <button onclick="cancelEdit(${productId})" class="cancel-btn">Cancel</button>
        </div>
    `;
    productDiv.querySelector('.product-info').style.display = 'none';
    productDiv.appendChild(editForm);
}

// Function to save product changes
function saveProductChanges(productId) {
    const productDiv = document.querySelector(`[data-product-id="${productId}"]`);
    const newName = productDiv.querySelector('.edit-name').value.trim();
    const newPrice = productDiv.querySelector('.edit-price').value;

    if (!newName || !newPrice) {
        alert('Please fill in all required fields');
        return;
    }

    const formData = new FormData();
    formData.append('id', productId);
    formData.append('name', newName);
    formData.append('price', newPrice);

    fetch('backend/update_product.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadUserProducts(); // Refresh the product list
        } else {
            alert('Error updating product: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating the product');
    });
}

// Function to cancel edit mode
function cancelEdit(productId) {
    const productDiv = document.querySelector(`[data-product-id="${productId}"]`);
    productDiv.querySelector('.edit-form').remove();
    productDiv.querySelector('.product-info').style.display = 'block';
}

function plusSlides(n) {
    showSlides(slideIndex += n);
}

function currentSlide(n) {
    showSlides(slideIndex = n);
}

function showSlides(n) {
    let i;
    let slides = document.getElementsByClassName("slides");
    let dots = document.getElementsByClassName("dot");
    
    if (n > slides.length) {slideIndex = 1}    
    if (n < 1) {slideIndex = slides.length}
    
    for (i = 0; i < slides.length; i++) {
        slides[i].style.display = "none";  
    }
    for (i = 0; i < dots.length; i++) {
        dots[i].className = dots[i].className.replace(" active", "");
    }
    
    slides[slideIndex-1].style.display = "block";  
    dots[slideIndex-1].className += " active";
}

// Auto advance slides every 5 seconds
setInterval(() => {
    plusSlides(1);
}, 5000);
