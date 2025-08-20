<?php
@session_start();
require_once("cabecalho.php");

$id_mesa = @$_POST['id_mesa'];
$pedido_balcao = @$_POST['pedido_balcao'];

$url_instagram = 'https://www.instagram.com/' . $instagram_sistema . '/';

// ========================
// CONTROLE DE SESSÃO
// ========================
if ($pedido_balcao != "") {
    unset($_SESSION['id_mesa'], $_SESSION['nome_mesa'], $_SESSION['id_ab_mesa'], $_SESSION['sessao_usuario'], $_SESSION['id_edicao']);
    $_SESSION['pedido_balcao'] = $pedido_balcao;
}

@$sessao_balcao = $_SESSION['pedido_balcao'];

if ($sessao_balcao != '') {
    $nome_sistema = 'Pedido Balcão';
}

if ($id_mesa != "") {
    $_SESSION['id_mesa'] = $id_mesa;
    unset($_SESSION['id_edicao']);
}

if (@$_SESSION['id_mesa'] != "") {
    $id_mesa = $_SESSION['id_mesa'];
    unset($_SESSION['id_edicao']);
}

// Buscar informações da edição do pedido
$id_edicao = @$_POST['id_edicao'];

if ($id_edicao != "") {
    $_SESSION['id_edicao'] = $id_edicao;
    unset($_SESSION['id_mesa'], $_SESSION['nome_mesa'], $_SESSION['id_ab_mesa'], $_SESSION['sessao_usuario']);
}

if (@$_SESSION['id_edicao'] != "") {
    $id_edicao = $_SESSION['id_edicao'];
    unset($_SESSION['id_mesa'], $_SESSION['nome_mesa'], $_SESSION['id_ab_mesa'], $_SESSION['sessao_usuario']);
}

// Buscar dados da mesa
$query2 = $pdo->query("SELECT * FROM mesas WHERE id = '$id_mesa'");
$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
$nome_mesa = 'Mesa: ' . @$res2[0]['nome'];

if (@$res2[0]['nome'] == "") {
    $_SESSION['nome_mesa'] = '';
} else {
    $_SESSION['nome_mesa'] = $nome_mesa;
}

$query2 = $pdo->query("SELECT * FROM abertura_mesa WHERE mesa = '$id_mesa' AND status = 'Aberta'");
$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
$id_ab_mesa = @$res2[0]['id'];
$_SESSION['id_ab_mesa'] = $id_ab_mesa;


// ========================
// EXIBIR TODAS AS CATEGORIAS (SEM LIMITE DE HORÁRIO)
// ========================
$query = $pdo->query("SELECT * FROM categorias WHERE ativo = 'Sim' ORDER BY nome");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_cat = count($res);

// ========================
// STATUS DO ESTABELECIMENTO
// ========================
$img = 'aberto.png';

if ($status_estabelecimento == "Fechado" && $id_mesa == "" && $sessao_balcao == "") {
    $img = 'fechado.png';
}

