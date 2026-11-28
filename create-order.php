<?php
require_once "config.php";
ensure_session();
if (!isset($_SESSION["user_id"])) { header("Location: login.php"); exit; }
if ($_SERVER["REQUEST_METHOD"] !== "POST" || !csrf_is_valid()) { http_response_code(400); exit("Permintaan tidak valid."); }
$products = [
  1 => ["name"=>"Oversized Shirt","price"=>85000],
  2 => ["name"=>"Classic Sneakers","price"=>175000],
  3 => ["name"=>"Minimal Jacket","price"=>135000],
  4 => ["name"=>"Everyday Trainers","price"=>160000],
  5 => ["name"=>"Daily Knitwear","price"=>95000],
  6 => ["name"=>"Retro Street Shoes","price"=>190000],
];
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
$items=[];$subtotal=0;
foreach($cart as $line){
  $id=(int)($line["id"]??0);$qty=(int)($line["quantity"]??0);
  if(!isset($products[$id])||$qty<1||$qty>99){http_response_code(422);exit("Data barang tidak valid.");}
  $product=$products[$id];$lineTotal=$product["price"]*$qty;$subtotal+=$lineTotal;
  $items[]=["id"=>$id,"name"=>$product["name"],"price"=>$product["price"],"quantity"=>$qty,"total"=>$lineTotal];
}
$code="TKO-".date("ymd")."-".strtoupper(bin2hex(random_bytes(3)));
try{
  $pdo=db();$pdo->beginTransaction();
  $stmt=$pdo->prepare("INSERT INTO orders (user_id,order_code,recipient_name,phone,province,city,district,postal_code,full_address,payment_method,subtotal,total,status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
  $stmt->execute([$_SESSION["user_id"],$code,$recipient,$phone,$province,$city,$district,$postal,$address,$method,$subtotal,$subtotal,"Menunggu pembayaran QRIS"]);
  $orderId=(int)$pdo->lastInsertId();
  $itemStmt=$pdo->prepare("INSERT INTO order_items (order_id,product_id,product_name,unit_price,quantity,line_total) VALUES (?,?,?,?,?,?)");
  foreach($items as $it)$itemStmt->execute([$orderId,$it["id"],$it["name"],$it["price"],$it["quantity"],$it["total"]]);
  $pdo->commit();
  unset($_SESSION["last_order_id"]);
  header("Location: payment.php?id=".$orderId);exit;
}catch(Throwable $e){if(isset($pdo)&&$pdo->inTransaction())$pdo->rollBack();http_response_code(500);exit("Pesanan belum dapat disimpan. Pastikan tabel pesanan sudah dibuat di database.");}
