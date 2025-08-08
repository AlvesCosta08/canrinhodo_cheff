<?php
@session_start();
require_once('../../sistema/conexao.php');

$sessao = @$_SESSION['sessao_usuario'];


$id_mesa = "";
if (@$_SESSION['id_ab_mesa'] != "") {
	$id_mesa = $_SESSION['id_ab_mesa'];
}



$nome_produto2 = '';
if ($id_mesa == "") {
	$query = $pdo->query("SELECT * FROM carrinho where sessao = '$sessao'");
} else {
	$query = $pdo->query("SELECT * FROM carrinho where mesa = '$id_mesa'");
}

$id_edicao = "";
if (@$_SESSION['id_edicao'] != "") {
	$id_edicao = $_SESSION['id_edicao'];
}

if($id_edicao != ""){
	$query = $pdo->query("SELECT * FROM carrinho where pedido = '$id_edicao'");
}


$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
$total_carrinho = 0;
$total_carrinhoF = 0;
if ($total_reg > 0) {
	for ($i = 0; $i < $total_reg; $i++) {
		foreach ($res[$i] as $key => $value) {
		}

		$id = $res[$i]['id'];
		$total_item = $res[$i]['total_item'];
		$produto = $res[$i]['produto'];
		$quantidade = $res[$i]['quantidade'];
		$obs = $res[$i]['obs'];
		$item = $res[$i]['item'];
		$variacao = $res[$i]['variacao'];
		$nome_produto_tab = $res[$i]['nome_produto'];
		$sabores = $res[$i]['sabores'];
		$borda = $res[$i]['borda'];
		$categoria = $res[$i]['categoria'];
		$valor_unit = $res[$i]['valor_unitario'];

		if ($valor_unit == "") {
			if ($total_item > 0 and $quantidade > 0) {
				$valor_unit = $total_item / $quantidade;
			} else {
				$valor_unit = 0;
			}
		}

		$total_item = $total_item * $quantidade;
		$total_carrinho += $total_item;


		$total_itemF = number_format($total_item, 2, ',', '.');
		$valor_unitF = number_format($valor_unit, 2, ',', '.');
		$total_carrinhoF = number_format($total_carrinho, 2, ',', '.');

		$query2 = $pdo->query("SELECT * FROM variacoes where id = '$variacao'");
		$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
		if (@count(@$res2) > 0) {
			$sigla_variacao = '(' . $res2[0]['sigla'] . ')';
		} else {
			$sigla_variacao = '';
		}


		$query2 = $pdo->query("SELECT * FROM produtos where id = '$produto'");
		$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
		if (@count(@$res2) > 0) {
			$nome_produto = $res2[0]['nome'];
			$foto_produto = $res2[0]['foto'];
		} else {
			$nome_produto = $nome_produto_tab;
			$foto_produto = "";
		}


		if ($obs == '') {
			$classe_obs = 'text-warning';
		} else {
			$classe_obs = 'text-danger';
		}

		if ($sabores > 0) {
			$nome_produto = $nome_produto_tab;
		}


		if($id_mesa > 0){
			$ocultar_excluir = 'ocultar';
		}else{
			$ocultar_excluir = '';
		}
		



		echo <<<HTML



						<li>
              <div class="tpcart__item" >
                <div class="tpcart__img">
                  <img src="sistema/painel/images/produtos/{$foto_produto}" alt="">
                </div>
                <div class="tpcart__content">
                  <span class="tpcart__content-title"><b>{$nome_produto} {$sigla_variacao}</b>
                  </span>
									
                  <div class="tpcart__cart-price">
                    <span class="quantity">{$quantidade} x</span>
                    <span class="text-success">R$ $total_itemF</span>
                  </div>
                </div>
								<div class="direita">
                    <a href="#" onclick="excluirCarrinhoIcone('{$id}')" class="link-neutro {$ocultar_excluir}"><i class="icon-x-circle text-warning"></i></a>
                  </div>
              </div>
							
            </li>


						




			</li>

HTML;
	}
} else {
	echo 'Nenhum item adicionado!';
}


?>

<script type="text/javascript">
	$("#total-carrinho-icone").text("<?= $total_carrinhoF ?>");
	$("#total-itens-carrinho").text("<?= $total_reg ?>");
	$("#total-carrinho-finalizar").text("<?= $total_carrinhoF ?>");


	function excluirCarrinhoIcone(id) {

		$.ajax({
			url: 'js/ajax/excluir-carrinho.php',
			method: 'POST',
			data: {
				id
			},
			dataType: "text",

			success: function(mensagem) {
				
				if (mensagem.trim() == "Excluido com Sucesso") {
					listarCarrinhoIcone();
				}

			},

		});
	}
</script>