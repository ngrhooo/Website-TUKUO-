<?php
require_once "config.php";ensure_session();
if(!isset($_SESSION["user_id"])){header("Location: login.php");exit;}
$id=(int)($_GET["id"]??0);
$stmt=db()->prepare("SELECT * FROM orders WHERE id=? AND user_id=? LIMIT 1");$stmt->execute([$id,$_SESSION["user_id"]]);$order=$stmt->fetch();
if(!$order){http_response_code(404);exit("Pesanan tidak ditemukan.");}
if($order["status"]!=="Menunggu pembayaran QRIS"){header("Location: receipt.php?id=".$id);exit;}
$itemsStmt=db()->prepare("SELECT * FROM order_items WHERE order_id=?");$itemsStmt->execute([$id]);$items=$itemsStmt->fetchAll();
?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Pembayaran QRIS — TUKUO!</title><link rel="stylesheet" href="style.css"></head>
<body class="shop-flow-page"><header class="flow-header"><a class="flow-brand" href="index.php">TUKUO!<small>EST. 2028</small></a><span>Pembayaran</span></header>
<main class="flow-main"><h1 class="flow-title">Pembayaran QRIS</h1><p class="flow-lead">Scan QRIS di bawah menggunakan aplikasi pembayaran yang mendukung QRIS.</p>
<div class="flow-grid"><section class="flow-card qris-wrap"><h2>Scan untuk membayar</h2><p class="receipt-code"><?=e($order["order_code"])?></p><img class="qris-image" src="aset_foto/qris-tukuo.svg" alt="QRIS pembayaran TUKUO"><h2><?= "Rp".number_format((float)$order["total"],0,",",".") ?></h2><p class="flow-small">Pastikan nominal pembayaran sesuai dengan total pesanan. QRIS yang diberikan merupakan QRIS statis; masukkan nominal sesuai total di aplikasi pembayaran jika diminta.</p>
<form action="confirm-payment.php" method="post"><input type="hidden" name="csrf_token" value="<?=e($_SESSION["csrf_token"])?>"><input type="hidden" name="order_id" value="<?=$id?>"><button class="flow-button" type="submit">Saya Sudah Membayar — Konfirmasi</button></form>
<p class="flow-small">Setelah menekan tombol, pesanan akan berstatus menunggu konfirmasi admin. Simpan bukti pembayaran untuk dikirim melalui WhatsApp.</p></section>
<aside class="flow-card"><h2>Ringkasan Pesanan</h2><?php foreach($items as $it):?><div class="flow-total"><span><?=e($it["product_name"])?> × <?=$it["quantity"]?></span><strong>Rp<?=number_format((float)$it["line_total"],0,",",".")?></strong></div><?php endforeach;?><div class="flow-total grand"><span>Total</span><strong>Rp<?=number_format((float)$order["total"],0,",",".")?></strong></div><p class="flow-small">Nama penerima: <?=e($order["recipient_name"])?><br>Alamat: <?=e($order["full_address"])?>, <?=e($order["district"])?>, <?=e($order["city"])?>, <?=e($order["province"])?> <?=e($order["postal_code"])?></p></aside></div></main></body></html>
