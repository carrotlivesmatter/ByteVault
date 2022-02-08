<?php
session_start();
include 'hfapi.class.php';
$bytes = new bytes();
if(!$_SESSION['logged_in']){
    $bytes->login();
}
$bytes->setAccessToken($_SESSION['access_token']);
if (isset($_POST["depositAmount"])) {
    $amount = $_POST['depositAmount'];
    $bytes->vaultDeposit($amount);
}

if (isset($_POST["withdrawAmount"])) {
    $amount = $_POST['withdrawAmount'];
    $bytes->vaultWithdraw($amount);
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>ByteVault</title>

    <link href="https://fonts.googleapis.com/css2?family=Roboto+Condensed:wght@300;700&family=Rouge+Script&display=swap"
          rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet"/>
    <style>
        .logo {
            font-family: 'Roboto Condensed', sans-serif;
            font-size: 40px;
            font-weight: 700;

        }

        body {
            font-family: 'Roboto Condensed', sans-serif;
            font-weight: 400;
        }

        .vault_balance {
            font-family: 'Roboto Condensed', sans-serif;
            font-size: 50px;
            font-weight: 700;
            text-align: center !important;
            color: #6c757d !important
        }

        @media (max-width: 767px) {
            .logo {
                margin: 0 auto;
            }
        }
    </style>
</head>
<body id="home">
<main>
    <div class="container">
        <header class="d-flex flex-wrap align-items-center justify-content-center justify-content-md-between py-3 mb-4 border-bottom">
            <a href="/" class="d-flex align-items-center col-md-6 mb-2 mb-md-0 text-dark text-decoration-none">
                <span class="logo text-secondary text-sm-center"><i class="fas fa-piggy-bank"></i> BYTEVAULT</span>
            </a>

            <div class="navbar-text">
                <span>Logged in as <strong><?=$_SESSION['data']['me']['username'];?></strong></span>
            </div>
        </header>
    </div>
    <div class="container">
        <div class="card">
            <div class="card-header text-center">
                Vault Byte Balance
            </div>
            <div class="card-body vault_balance">
                <?php echo $bytes->vaultBalance();?>
            </div>
            <div class="card-footer">
                <div class="btn-group btn-block" role="group" aria-label="Basic example">
                    <button type="button" class="btn btn-outline-dark" data-toggle="modal" data-target="#vaultDeposit">
                        Deposit
                    </button>
                    <button type="button" class="btn btn-outline-dark" data-toggle="modal" data-target="#vaultWithdraw">
                        Withdraw
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="vaultWithdraw" data-backdrop="static" data-keyboard="false" tabindex="-1"
         aria-labelledby="vaultWithdrawLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">Withdraw from Personal Vault</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true"></span><i class="fas fa-times-circle"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="index.php" id="vault_withdraw" method="post" accept-charset="utf-8">
                        <div class="form-group">
                            <label for="withdrawAmount">Amount of bytes to Withdraw:</label>
                            <input type="number" class="form-control" name="withdrawAmount" id="withdrawAmount">
                            <small>Minimum amount: 100 bytes</small>
                        </div>
                </div>
                <div class="modal-footer">
                    <input class="btn btn-block btn-outline-dark btn-round shadow-none" type="submit" value="Withdraw"/>
                </div>
            </div>
        </div>
        </form>
    </div>

    <div class="modal fade" id="vaultDeposit" data-backdrop="static" data-keyboard="false" tabindex="-1"
         aria-labelledby="vaultDepositLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">Deposit to Personal Vault</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true"></span><i class="fas fa-times-circle"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="index.php" id="vault_deposit" method="post" accept-charset="utf-8">
                        <div class="form-group">
                            <label for="depositAmount">Amount of bytes to Deposit:</label>
                            <input type="number" class="form-control" name="depositAmount" id="depositAmount">
                            <small>Minimum amount: 100 bytes</small>
                        </div>
                </div>
                <div class="modal-footer">
                    <input class="btn btn-block btn-outline-dark btn-round shadow-none" type="submit" value="Deposit"/>
                </div>
            </div>
        </div>
        </form>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>