<?php
include '../config.php';
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../");
    exit();
}
$username = $_SESSION['username'];
date_default_timezone_set('Asia/Jakarta');
$time = date('d-m-Y, H:i a');

$data = $conn->query("SELECT * FROM `$username`");
$data = $data->fetch_all();
$data = array_reverse($data);
$saldo = $data[0][3];
// // Menangani proses tambah saldo atau tarik saldo
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
    if ($amount > 0 ) {
        if (isset($_POST['deposit'])) {
            $saldo = $saldo + $amount;
            $conn->query("
                INSERT INTO `$username` 
                (`type`, `amount`, `date`, `saldo`) 
                VALUES 
                ('Deposit', '$amount', '$time', '$saldo');
            ");
            header("Location: index.php");
            exit();

        } elseif (isset($_POST['withdraw']) && ($saldo>=$amount)) {
            $saldo = $saldo - $amount;
            $conn->query("
                INSERT INTO `$username` 
                (`type`, `amount`, `date`, `saldo`) 
                VALUES 
                ('Withdraw', '$amount', '$time', '$saldo');
                ");
            header("Location: index.php");
            exit();
        } else {
            $error = "Saldo tidak cukup!";
        }
    } else {
        $error = "Masukkan jumlah uang yang valid!";
        
    }
    if (isset($_POST['transfer'])){
        $recipient = $_POST['recipient'];
        if ($recipient == $username){
            $error = "Penerima tidak valid";
        }else{
        try {
            $recData = $conn->query("SELECT * FROM `$recipient`");
            $recData = $recData->fetch_all();
            $recData = array_reverse($recData);
            $recSaldo = $recData[0][3];
            // echo ($recSaldo[0][3]);
            echo $saldo.'<br>';
            echo $recSaldo.'<br>';
            //Mengurangi saldo
            $saldo = $saldo - $amount;
            $conn->query("
                INSERT INTO `$username` 
                (`type`, `amount`, `date`, `saldo`) 
                VALUES 
                ('Withdraw ke $recipient', '$amount', '$time', '$saldo');
            ");
            // sleep(0.1);
            //Menambahkan saldo akun tujuan
            $recSaldo = $recSaldo + $amount;
            $conn->query("
                INSERT INTO `$recipient` 
                (`type`, `amount`, `date`, `saldo`) 
                VALUES 
                ('Deposit dari $username', '$amount', '$time', '$recSaldo');
            ");
            echo $saldo.'<br>';
            echo $recSaldo.'<br>';
            header("Location: index.php");
            exit();
        } catch(Exception $e){
            $error = "Penerima tidak valid";
            
        }}
    }
}
unset($_POST);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NANA.bung</title>
    <style>
    /* General reset */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f9;
    color: #333;
}

/* Header styling */
.header {
    background-color: #4CAF50;
    padding: 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.header-link {
    color: white;
    text-decoration: none;
    font-size: 18px;
    font-weight: bold;
}

.header-link.logout {
    font-size: 16px;
    background-color: #e74c3c;
    padding: 5px 15px;
    border-radius: 5px;
    transition: background-color 0.3s ease;
}

.header-link.logout:hover {
    background-color: #c0392b;
}

/* Body: Saldo */
.body_saldo {
    text-align: center;
    margin-top: 30px;
}

.body_saldo h1 {
    color: #333;
    font-size: 24px;
}

.body_saldo h4 {
    color: #666;
    font-style: italic;
}

.body_saldo h3 {
    color: #333;
}

.body_saldo .rupiah {
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 28px;
    color: #27ae60;
    font-weight: bold;
}

.rp {
    margin-right: 5px;
}

.amount {
    font-weight: bold;
}

/* Body: Transaksi & Transfer */
.body_mutasi {
    display: flex;
    justify-content: space-around;
    margin-top: 30px;
    padding: 20px;
    background: #fff;
    border-radius: 10px;
    box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
}

.transaksi, .transfer {
    width: 45%;
    padding: 15px;
    border-radius: 10px;
    background: #f9f9f9;
}

.transaksi_head, .transfer_head {
    font-size: 18px;
    font-weight: bold;
    margin-bottom: 10px;
}

.transaksi input, .transfer input {
    width: 100%;
    padding: 12px;
    font-size: 16px;
    margin-bottom: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
}

button {
    width: 100%;
    padding: 12px;
    font-size: 16px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    margin-top: 10px;
    transition: background-color 0.3s ease;
}


button.deposit, button.transfer {
    background-color: #2ecc71;
}

button.deposit:hover, button.transfer:hover {
    background-color: #27ae60;
}

button.withdraw {
    background-color: #e74c3c;
}

button.withdraw:hover {
    background-color: #c0392b;
}

button[type="submit"]:disabled {
    background-color: #bdc3c7;
    cursor: not-allowed;
}

/* Error message styling */
.error {
    color: red;
    font-size: 14px;
    margin-top: 5px;
}

/* Body: Transaction History */
.body_riwayat {
    margin-top: 50px;
    text-align: center;
}

.riwayat {
    font-size: 22px;
    color: #333;
    font-weight: bold;
    margin-bottom: 20px;
}

table {
    width: 80%;
    margin: 0 auto;
    border-collapse: collapse;
}

table th, table td {
    padding: 12px;
    border: 1px solid #ddd;
    font-size: 16px;
}

table th {
    background-color: #f2f2f2;
    color: #333;
}

table tr:nth-child(even) {
    background-color: #f9f9f9;
}

/* Responsive Design */
@media (max-width: 768px) {
    .body_mutasi {
        flex-direction: column;
        align-items: center;
    }

    .transaksi, .transfer {
        width: 80%;
        margin-bottom: 20px;
    }

    button {
        width: 100%;
    }

    table {
        width: 95%;
    }
}

@media (max-width: 600px) {
    .header-link {
        font-size: 14px;
    }

    .header-link.logout {
        font-size: 14px;
    }
}

    </style>
</head>
<body>
    <div class="header">
        <a class="header-link" href="../">NANAbung</a>
        <a class="header-link logout" href="../logout.php">Log out</a>
    </div>

    <div class="body_saldo">
        <h1>Hai <?php echo $_SESSION['username'] ?></h1>
        <h4>Selamat menabung!</h4>

        <div>
            <h3>Saldo tabungan</h3>
            <h3>Tersedia</h3>
            <div class="rupiah">
            <p class="rp">Rp. </p> <p class="amount"><?php echo number_format($saldo,0,",",".")?></p>
            </div>
        </div>
    </div>

    <div class="body_mutasi">
        <div class="transaksi">
            <p class="transaksi_head">Transaksi</p>
            <form method="post">
            <input type="number" name="amount" placeholder="Masukkan jumlah uang" required>
            <div class="error"><?php echo isset($error) ? $error : ''; ?></div>
            <button type="submit" name="deposit" class="deposit">Tambah</button>
            <button type="submit" name="withdraw" class="withdraw">Tarik</button>
            </form>
        </div>
        <div class="transfer">
            <p class="transfer_head">Transfer</p>
            <form method="post">
            <input type="number" name="amount" placeholder="Masukkan jumlah uang" required>
            <input type="text" name="recipient" placeholder="username tujuan" required>
            <button type="submit" name="transfer">kirim</button>
            </form>
        </div>
    </div>

    <div class="body_riwayat">
        <p class="riwayat">riwayat transaksi</p>
        <table>
            <thead>
                <tr>
                    <th>Transaksi</th>
                    <th>jumlah</th>
                    <th>waktu</th>
                </tr>
            </thead>
            <tbody>
                <?php array_pop($data); foreach ($data as $d):  ?>
                <tr>
                    <th><?php echo $d['0']; ?></th>
                    <th><?php echo number_format($d['1'],0,",","."); ?></th>
                    <th><?php echo $d['2']; ?></th>
                </tr>    
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</body>
</html>