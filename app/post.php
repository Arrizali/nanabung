<?php
include '../config.php';
session_start();

$username = $_SESSION['username'];
date_default_timezone_set('Asia/Jakarta');
$time = date('d F Y H:i a');
echo 'wfwefwef';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
    if ($amount > 0) {
        if (isset($_POST['deposit'])) {
            $conn->query("
                INSERT INTO `$username` 
                (`type`, `amount`, `date`, `saldo`) 
                VALUES 
                ('Deposit', '$amount', '$time', '0');
                ");
                

        } elseif (isset($_POST['withdraw']) && $amount <= $_SESSION['saldo']) {
            $conn->query("
                INSERT INTO `$username` 
                (`type`, `amount`, `date`, `saldo`) 
                VALUES 
                ('Withdraw', '$amount', '$time', '0');
                ");
        } else {
            $error = "Saldo tidak cukup!";
        }
    } else {
        $error = "Masukkan jumlah uang yang valid!";
    }
}
exit();