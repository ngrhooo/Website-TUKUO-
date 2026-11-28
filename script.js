const products = (window.TUKUO_PRODUCTS || []).map(product => ({ ...product, id: Number(product.id), price: Number(product.price), stock: Number(product.stock) }));

const productGrid = document.querySelector("#product-grid");
const searchInput = document.querySelector("#search-input");
const emptyState = document.querySelector("#empty-state");
const filterButtons = document.querySelectorAll(".filter-button");
const cartPanel = document.querySelector("#cart-panel");
const cartBackdrop = document.querySelector("#drawer-backdrop");
const cartItemsElement = document.querySelector("#cart-items");
const cartCountElement = document.querySelector("#cart-count");
const cartSubtotalElement = document.querySelector("#cart-subtotal");
const toast = document.querySelector("#toast");
const productDialog = document.querySelector("#product-dialog");
let activeDetailProduct = null;

let activeCategory = "Semua";
let cart = [];
try {
  const savedCart = JSON.parse(localStorage.getItem("tukuo_cart") || "[]");
  if (Array.isArray(savedCart)) cart = savedCart.filter(item =>
    products.some(product => product.id === Number(item.id)) &&
    Number(item.quantity) > 0
  ).map(item => {
    const product = products.find(product => product.id === Number(item.id));
    return { ...product, quantity: Math.min(99, Number(item.quantity)) };
  });
} catch (_) { cart = []; }
let toastTimer;

