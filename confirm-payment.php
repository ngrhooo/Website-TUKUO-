<?php
require_once "config.php";ensure_session();
if(!isset($_SESSION["user_id"])){header("Location: login.php");exit;}
if($_SERVER["REQUEST_METHOD"]!=="POST"||!csrf_is_valid()){http_response_code(400);exit("Permintaan tidak valid.");}
$id=(int)($_POST["order_id"]??0);
$stmt=db()->prepare("UPDATE orders SET status='Menunggu konfirmasi admin', confirmed_at=NOW() WHERE id=? AND user_id=? AND status='Menunggu pembayaran QRIS'");
$stmt->execute([$id,$_SESSION["user_id"]]);
header("Location: receipt.php?id=".$id);exit;
