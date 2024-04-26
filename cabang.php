<?php
include 'conn.php';

// Variabel pesan error
$error = "";

// Variabel untuk menyimpan data yang akan diupdate
$update_data = array('nama_cabang' => '', 'alamat_cabang' => '', 'nama_manager' => '');

// Fungsi untuk mendapatkan data cabang berdasarkan ID
function getCabangById($conn, $id) {
    $sql = "SELECT * FROM cabang WHERE id_cabang=$id";
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

        // Ambil data cabang berdasarkan ID
        $conn = OpenCon();
        $update_data = getCabangById($conn, $id);
        CloseCon($conn);

        // Memasukkan data ke dalam variabel untuk digunakan dalam form
        $update_nama_cabang = $update_data['nama_cabang'];
        $update_alamat_cabang = $update_data['alamat_cabang'];
        $update_nama_manager = $update_data['nama_manager'];
    } elseif (isset($_POST['submit_update'])) { // Jika tombol "Update" pada form ditekan, lakukan query update
        // Ambil data dari form
        $id = $_POST['id'];
        $nama_cabang = $_POST['nama_cabang'];
        $alamat_cabang = $_POST['alamat_cabang'];
        $nama_manager = $_POST['nama_manager'];

        // Persiapkan statement SQL untuk update
        $sql = "UPDATE cabang SET nama_cabang='$nama_cabang', alamat_cabang='$alamat_cabang', nama_manager='$nama_manager' WHERE id_cabang=$id";

        // Eksekusi statement SQL
        $conn = OpenCon();
        if ($conn->query($sql) === TRUE) {
            echo "Data cabang berhasil diupdate.";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
        CloseCon($conn);
    } elseif (isset($_POST['delete'])) { // Jika ada parameter 'delete', berarti mode delete
        // Ambil ID dari form
        $id = $_POST['id'];

        // Persiapkan statement SQL untuk delete
        $sql = "DELETE FROM cabang WHERE id_cabang=$id";

        // Eksekusi statement SQL
        $conn = OpenCon();
        if ($conn->query($sql) === TRUE) {
            echo "Data cabang berhasil dihapus.";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
        CloseCon($conn);

        // Refresh halaman
        header("Location: cabang.php");
        exit();
    } else { // Jika tidak ada parameter 'update' atau 'delete', berarti mode tambah
        // Ambil data dari form
        $nama_cabang = $_POST['nama_cabang'];
        $alamat_cabang = $_POST['alamat_cabang'];
        $nama_manager = $_POST['nama_manager'];

        // Koneksi ke database
        if (empty($nama_cabang) || empty($alamat_cabang) || empty($nama_manager)) {
            $error = "Semua field harus diisi.";
        } else {
            // Koneksi ke database
            $conn = OpenCon();

            // Persiapkan statement SQL untuk tambah data
            $sql = "INSERT INTO cabang (nama_cabang, alamat_cabang, nama_manager) VALUES ('$nama_cabang', '$alamat_cabang', '$nama_manager')";

            // Eksekusi statement SQL
            if ($conn->query($sql) === TRUE) {
                echo "Data cabang berhasil disimpan.";
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
    <title>Cabang</title>
</head>
<body>
    <form method="post" action="cabang.php">
        Nama Cabang: <input type="text" name="nama_cabang" value="<?php echo $update_data['nama_cabang']; ?>"><br><br>
        Alamat Cabang: <input type="text" name="alamat_cabang" value="<?php echo $update_data['alamat_cabang']; ?>"><br><br>
        Nama Manager: <input type="text" name="nama_manager" value="<?php echo $update_data['nama_manager']; ?>"><br><br>
        <?php if(isset($_POST['update'])) { ?>
            <input type="hidden" name="id" value="<?php echo $_POST['id']; ?>">
            <input type="submit" name="submit_update" value="Update">
        <?php } else { ?>
            <input type="submit" value="Submit">
        <?php } ?>
    </form>

    <?php
    // Menampilkan data cabang
    $conn = OpenCon();
    $sql = "SELECT * FROM cabang";
    $result = $conn->query($sql);

    echo "<h2>Data Cabang</h2>";
    if ($result->num_rows > 0) {
        echo "<table border='1'>";
        echo "<tr><th>Nama Cabang</th><th>Alamat Cabang</th><th>Nama Manager</th><th>Update</th><th>Delete</th></tr>";
        while($row = $result->fetch_assoc()) {
            echo "<tr><td>".$row["nama_cabang"]."</td><td>".$row["alamat_cabang"]."</td><td>".$row["nama_manager"]."</td>";
            echo "<td>
                    <form method='post' action='cabang.php'>
                        <input type='hidden' name='id' value='".$row["id_cabang"]."'>
                        <input type='submit' name='update' value='Update'>
                    </form>
                    <td>
                    <form method='post' action='cabang.php' onsubmit=\"return confirm('Apakah Anda yakin ingin menghapus data ini?');\">
                        <input type='hidden' name='id' value='".$row["id_cabang"]."'>
                        <input type='submit' name='delete' value='Delete'>
                    </form>
                    </td>
                </td></tr>";
        }
        echo "</table>";
    } else {
        echo "Tidak ada data cabang.";
    }
    CloseCon($conn);
    ?>
</body>
</html>
