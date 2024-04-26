<?php
include 'conn.php';

// Variabel pesan error
$error = "";

// Fungsi untuk mendapatkan data produk berdasarkan ID
function getProdukById($conn, $id) {
    $sql = "SELECT * FROM produk WHERE id_produk=$id";
    $result = $conn->query($sql);
    if ($result->num_rows == 1) {
        return $result->fetch_assoc();
    } else {
        return null;
    }
}

// Jika form disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Jika ada parameter 'update', berarti mode update
    if (isset($_POST['update'])) {
        // Ambil ID dari form
        $id = $_POST['id'];

        // Ambil data produk berdasarkan ID
        $conn = OpenCon();
        $update_data = getProdukById($conn, $id);
        CloseCon($conn);

        // Memasukkan data ke dalam variabel untuk digunakan dalam form
        $update_nama_produk = $update_data['nama_produk'];
        $update_stock = $update_data['stock'];
        $update_price = $update_data['price'];
    } elseif (isset($_POST['submit_update'])) { // Jika tombol "Update" pada form ditekan, lakukan query update
        // Ambil data dari form
        $id = $_POST['id'];
        $nama_produk = $_POST['nama_produk'];
        $stock = $_POST['stock'];
        $price = $_POST['price'];

        // Persiapkan statement SQL untuk update
        $sql = "UPDATE produk SET nama_produk='$nama_produk', stock='$stock', price='$price' WHERE id_produk=$id";

        // Eksekusi statement SQL
        $conn = OpenCon();
        if ($conn->query($sql) === TRUE) {
            echo "Data produk berhasil diupdate.";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
        CloseCon($conn);
    } elseif (isset($_POST['delete'])) { // Jika ada parameter 'delete', berarti mode delete
        // Ambil ID dari form
        $id = $_POST['id'];

        // Persiapkan statement SQL untuk delete
        $sql = "DELETE FROM produk WHERE id_produk=$id";

        // Eksekusi statement SQL
        $conn = OpenCon();
        if ($conn->query($sql) === TRUE) {
            echo "Data produk berhasil dihapus.";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
        CloseCon($conn);

        // Refresh halaman
        header("Location: produk.php");
        exit();
    } else { // Jika tidak ada parameter 'update' atau 'delete', berarti mode tambah
        // Ambil data dari form
        $nama_produk = $_POST['nama_produk'];
        $stock = $_POST['stock'];
        $price = $_POST['price'];

        // Koneksi ke database
        if (empty($nama_produk) || empty($stock) || empty($price)) {
            $error = "Semua field harus diisi.";
        } else {
            // Koneksi ke database
            $conn = OpenCon();

            // Persiapkan statement SQL untuk tambah data
            $sql = "INSERT INTO produk (nama_produk, stock, price) VALUES ('$nama_produk', '$stock', '$price')";

            // Eksekusi statement SQL
            if ($conn->query($sql) === TRUE) {
                echo "Data produk berhasil disimpan.";
            } else {
                echo "Error: " . $sql . "<br>" . $conn->error;
            }

            // Tutup koneksi
            CloseCon($conn);
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produk</title>
</head>
<body>
<form method="post" action="produk.php">
    Nama Produk: <input type="text" name="nama_produk" value="<?php echo isset($update_nama_produk) ? $update_nama_produk : ''; ?>"><br><br>
    Stock: <input type="number" name="stock" value="<?php echo isset($update_stock) ? $update_stock : ''; ?>"><br><br>
    Price: <input type="number" name="price" value="<?php echo isset($update_price) ? $update_price : ''; ?>"><br><br>
    <?php if(isset($_POST['update'])) { ?>
        <input type="hidden" name="id" value="<?php echo $_POST['id']; ?>">
        <input type="submit" name="submit_update" value="Update">
    <?php } else { ?>
        <input type="submit" value="Submit">
    <?php } ?>
</form>


    <?php
    // Menampilkan data produk
    $conn = OpenCon();
    $sql = "SELECT * FROM produk";
    $result = $conn->query($sql);

    echo "<h2>Data Produk</h2>";
    if ($result->num_rows > 0) {
        echo "<table border='1'>";
        echo "<tr><th>Nama Produk</th><th>Stock</th><th>Price</th><th>Update</th><th>Delete</th></tr>";
        while($row = $result->fetch_assoc()) {
            echo "<tr><td>".$row["nama_produk"]."</td><td>".$row["stock"]."</td><td>Rp. ".$row["price"].",00</td>";
            echo "<td>
                    <form method='post' action='produk.php'>
                        <input type='hidden' name='id' value='".$row["id_produk"]."'>
                        <input type='submit' name='update' value='Update'>
                    </form>
                    <td>
                    <form method='post' action='produk.php' onsubmit=\"return confirm('Apakah Anda yakin ingin menghapus data ini?');\">
                        <input type='hidden' name='id' value='".$row["id_produk"]."'>
                        <input type='submit' name='delete' value='Delete'>
                    </form>
                    </td>
                </td></tr>";
        }
        echo "</table>";
    } else {
        echo "Tidak ada data produk.";
    }
    CloseCon($conn);
    ?>
</body>
</html>
