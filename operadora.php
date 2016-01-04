<?php
	function dados_telefone($num)
	{
		$num = preg_replace("/[^\d]/", "", $num);
		if(strlen($num) < 10)
		{
			return false;
		}
		else
		{
			include_once('src/simple_html_dom.php');
			$query = http_build_query(array('tel' => $num));
			$options = array(
				'http' => array(
					'header' => "Content-Type: application/x-www-form-urlencoded\r\n".
								"Content-Length: ".strlen($query)."\r\n".
								"User-Agent:Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2311.90 Safari/537.36\r\n",
					'method'  => "POST",
					'content' => $query,
				),
			);
			$context = stream_context_create($options);
			$result = file_get_html("http://consultanumero.info/consulta", false, $context);
			
			$resultado = @$result->find('div[class=resultado]',0)->children(1)->outertext;
			
			if(empty($resultado))
			{
				return false;
			}
			else
			{
				$resultado = str_get_html($resultado);
				
				$img = @$result->find('div[class=a]',0)->children(0)->outertext;
				preg_match('%<img.*?title=["\'](.*?)["\'].*?/>%i', $img , $operadora);
				
				$data['operadora'] = $operadora[1];
				$data['tipo'] = substr(strrchr(strip_tags($resultado->find('p', 0)->outertext), ' &raquo; '), 1);
				$data['portabilidade'] = (strtolower(substr(strrchr(strip_tags($resultado->find('p', 1)->outertext), ' &raquo; '), 1)) == 'sim' ? "Sim" : "Não");
				$data['estado'] = str_replace(array('(', ')'), '', substr(strrchr(strip_tags($resultado->find('p', 2)->outertext), ' &raquo; '), 1));
				$cidade = explode(' &raquo; ', strip_tags($resultado->find('p', 3)->outertext));
				$data['cidade'] = $cidade[1];
				
				return $data;
			}
		}
	}
	
	$dados = dados_telefone('(##)# ####-####');
	print_r($dados);
	if($dados == false)
		echo "Número inválido ou não encontrado!";
	else
		print_r($dados);
?>
