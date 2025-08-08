<?php 
$tabela = 'receber';
require_once("../../../conexao.php");

@session_start();
$id_usuario = @$_SESSION['id'];

$hora = date('H');

if ($hora < 12 && $hora >= 6)
	$saudacao = "Bom Dia";

if ($hora >= 12 && $hora < 18)
	$saudacao = "Boa Tarde";

if ($hora >= 18 && $hora <= 23)
	$saudacao = "Boa Noite";

if ($hora < 6 && $hora >= 0)
	$saudacao = "Boa madrugada";


// Recebimento dos dados
$descricao = $_POST['descricao'] ?? '';
$valor = $_POST['valor'] ?? '';
$cliente = $_POST['cliente'] ?? '';
$vencimento = $_POST['vencimento'] ?? '';
$data_pgto = $_POST['data_pgto'] ?? '';
$forma_pgto = $_POST['forma_pgto'] ?? '';
$frequencia = $_POST['frequencia'] ?? 0;
$obs = $_POST['obs'] ?? '';
$id = $_POST['id'] ?? '';

// Tratamento do valor
$valor = str_replace(',', '.', $valor);
$valorF = @number_format($valor, 2, ',', '.');

// Ajuste de campos vazios
$cliente = $cliente ?: 0;
$forma_pgto = $forma_pgto ?: 0;
$frequencia = $frequencia ?: 0;

// Verifica pagamento
if($data_pgto == ""){
	$pgto = '';
	$usu_pgto = '';
	$pago = 'Não';
}else{
	$pgto = " ,data_pgto = '$data_pgto'";
	$usu_pgto = " ,usuario_pgto = '$id_usuario'";
	$pago = 'Sim';
}

// Validação: precisa de descrição ou cliente
if($descricao == "" && $cliente == 0){
	echo 'Selecione um Cliente ou uma Descrição!';
	exit();
}

// Busca dados do cliente
$nome_cliente = 'Sem Registro';
$telefone_cliente = '';
if($cliente > 0) {
    $query2 = $pdo->prepare("SELECT nome, telefone FROM clientes WHERE id = ?");
    $query2->execute([$cliente]);
    $res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
    if(count($res2) > 0){
        $nome_cliente = $res2[0]['nome'];
        $telefone_cliente = $res2[0]['telefone'];
    }
}

// Primeiro nome para saudação
$primeiroNome = explode(" ", $nome_cliente);
$primeiroNome = $primeiroNome[0] ?? $nome_cliente;

// Define descrição padrão
if($descricao == ""){
	$descricao = $nome_cliente;
}

// Busca dados atuais (para edição)
$foto = 'sem-foto.png';
$vencimento_antiga = '';
if($id != "") {
    $query = $pdo->prepare("SELECT arquivo, vencimento, hash FROM $tabela WHERE id = ?");
    $query->execute([$id]);
    $res = $query->fetchAll(PDO::FETCH_ASSOC);
    if(count($res) > 0){
        $foto = $res[0]['arquivo'];
        $vencimento_antiga = $res[0]['vencimento'];
    }
}

// Upload de arquivo
if(isset($_FILES['foto']) && $_FILES['foto']['name'] != ""){
	$nome_img = date('d-m-Y H:i:s') . '-' . $_FILES['foto']['name'];
	$nome_img = preg_replace('/[ :]+/', '-', $nome_img);
	$caminho = '../../images/contas/' . $nome_img;
	$imagem_temp = $_FILES['foto']['tmp_name'];

	$ext = strtolower(pathinfo($nome_img, PATHINFO_EXTENSION));   
	$extensoes_permitidas = ['png','jpg','jpeg','gif','pdf','rar','zip','doc','docx','webp','xlsx','xlsm','xls','xml'];
	
	if(in_array($ext, $extensoes_permitidas)){ 
	
		if($foto != "sem-foto.png"){
			@unlink('../../images/contas/'.$foto);
		}

		$foto = $nome_img;

		// Verifica tamanho da imagem
		if(in_array($ext, ['png','jpg','jpeg','gif','webp'])) {
			list($largura, $altura) = @getimagesize($imagem_temp);
			if ($largura > 1400) {
				echo 'Diminua a imagem para no máximo 1400px de largura!';
				exit();
			}
		}

		if(!move_uploaded_file($imagem_temp, $caminho)) {
			echo 'Erro ao salvar o arquivo.';
			exit();
		}

	} else {
		echo 'Extensão de arquivo não permitida!';
		exit();
	}
}

// Verifica caixa aberto
$id_caixa = 0;
$query1 = $pdo->prepare("SELECT id FROM caixas WHERE operador = ? AND data_fechamento IS NULL ORDER BY id DESC LIMIT 1");
$query1->execute([$id_usuario]);
$res1 = $query1->fetchAll(PDO::FETCH_ASSOC);
if(count($res1) > 0){
	$id_caixa = $res1[0]['id'];
}

