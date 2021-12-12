<?php
/*
    VER: 1.0
    LAST-UPDATE: 17/07/2021
*/

    function ctrl_util_nfe($args){

        /* EXTENSAO DE CLASSE CASO NECESSARIO */

        // (new APPControls)->loadPlugin("plugin_exemplo");

        return new class extends APPControls {
			function read($xml){
				$doc = new DOMDocument();
				$doc->preservWhiteSpace = FALSE; //elimina espaÃ§os em branco
				$doc->formatOutput = FALSE;
				$doc->loadXML($xml,LIBXML_NOBLANKS | LIBXML_NOEMPTYTAG);
				$node = $doc->getElementsByTagName('infNFe')->item(0);
				$dados = [];
				//obtem a versÃ£o do layout da NFe
				$dados['versao']=trim($node->getAttribute("versao"));
				$dados['chave']= substr(trim($node->getAttribute("Id")),3);

				// Reconhecimento dos campos do XML

				$dados['dataEmissao']=$this->tagValue($doc,"dEmi");
				$dados['dataMovimento']=$this->tagValue($doc,"dSaiEnt")." ".$this->tagValue($doc,"hSaiEnt");
				$dados['codigonf']=$this->tagValue($doc,"cNF");
				$dados['natureza']=$this->tagValue($doc,"natOp");
				$dados['numero']=$this->tagValue($doc,"nNF");
				$dados['modelo']=$this->tagValue($doc,"mod");
				$dados['serie']=$this->tagValue($doc,"serie");

				// Emitente:
				$emi=$doc->getElementsByTagName('emit')->item(0);
				$c1=$this->tagValue($emi,"CNPJ");
				$c2=substr($c1,0,2).".".substr($c1,2,3).".".substr($c1,5,3)."/".substr($c1,8,4)."-".substr($c1,12,2);
				$dados['emitenteCnpj']=$c1;
				$dados['emitenteCnpjFormatado']=$c2;
				$dados['emitenteRazaoSocial']=$this->tagValue($emi,"xNome");
				$dados['emitenteNome']=$this->tagValue($emi,"xFant");
				$dados['emitenteInscricaoEstadual']=$this->tagValue($emi,"IE");
				$dados['emitenteInscricaoMunicipal']=$this->tagValue($emi,"IM");
				$dados['emitenteCnae']=$this->tagValue($emi,"CNAE");
				$dados['emitenteEndereco']=$this->tagValue($emi,"xLgr");
				$dados['emitenteNumero']=$this->tagValue($emi,"nro");
				$dados['emitenteBairro']=$this->tagValue($emi,"xBairro");
				$dados['emitenteMunicipio']=$this->tagValue($emi,"xMun");
				$dados['emitenteMunicipioIbge']=$this->tagValue($emi,"cMun");
				$dados['emitenteCep']=$this->tagValue($emi,"CEP");
				$dados['emitenteUF']=$this->tagValue($emi,"UF");
				$dados['emitentePaisIbge']=$this->tagValue($emi,"cPais");
				$dados['emitentePais']=$this->tagValue($emi,"xPais");
				$dados['emitenteTelefone']=$this->tagValue($emi,"fone");

				// Destinatário:
				$dst=$doc->getElementsByTagName('dest')->item(0);
				$c1=$this->tagValue($dst,"CNPJ");
				$c2=substr($c1,0,2).".".substr($c1,2,3).".".substr($c1,5,3)."/".substr($c1,8,4)."-".substr($c1,12,2);
				$dados['destinatarioCnpj']=$c1;
				$dados['destinatarioCnpjFormatado']=$c2;
				$dados['destinatarioRazaoSocial']=$this->tagValue($dst,"xNome");
				$dados['destinatarioNome']=$this->tagValue($dst,"xFant");
				$dados['destinatarioInscricaoEstadual']=$this->tagValue($dst,"IE");
				$dados['destinatarioInscricaoMunicipal']=$this->tagValue($dst,"IM");
				$dados['destinatarioEndereco']=$this->tagValue($dst,"xLgr");
				$dados['destinatarioNumero']=$this->tagValue($dst,"nro");
				$dados['destinatarioBairro']=$this->tagValue($dst,"xBairro");
				$dados['destinatarioMunicipio']=$this->tagValue($dst,"xMun");
				$dados['destinatarioMunicipioIbge']=$this->tagValue($dst,"cMun");
				$dados['destinatarioCep']=$this->tagValue($dst,"CEP");
				$dados['destinatarioUF']=$this->tagValue($dst,"UF");
				$dados['destinatarioPaisIbge']=$this->tagValue($dst,"cPais");
				$dados['destinatarioPais']=$this->tagValue($dst,"xPais");
				$dados['destinatarioTelefone']=$this->tagValue($dst,"fone");

				$dados['pesoLiquido']=floatval($this->tagValue($doc,"pesoL"));
				$dados['pesoBruto']=floatval($this->tagValue($doc,"pesoB"));

				$dados['dataRecibo']=$this->tagValue($doc,"dhRecbto");
				$dados['protocolo']=$this->tagValue($doc,"nProt");



				// Totais da NF-e. Para fazer a alimentação no database:
				$total=$doc->getElementsByTagName('ICMSTot')->item(0);
				$dados['basecalculo']=$this->tagValue($total,"vBC");
				$dados['valoricms']=$this->tagValue($total,"vICMS");
				$dados['valorbcst']=$this->tagValue($total,"vBCST");
				$dados['valorst']=$this->tagValue($total,"vST");
				$dados['totalprodutos']=$this->tagValue($total,"vProd");
				$dados['valorfrete']=$this->tagValue($total,"vFrete");
				$dados['valorseguro']=$this->tagValue($total,"vSeg");
				$dados['valorfrete']=$this->tagValue($total,"vFrete");
				$dados['valordesconto']=$this->tagValue($total,"vDesc");
				$dados['valorii']=$this->tagValue($total,"vII");
				$dados['valoripi']=$this->tagValue($total,"vIPI");
				$dados['valorpis']=$this->tagValue($total,"vPIS");
				$dados['valorcofins']=$this->tagValue($total,"vCOFINS");
				$dados['valoroutro']=$this->tagValue($total,"vOutro");
				$dados['valortotalnf']=$this->tagValue($total,"vNF");

				// Tag det dos itens unitários:
				$det=$doc->getElementsByTagName('det');
				$itens=[];
				for ($i = 0; $i < $det->length; $i++) {
					$item=$det->item($i);
					$s=[];
					$s['codigo']=$this->tagValue($item,"cProd");
					$s['ean']=$this->tagValue($item,"cEAN");
					$s['nome']=$this->tagValue($item,"xProd");
					$s['ncm']=$this->tagValue($item,"NCM");
					$s['cfop']=$this->tagValue($item,"CFOP");
					$s['unidade']=$this->tagValue($item,"uCom");
					$s['quantidade']=$this->tagValue($item,"qCom");
					$s['valor']=$this->tagValue($item,"vUnCom");
					$s['valorTotal']=$this->tagValue($item,"vProd");
					$s['valoricms']=$this->tagValue($item,"vICMS");
					$s['valoripi']=$this->tagValue($item,"vIPI");
					$itens[] = $s;

				}
				$dados['itens']=$itens;
				return($dados);
			}

			private function tagValue($node,$tag){
				return @$node->getElementsByTagName("$tag")->item(0)->nodeValue;
			}
        };
    }
