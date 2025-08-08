<?php
require_once("../../../conexao.php");
$tabela = 'produtos';

$categoria = @$_POST['p1'];


if ($categoria != "") {
	$query = $pdo->query("SELECT * from $tabela where categoria = '$categoria' order by id desc");
} else {
	$query = $pdo->query("SELECT * from $tabela order by id desc");
}


$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
if ($total_reg > 0) {

	echo <<<HTML
	<small>
	<table class="table table-hover table-bordered text-nowrap border-bottom dt-responsive" id="tabela">
	<thead>
	<tr>
		<th align="center" width="5%" class="text-center">Selecionar</th>
	<th>Nome</th>	
	<th>Categoria</th>
	<th>Valor Venda</th>
	<th>Estoque</th>
	<th>Delivery</th>
	<th>Preparado</th>
	<th>Ações</th>
	</tr>
	</thead>
	<tbody>
HTML;

	for ($i = 0; $i < $total_reg; $i++) {
		foreach ($res[$i] as $key => $value) {
		}
		$id = $res[$i]['id'];
		$nome = $res[$i]['nome'];
		$descricao = $res[$i]['descricao'];
		$categoria = $res[$i]['categoria'];
		$valor_compra = $res[$i]['valor_compra'];
		$valor_venda = $res[$i]['valor_venda'];
		$foto = $res[$i]['foto'];
		$estoque = $res[$i]['estoque'];
		$nivel_estoque = $res[$i]['nivel_estoque'];
		$tem_estoque = $res[$i]['tem_estoque'];
		$ativo = $res[$i]['ativo'];
		$combo = $res[$i]['combo'];
		$promocao = $res[$i]['promocao'];
		$preparado = $res[$i]['preparado'];
		$delivery = $res[$i]['delivery'];
	
		$val_promocional = $res[$i]['val_promocional'];

		$nomeF = mb_strimwidth($nome, 0, 25, "...");

		$valor_vendaF = @number_format($valor_venda, 2, ',', '.');
		$valor_compraF = @number_format($valor_compra, 2, ',', '.');
		$val_promocionalF = @number_format($val_promocional, 2, ',', '.');


		if ($ativo == 'Sim') {
			$icone = 'fa-check-square';
			$titulo_link = 'Desativar Item';
			$acao = 'Não';
			$classe_linha = '';
		} else {
			$icone = 'fa-square-o';
			$titulo_link = 'Ativar Item';
			$acao = 'Sim';
			$classe_linha = '#c4c4c4';
		}


		$query2 = $pdo->query("SELECT * FROM categorias where id = '$categoria'");
		$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
		$total_reg2 = @count($res2);
		if ($total_reg2 > 0) {
			$nome_cat = $res2[0]['nome'];
			$mais_sabores = $res2[0]['mais_sabores'];
		} else {
			$nome_cat = 'Sem Referência!';
			$mais_sabores = '';
		}

		$ocultar_ad = '';
		if ($mais_sabores == 'Sim') {
			$ocultar_ad = 'ocultar';
		}


		if ($nivel_estoque >= $estoque and $tem_estoque == 'Sim') {
			$alerta_estoque = 'text-danger';
		} else {
			$alerta_estoque = '';
		}

		$classe_promo = 'ocultar';
		if ($promocao == "Sim") {
			$classe_promo = '';
		}

		$ocultar_grade = '';
		$ocultar_var = 'ocultar';
		if ($mais_sabores == 'Sim') {
			$ocultar_grade = 'ocultar';
			$ocultar_var = '';
		}

		if ($tem_estoque == 'Não') {
			$mostrar_ent_said = 'ocultar';
		} else {
			$mostrar_ent_said = '';
		}

		$cor = '';

		if ($estoque == 0 and $tem_estoque == 'Sim') {
			$cor = '#ffdddd'; //RED
		} else if ($estoque <= $nivel_estoque and $tem_estoque == 'Sim') {
			$cor = '#fff8dd'; // WARNING
		} else if ($estoque > 0 and $estoque != $nivel_estoque) {
			$cor = '#ddffe1'; //GREEN
		}

		echo <<<HTML
		<input type="text" id="descricao_{$id}" value="{$descricao}" style="display:none">

<tr style="background: {$cor}" class="{$alerta_estoque}">
<td align="center">
<div class="custom-checkbox custom-control">
<input type="checkbox" class="custom-control-input" id="seletor-{$id}" onchange="selecionar('{$id}')">
<label for="seletor-{$id}" class="custom-control-label mt-1 text-dark"></label>
</div>
</td>
<td style="color:{$classe_linha}">
<img class="rounded-circle mr-2" src="images/produtos/{$foto}" width="30px" height="30px">
{$nomeF} <span class="{$classe_promo} text-primary" style="font-size: 10px">(Promoção)</span>
</td>
<td style="color:{$classe_linha}">{$nome_cat}</td>
<td style="color:{$classe_linha}">R$ {$valor_vendaF}</td>
<td style="color:{$classe_linha}">{$estoque}</td>
<td style="color:{$classe_linha}">{$delivery}</td>
<td style="color:{$classe_linha}">{$preparado}</td>
<td>
	<a class="btn btn-info-light btn-sm" href="#" onclick="editar('{$id}','{$nome}', '{$categoria}', '{$valor_compra}', '{$valor_venda}', '{$foto}', '{$nivel_estoque}', '{$tem_estoque}', '{$combo}', '{$promocao}', '{$preparado}', '{$delivery}', '{$val_promocional}')" title="Editar Dados"><i class="fa fa-edit"></i></a>

		<a class="btn btn-primary-light btn-sm" href="#" onclick="mostrar('{$id}','{$nome}', '{$nome_cat}', '{$valor_compraF}',  '{$valor_vendaF}', '{$estoque}', '{$foto}', '{$nivel_estoque}', '{$tem_estoque}', '{$combo}', '{$promocao}', '{$preparado}', '{$delivery}', '{$val_promocionalF}')" title="Ver Dados"><i class="fa fa-info-circle"></i></a>

<big><a href="#" class="btn btn-danger-light btn-sm" onclick="excluir('{$id}')" title="Excluir"><i class="fa fa-trash-can text-danger"></i></a></big>



		<a class="btn btn-success-light btn-sm" href="#" onclick="ativar('{$id}', '{$acao}')" title="{$titulo_link}"><i class="fa {$icone}"></i></a>


		<a class="btn btn-danger-light btn-sm {$mostrar_ent_said}"  href="#" onclick="saida('{$id}','{$nome}', '{$estoque}')" title="Saída de Produto"><i class="fa fa-sign-out"></i></a>

		<a class="btn btn-success-light btn-sm {$mostrar_ent_said}" href="#" onclick="entrada('{$id}','{$nome}', '{$estoque}')" title="Entrada de Produto"><i class="fa fa-sign-in"></i></a>


		<a class="{$ocultar_grade} btn btn-warning-light btn-sm" href="#" onclick="grades('{$id}','{$nome}','{$categoria}')" title="Grade de Produtos"><i class="fa fa-list"></i></a>

		<a class="{$ocultar_var} btn btn-info-light btn-sm" href="#" onclick="variacoes('{$id}','{$nome}','{$categoria}')" title="Variações do Produto"><i class="fa fa-list"></i></a>

		


</td>
</tr>
HTML;
	}

	echo <<<HTML
	</tbody>
	<small><div align="center" id="mensagem-excluir"></div></small>
	</table>
	</small>
HTML;
} else {
	echo 'Não possui registros cadastrados!';
}


