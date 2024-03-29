<?php
	ini_set('display_errors', true);
	error_reporting(E_ALL);
	use SIAC\Sessao;
	use SIAC\Paginacao;
	
	$oSessao = new Sessao();
	
	if(!$oSessao->estaLogado()){	
		$oSessao->efetuarLogout();
		header("Location: ../models/AreaRestrita.php");
		exit();
	}	
	
	$iIdUsuario = $oSessao->getVariavelSessao("iIdUsuario");
	$sNivelAcesso = $oSessao->getVariavelSessao("nivelAcesso");
	$sNomeUsuario = $oSessao->getVariavelSessao("sNomeUsuario");
	
	$opcao = @$_GET['opcao'];

	switch($opcao){
		case 0://LISTAR TODOS AS DISCIPLINAS
			$sConsulta = "
				SELECT
					iddisciplina, nome, curso_idcurso, nome_curso
				FROM
					disciplina INNER JOIN curso 
					ON disciplina.curso_idcurso = curso.idcurso
				ORDER BY
					iddisciplina ASC
				LIMIT";
			
			$sConsultaQtdRegistros = "
				SELECT COUNT(*) 
				FROM 
					disciplina INNER JOIN curso 
					ON disciplina.curso_idcurso = curso.idcurso";
			break;
			
		case 1://LISTAR RESULTADO DA BUSCA
			$sQuery = $_GET['campoBusca'];
			$parametro = $_GET['radioParametro'];
			if($parametro == 1) $sQuery = "iddisciplina = ".$sQuery;
			if($parametro == 2) $sQuery = "nome LIKE '%".$sQuery."%'";
			if($parametro == 3){
				if(is_numeric($sQuery)) $sQuery = "curso_idcurso = ".$sQuery;
				else $sQuery = "curso.nome_curso LIKE '%".$sQuery."%'";
			}
			$sConsulta = "
				SELECT
					iddisciplina, nome, curso_idcurso, nome_curso
				FROM
					disciplina INNER JOIN curso 
					ON disciplina.curso_idcurso = curso.idcurso
				WHERE
					".$sQuery."
				ORDER BY
					iddisciplina ASC
				LIMIT";
			
			$sConsultaQtdRegistros = "
				SELECT COUNT(*) 
				FROM 
					disciplina INNER JOIN curso 
					ON disciplina.curso_idcurso = curso.idcurso
				WHERE
					".$sQuery;
			break;
	}
	
	$oPaginacao = new Paginacao(10,$sConsulta,$sConsultaQtdRegistros);
	
	if(!@$pagina = $_GET['pagina']){
		$oPaginacao->setINumeroPagina();
	}else{
		$oPaginacao->setINumeroPagina($pagina);
	}
	
	$oPaginacao->calculaPrimeiroRegistro();
	$oPaginacao->calculaTotalRegistros();
	$oPaginacao->calculaTotalPaginas();
	$oPaginacao->geraPainelNavegacao();
	
	$aDados = Array();
	
	$aDados = $oPaginacao->recuperaDados();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<title>..: SECRETARIA ON-LINE :..</title>
		<link rel='stylesheet' href='../css/estilo_secretaria.css' type='text/css' media='screen'>
		<link rel="stylesheet" href="../css/menu_style.css" type="text/css" media='screen'/>
		<script src="../js/secretaria.js" type="text/javascript"></script>
		<script type="text/javascript" language='javascript' src="http://jqueryjs.googlecode.com/files/jquery-1.3.2.min.js"></script>
		<script src="../js/accordion.js" type="text/javascript" language='javascript'></script>
	</head>	
	
	<body>		
		<div id='geral'>
	
			<div id='menu_superior'>
				<div id='text_menu_esquerda'>Seja bem-vindo(a) <?php echo $sNomeUsuario;?></div>
				<div id='text_menu_direita'><a href='../portal/index.php' class='link_sair'>SAIR</a></div>
			</div>
			
			<?php 
				require_once("painel.php");
				$sPainelSecretaria = gerarPainel($sNivelAcesso);
				echo $sPainelSecretaria;
			?>
			
			<div id='painel_exibicao'>
				<form name='formMensagem' action='excluirMensagem.php' method='post'>
				
				<div id='menu_aba'>
					<b>DISPLINAS CADASTRADAS</b><br/>
					Clique sobre uma disciplina para exibir seu cadastro completo
					<div id='botoes'>
						<input type='button' value='Excluir' onClick='excluirMensagem()' disabled />							
						<input type='hidden' name='campoOculto' id='campoOculto' value=''/>
					</div>
				</div>
				
				<div id='painel_exibicao_conteudo'>

					<table class='tabela'>
						<tr class='titulo'>
							<td colspan=2 class='celTituloIDUsuario'>ID</td>
							<td class='celTituloNome'>Nome</td>								
							<td class='celTituloNivelAcesso'>Curso</td>
						</tr>
						<?php
						if(!$oPaginacao->getIQtdTotalRegistros()){
							echo "
								<tr class='linhaDados'>
									<td colspan=5><b>NENHUMA DISCIPLINA ENCONTRADA</b></td>
								</tr>";
						}else{
							while (list($iddisciplina, $nome, $curso_idcurso, $nome_curso) = mysql_fetch_array($aDados)) {
								echo "
									<tr class='linhaDados'>
										<td class='celCheck'><input type='checkbox' name='check$iddisciplina' value='$iddisciplina'/></td>
										<td class='celIDUsuario'><a href='./cadDisciplina.php?opcao=1&iddisciplina=$iddisciplina'>$iddisciplina</a></td>
										<td class='celNome'><a href='./cadDisciplina.php?opcao=1&iddisciplina=$iddisciplina'>$nome</a></td>
										<td class='celNivelAcesso'><a href='./cadDisciplina.php?opcao=1&iddisciplina=$iddisciplina'>$nome_curso</a></td>
									</tr>";
							}
						}
						?>			
					</table>
				</div>
				</form>
				
				<div id='paginacao'>
					<?php							
						// exibir painel de navega��o
						echo $oPaginacao->getSPainelNavegacao();
					?>
				</div>
			</div>
			
			<div id='menu_inferior'>
				<ul>
					<li><a href="../perfil/index.php" target="_self">PERFIL</a></li>
                                        <li><a href="../forum/index.php" target="_self">F&Oacute;RUM</a></li>
					<?php if($sNivelAcesso == "Administrador" || $sNivelAcesso == "Coordenador" || $sNivelAcesso == "Secretaria"){?>
                                        <li><a href="../noticias/index.php" target="_self">NOT&Iacute;CIAS</a></li>
					<?php }?>
					<li><a href="../portal/index.php" target="_self">SAIR</a></li>						
				</ul>
			</div>
			
		</div>
	</body>
</html>