// Prepara a query de INSERT ou UPDATE
if($id == ""){
    // NOVO REGISTRO
    $query = $pdo->prepare("INSERT INTO $tabela SET 
        descricao = ?, 
        cliente = ?, 
        valor = ?, 
        vencimento = ?, 
        data_lanc = CURDATE(), 
        forma_pgto = ?, 
        frequencia = ?, 
        obs = ?, 
        arquivo = ?, 
        foto = ?, 
        subtotal = ?, 
        usuario_lanc = ?, 
        pago = ?, 
        referencia = 'Conta', 
        caixa = ?, 
        hora = CURTIME() 
        $pgto $usu_pgto"
    );

    $params = [
        $descricao, $cliente, $valor, $vencimento, $forma_pgto, $frequencia, $obs, $foto, $foto, $valor, $id_usuario, $pago, $id_caixa
    ];

} else {
    // ATUALIZAÇÃO
    $query = $pdo->prepare("UPDATE $tabela SET 
        descricao = ?, 
        cliente = ?, 
        valor = ?, 
        vencimento = ?, 
        forma_pgto = ?, 
        frequencia = ?, 
        obs = ?, 
        arquivo = ?, 
        foto = ?, 
        subtotal = ? 
        $pgto $usu_pgto 
        WHERE id = ?"
    );

    $params = [
        $descricao, $cliente, $valor, $vencimento, $forma_pgto, $frequencia, $obs, $foto, $foto, $valor, $id, $id_usuario
    ];

    // Se o vencimento mudou, cancela o agendamento antigo
    if ($vencimento_antiga != $vencimento && $api_whatsapp != 'Não' && $telefone_cliente != '') {
        $query4 = $pdo->prepare("SELECT hash FROM $tabela WHERE id = ?");
        $query4->execute([$id]);
        $res4 = $query4->fetchAll(PDO::FETCH_ASSOC);
        $hash = $res4[0]['hash'] ?? '';

        if ($hash != '') {
            require("../../apis/cancelar_agendamento.php");
        }

        $vencimentoF = implode('/', array_reverse(explode('-', $vencimento)));
        $telefone_envio = '55' . preg_replace('/[ ()-]+/', '', $telefone_cliente);

        $mensagem_whatsapp = '🔔_Lembrete Automático de Vencimento!_ %0A%0A';
        $mensagem_whatsapp .= $saudacao . ' *' . $primeiroNome . '* tudo bem? 😀%0A%0A';
        $mensagem_whatsapp .= '_Queremos lembrar que você tem uma Conta Vencendo_ %0A%0A';
        $mensagem_whatsapp .= 'Empresa: *' . $nome_sistema . '* %0A';
        $mensagem_whatsapp .= 'Nome: *' . $nome_cliente . '* %0A';
        $mensagem_whatsapp .= 'Valor: *R$ ' . $valorF . '* %0A';
        $mensagem_whatsapp .= 'Data de Vencimento: *' . $vencimentoF . '* %0A%0A';
        $mensagem_whatsapp .= '_Entre em contato conosco para acertar o pagamento!_ %0A%0A';

        if ($dados_pagamento != "") {
            $mensagem_whatsapp .= '*Dados para o Pagamento:* %0A';
            $mensagem_whatsapp .= $dados_pagamento;
        }

        $mensagem_whatsapp .= '%0A';
        $mensagem_whatsapp .= '🤖 _Esta é uma mensagem automática!_';

        $data_agd = $vencimento . ' 09:00:00';
        require('../../apis/agendar.php');

        $pdo->query("UPDATE $tabela SET hash = '$hash' WHERE id = '$id'");
    }
}

// Executa a query
$query->execute($params);
$ultimo_id = $pdo->lastInsertId();

// WhatsApp para a conta nova (não paga)
if($api_whatsapp != 'Não' && $telefone_cliente != '' && $id == "" && $data_pgto == ""){
    $telefone_envio = '55' . preg_replace('/[ ()-]+/', '', $telefone_cliente);
    $vencimentoF = implode('/', array_reverse(explode('-', $vencimento)));

    $mensagem_whatsapp = '🔔_Lembrete Automático de Vencimento!_ %0A%0A';
    $mensagem_whatsapp .= $saudacao . ' *' . $primeiroNome . '* tudo bem? 😀%0A%0A';
    $mensagem_whatsapp .= '_Seu vencimento está marcado para_ *' . $vencimentoF . '* %0A%0A';
    $mensagem_whatsapp .= 'Descrição: *' . $descricao . '* %0A';
    $mensagem_whatsapp .= 'Valor: *R$ ' . $valorF . '* %0A%0A';
    
    if($dados_pagamento != ""){
        $mensagem_whatsapp .= '*Dados para o Pagamento:* %0A';
        $mensagem_whatsapp .= $dados_pagamento;
    }

    $mensagem_whatsapp .= '%0A';
    $mensagem_whatsapp .= '🤖 _Mensagem automática. Não responda._';

    $data_agd = $vencimento . ' 09:00:00';
    require('../../apis/agendar.php');

    if($ultimo_id) {
        $pdo->query("UPDATE $tabela SET hash = '$hash' WHERE id = '$ultimo_id'");
    }
}

echo 'Salvo com Sucesso';
