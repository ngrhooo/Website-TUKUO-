<?php
require_once "config.php";
ensure_session();
if (!isset($_SESSION["user_id"])) { header("Location: login.php"); exit; }
$name = $_SESSION["user_name"] ?? "";
?>
<!doctype html><html lang="id"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Checkout — TUKUO!</title><link rel="stylesheet" href="style.css"></head>
<body class="shop-flow-page">
<header class="flow-header"><a class="flow-brand" href="index.php">TUKUO!<small>EST. 2028</small></a><a class="flow-back" href="index.php">← Kembali ke toko</a></header>
<main class="flow-main">
<h1 class="flow-title">Checkout</h1><p class="flow-lead">Lengkapi alamat pengiriman dan periksa kembali barang pesananmu.</p>
<div id="checkout-alert" class="flow-alert" hidden></div>
<form id="checkout-form" action="create-order.php" method="post">
<input type="hidden" name="csrf_token" value="<?= e($_SESSION["csrf_token"]) ?>">
<input type="hidden" name="cart_json" id="cart-json">
<div class="flow-grid">
<section class="flow-card">
<h2>Informasi Penerima</h2>
<div class="flow-fields">
<div class="flow-field"><label for="recipient">Nama lengkap penerima</label><input id="recipient" name="recipient_name" maxlength="120" required value="<?= e($name) ?>"></div>
<div class="flow-field"><label for="phone">Nomor HP penerima</label><input id="phone" name="phone" type="tel" maxlength="25" required placeholder="08xxxxxxxxxx"></div>
<div class="flow-field"><label for="province">Provinsi</label><input id="province" name="province" required maxlength="100"></div>
<div class="flow-field"><label for="city">Kota/Kabupaten</label><input id="city" name="city" required maxlength="100"></div>
<div class="flow-field"><label for="district">Kecamatan</label><input id="district" name="district" required maxlength="100"></div>
<div class="flow-field"><label for="postal">Kode pos</label><input id="postal" name="postal_code" required maxlength="12"></div>
<div class="flow-field full"><label for="address">Alamat lengkap (nama jalan, nomor rumah, RT/RW, patokan)</label><textarea id="address" name="full_address" required></textarea></div>
</div>
<h2 style="margin-top:28px">Metode Pembayaran</h2>
<div class="payment-options">
<label class="payment-choice"><input type="radio" name="payment_method" value="QRIS" checked><span><strong>QRIS</strong><span>Scan menggunakan aplikasi pembayaran yang mendukung QRIS</span></span></label>
<label class="payment-choice"><input type="radio" name="payment_method" value="BCA"><span><strong>Transfer Bank BCA</strong><span>Metode pembayaran ini belum tersedia</span></span></label>
<label class="payment-choice"><input type="radio" name="payment_method" value="BRI"><span><strong>Transfer Bank BRI</strong><span>Metode pembayaran ini belum tersedia</span></span></label>
<label class="payment-choice"><input type="radio" name="payment_method" value="BNI"><span><strong>Transfer Bank BNI</strong><span>Metode pembayaran ini belum tersedia</span></span></label>
</div>
<div id="payment-unavailable" class="payment-unavailable">Metode pembayaran ini belum tersedia. Silakan pilih QRIS untuk melanjutkan.</div>
<button class="flow-button" id="checkout-submit" type="submit">Lanjutkan ke Pembayaran →</button>
</section>
<aside class="flow-card">
<h2>Detail Barang</h2><div id="checkout-items" class="flow-items"></div>
<div class="flow-total"><span>Subtotal barang</span><strong id="checkout-subtotal">Rp0</strong></div>
<div class="flow-total"><span>Ongkos kirim</span><strong>Dikonfirmasi admin</strong></div>
<div class="flow-total grand"><span>Total barang</span><strong id="checkout-total">Rp0</strong></div>
<p class="flow-small">Ongkos kirim akan dikonfirmasi oleh admin melalui WhatsApp. Total di atas merupakan subtotal barang.</p>
</aside>
</div>
</form></main>
<script>
const money=n=>new Intl.NumberFormat("id-ID",{style:"currency",currency:"IDR",maximumFractionDigits:0}).format(n);
let items=[]; try{items=JSON.parse(localStorage.getItem("tukuo_cart")||"[]")}catch(e){}
const alertBox=document.getElementById("checkout-alert");
const submit=document.getElementById("checkout-submit");
if(!Array.isArray(items)||!items.length){alertBox.hidden=false;alertBox.textContent="Keranjang kosong. Silakan pilih barang terlebih dahulu.";submit.disabled=true;}
else{
 const total=items.reduce((s,x)=>s+(Number(x.price)||0)*(Number(x.quantity)||0),0);
 document.getElementById("checkout-items").innerHTML=items.map(x=>`<div class="flow-item"><img src="${String(x.image).replace(/"/g,"")}" alt=""><div><h3>${String(x.name).replace(/[&<>"]/g,c=>({"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;"}[c]))}</h3><p>${money(Number(x.price))} × ${Number(x.quantity)}</p></div><strong>${money(Number(x.price)*Number(x.quantity))}</strong></div>`).join("");
 document.getElementById("checkout-subtotal").textContent=money(total);
 document.getElementById("checkout-total").textContent=money(total);
}
const payRadios=[...document.querySelectorAll('input[name="payment_method"]')];
function updatePayment(){const qris=document.querySelector('input[name="payment_method"]:checked')?.value==="QRIS";document.getElementById("payment-unavailable").style.display=qris?"none":"block";submit.disabled=!qris||!items.length;}
payRadios.forEach(x=>x.addEventListener("change",updatePayment));updatePayment();
document.getElementById("checkout-form").addEventListener("submit",e=>{
 if(!items.length){e.preventDefault();return;}
 const chosen=document.querySelector('input[name="payment_method"]:checked')?.value;
 if(chosen!=="QRIS"){e.preventDefault();updatePayment();return;}
 document.getElementById("cart-json").value=JSON.stringify(items.map(x=>({id:Number(x.id),quantity:Number(x.quantity)})));
});
</script></body></html>
