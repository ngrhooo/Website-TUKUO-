<?php
require_once "config.php";ensure_session();
if(!isset($_SESSION["user_id"])){header("Location: login.php");exit;}
$id=(int)($_GET["id"]??0);
$stmt=db()->prepare("SELECT * FROM orders WHERE id=? AND user_id=? LIMIT 1");$stmt->execute([$id,$_SESSION["user_id"]]);$order=$stmt->fetch();
if(!$order){http_response_code(404);exit("Struk tidak ditemukan.");}
$it=db()->prepare("SELECT * FROM order_items WHERE order_id=?");$it->execute([$id]);$items=$it->fetchAll();
$wa="62882005764682";
$msg="Halo Admin TUKUO!, saya ingin konfirmasi pesanan ".$order["order_code"].". Nama: ".$order["recipient_name"].". Total: Rp".number_format((float)$order["total"],0,",",".").". Saya akan mengirim bukti pembayaran.";
$waUrl="https://wa.me/".$wa."?text=".rawurlencode($msg);
?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Struk Pesanan — TUKUO!</title><link rel="stylesheet" href="style.css"></head>
<body class="shop-flow-page"><header class="flow-header"><a class="flow-brand" href="index.php">TUKUO!<small>EST. 2028</small></a><a class="flow-back" href="index.php">Kembali ke toko</a></header>
<main class="flow-main"><section class="flow-card order-success"><div class="success-mark">✓</div><h1 class="flow-title">Pesanan Berhasil Dibuat!</h1><p class="flow-lead">Terima kasih sudah berbelanja di TUKUO!.</p><p class="receipt-code"><?=e($order["order_code"])?></p>
<div class="receipt-status"><strong>Pesanan sudah dibuat dan sedang diproses.</strong><br>Status saat ini: <?=e($order["status"])?>.<br>Admin akan mengonfirmasi pesanan dan pengiriman melalui WhatsApp.</div>
<div style="max-width:650px;margin:25px auto;text-align:left"><h2>Detail Struk</h2><p><strong>Nama:</strong> <?=e($order["recipient_name"])?><br><strong>Nomor HP:</strong> <?=e($order["phone"])?><br><strong>Alamat:</strong> <?=e($order["full_address"])?>, <?=e($order["district"])?>, <?=e($order["city"])?>, <?=e($order["province"])?> <?=e($order["postal_code"])?><br><strong>Metode pembayaran:</strong> QRIS</p>
<?php foreach($items as $item):?><div class="flow-total"><span><?=e($item["product_name"])?> × <?=$item["quantity"]?></span><strong>Rp<?=number_format((float)$item["line_total"],0,",",".")?></strong></div><?php endforeach;?>
<div class="flow-total grand"><span>Total barang</span><strong>Rp<?=number_format((float)$order["total"],0,",",".")?></strong></div></div>
<p>Konfirmasi pesanan atau tanyakan status pengiriman kepada admin:</p><p><strong>WhatsApp: 0882005764682</strong></p><a class="whatsapp-button" href="<?=e($waUrl)?>" target="_blank" rel="noopener">Konfirmasi melalui WhatsApp</a><a class="auth-back" href="index.php">← Kembali ke TUKUO!</a></section></main>
<script>localStorage.removeItem("tukuo_cart");</script></body></html>