function escapeHTML(value) {
  return String(value).replace(/[&<>"\']/g, char => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"' : "&quot;", "'" : "&#39;" }[char]));
}

function formatPrice(price) {
  return new Intl.NumberFormat("id-ID", {
    style: "currency",
    currency: "IDR",
    maximumFractionDigits: 0
  }).format(price);
}

function renderProducts() {
  const keyword = searchInput.value.trim().toLowerCase();
  const visibleProducts = products.filter((product) => {
    const matchesCategory = activeCategory === "Semua" || product.category === activeCategory;
    const matchesSearch = product.name.toLowerCase().includes(keyword);
    return matchesCategory && matchesSearch;
  });

  productGrid.innerHTML = visibleProducts.map((product) => `
    <article class="product-card">
      <div class="product-image-wrap">
        <img class="product-image" src="${escapeHTML(product.image)}" alt="${escapeHTML(product.name)}" loading="lazy">
        <span class="condition-tag">${product.stock > 0 ? "PRE-LOVED" : "HABIS"}</span>
      </div>
      <div class="product-details">
        <div class="product-meta"><span>${escapeHTML(product.category)}</span><span>Size ${escapeHTML(product.size)}</span></div>
        <h3><button class="product-title" type="button" data-detail="${product.id}">${escapeHTML(product.name)}</button></h3>
        <div class="product-bottom">
          <span class="price">${formatPrice(product.price)}</span>
          <button class="add-button" type="button" data-add="${product.id}">+ Tambah</button>
        </div>
      </div>
    </article>
  `).join("");

  emptyState.hidden = visibleProducts.length > 0;
}

function openProductDetail(productId) {
  const product = products.find((item) => item.id === productId);
  if (!product) return;

  activeDetailProduct = product;
  document.querySelector("#detail-image").src = product.image;
  document.querySelector("#detail-image").alt = product.name;
  document.querySelector("#detail-category").textContent = product.category;
  document.querySelector("#detail-name").textContent = product.name;
  document.querySelector("#detail-price").textContent = formatPrice(product.price);
  document.querySelector("#detail-description").textContent = product.description;
  document.querySelector("#detail-size").textContent = product.size;
  document.querySelector("#detail-condition").textContent = product.condition;
  document.querySelector("#detail-type").textContent = product.category;
  productDialog.showModal();
}

function showToast(message) {
  toast.textContent = message;
  toast.classList.add("is-visible");
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => toast.classList.remove("is-visible"), 2200);
}

function addToCart(productId) {
  const selectedProduct = products.find((product) => product.id === productId);
  const existingItem = cart.find((item) => item.id === productId);

  if (existingItem) {
    existingItem.quantity += 1;
  } else {
    cart.push({ ...selectedProduct, quantity: 1 });
  }

  renderCart();
  showToast(`${selectedProduct.name} ditambahkan ke keranjang`);
}

function updateQuantity(productId, change) {
  const item = cart.find((cartItem) => cartItem.id === productId);
  if (!item) return;

  item.quantity += change;
  cart = cart.filter((cartItem) => cartItem.quantity > 0);
  renderCart();
}

function renderCart() {
  localStorage.setItem("tukuo_cart", JSON.stringify(cart));
  const totalItems = cart.reduce((total, item) => total + item.quantity, 0);
  const subtotal = cart.reduce((total, item) => total + item.price * item.quantity, 0);

  cartCountElement.textContent = totalItems;
  cartSubtotalElement.textContent = formatPrice(subtotal);

  if (cart.length === 0) {
    cartItemsElement.innerHTML = '<p class="cart-empty">Keranjangmu masih kosong.</p>';
    return;
  }

  cartItemsElement.innerHTML = cart.map((item) => `
    <article class="cart-item">
      <img src="${escapeHTML(item.image)}" alt="${escapeHTML(item.name)}" loading="lazy">
      <div>
        <h3>${escapeHTML(item.name)}</h3>
        <p>${formatPrice(item.price)}</p>
        <div class="quantity-control" aria-label="Jumlah ${item.name}">
          <button type="button" data-quantity="-1" data-id="${item.id}" aria-label="Kurangi ${item.name}">−</button>
          <span>${item.quantity}</span>
          <button type="button" data-quantity="1" data-id="${item.id}" aria-label="Tambah ${item.name}">+</button>
        </div>
      </div>
      <button class="remove-item" type="button" data-remove="${item.id}" aria-label="Hapus ${item.name}">×</button>
    </article>
  `).join("");
}

function setCartOpen(isOpen) {
  cartPanel.classList.toggle("is-open", isOpen);
  cartPanel.setAttribute("aria-hidden", String(!isOpen));
  cartPanel.inert = !isOpen;
  cartBackdrop.hidden = !isOpen;
  document.querySelector("#cart-toggle").setAttribute("aria-expanded", String(isOpen));
  document.body.style.overflow = isOpen ? "hidden" : "";
  if (isOpen) document.querySelector("#cart-close").focus();
}

filterButtons.forEach((button) => {
  button.addEventListener("click", () => {
    activeCategory = button.dataset.category;
    filterButtons.forEach((item) => item.classList.toggle("is-active", item === button));
    renderProducts();
  });
});

searchInput.addEventListener("input", renderProducts);

productGrid.addEventListener("click", (event) => {
  const detailButton = event.target.closest("[data-detail]");
  const addButton = event.target.closest("[data-add]");
  if (detailButton) openProductDetail(Number(detailButton.dataset.detail));
  if (addButton) addToCart(Number(addButton.dataset.add));
});

cartItemsElement.addEventListener("click", (event) => {
  const quantityButton = event.target.closest("[data-quantity]");
  const removeButton = event.target.closest("[data-remove]");

  if (quantityButton) {
    updateQuantity(Number(quantityButton.dataset.id), Number(quantityButton.dataset.quantity));
  }

  if (removeButton) {
    cart = cart.filter((item) => item.id !== Number(removeButton.dataset.remove));
    renderCart();
  }
});

document.querySelector("#detail-close").addEventListener("click", () => productDialog.close());
productDialog.addEventListener("click", (event) => {
  if (event.target === productDialog) productDialog.close();
});
document.querySelector("#detail-add").addEventListener("click", () => {
  if (!activeDetailProduct) return;
  addToCart(activeDetailProduct.id);
  productDialog.close();
});

document.querySelector("#cart-toggle").addEventListener("click", () => setCartOpen(true));
document.querySelector("#cart-close").addEventListener("click", () => setCartOpen(false));
cartBackdrop.addEventListener("click", () => setCartOpen(false));
document.addEventListener("keydown", (event) => {
  if (event.key === "Escape" && cartPanel.classList.contains("is-open")) setCartOpen(false);
});

document.querySelector("#checkout-button").addEventListener("click", () => {
  const isLoggedIn = document.body.dataset.loggedIn === "1";
  if (!isLoggedIn) {
    window.location.href = "login.php";
    return;
  }
  if (cart.length === 0) {
    showToast("Tambahkan produk terlebih dahulu.");
    return;
  }
  localStorage.setItem("tukuo_cart", JSON.stringify(cart));
  window.location.href = "checkout.php";
});

renderProducts();
renderCart();