?>


<script type="text/javascript">
	$(document).ready(function() {
		$('#tabela').DataTable({
			"ordering": false,
			"stateSave": true
		});
		$('#tabela_filter label input').focus();
	});
</script>




<script type="text/javascript">
	function editar(id, nome, categoria, valor_compra, valor_venda, foto, nivel_estoque, tem_estoque, combo, promocao, preparado, delivery, val_promocional) {

		var descricao = $('#descricao_' + id).val();
		$('#descricao').val(descricao);

		$('#id').val(id);
		$('#nome').val(nome);
		$('#valor_venda').val(valor_venda);
		$('#valor_compra').val(valor_compra);
		$('#categoria').val(categoria).change();
		$('#nivel_estoque').val(nivel_estoque);
		$('#tem_estoque').val(tem_estoque).change();
		$('#combo').val(combo).change();
		$('#promocao').val(promocao).change();
		$('#preparado').val(preparado).change();
		$('#delivery').val(delivery).change();
		$('#val_promocional').val(val_promocional);

		$('#titulo_inserir').text('Editar Registro');
		$('#modalForm').modal('show');
		$('#foto').val('');
		$('#target').attr('src', 'images/produtos/' + foto);
	}

	function limparCampos() {
		$('#id').val('');
		$('#nome').val('');
		$('#valor_compra').val('');
		$('#valor_venda').val('');
		$('#descricao').val('');
		$('#foto').val('');
		$('#val_promocional').val('');
		$('#nivel_estoque').val('');
		$('#combo').val('Não').change();
		$('#tem_estoque').val('Sim').change();
		$('#promocao').val('Não').change();
		$('#preparado').val('Sim').change();
		$('#delivery').val('Sim').change();
		$('#target').attr('src', 'images/produtos/sem-foto.jpg');
		$('#id_it_var').val('');
	}
