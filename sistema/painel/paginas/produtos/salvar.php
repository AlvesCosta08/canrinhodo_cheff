<?php 
require_once("../../../conexao.php");
$tabela = 'produtos';

$id = $_POST['id'];
$nome = $_POST['nome'];
$valor_compra = $_POST['valor_compra'];
$valor_compra = str_replace(',', '.', $valor_compra);
$valor_venda = $_POST['valor_venda'];

$descricao = $_POST['descricao'];
$nivel_estoque = $_POST['nivel_estoque'];
$tem_estoque = $_POST['tem_estoque'];
$promocao = @$_POST['promocao'];
$combo = @$_POST['combo'];
$preparado = @$_POST['preparado'];
$delivery = @$_POST['delivery'];
$categoria = @$_POST['categoria'];
$val_promocional = @$_POST['val_promocional'];
$val_promocional = str_replace(',', '.', $val_promocional);



if($promocao == 'Sim' and $val_promocional <= 0){
	echo 'Você precisa adicionar um valor Promocional ou desativar o campo Pormoção!';
	die;
}

$valor_venda = str_replace(',', '.', $valor_venda);

$nome_novo = strtolower( preg_replace("[^a-zA-Z0-9-]", "-", 
        strtr(@utf8_decode(trim($nome)), @utf8_decode("áàãâéêíóôõúüñçÁÀÃÂÉÊÍÓÔÕÚÜÑÇ"),
        "aaaaeeiooouuncAAAAEEIOOOUUNC-")) );
$url = preg_replace('/[ -]+/' , '-' , $nome_novo);
$url = str_replace('+', '-', $url);
$url = str_replace('/', '-', $url);
$url = str_replace('"', '-', $url);
$url = str_replace('%', '', $url);


if($categoria == 0 || $categoria == ""){
	echo 'Cadastre uma Categoria de Produtos para o Produto';
	exit();
}



//validar nome
$query = $pdo->query("SELECT * from $tabela where nome = '$nome'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
if(@count($res) > 0 and $id != $res[0]['id']){
	echo 'Produto já Cadastrado, escolha outro nome!!';
	exit();
}




//validar troca da foto
$query = $pdo->query("SELECT * FROM $tabela where id = '$id'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
if($total_reg > 0){
	$foto = $res[0]['foto'];
}else{
	$foto = 'sem-foto.jpg';
}


//SCRIPT PARA SUBIR FOTO NO SERVIDOR
$nome_img = date('d-m-Y H:i:s') .'-'.@$_FILES['foto']['name'];
$nome_img = preg_replace('/[ :]+/' , '-' , $nome_img);

$caminho = '../../images/produtos/' .$nome_img;

$imagem_temp = @$_FILES['foto']['tmp_name']; 

if(@$_FILES['foto']['name'] != ""){
	$ext = pathinfo($nome_img, PATHINFO_EXTENSION);   
	if($ext == 'png' or $ext == 'jpg' or $ext == 'jpeg' or $ext == 'gif' or $ext == 'pdf' or $ext == 'rar' or $ext == 'zip' or $ext == 'doc' or $ext == 'docx' or $ext == 'webp' or $ext == 'PNG' or $ext == 'JPG' or $ext == 'JPEG' or $ext == 'GIF' or $ext == 'PDF' or $ext == 'RAR' or $ext == 'ZIP' or $ext == 'DOC' or $ext == 'DOCX' or $ext == 'WEBP' or $ext == 'xlsx' or $ext == 'xlsm' or $ext == 'xls' or $ext == 'xml'){ 
	
			//EXCLUO A FOTO ANTERIOR
			if($foto != "sem-foto.jpg"){
				@unlink('../../images/produtos/'.$foto);
			}

			$foto = $nome_img;

		//pegar o tamanho da imagem
		list($largura, $altura) = getimagesize($imagem_temp);
		if ($largura > 1400) {
			echo 'Diminua a imagem para um tamanho de no máximo 1400px de largura!';
			exit();
		} else {
			move_uploaded_file($imagem_temp, $caminho);
		}
	}else{
		echo 'Extensão de Imagem não permitida!';
		exit();
	}
}




if($id == ""){
	$query = $pdo->prepare("INSERT INTO $tabela SET nome = :nome, categoria = '$categoria', valor_compra = :valor_compra, valor_venda = :valor_venda, descricao = :descricao, foto = '$foto', nivel_estoque = '$nivel_estoque', tem_estoque = '$tem_estoque', ativo = 'Sim', url = '$url', promocao = '$promocao', combo = '$combo', preparado = '$preparado', delivery = '$delivery', val_promocional = :val_promocional");
}else{
	$query = $pdo->prepare("UPDATE $tabela SET nome = :nome, categoria = '$categoria', valor_compra = :valor_compra, valor_venda = :valor_venda, descricao = :descricao, foto = '$foto', nivel_estoque = '$nivel_estoque', tem_estoque = '$tem_estoque', url = '$url', promocao = '$promocao', combo = '$combo', preparado = '$preparado', delivery = '$delivery', val_promocional = :val_promocional WHERE id = '$id'");
}

$query->bindValue(":nome", "$nome");
$query->bindValue(":valor_venda", "$valor_venda");
$query->bindValue(":valor_compra", "$valor_compra");
$query->bindValue(":descricao", "$descricao");
$query->bindValue(":val_promocional", "$val_promocional");
$query->execute();

echo 'Salvo com Sucesso';

?>