<?php
require_once "config.php";
ensure_session();
if (!isset($_SESSION["user_id"])) { header("Location: login.php"); exit; }
$stmt = db()->prepare("SELECT role FROM users WHERE id = ?");
$stmt->execute([$_SESSION["user_id"]]);
if ($stmt->fetchColumn() !== "admin") { http_response_code(403); exit("Akses ditolak."); }

$pdo = db();
$error = "";
$success = "";
$editing = null;
$uploadDir = __DIR__ . "/uploads/products";
if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
    http_response_code(500); exit("Folder upload tidak dapat dibuat.");
}
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!csrf_is_valid()) { http_response_code(400); exit("Sesi formulir tidak valid."); }
    $action = $_POST["action"] ?? "";
    try {
        if ($action === "delete") {
            $id = (int)($_POST["id"] ?? 0);
            $delete = $pdo->prepare("DELETE FROM products WHERE id = ?");
            $delete->execute([$id]);
            $success = "Produk berhasil dihapus.";
        } elseif ($action === "save") {
            $id = (int)($_POST["id"] ?? 0);
            $name = trim($_POST["name"] ?? "");
            $category = $_POST["category"] ?? "";
            $size = trim($_POST["size"] ?? "");
            $condition = trim($_POST["condition_label"] ?? "");
            $description = trim($_POST["description"] ?? "");
            $price = filter_var($_POST["price"] ?? null, FILTER_VALIDATE_INT);
            $stock = filter_var($_POST["stock"] ?? null, FILTER_VALIDATE_INT);
            if ($name === "" || !in_array($category, ["Pakaian","Sepatu"], true) || $size === "" || $condition === "" || $description === "" || $price === false || $price < 0 || $stock === false || $stock < 0) {
                throw new RuntimeException("Periksa kembali data produk.");
            }
            $image = trim($_POST["existing_image"] ?? "");
            if (isset($_FILES["image"]) && $_FILES["image"]["error"] !== UPLOAD_ERR_NO_FILE) {
                if ($_FILES["image"]["error"] !== UPLOAD_ERR_OK || $_FILES["image"]["size"] > 5 * 1024 * 1024) throw new RuntimeException("Foto gagal diunggah atau melebihi 5 MB.");
                $mime = (new finfo(FILEINFO_MIME_TYPE))->file($_FILES["image"]["tmp_name"]);
                $extensions = ["image/jpeg"=>"jpg","image/png"=>"png","image/webp"=>"webp"];
                if (!isset($extensions[$mime])) throw new RuntimeException("Format foto harus JPG, PNG, atau WEBP.");
                $filename = bin2hex(random_bytes(16)) . "." . $extensions[$mime];
                if (!move_uploaded_file($_FILES["image"]["tmp_name"], $uploadDir . "/" . $filename)) throw new RuntimeException("Foto tidak dapat disimpan.");
                $image = "uploads/products/" . $filename;
            }
            if ($image === "") throw new RuntimeException("Foto produk wajib diisi.");
            if ($id > 0) {
                $save = $pdo->prepare("UPDATE products SET name=?,category=?,size=?,condition_label=?,description=?,price=?,stock=?,image=? WHERE id=?");
                $save->execute([$name,$category,$size,$condition,$description,$price,$stock,$image,$id]);
                $success = "Produk berhasil diperbarui.";
            } else {
                $save = $pdo->prepare("INSERT INTO products (name,category,size,condition_label,description,price,stock,image) VALUES (?,?,?,?,?,?,?,?)");
                $save->execute([$name,$category,$size,$condition,$description,$price,$stock,$image]);
                $success = "Produk berhasil ditambahkan.";
            }
        }
    } catch (Throwable $exception) { $error = $exception instanceof RuntimeException ? $exception->getMessage() : "Perubahan produk gagal disimpan."; }
}
if (isset($_GET["edit"])) {
    $find = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $find->execute([(int)$_GET["edit"]]);
    $editing = $find->fetch() ?: null;
}
$products = $pdo->query("SELECT * FROM products ORDER BY created_at DESC")->fetchAll();
$totalStock = array_sum(array_column($products, "stock"));
$csrf = e($_SESSION["csrf_token"]);
?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Admin — TUKUO!</title><link rel="stylesheet" href="style.css"></head>
<body class="admin-page">
<header class="admin-header"><a class="logo" href="index.php">TUKUO<span>!</span></a><div><a href="index.php">Lihat toko ↗</a><form action="logout.php" method="post"><input type="hidden" name="csrf_token" value="<?= $csrf ?>"><button type="submit">Keluar</button></form></div></header>
<main class="admin-main">
<div class="admin-heading"><div><p class="eyebrow">TUKUO! MANAGEMENT</p><h1>Dashboard<span>.</span></h1><p>Kelola katalog dan persediaan produk.</p></div><a class="button button-dark" href="#product-form">+ Tambah produk</a></div>
<?php if ($error): ?><p class="admin-alert"><?= e($error) ?></p><?php endif; ?><?php if ($success): ?><p class="admin-success"><?= e($success) ?></p><?php endif; ?>
<div class="admin-stats"><article><span>Total produk</span><strong><?= count($products) ?></strong></article><article><span>Total stok</span><strong><?= $totalStock ?></strong></article><article><span>Stok habis</span><strong><?= count(array_filter($products, fn($p) => (int)$p["stock"] === 0)) ?></strong></article></div>
<section class="admin-section" id="product-form"><div class="admin-section-title"><div><p class="eyebrow">PRODUCT EDITOR</p><h2><?= $editing ? "Edit produk" : "Tambah produk" ?></h2></div><?php if ($editing): ?><a href="admin.php">Batal edit</a><?php endif; ?></div>
<form class="admin-form" method="post" enctype="multipart/form-data"><input type="hidden" name="csrf_token" value="<?= $csrf ?>"><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= (int)($editing["id"] ?? 0) ?>"><input type="hidden" name="existing_image" value="<?= e($editing["image"] ?? "") ?>">
<label>Nama produk<input name="name" maxlength="160" required value="<?= e($editing["name"] ?? "") ?>"></label>
<label>Kategori<select name="category" required><option value="Pakaian" <?= ($editing["category"] ?? "") === "Pakaian" ? "selected" : "" ?>>Pakaian</option><option value="Sepatu" <?= ($editing["category"] ?? "") === "Sepatu" ? "selected" : "" ?>>Sepatu</option></select></label>
<label>Ukuran<input name="size" maxlength="40" required value="<?= e($editing["size"] ?? "") ?>"></label>
<label>Kondisi<input name="condition_label" maxlength="80" required value="<?= e($editing["condition_label"] ?? "") ?>"></label>
<label>Harga (Rupiah)<input name="price" type="number" min="0" step="1" required value="<?= e((string)($editing["price"] ?? "")) ?>"></label>
<label>Stok<input name="stock" type="number" min="0" step="1" required value="<?= e((string)($editing["stock"] ?? "1")) ?>"></label>
<label class="admin-full">Deskripsi<textarea name="description" rows="3" required><?= e($editing["description"] ?? "") ?></textarea></label>
<label class="admin-full">Foto produk (JPG, PNG, WEBP · maks. 5 MB)<input type="file" name="image" accept="image/jpeg,image/png,image/webp" <?= $editing ? "" : "required" ?>></label>
<button class="button button-dark" type="submit"><?= $editing ? "Simpan perubahan" : "Simpan produk" ?> →</button></form></section>
<section class="admin-section"><div class="admin-section-title"><div><p class="eyebrow">INVENTORY</p><h2>Semua produk</h2></div><span><?= count($products) ?> item</span></div>
<div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Produk</th><th>Kategori</th><th>Harga</th><th>Stok</th><th>Aksi</th></tr></thead><tbody>
<?php foreach ($products as $product): ?><tr><td><div class="admin-product"><img src="<?= e($product["image"]) ?>" alt=""><div><strong><?= e($product["name"]) ?></strong><small><?= e($product["size"]) ?> · <?= e($product["condition_label"]) ?></small></div></div></td><td><?= e($product["category"]) ?></td><td>Rp<?= number_format((float)$product["price"],0,",",".") ?></td><td><?= (int)$product["stock"] ?></td><td><div class="admin-actions"><a href="admin.php?edit=<?= (int)$product["id"] ?>#product-form">Edit</a><form method="post" onsubmit="return confirm('Hapus produk ini?')"><input type="hidden" name="csrf_token" value="<?= $csrf ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$product["id"] ?>"><button type="submit">Hapus</button></form></div></td></tr><?php endforeach; ?>
<?php if (!$products): ?><tr><td colspan="5">Belum ada produk.</td></tr><?php endif; ?></tbody></table></div></section>
</main></body></html>