<?php
session_start();
include 'conn.php';

// Ambil data cabang untuk combo box
$conn = OpenCon();
$sql_cabang = "SELECT id_cabang, nama_cabang FROM cabang";
$result_cabang = $conn->query($sql_cabang);
$cabang_options = "";
if ($result_cabang->num_rows > 0) {
    while($row_cabang = $result_cabang->fetch_assoc()) {
        $cabang_options .= "<option value='".$row_cabang["id_cabang"]."'>".$row_cabang["nama_cabang"]."</option>";
    }
}

// Ambil data produk untuk combo box
$sql_produk = "SELECT id_produk, nama_produk, price FROM produk";
$result_produk = $conn->query($sql_produk);
$produk_options = "";
if ($result_produk->num_rows > 0) {
    while($row_produk = $result_produk->fetch_assoc()) {
        $produk_options .= "<option value='".$row_produk["id_produk"]."'>".$row_produk["nama_produk"]."</option>";
    }
}

// Inisialisasi session keranjang jika belum ada
if (!isset($_SESSION['keranjang'])) {
    $_SESSION['keranjang'] = array();
}

// Fungsi untuk menambahkan item ke keranjang
function tambahItemKeKeranjang($id_produk, $qty) {
    $_SESSION['keranjang'][] = array('id_produk' => $id_produk, 'qty' => $qty);
}

// Jika form disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['submit'])) {
        $id_produk = $_POST['id_produk'];
        $qty = $_POST['qty'];

        // Tambahkan item ke keranjang
        tambahItemKeKeranjang($id_produk, $qty);
    } elseif (isset($_POST['checkout'])) {
        // Lakukan operasi untuk menyimpan data penjualan dan detail penjualan ke database
        // Ambil id_cabang dari form
        $id_cabang = $_POST['id_cabang'];
        
        // Tanggal penjualan
        $tanggal_penjualan = date("Y-m-d H:i:s");

        // Hitung grand total
        $grand_total = hitungTotalHarga();

        // Insert ke tabel penjualan
        $sql_insert_penjualan = "INSERT INTO penjualan (id_cabang, tanggal_penjualan, grand_total) VALUES ('$id_cabang', '$tanggal_penjualan', '$grand_total')";
        $result_insert_penjualan = $conn->query($sql_insert_penjualan);
        
        // Ambil id_penjualan yang baru saja di-generate
        $id_penjualan = $conn->insert_id;

        // Insert ke tabel detail
        foreach ($_SESSION['keranjang'] as $item) {
            $id_produk = $item['id_produk'];
            $qty = $item['qty'];
            $harga_total = $qty * ambilHargaProdukDariDatabase($id_produk);

            $sql_insert_detail = "INSERT INTO detail (id_penjualan, id_produk, qty, harga_total) VALUES ('$id_penjualan', '$id_produk', '$qty', '$harga_total')";
            $result_insert_detail = $conn->query($sql_insert_detail);
        }

        // Hapus isi keranjang setelah checkout
        $_SESSION['keranjang'] = array();
    }
}

// Fungsi untuk mengambil nama produk dari database berdasarkan id_produk
function ambilNamaProdukDariDatabase($id_produk) {
    $conn = OpenCon();
    $sql = "SELECT nama_produk FROM produk WHERE id_produk=$id_produk";
    $result = $conn->query($sql);
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        return $row['nama_produk'];
    } else {
        return "Produk tidak ditemukan";
    }
    CloseCon($conn);}

// Fungsi untuk mengambil harga produk dari database berdasarkan id_produk
function ambilHargaProdukDariDatabase($id_produk) {
    $conn = OpenCon();
    $sql = "SELECT price FROM produk WHERE id_produk=$id_produk";
    $result = $conn->query($sql);
    if ($result->num_rows == 1): {
        $row = $result->fetch_assoc();
        return $row['price'];
    } else {
        return 0;
    }
    CloseCon($conn);
}

// Fungsi untuk menghitung total harga
function hitungTotalHarga() {
    $total = 0;
    foreach ($_SESSION['keranjang'] as $item) {
        $harga = ambilHargaProdukDariDatabase($item['id_produk']);
        $total += $harga * $item['qty'];
    }
    return $total;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penjualan</title>
</head>
<body>
    <form method="post" action="penjualan.php">
        <label for="cabang">Cabang:</label>
        <select name="id_cabang" id="cabang">
            <?php echo $cabang_options; ?>
        </select>
        <br><br>
        Nama Produk: 
        <select name="id_produk" id="id_produk">
            <?php echo $produk_options; ?>
        </select><br><br>
        Qty: <input type="number" name="qty"><br><br>
        <input type="submit" name="submit" value="Submit">

        <h2>KERANJANG</h2>
    <ul>
        <?php foreach ($_SESSION['keranjang'] as $item): ?>
            <li>
                <?php 
                    $nama_produk = ambilNamaProdukDariDatabase($item['id_produk']);
                    echo $nama_produk; 
                ?> - <?php echo $item['qty']; ?> 
                - Total: 
                <?php 
                    $harga = ambilHargaProdukDariDatabase($item['id_produk']);
                    echo $harga * $item['qty']; 
                ?>
            </li>
        <?php endforeach; ?>
    </ul>
    <p>Grand Total: <?php echo hitungTotalHarga(); ?></p>
        <input type="submit" id="checkout" name="checkout" value="Checkout">
    </form>
    <script src="aturCBCabang.js"></script>
</body>
</html>
