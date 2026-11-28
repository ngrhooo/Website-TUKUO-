<?php
require_once "config.php";
ensure_session();
if (!isset($_SESSION["user_id"])) { header("Location: login.php"); exit; }
if ($_SERVER["REQUEST_METHOD"] !== "POST" || !csrf_is_valid()) { http_response_code(400); exit("Permintaan tidak valid."); }
$recipient=trim($_POST["recipient_name"]??"");
$phone=trim($_POST["phone"]??"");
$province=trim($_POST["province"]??"");
$city=trim($_POST["city"]??"");
$district=trim($_POST["district"]??"");
$postal=trim($_POST["postal_code"]??"");
$address=trim($_POST["full_address"]??"");
$method=$_POST["payment_method"]??"";
$cart=json_decode($_POST["cart_json"]??"",true);
if($method!=="QRIS"){http_response_code(400);exit("Metode pembayaran ini belum tersedia.");}
if(!$recipient||!$phone||!$province||!$city||!$district||!$postal||!$address||!is_array($cart)||!count($cart)){http_response_code(422);exit("Lengkapi data penerima dan keranjang pesanan.");}
try {
  $pdo=db();$pdo->beginTransaction();$items=[];$subtotal=0;
  $productStmt=$pdo->prepare("SELECT id,name,price,stock FROM products WHERE id=? FOR UPDATE");
  foreach($cart as $line){
    $id=(int)($line["id"]??0);$qty=(int)($line["quantity"]??0);
    if($qty<1||$qty>99)throw new RuntimeException("Jumlah barang tidak valid.");
    $productStmt->execute([$id]);$product=$productStmt->fetch();
    if(!$product||$product["stock"]<$qty)throw new RuntimeException("Produk tidak tersedia atau stok tidak mencukupi.");
    $price=(int)$product["price"];$lineTotal=$price*$qty;$subtotal+=$lineTotal;
    $items[]=["id"=>$id,"name"=>$product["name"],"price"=>$price,"quantity"=>$qty,"total"=>$lineTotal];
  }
  $code="TKO-".date("ymd")."-".strtoupper(bin2hex(random_bytes(3)));
  $stmt=$pdo->prepare("INSERT INTO orders (user_id,order_code,recipient_name,phone,province,city,district,postal_code,full_address,payment_method,subtotal,total,status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
  $stmt->execute([$_SESSION["user_id"],$code,$recipient,$phone,$province,$city,$district,$postal,$address,$method,$subtotal,$subtotal,"Menunggu pembayaran QRIS"]);
  $orderId=(int)$pdo->lastInsertId();
  $itemStmt=$pdo->prepare("INSERT INTO order_items (order_id,product_id,product_name,unit_price,quantity,line_total) VALUES (?,?,?,?,?,?)");
  $stockStmt=$pdo->prepare("UPDATE products SET stock=stock-? WHERE id=? AND stock>=?");
  foreach($items as $it){
    $itemStmt->execute([$orderId,$it["id"],$it["name"],$it["price"],$it["quantity"],$it["total"]]);
    $stockStmt->execute([$it["quantity"],$it["id"],$it["quantity"]]);
    if($stockStmt->rowCount()!==1)throw new RuntimeException("Stok berubah. Silakan coba kembali.");
  }
  $pdo->commit();header("Location: payment.php?id=".$orderId);exit;
} catch(Throwable $e) {
  if(isset($pdo)&&$pdo->inTransaction())$pdo->rollBack();
  http_response_code($e instanceof RuntimeException?422:500);
  exit($e instanceof RuntimeException?$e->getMessage():"Pesanan belum dapat disimpan. Periksa koneksi database.");
}