</script>



<script type="text/javascript">
	function mostrar(id, nome, categoria, valor_compra, valor_venda, estoque, foto, nivel_estoque, tem_estoque, combo, promocao, preparado, delivery, val_promocional) {

		var descricao = $('#descricao_' + id).val();

		$('#nome_dados').text(nome);
		$('#categoria_dados').text(categoria);
		$('#descricao_dados').text(descricao);
		$('#valor_compra_dados').text(valor_compra);
		$('#valor_venda_dados').text(valor_venda);

		$('#estoque_dados').text(estoque);
		$('#nivel_estoque_dados').text(nivel_estoque);
		$('#tem_estoque_dados').text(tem_estoque);



		$('#combo_dados').text(combo);
		$('#promocao_dados').text(promocao);

		$('#preparado_dados').text(preparado);
		$('#delivery_dados').text(delivery);
		$('#val_promocional_dados').text(val_promocional);


		$('#target_mostrar').attr('src', 'images/produtos/' + foto);

		$('#modalDados').modal('show');
	}
</script>





<script type="text/javascript">
	function saida(id, nome, estoque) {

		$('#nome_saida').text(nome);
		$('#estoque_saida').val(estoque);
		$('#id_saida').val(id);

		$('#modalSaida').modal('show');
	}
</script>


<script type="text/javascript">
	function entrada(id, nome, estoque) {

		$('#nome_entrada').text(nome);
		$('#estoque_entrada').val(estoque);
		$('#id_entrada').val(id);

		$('#modalEntrada').modal('show');
	}
</script>


<script type="text/javascript">
	function variacoes(id, nome, cat) {

		$('#titulo_nome_var').text(nome);
		$('#id_var').val(id);

		listarVariacoes(id);
		listarVarCat(cat);
		$('#modalVariacoes').modal('show');
		limparCamposVar();
	}
</script>


<script type="text/javascript">
	function grades(id, nome, cat) {

		$('#titulo_nome_grades').text(nome);
		$('#id_grades').val(id);

		listarGrades(id);

		$('#id_grade_editar').val('');
		$('#btn_grade').text('Salvar');
		$('#modalGrades').modal('show');
		limparCamposGrades();
	}
</script>

