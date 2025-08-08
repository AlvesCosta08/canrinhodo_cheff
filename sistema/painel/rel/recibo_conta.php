<?php
require_once("../../conexao.php");
require_once("../funcoes/extenso.php");

$id = $_GET['id'];

$query = $pdo->query("SELECT * FROM receber WHERE id = '$id'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);

if (@count($res) > 0) {
    $descricao = $res[0]['descricao'];
    $pessoa = $res[0]['cliente'];
    $valor = $res[0]['subtotal'];
    $data_pgto = $res[0]['data_pgto'];

    $nome_pessoa = 'Cliente';

    $query2 = $pdo->query("SELECT * FROM clientes WHERE id = '$pessoa'");
    $res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
    if (@count($res2) > 0) {
        $nome_pessoa = $res2[0]['nome'];
    }

    $valorF = number_format($valor, 2, ',', '.');
    $data_pgtoF = implode('/', array_reverse(explode('-', $data_pgto)));

    $valor_extenso = valor_por_extenso(intval($valor));
    if (fmod($valor, 1) > 0) {
        $centavos = intval(fmod($valor, 1) * 100);
        $valor_extenso .= ' e ' . $centavos . ' centavo' . ($centavos != 1 ? 's' : '');
    }
} else {
    die("Recibo não encontrado.");
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo Ultra Moderno</title>
    <style>
        @page {
            size: 80mm auto;
            margin: 0;
        }

        body {
            width: 76mm;
            margin: 0 auto;
            font-family: 'Helvetica Neue', 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #222;
            background: #fff;
            padding: 10px 12px 18px;
        }

        .center {
            text-align: center;
        }

        .thin {
            font-weight: 300;
            letter-spacing: 0.5px;
        }

        .bold {
            font-weight: 600;
        }

        .small {
            font-size: 10px;
            color: #777;
        }

        .tiny {
            font-size: 9px;
            color: #aaa;
        }

        .logo {
            width: 40px;
            height: auto;
            display: block;
            margin: 0 auto 6px;
            border-radius: 8px;
            opacity: 0.95;
        }

        /* Nome da empresa com estilo premium */
        .brand {
            font-size: 14px;
            font-weight: 500;
            color: #000;
            margin: 2px 0;
            letter-spacing: -0.3px;
        }

        .detail {
            margin: 4px 0;
        }

        .amount {
            font-size: 15px;
            font-weight: 600;
            color: #1a1a1a;
            text-align: center;
            margin: 8px 0 4px;
        }

        .description {
            font-size: 11.5px;
            text-align: center;
            color: #444;
            margin: 5px 0;
        }

        .divider {
            width: 100%;
            height: 1px;
            background: #ddd;
            margin: 10px auto 8px;
            border: 0;
        }

        .signature {
            margin-top: 22px;
            text-align: center;
        }

        .signature-line {
            border-top: 1px dashed #bbb;
            margin: 14px auto 6px;
            width: 60%;
        }

        .footer {
            text-align: center;
            font-size: 9px;
            color: #ccc;
            letter-spacing: 1px;
        }

        .footer strong {
            color: #999;
            font-weight: 500;
        }
    </style>
</head>
<body onload="window.print();">

    <!-- Logo pequeno e centralizado -->
    <img src="<?php echo $url_sistema; ?>sistema/img/logo.jpg" alt="Logo" class="logo">

    <!-- Nome da empresa (estilo premium) -->
    <div class="center brand thin"><?php echo $nome_sistema; ?></div>
    <div class="center small"><?php echo $telefone_sistema; ?></div>

    <!-- Data e número (discreto) -->
    <div class="center tiny">
        #<?php echo str_pad($id, 4, '0', STR_PAD_LEFT); ?> • <?php echo date('d/m H:i'); ?>
    </div>

    <hr class="divider">

    <!-- Valor em destaque -->
    <div class="amount">R$ <?php echo $valorF; ?></div>
    <div class="description"><?php echo $nome_pessoa; ?></div>

    <hr class="divider">

    <!-- Mensagem curta e elegante -->
    <div class="description">
        Pagamento por <?php echo strtolower($descricao); ?>.
    </div>

    <!-- Assinatura minimalista -->
    <div class="signature">
        <div class="signature-line"></div>
        <div class="small">Assinatura</div>
    </div>

    <!-- Rodapé sutil -->
    <div class="footer">
        <strong>✔ Válido como recibo</strong>
    </div>

</body>
</html>