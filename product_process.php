<?php
// FILE: product_process.php (Handler CRUD Seller - UPDATED FOR store_id)
session_start();

// CEK LOGIN DAN ROLE SELLER
if (!isset($_SESSION["user"]) || ($_SESSION["user"]["role"] ?? 'customer') !== 'seller') {
    header("Location: Login.php"); 
    exit;
}

// Dapatkan ID Toko
$current_user = $_SESSION["user"]; 
$store_id = $current_user['store_id'] ?? 1; 

// Pastikan ada data POST dan aksi yang ditentukan
if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_POST['action'])) {
    header("Location: seller_manage_products.php");
    exit;
}

$action = $_POST['action'];

// -----------------------------------------------------------
// FUNGSI UTILITY
// -----------------------------------------------------------

// Fungsi untuk mendapatkan ID baru (maksimum ID + 1)
function get_new_product_id($products) {
    // Jika tidak ada produk, mulai dari ID 101 (sesuai contoh sebelumnya)
    if (empty($products)) {
        return 101;
    }
    $ids = array_column($products, 'id');
    return max($ids) + 1;
}

// Fungsi untuk menyimpan pesan status
function set_product_message($text, $type = 'success') {
    $_SESSION['product_message'] = ['text' => $text, 'type' => $type];
}

// -----------------------------------------------------------
// LOGIKA CRUD
// -----------------------------------------------------------

// Ambil data produk toko saat ini
$products = $_SESSION['store_products'][$store_id] ?? [];
$product_id = (int)($_POST['product_id'] ?? 0);

switch ($action) {
    
    // --- DELETE ACTION ---
    case 'delete':
        $initial_count = count($products);
        $updated_products = [];
        
        foreach ($products as $product) {
            if ($product['id'] !== $product_id) {
                $updated_products[] = $product;
            }
        }
        
        // Simpan perubahan kembali ke session di bawah store_id yang benar
        $_SESSION['store_products'][$store_id] = $updated_products;
        
        if (count($updated_products) < $initial_count) {
            set_product_message("Produk ID {$product_id} berhasil dihapus.");
        } else {
            set_product_message("Gagal menghapus produk ID {$product_id}.", 'error');
        }
        break;

    // --- ADD/EDIT ACTION ---
    case 'add':
    case 'edit':
        
        $name = trim($_POST['name'] ?? '');
        $price_raw = trim($_POST['price'] ?? '');
        $stock = (int)($_POST['stock'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        $status = $_POST['status'] ?? 'Aktif';
        
        // Sederhanakan harga untuk ditampilkan
        $price_formatted = number_format((int)str_replace(['.', ','], '', $price_raw), 0, ',', '.');
        
        // Simulasikan penanganan upload gambar
        $image_name = ''; // Akan digunakan untuk menyimpan nama file gambar
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
             // Simulasi: Menggunakan nama asli file sebagai nama gambar
            $image_name = basename($_FILES['image']['name']);
        }
        
        $new_product_data = [
            'name' => $name,
            'price' => $price_formatted,
            'stock' => $stock,
            'description' => $description,
            'status' => $status,
        ];
        
        if ($action === 'add') {
            
            $new_id = get_new_product_id($products);
            $new_product_data['id'] = $new_id;
            $new_product_data['image'] = $image_name ?: 'default.jpg';
            
            $products[] = $new_product_data;
            // Simpan perubahan kembali ke session di bawah store_id yang benar
            $_SESSION['store_products'][$store_id] = $products;
            set_product_message("Produk **{$name}** berhasil ditambahkan dengan ID {$new_id}.");

        } elseif ($action === 'edit') {
            
            $updated_products = [];
            $found = false;
            
            foreach ($products as $product) {
                if ($product['id'] === $product_id) {
                    // Update data produk yang ditemukan
                    $product['name'] = $new_product_data['name'];
                    $product['price'] = $new_product_data['price'];
                    $product['stock'] = $new_product_data['stock'];
                    $product['description'] = $new_product_data['description'];
                    $product['status'] = $new_product_data['status'];
                    // Update gambar hanya jika file baru di-upload
                    if ($image_name) {
                        $product['image'] = $image_name;
                    }
                    $found = true;
                }
                $updated_products[] = $product;
            }
            
            // Simpan perubahan kembali ke session di bawah store_id yang benar
            $_SESSION['store_products'][$store_id] = $updated_products;
            
            if ($found) {
                set_product_message("Produk ID {$product_id} berhasil diperbarui.");
            } else {
                set_product_message("Gagal memperbarui: Produk ID {$product_id} tidak ditemukan.", 'error');
            }
        }
        break;

    default:
        set_product_message("Aksi tidak valid.", 'error');
        break;
}

// Redirect ke halaman daftar produk setelah proses selesai
header("Location: seller_manage_products.php");
exit;
?>