if ($id_mesa == "" && $sessao_balcao == "") {
    $data = date('Y-m-d');
    $diasemana = ["Domingo", "Segunda-Feira", "Terça-Feira", "Quarta-Feira", "Quinta-Feira", "Sexta-Feira", "Sábado"];
    $diasemana_numero = date('w', strtotime($data));
    $dia_procurado = $diasemana[$diasemana_numero];

    $query_dias = $pdo->query("SELECT * FROM dias WHERE dia = '$dia_procurado'");
    $res_dias = $query_dias->fetchAll(PDO::FETCH_ASSOC);
    if (@count($res_dias) > 0) {
        $img = 'fechado.png';
    }

    $start = strtotime(date('Y-m-d') . ' ' . $horario_abertura);
    $end = strtotime(date('Y-m-d') . ' ' . $horario_fechamento);
    $now = time();

    if (!($start <= $now && $now <= $end)) {
        if ($end < $start) {
            if (!($now > $start || $now < $end)) {
                $img = 'fechado.png';
            }
        } else {
            $img = 'fechado.png';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $nome_sistema ?></title>

    <!-- Favicon -->
    <link rel="icon" href="sistema/img/<?php echo $logo_sistema ?>" type="image/x-icon">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Estilos Personalizados -->
    <style>
        :root {
            --primary: #FEA116;
            --dark: #1a1a1a;
            --light: #f8f9fa;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f5f5;
            color: #333;
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }

        /* Navbar */
        .navbar {
            padding: 0.8rem 1rem;
            background: white !important;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar-brand {
            font-weight: 600;
            color: #333;
        }

        .navbar-brand img {
            margin-right: 8px;
        }

        /* Scroll to Top */
        .scroll-top {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 50px;
            height: 50px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s;
            z-index: 1000;
        }

        .scroll-top.show {
            opacity: 1;
            visibility: visible;
        }

        /* Campo de Busca */
        .search-container {
            padding: 16px;
            margin: 0;
        }

        .search-container input {
            width: 100%;
            padding: 14px 50px 14px 20px;
            border: none;
            border-radius: 12px;
            outline: none;
            background: white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            font-size: 0.95rem;
            transition: box-shadow 0.3s;
        }

        .search-container input:focus {
            box-shadow: 0 4px 15px rgba(254, 161, 22, 0.2);
        }

        .search-container i {
            position: absolute;
            right: 30px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary);
        }

        /* Área de busca dinâmica */
        .area-busca {
            margin: 0 16px 16px;
            max-height: 300px;
            overflow-y: auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            display: none;
            position: relative;
            z-index: 999;
        }

        .area-busca-item {
            padding: 12px 16px;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .area-busca-item img {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            object-fit: cover;
        }

        /* Cards de Categoria */
        .card-category-mobile {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            transition: transform 0.2s, box-shadow 0.2s;
            height: 180px;
            display: flex;
            flex-direction: column;
        }

        .card-category-mobile:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        }

        .category-img-mobile {
            height: 110px;
            background-size: cover;
            background-position: center;
            border-top-left-radius: 16px;
            border-top-right-radius: 16px;
            background-color: #f8f9fa;
        }

        .category-info-mobile {
            padding: 8px 10px;
            text-align: center;
        }

        .category-info-mobile h6 {
            font-size: 0.95rem;
            font-weight: 600;
            color: #333;
            margin: 0 0 4px 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .category-info-mobile small {
            font-size: 0.8rem;
        }

        /* Mensagem Fora de Horário */
        .mensagem-fora-horario-mobile {
            background: #fff8f0;
            border-radius: 12px;
            padding: 18px;
            margin: 20px 16px;
            max-width: 350px;
            margin-left: auto;
            margin-right: auto;
            text-align: center;
            border: 1px solid #ffe0c0;
            font-size: 0.95rem;
        }

        .mensagem-fora-horario-mobile h5 {
            margin: 0 0 8px 0;
            color: #d97706;
        }

        .mensagem-fora-horario-mobile p {
            margin: 5px 0;
            color: #555;
            line-height: 1.5;
        }

        /* Rodapé */
        .footer {
            background: #1a1a1a;
            color: white;
            padding: 18px 16px;
            margin-top: 40px;
            text-align: center;
            font-size: 0.9rem;
        }

        .footer a {
            color: #ddd;
            text-decoration: none;
            margin: 0 8px;
        }

        .footer a:hover {
            color: var(--primary);
        }

        /* Ícone de status */
        .img-aberto {
            position: fixed;
            bottom: 20px;
            left: 20px;
            width: 70px;
            z-index: 1000;
            animation: fadeInUp 1s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>

<!-- Scroll to Top -->
<button class="scroll-top"><i class="fas fa-arrow-up"></i></button>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg bg-white shadow-sm">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="index.php">
            <img src="sistema/img/<?php echo $logo_sistema ?>" alt="Logo" width="36">
            <span><?php echo $nome_sistema ?></span>
        </a>
        <?php require_once("icone-carrinho.php") ?>
    </div>
</nav>

<!-- Main Content -->
<div class="main-container" style="padding-bottom: 80px;">

    <!-- Campo de Busca -->
    <div class="search-container position-relative">
        <input 
            type="text" 
            id="buscar" 
            placeholder="Buscar produto..." 
            onkeyup="buscarProduto()"
        >
        <i class="fas fa-search"></i>
    </div>

    <!-- Resultados da Busca -->
    <div id="area_busca" class="area-busca"></div>

    <!-- Mensagem Fora do Horário -->
    <?php if ($total_cat == 0 && $id_mesa == "" && $sessao_balcao == ""): ?>
        <div class="text-center p-4">
            <!--<img src="img/fechado.png" alt="Estabelecimento fechado" width="80" class="mb-3">-->
            <div class="mensagem-fora-horario-mobile">
                <h5><i class="fas fa-clock"></i> Fora do Horário</h5>
                <p>
                    <strong>🌅 Manhã:</strong> 06:30 - 10:00<br>
                    <strong>🌙 Noite:</strong> 19:00 - 22:30
                </p>
                <p>Volte mais tarde! Agradecemos ❤️</p>
            </div>
        </div>
    <?php else: ?>

        <!-- Seção de Categorias -->
        <div class="px-3 pb-5">
            <div class="text-center mb-4">
                <h5 class="fw-bold" style="color: #333;">Nossas Categorias</h5>
                <p class="text-muted" style="font-size: 0.9rem;">Toque para ver os produtos</p>
            </div>

            <div class="row g-3">
                <?php foreach ($res as $item):
                    $id = $item['id'];
                    $foto = $item['foto'];
                    $nome = $item['nome'];
                    $url = $item['url'];
                    $delivery = $item['delivery'];
                    $mais_sabores = $item['mais_sabores'];

                    if ($id_mesa == "" && $delivery == 'Não' && $sessao_balcao == '') continue;

                    $query_prod = $pdo->prepare("SELECT * FROM produtos WHERE categoria = ? AND ativo = 'Sim'");
                    $query_prod->execute([$id]);
                    $tem_produto = $query_prod->rowCount();

                    $link_cat = $mais_sabores == 'Sim' ? "categoria-sabores-" . $url : "categoria-" . $url;
                    $link_cat = $tem_produto ? $link_cat : '#';
                ?>
                    <div class="col-6">
                        <a href="<?php echo $link_cat; ?>" class="text-decoration-none">
                            <div class="card-category-mobile">
                                <div class="category-img-mobile"
                                     style="background-image: url('sistema/painel/images/categorias/<?php echo $foto; ?>');">
                                </div>
                                <div class="category-info-mobile">
                                    <h6><?php echo $nome; ?></h6>
                                    <?php if ($tem_produto): ?>
                                        <small style="color: #00b894;">✅ <?php echo $tem_produto; ?> itens</small>
                                    <?php else: ?>
                                        <small style="color: #d63031;"><i class="fas fa-exclamation-triangle"></i> Esgotado</small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    <?php endif; ?>

</div>

<!-- Rodapé -->
<?php if ($id_mesa == "" && $sessao_balcao == ""): ?>
    <footer class="footer">
        <p><?php echo $nome_sistema ?></p>
        <div>
            <a href="http://api.whatsapp.com/send?1=pt_BR&phone=<?php echo $tel_whats ?>" target="_blank">
                <i class="fab fa-whatsapp"></i> <?php echo $telefone_sistema ?>
            </a>
            <span> | </span>
            <a href="<?php echo $url_instagram ?>" target="_blank">
                <i class="fab fa-instagram"></i> @<?php echo $instagram_sistema ?>
            </a>
        <?php if (isset($mostrar_painel) && strtolower(trim($mostrar_painel)) === "sim"): ?>
            <span> | </span>
            <a href="https://codigoquatro.com.br/delivery/sistema" style="color: white;">
                <i class="fas fa-lock text-warning"></i> Painel
            </a>
        <?php endif; ?>

        </div>
        <small>© 2025 - Todos os direitos reservados</small>
    </footer>

    <!-- Ícone de Status -->
    <?php if ($img == "aberto.png" || @$mostrar_aberto == "Sim"): ?>
        <img src="img/<?php echo $img ?>" class="img-aberto" alt="Status">
    <?php endif; ?>
<?php endif; ?>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Scroll to Top
    const scrollTopBtn = document.querySelector('.scroll-top');
    window.addEventListener('scroll', () => {
        scrollTopBtn.classList.toggle('show', window.scrollY > 300);
    });
    scrollTopBtn.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    // Busca de produtos
    function buscarProduto() {
        const buscar = $('#buscar').val().trim();
        if (buscar === '') {
            $('#area_busca').hide();
            return;
        }

        $.ajax({
            url: 'js/ajax/buscar_produtos.php',
            method: 'POST',
             { buscar: buscar },
            beforeSend: function () {
                $('#area_busca').html('<p class="p-3 text-center text-muted">Buscando...</p>').show();
            },
            success: function (response) {
                $('#area_busca').html(response);
            },
            error: function () {
                $('#area_busca').html('<p class="p-3 text-center text-danger">Erro ao buscar.</p>');
            }
        });
    }
</script>

</body>
</html>