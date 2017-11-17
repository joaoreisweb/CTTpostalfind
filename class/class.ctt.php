<?php
/**
*
* @ Project : CTTpostalfind
* @ Author  : João Reis
* @ Email   : web@joaoreis.pt
* @ Date    : 28.Abril.2016
* @ Version : 2.0
* @ License : MIT
*
* @ Desc    : 
* Baseado no documento dos CTT
* Manual de Validação de Códigos Postais em HTML ou XML
* Versão 1.6 - Dezembro 2014
* https://www.ctt.pt/correio-e-encomendas/ajuda/impressos.html
*
*/

//header("Content-Type: text/html; charset=utf-8",true);

class CTTpostalfind{

	private $_cttWsUrl="http://www.ctt.pt/pdcp/xml_pdcp";
	private $_urltemp=array();
	private $_cttresponse = array();
	private $_distrito;
  	private $_concelho;
  	private $_local;
  	private $_rua;
  	private $_porta;
  	private $_codpos;
  	private $_cliente;

  	private $_pag = '1';
  	private $_maxpg = '30';
  	private $_idlo;
  	private $_idar;
  	private $_idep;
  	private $_ep;
  	private $_apartado;

  	

	public function __construct(){
      	echo 'A classe "', __CLASS__, '" foi instanciada!<br />';
  	}

  	public function setWebserviceUrl($newurl){
  		$this->_cttWsUrl = $newurl;
	}

	/** Os parâmetros indicados abaixo permitem especificar os critérios de pesquisa do código postal. A pesquisa é “case-insensitive” (é indiferente o uso de maiúsculas ou minúsculas) e não leva em consideração o uso ou não de acentos. **/


	// Distrito a que pertence a morada indicada
	public function setDistrito($newvalue){
  		$this->_distrito = $newvalue;
	}
 
	// Concelho a que pertence a morada indicada
	public function setConcelho($newvalue){
  		$this->_concelho = $newvalue;
	}

	// Localidade da morada
	public function setLocal($newvalue){
  		$this->_local = $newvalue;
	}

	// Rua ou equivalente da morada
	public function setRua($newvalue){
  		$this->_rua = $newvalue;
	}

	// Número de polícia da morada
	public function setPorta($newvalue){
  		$this->_porta = $newvalue;
	}
	 
	// Código postal definido na forma CP4 – CP3 Designação
	public function setCodpos($newvalue){
  		$this->_codpos = $newvalue;
	}

	// Nome da instituição/empresa cliente dos CTT	
	public function setCliente($newvalue){
  		$this->_cliente = $newvalue;
	}

	/**  Quando os parâmetros indicados não correspondem a um código postal específico, mas a um conjunto, por vezes de grande dimensão, os resultados são numerados e retornados por partes recorrendo aos seguintes parâmetros:
	*/

	// Número sequencial da página de resultados a retornar
	public function setPag($newvalue){
  		$this->_pag = $newvalue;
	}

	//Número máximo de registos a retornar por cada chamada. Se não for especificado é assumido o valor por defeito definido no sistema.
	public function setMaxpag($newvalue){
  		$this->_maxpag = $newvalue;
	}

	/**  Os resultados são organizados agrupando códigos postais por localidades e por arruamentos, havendo a possibilidade de à posteriori serem retornados recorrendo aos seguintes parâmetros:
	*/

	// Número que identifica a localidade
	public function setIDlo($newvalue){
  		$this->_idlo = $newvalue;
	}

	// Número que identifica o arruamento
	public function setIDar($newvalue){
  		$this->_idar = $newvalue;
	}

	// Código do estabelecimento postal dos CTT
	public function setIDep($newvalue){
  		$this->_idep = $newvalue;
	}

	// Nome do estabelecimento postal dos CTT
	public function setEp($newvalue){
  		$this->_ep = $newvalue;
	}

	// Número do apartado
	public function setApartado($newvalue){
  		$this->_apartado = $newvalue;
	}

	public function getParams(){
  		return $this->createParams();
	}

	public function search($detail,$json=true){
		if($detail=='original'){
			return $this->builtOriginalResponse($this->_cttWsUrl, $this->createParams(), $json);
		}
		if($detail=='simple'){
			return $this->builtSimpleResponse($this->_cttWsUrl, $this->createParams(), $json);
		}
		if($detail=='all'){
			return $this->builtCompleteResponse($this->_cttWsUrl, $this->createParams(), $json);
		}
  		
	}

	private function builtOriginalResponse($url, $params, $json){

		$sXML = $this->builtCompleteResponse($url, $params , $json);

		return $sXML;
	}

	private function builtSimpleResponse($url, $params, $json){
		$paramsTemp = http_build_query($params);
		$sXML = $this->callCTT($url, $paramsTemp );
		$oXML = $sXML;
		$oXML = new SimpleXMLElement($sXML);
		
		$this->_cttresponse=array();
		if($oXML->getName()=='OK'){
			/* Localidade */
			/*$this->_cttresponse['localidade']["num"] = (int)$oXML->Localidade->attributes()->num;
			$this->_cttresponse['localidade']["tipo"] = (string)$oXML->Localidade->attributes()->tipo;
			$this->_cttresponse['localidade']["tipo_msg"] = (string)$this->echoMessage($oXML->Localidade->attributes()->tipo);
			$this->_cttresponse['localidade']["id"] = (int)$oXML->Localidade->attributes()->idlo;*/

			$this->_cttresponse['localidade']["designacao"] = (string)$oXML->Localidade->Designacao;
			$this->_cttresponse['localidade']["distrito"] = (string)$oXML->Localidade->Distrito;
			$this->_cttresponse['localidade']["concelho"] = (string)$oXML->Localidade->Concelho;
			//$this->_cttresponse['localidade']["freguesia"] = (string)$oXML->Localidade->Freguesia;	
			//$this->_cttresponse['localidade']["freguesia_id"] = (int)$oXML->Localidade->Freguesia->attributes()->idfr;
				

			/* Localidade->Arruamentos->Rua */
			if(!empty($oXML->Localidade->Arruamentos->Rua)){
				/* rua attributes */
				/*$this->_cttresponse['localidade']["rua_num"] = (string)$oXML->Localidade->Arruamentos->Rua->attributes()->num;
				$this->_cttresponse['localidade']["rua_tipo"] = (string)$oXML->Localidade->Arruamentos->Rua->attributes()->tipo;
				$this->_cttresponse['localidade']["rua_tipo_msg"] = (string)$this->echoMessage($oXML->Localidade->Arruamentos->Rua->attributes()->tipo);
				$this->_cttresponse['localidade']["rua_id"] = (string)$oXML->Localidade->Arruamentos->Rua->attributes()->id;
				$this->_cttresponse['localidade']["rua_cod"] = (string)$oXML->Localidade->Arruamentos->Rua->attributes()->cod;*/
				/* rua object */
				$this->_cttresponse['localidade']["rua"] = (string)$oXML->Localidade->Arruamentos->Rua->Designacao;
				$this->_cttresponse['localidade']["rua_freguesia"] = (string)$oXML->Localidade->Arruamentos->Rua->Freguesia;
				//$this->_cttresponse['localidade']["rua_freguesia_id"] = (int)$oXML->Localidade->Arruamentos->Rua->Freguesia->attributes()->idfr;

				/*$this->_cttresponse['localidade']["codpos"] = (int)$oXML->Localidade->Arruamentos->Rua->CodPos->Geo;
				$this->_cttresponse['localidade']["codpos_for"] = substr_replace((string)$oXML->Localidade->Arruamentos->Rua->CodPos->Geo, '-', 4, 0);
				$this->_cttresponse['localidade']["codpos_des"] = (string)$oXML->Localidade->Arruamentos->Rua->CodPos->Designacao;
				$this->_cttresponse['localidade']["codpos_cp4"] = (int)$oXML->Localidade->Arruamentos->Rua->CodPos->CP4;
				$this->_cttresponse['localidade']["codpos_cp3"] = (int)$oXML->Localidade->Arruamentos->Rua->CodPos->CP3;*/
				
				// Designacao tipo (O | H)
				$this->_cttresponse['localidade']["designacao"] = (string)$oXML->Localidade->Arruamentos->Rua->Designacao;
				/*$this->_cttresponse['localidade']["desigSeg_tipo"] = (string)$oXML->Localidade->Arruamentos->Rua->DesigSeg->Tipo;
				$this->_cttresponse['localidade']["desigSeg_priprep"] = (string)$oXML->Localidade->Arruamentos->Rua->DesigSeg->PriPrep;
				$this->_cttresponse['localidade']["desigSeg_titulo"] = (string)$oXML->Localidade->Arruamentos->Rua->DesigSeg->Titulo;
				$this->_cttresponse['localidade']["desigSeg_segprep"] = (string)$oXML->Localidade->Arruamentos->Rua->DesigSeg->SegPrep;
				$this->_cttresponse['localidade']["desigSeg_nome"] = (string)$oXML->Localidade->Arruamentos->Rua->DesigSeg->Nome;
				$this->_cttresponse['localidade']["desigSeg_local"] = (string)$oXML->Localidade->Arruamentos->Rua->DesigSeg->Local;*/
			}

			/* Localidade->Arruamentos->Rua->Trocos */
			if(!empty($oXML->Localidade->Arruamentos->Rua->Trocos)){
				// TROÇO TIPO (I | P | V | T | S)
				$this->_cttresponse['localidade']["troco_tipo"] = (string)$oXML->Localidade->Arruamentos->Rua->Trocos->Troco->attributes()->tipo;
				$this->_cttresponse['localidade']["troco_tipo_msg"] = (string)$this->echoMessage($oXML->Localidade->Arruamentos->Rua->Trocos->Troco->attributes()->tipo);
				//$this->_cttresponse['localidade']["troco_num"] = (string)$oXML->Localidade->Arruamentos->Rua->Trocos->Troco->attributes()->num);

				$this->_cttresponse['localidade']["troco_codpos"] = (int)$oXML->Localidade->Arruamentos->Rua->Trocos->Troco->CodPos->Geo;
				$this->_cttresponse['localidade']["troco_codpos_for"] = substr_replace((string)$oXML->Localidade->Arruamentos->Rua->Trocos->Troco->CodPos->Geo, '-', 4, 0);
				$this->_cttresponse['localidade']["troco_codpos_des"] = (string)$oXML->Localidade->Arruamentos->Rua->Trocos->Troco->CodPos->Designacao;
				//$this->_cttresponse['localidade']["troco_codpos_cp4"] = (int)$oXML->Localidade->Arruamentos->Rua->Trocos->Troco->CodPos->CP4;
				//$this->_cttresponse['localidade']["troco_codpos_cp3"] = (int)$oXML->Localidade->Arruamentos->Rua->Trocos->Troco->CodPos->CP3;
			}
			/* Localidade->Arruamentos->Rua->Clientes */
			if(!empty($oXML->Localidade->Arruamentos->Rua->Clientes)){
				$this->_cttresponse['localidade']["cliente"]["designacao"] = (string)$oXML->Localidade->Arruamentos->Rua->Clientes->Cliente->Designacao;
				$this->_cttresponse['localidade']["cliente"]["porta"] = (string)$oXML->Localidade->Arruamentos->Rua->Clientes->Cliente->Num_Porta;
				$this->_cttresponse['localidade']["cliente"]["freguesia"] = (string)$oXML->Localidade->Arruamentos->Rua->Clientes->Cliente->Freguesia;
				$this->_cttresponse['localidade']["cliente"]["freguesia_id"] = (string)$oXML->Localidade->Arruamentos->Rua->Clientes->Cliente->Freguesia->attributes()->idfr;
				$this->_cttresponse['localidade']["cliente"]["codpos"] = (int)$oXML->Localidade->Arruamentos->Rua->Clientes->Cliente->CodPos->Geo;
				$this->_cttresponse['localidade']["cliente"]["codpos_for"] = (string)substr_replace((string)$oXML->Localidade->Arruamentos->Rua->Clientes->Cliente->CodPos->Geo, '-', 4, 0);
				$this->_cttresponse['localidade']["cliente"]["codpos_des"] = (string)$oXML->Localidade->Arruamentos->Rua->Clientes->Cliente->CodPos->Designacao;
				/*$this->_cttresponse['localidade']["cliente"]["codpos_cp4"] = (int)$oXML->Localidade->Arruamentos->Rua->Clientes->Cliente->CodPos->CP4;
				$this->_cttresponse['localidade']["cliente"]["codpos_cp3"] = (int)$oXML->Localidade->Arruamentos->Rua->Clientes->Cliente->CodPos->CP3;*/
			}

			/* Localidade->Eps */
			if(!empty($oXML->Localidade->Eps)){
				//$this->_cttresponse['localidade']["eps_total"] = (string)$oXML->Localidade->Eps->attributes()->Total);
				// EP tipo (EC | CDP | BEC | POSTO)
				$this->_cttresponse['localidade']["eps_ep_id"] = (string)$oXML->Localidade->Eps->Ep->attributes()->id;
				$this->_cttresponse['localidade']["eps_ep_tipo"] = (string)$oXML->Localidade->Eps->Ep->attributes()->tipo;

				$this->_cttresponse['localidade']["eps_ep_designacao"] = (string)$oXML->Localidade->Eps->Ep->Designacao;

				//$this->_cttresponse['localidade']["eps_ep_designacao_tipo"] = (string)$oXML->Localidade->Eps->Ep->Designacao->attributes()->tipo;

				// APARTADO tipo (G | B)
				//$this->_cttresponse['localidade']["eps_ep_apartado_tipo"] = (string)$oXML->Localidade->Eps->Ep->Apartados->Apartado->attributes()->tipo;

				$this->_cttresponse['localidade']["eps_ep_apartado_liminf"] = (string)$oXML->Localidade->Eps->Ep->Apartados->Apartado->LimInf;
				$this->_cttresponse['localidade']["eps_ep_apartado_limsup"] = (string)$oXML->Localidade->Eps->Ep->Apartados->Apartado->LimSup;

				$this->_cttresponse['localidade']["eps_ep_apartado_codpos"] = (int)$oXML->Localidade->Eps->Ep->Apartados->Apartado->CodPos->Geo;
				$this->_cttresponse['localidade']["eps_ep_apartado_codpos_for"] = substr_replace((string)$oXML->Localidade->Eps->Ep->Apartados->Apartado->CodPos->Geo, '-', 4, 0);
				//$this->_cttresponse['localidade']["eps_ep_apartado_codpos_cp3"] = (int)$oXML->Localidade->Eps->Ep->Apartados->Apartado->CodPos->CP3;
				//$this->_cttresponse['localidade']["eps_ep_apartado_codpos_cp4"] = (int)$oXML->Localidade->Eps->Ep->Apartados->Apartado->CodPos->CP4;
				$this->_cttresponse['localidade']["eps_ep_apartado_codpos_designacao"] = (string)$oXML->Localidade->Eps->Ep->Apartados->Apartado->CodPos->Designacao;
			}

		}

		if($oXML->getName()=='Erro'){
			$this->_cttresponse['erro_razao'] = (string)$oXML->attributes()->razao;
			$this->_cttresponse['erro_razao_msg'] = (string)$this->echoMessage($oXML->attributes()->razao);
			$this->_cttresponse['totalresults'] = (string)$oXML->attributes()->total;
			$this->_cttresponse['resultstart'] = (string)$oXML->attributes()->inicio;
			$this->_cttresponse['resultend'] = (string)$oXML->attributes()->fim;
		}

		$oXML = $this->_cttresponse;

		if($json){
			$oXML = json_encode($this->_cttresponse);
		}
		return   $oXML;	
	}

	private function builtCompleteResponse($url, $params, $json){
	    $paramsTemp = http_build_query($params);

		$sXML = $this->callCTT($url, $paramsTemp ); // POST request
		//var_dump($sXML);
		
		/**
		* RESPONSE
		*/
		$oXML = new SimpleXMLElement($sXML);
		//var_dump($oXML);

		
		//$jsondata = json_decode(json_encode($rXML));
		//var_dump($jsondata);
		//var_dump($oXML->Criterio);
		//var_dump($oXML->Result);
		//var_dump((string)$oXML->Localidade->Arruamentos->attributes()->total));
		//var_dump($oXML->Localidade->Eps->Ep->Apartados);

		/**
		* Apresenta apenas um resultado
		*
		*/
		$this->_cttresponse=array();

		if($oXML->getName()!='Erro'){
			// ALWAYS
			$this->_cttresponse['criterio']['distrito'] = (string)$oXML->Criterio->inDistrito;
			$this->_cttresponse['criterio']['concelho'] = (string)$oXML->Criterio->inConcelho;
			$this->_cttresponse['criterio']['local'] = (string)$oXML->Criterio->inLocal;
			$this->_cttresponse['criterio']['rua'] = (string)$oXML->Criterio->inRua;
			$this->_cttresponse['criterio']['porta'] = (string)$oXML->Criterio->inPorta;
			$this->_cttresponse['criterio']['codpos'] = (string)$oXML->Criterio->inCodPos;
			$this->_cttresponse['criterio']['cliente'] = (string)$oXML->Criterio->inCliente;
			$this->_cttresponse['criterio']['idlocal'] = (string)$oXML->Criterio->inIdLocal;
			$this->_cttresponse['criterio']['idrua'] = (string)$oXML->Criterio->inIdRua;
			$this->_cttresponse['criterio']['ep'] = (string)$oXML->Criterio->inEP;
			$this->_cttresponse['criterio']['apartado'] = (string)$oXML->Criterio->inApartado;
			$this->_cttresponse['criterio']['idep'] = (int)$oXML->Criterio->inIdEp;
			$this->_cttresponse['criterio']['pag'] = (int)$oXML->Criterio->inPag;
			$this->_cttresponse['criterio']['maxpag'] = (int)$oXML->Criterio->inMaxPag;
			$this->_cttresponse['criterio']['idpesq'] = (int)$oXML->Criterio->Id_Pesq;
			$this->_cttresponse['result']['inicio'] = (int)$oXML->Result->Pagina->attributes()->inicio;
			$this->_cttresponse['result']['fim'] = (int)$oXML->Result->Pagina->attributes()->fim;
			$this->_cttresponse['result']['num'] = (int)$oXML->Result->Pagina->attributes()->num;
		}

		//var_dump($this->_cttresponse);
		//print_r($this->_cttresponse);

		
		if($oXML->getName()=='OK'){
			/* Localidade */
			$this->_cttresponse['localidade']["num"] = (int)$oXML->Localidade->attributes()->num;
			$this->_cttresponse['localidade']["tipo"] = (string)$oXML->Localidade->attributes()->tipo;
			$this->_cttresponse['localidade']["tipo_msg"] = (string)$this->echoMessage($oXML->Localidade->attributes()->tipo);
			$this->_cttresponse['localidade']["id"] = (int)$oXML->Localidade->attributes()->idlo;

			$this->_cttresponse['localidade']["designacao"] = (string)$oXML->Localidade->Designacao;
			$this->_cttresponse['localidade']["distrito"] = (string)$oXML->Localidade->Distrito;
			$this->_cttresponse['localidade']["concelho"] = (string)$oXML->Localidade->Concelho;
			$this->_cttresponse['localidade']["freguesia_id"] = (int)$oXML->Localidade->Freguesia->attributes()->idfr;
			$this->_cttresponse['localidade']["freguesia"] = (string)$oXML->Localidade->Freguesia;		

			/* Localidade->Arruamentos->Rua */
			if(!empty($oXML->Localidade->Arruamentos->Rua)){
				/* rua attributes */
				$this->_cttresponse['localidade']["rua_num"] = (int)$oXML->Localidade->Arruamentos->Rua->attributes()->num;
				$this->_cttresponse['localidade']["rua_tipo"] = (string)$oXML->Localidade->Arruamentos->Rua->attributes()->tipo;
				$this->_cttresponse['localidade']["rua_tipo_msg"] = (string)$this->echoMessage($oXML->Localidade->Arruamentos->Rua->attributes()->tipo);
				$this->_cttresponse['localidade']["rua_id"] = (int)$oXML->Localidade->Arruamentos->Rua->attributes()->id;
				$this->_cttresponse['localidade']["rua_cod"] = (int)$oXML->Localidade->Arruamentos->Rua->attributes()->cod;
				/* rua object */
				$this->_cttresponse['localidade']["rua"] = (string)$oXML->Localidade->Arruamentos->Rua->Designacao;
				$this->_cttresponse['localidade']["freguesia"] = (string)$oXML->Localidade->Arruamentos->Rua->Freguesia;
				$this->_cttresponse['localidade']["rua_freguesia_id"] = (int)$oXML->Localidade->Arruamentos->Rua->Freguesia->attributes()->idfr;

				//$this->_cttresponse['localidade']["codpos"] = (int)$oXML->Localidade->Arruamentos->Rua->CodPos->Geo;
				//$this->_cttresponse['localidade']["codpos_for"] = substr_replace((string)$oXML->Localidade->Arruamentos->Rua->CodPos->Geo, '-', 4, 0);
				//$this->_cttresponse['localidade']["codpos_des"] = (string)$oXML->Localidade->Arruamentos->Rua->CodPos->Designacao;
				//$this->_cttresponse['localidade']["codpos_cp4"] = (int)$oXML->Localidade->Arruamentos->Rua->CodPos->CP4;
				//$this->_cttresponse['localidade']["codpos_cp3"] = (int)$oXML->Localidade->Arruamentos->Rua->CodPos->CP3;

				$this->_cttresponse['localidade']["designacao"] = (string)$oXML->Localidade->Arruamentos->Rua->Designacao;
				$this->_cttresponse['localidade']["desigSeg_tipo"] = (string)$oXML->Localidade->Arruamentos->Rua->DesigSeg->Tipo;
				$this->_cttresponse['localidade']["desigSeg_priprep"] = (string)$oXML->Localidade->Arruamentos->Rua->DesigSeg->PriPrep;
				$this->_cttresponse['localidade']["desigSeg_titulo"] = (string)$oXML->Localidade->Arruamentos->Rua->DesigSeg->Titulo;
				$this->_cttresponse['localidade']["desigSeg_segprep"] = (string)$oXML->Localidade->Arruamentos->Rua->DesigSeg->SegPrep;
				$this->_cttresponse['localidade']["desigSeg_nome"] = (string)$oXML->Localidade->Arruamentos->Rua->DesigSeg->Nome;
				$this->_cttresponse['localidade']["desigSeg_local"] = (string)$oXML->Localidade->Arruamentos->Rua->DesigSeg->Local;
			}

			/* Localidade->Arruamentos->Rua->Trocos */
			if(!empty($oXML->Localidade->Arruamentos->Rua->Trocos)){
				$this->_cttresponse['localidade']["troco_tipo"] = (string)$oXML->Localidade->Arruamentos->Rua->Trocos->Troco->attributes()->tipo;
				$this->_cttresponse['localidade']["troco_tipo_msg"] = (string)$this->echoMessage($oXML->Localidade->Arruamentos->Rua->Trocos->Troco->attributes()->tipo);
				$this->_cttresponse['localidade']["troco_num"] = (int)$oXML->Localidade->Arruamentos->Rua->Trocos->Troco->attributes()->num;

				$this->_cttresponse['localidade']["troco_codpos"] = (int)$oXML->Localidade->Arruamentos->Rua->Trocos->Troco->CodPos->Geo;
				$this->_cttresponse['localidade']["troco_codpos_for"] = substr_replace((string)$oXML->Localidade->Arruamentos->Rua->Trocos->Troco->CodPos->Geo, '-', 4, 0);
				$this->_cttresponse['localidade']["troco_codpos_des"] = (string)$oXML->Localidade->Arruamentos->Rua->Trocos->Troco->CodPos->Designacao;
				$this->_cttresponse['localidade']["troco_codpos_cp4"] = (int)$oXML->Localidade->Arruamentos->Rua->Trocos->Troco->CodPos->CP4;
				$this->_cttresponse['localidade']["troco_codpos_cp3"] = (int)$oXML->Localidade->Arruamentos->Rua->Trocos->Troco->CodPos->CP3;
			}
			/* Localidade->Arruamentos->Rua->Clientes */
			if(!empty($oXML->Localidade->Arruamentos->Rua->Clientes)){
				$this->_cttresponse['localidade']["cliente"]["designacao"] = (string)$oXML->Localidade->Arruamentos->Rua->Clientes->Cliente->Designacao;
				$this->_cttresponse['localidade']["cliente"]["porta"] = (string)$oXML->Localidade->Arruamentos->Rua->Clientes->Cliente->Num_Porta;
				$this->_cttresponse['localidade']["cliente"]["freguesia"] = (string)$oXML->Localidade->Arruamentos->Rua->Clientes->Cliente->Freguesia;
				$this->_cttresponse['localidade']["cliente"]["freguesia_id"] = (int)$oXML->Localidade->Arruamentos->Rua->Clientes->Cliente->Freguesia->attributes()->idfr;
				$this->_cttresponse['localidade']["cliente"]["codpos"] = (int)$oXML->Localidade->Arruamentos->Rua->Clientes->Cliente->CodPos->Geo;
				$this->_cttresponse['localidade']["cliente"]["codpos_for"] = substr_replace((string)$oXML->Localidade->Arruamentos->Rua->Clientes->Cliente->CodPos->Geo, '-', 4, 0);
				//substr_replace($oldstr, $str_to_insert, $pos, 0);
				$this->_cttresponse['localidade']["cliente"]["codpos_des"] = (string)$oXML->Localidade->Arruamentos->Rua->Clientes->Cliente->CodPos->Designacao;
				$this->_cttresponse['localidade']["cliente"]["codpos_cp4"] = (int)$oXML->Localidade->Arruamentos->Rua->Clientes->Cliente->CodPos->CP4;
				$this->_cttresponse['localidade']["cliente"]["codpos_cp3"] = (int)$oXML->Localidade->Arruamentos->Rua->Clientes->Cliente->CodPos->CP3;
			}

			/* Localidade->Eps */
			if(!empty($oXML->Localidade->Eps)){
				$this->_cttresponse['localidade']["eps_total"] = (string)$oXML->Localidade->Eps->attributes()->Total;

				$this->_cttresponse['localidade']["eps_ep_id"] = (string)$oXML->Localidade->Eps->Ep->attributes()->id;
				$this->_cttresponse['localidade']["eps_ep_tipo"] = (string)$oXML->Localidade->Eps->Ep->attributes()->tipo;

				$this->_cttresponse['localidade']["eps_ep_designacao"] = (string)$oXML->Localidade->Eps->Ep->Designacao;
				$this->_cttresponse['localidade']["eps_ep_designacao_tipo"] = (string)$oXML->Localidade->Eps->Ep->Designacao->attributes()->tipo;

				$this->_cttresponse['localidade']["eps_ep_apartado_tipo"] = (string)$oXML->Localidade->Eps->Ep->Apartados->Apartado->attributes()->tipo;

				$this->_cttresponse['localidade']["eps_ep_apartado_liminf"] = (string)$oXML->Localidade->Eps->Ep->Apartados->Apartado->LimInf;
				$this->_cttresponse['localidade']["eps_ep_apartado_limsup"] = (string)$oXML->Localidade->Eps->Ep->Apartados->Apartado->LimSup;

				$this->_cttresponse['localidade']["eps_ep_apartado_codpos"] = (int)$oXML->Localidade->Eps->Ep->Apartados->Apartado->CodPos->Geo;
				$this->_cttresponse['localidade']["eps_ep_apartado_codpos_for"] = substr_replace((string)$oXML->Localidade->Eps->Ep->Apartados->Apartado->CodPos->Geo, '-', 4, 0);
				$this->_cttresponse['localidade']["eps_ep_apartado_codpos_cp3"] = (int)$oXML->Localidade->Eps->Ep->Apartados->Apartado->CodPos->CP3;
				$this->_cttresponse['localidade']["eps_ep_apartado_codpos_cp4"] = (int)$oXML->Localidade->Eps->Ep->Apartados->Apartado->CodPos->CP4;
				$this->_cttresponse['localidade']["eps_ep_apartado_codpos_designacao"] = (string)$oXML->Localidade->Eps->Ep->Apartados->Apartado->CodPos->Designacao;
			}

		}



		if($oXML->getName()=='Erro'){


			$this->_cttresponse['erro_razao'] = (string)$oXML->attributes()->razao;
			$this->_cttresponse['erro_razao_msg'] = (string)$this->echoMessage($oXML->attributes()->razao);
			$this->_cttresponse['totalresults'] = (string)$oXML->attributes()->total;
			$this->_cttresponse['resultstart'] = (string)$oXML->attributes()->inicio;
			$this->_cttresponse['resultend'] = (string)$oXML->attributes()->fim;

			if($oXML->attributes()->razao!="NADA"){
				// NADA nenhum registo encontrado
				if(count($oXML->Localidade)>0){

					$totallocalidades = $oXML->attributes()->total;
					if($totallocalidades>0){
						/* rua attributes */
						$allLocalidades=$oXML->Localidade;
						foreach  ($allLocalidades as $value) { 
							//var_dump($value->attributes()->num);
							$numid=(int)$value->attributes()->num;
							//print_r($numid);
							$this->_cttresponse{'localidade'}[$numid]['local_num'] = (string)$value->attributes()->num;
							$this->_cttresponse{'localidade'}[$numid]['local_id'] = (string)$value->attributes()->idlo;
							$this->_cttresponse{'localidade'}[$numid]['local_tipo'] = (string)$value->attributes()->tipo;
							$this->_cttresponse{'localidade'}[$numid]['local_tipo_msg'] = (string)$this->echoMessage($value->attributes()->tipo);

							$this->_cttresponse{'localidade'}[$numid]['nome'] = (string)$value->Designacao;
							$this->_cttresponse{'localidade'}[$numid]['designacao_tipo'] = (string)$value->Designacao->attributes()->tipo;

							$this->_cttresponse['localidade']{'rua'}[$numid]['distrito'] = (string)$value->Distrito;
							$this->_cttresponse['localidade']{'rua'}[$numid]['concelho'] = (string)$value->Concelho;
							$this->_cttresponse['localidade']{'rua'}[$numid]['freguesia'] = (string)$value->Freguesia;
						}
					}
						//var_dump($this->_cttresponse['localidade']);
				}

				if(count($oXML->Localidade->Arruamentos->Rua)>0){

					$totalruas = $oXML->Localidade->Arruamentos->attributes()->total;
					if($totalruas>0){
						/* rua attributes */
						$allRuas=$oXML->Localidade->Arruamentos->Rua;
						foreach  ($allRuas as $value) { 
							//var_dump($value->attributes()->num);
							$numid=(int)$value->attributes()->num;
							//print_r($numid);
							$this->_cttresponse['localidade']{'rua'}[$numid]['rua_id'] = (string)$value->attributes()->id;
							$this->_cttresponse['localidade']{'rua'}[$numid]['rua_num'] = (string)$value->attributes()->num;
							$this->_cttresponse['localidade']{'rua'}[$numid]['rua_tipo'] = (string)$value->attributes()->tipo;
							$this->_cttresponse['localidade']{'rua'}[$numid]['rua_tipo_msg'] = (string)$this->echoMessage($value->attributes()->tipo);
							$this->_cttresponse['localidade']{'rua'}[$numid]['rua_cod'] = (string)$value->attributes()->cod;

							$this->_cttresponse['localidade']{'rua'}[$numid]['nome'] = (string)$value->Designacao;
							$this->_cttresponse['localidade']{'rua'}[$numid]['freguesia'] = (string)$value->Freguesia;

							$this->_cttresponse['localidade']{'rua'}[$numid]['freguesia'] = (string)$value->Freguesia;

							$this->_cttresponse['localidade']{'rua'}[$numid]["codpos"] = (int)$value->CodPos->Geo;
							$this->_cttresponse['localidade']{'rua'}[$numid]["codpos_for"] = substr_replace((string)$value->CodPos->Geo, '-', 4, 0);
							$this->_cttresponse['localidade']{'rua'}[$numid]["codpos_des"] = (string)$value->CodPos->Designacao;
							$this->_cttresponse['localidade']{'rua'}[$numid]["codpos_cp4"] = (int)$value->CodPos->CP4;
							$this->_cttresponse['localidade']{'rua'}[$numid]["codpos_cp3"] = (int)$value->CodPos->CP3;

							$this->_cttresponse['localidade']{'rua'}[$numid]["desigSeg_tipo"] = (string)$value->DesigSeg->Tipo;
							$this->_cttresponse['localidade']{'rua'}[$numid]["desigSeg_priprep"] = (string)$value->DesigSeg->PriPrep;
							$this->_cttresponse['localidade']{'rua'}[$numid]["desigSeg_titulo"] = (string)$value->DesigSeg->Titulo;
							$this->_cttresponse['localidade']{'rua'}[$numid]["desigSeg_segprep"] = (string)$value->DesigSeg->SegPrep;
							$this->_cttresponse['localidade']{'rua'}[$numid]["desigSeg_nome"] = (string)$value->DesigSeg->Nome;
							$this->_cttresponse['localidade']{'rua'}[$numid]["desigSeg_local"] = (string)$value->DesigSeg->Local;
						}
					}
						//var_dump($this->_cttresponse['localidade']['rua']);
				}

			}
		}

		$oXML = $this->_cttresponse;

		if($json){
			$oXML = json_encode($this->_cttresponse);
		}
		return   $oXML;	
	}

	private function createParams(){
		$urltemp=array();
		if(!empty($this->_distrito)) { $urltemp["indistrito"] = $this->_distrito; };
		if(!empty($this->_concelho)) { $urltemp["inconcelho"] = $this->_concelho; };
		if(!empty($this->_local)) { $urltemp["inlocal"] = $this->_local; };
		if(!empty($this->_rua)) { $urltemp["inrua"] = $this->_rua; };
		if(!empty($this->_porta)) { $urltemp["inporta"] = $this->_porta; };
		if(!empty($this->_codpos)) { $urltemp["incodpos"] = $this->_codpos; };
		if(!empty($this->_cliente)) { $urltemp["incliente"] = $this->_cliente; };
		if(!empty($this->_pag)) { $urltemp["inpag"] = $this->_pag; };
		if(!empty($this->_maxpg)) { $urltemp["inmaxpg"] = $this->_maxpg; };
		if(!empty($this->_idlo)) { $urltemp["inidlo"] = $this->_idlo; };
		if(!empty($this->_idar)) { $urltemp["inep"] = $this->_idar; };
		if(!empty($this->_idep)) { $urltemp["inidep"] = $this->_idep; };
		if(!empty($this->_ep)) { $urltemp["inep"] = $this->_ep; };
		if(!empty($this->_apartado)) { $urltemp["inapartado"] = $this->_apartado; };
		return $urltemp;
	}

  	private function callCTT($url, $post = null) {
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_FAILONERROR,1);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
	    if(!empty($post)) {
	        curl_setopt($ch, CURLOPT_POST, true);
	        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
	    } 
	    $result = curl_exec($ch);
	    curl_close($ch);

	    return $result;
	}

	/////////////////////////////////////////////////////
	/////////// *** ECHO ALL RESPONSES *** //////////////

	private function echoMessage($cod){
	    switch ($cod) {

	    	/** Erro > Atributos > razao **/
	    case "NADA":
	        return "Pesquisa não retorna registos";       
	        break;
	    case "PEVA":
	        return "Pesquisa retorna mais que um registo";       
	        break;
	    case "EXCE":
	        return "Pesquisa retorna demasiados registos";       
	        break;
	    case "POER":
	        return "Número de porta não encontrado";       
	        break;
	    case "CPER":
	        return "Código postal errado";       
	        break;
	    case "CNPR":
	        return "A empresa/instituição não foi encontrada na rua especificada";       
	        break;
	    case "CNPL":
	        return "A empresa/instituição não foi encontrada na localidade especificada";       
	        break;
	    case "CNPC":
	        return "A empresa/instituição não foi encontrada no concelho especificado";       
	        break;
	    case "CNPD":
	        return "A empresa/instituição não foi encontrada no distrito especificado";       
	        break;
	    case "RNPL":
	        return "A rua não foi encontrada na localidade especificada";       
	        break;
	    case "RNPC":
	        return "A rua não foi encontrada no concelho especificado";       
	        break;
	    case "RNPD":
	        return "A rua não foi encontrada no distrito especificado";       
	        break;
	    case "LNPC":
	        return "A localidade escolhida não foi encontrada no concelho especificado";       
	        break;
	    case "LNPD":
	        return "A localidade escolhida não foi encontrada no distrito especificado";       
	        break;
	    case "LOFR":
	        return "A localidade escolhida não foi encontrada, mas existem freguesias com o mesmo nome";       
	        break;
	    case "CPNV":
	        return "Código Postal antigo substituído por novo(s) código(s) postal(ais)";       
	        break;

	        /** Localidade > Atributos > tipo **/
	    case "LOCA":
	        return "Código postal único para toda a localidade";       
	        break;
	    case "AGLO":
	        return "Códigos agrupados por localidade";       
	        break;

	        /** Rua > Atributos > tipo **/
	    case "UNRU":
	        return "Código postal único para toda a rua";       
	        break;
	    case "AGRU":
	        return "Códigos agrupados por rua";       
	        break;

	        /** Trocos > Atributos > tipo **/
	    case "I":
	        return "Números ímpares";       
	        break;
	    case "P":
	        return "Números pares";       
	        break;
	    case "V":
	        return "Nome";       
	        break;
	    case "T":
	        return "Lote";       
	        break;
	    case "S":
	        return "Sequencial";       
	        break;

	        /** Ep > Atributos > tipo **/
	    case "EC":
	        return "Estação de Correios";       
	        break;
	    case "CDP":
	        return "Centro de Distribuição Postal";       
	        break;
	    case "BEC":
	        return "Balcão Exterior de Correios";       
	        break;
	    case "POSTO":
	        return "Posto de Correios";       
	        break;

	    	/** Apartado > Atributos > tipo **/
	    case "G":
	        return "Grandes Clientes";       
	        break;
	    case "B":
	        return "Blocos de Apartados";       
	        break;

	    default: 
	        return "Sem comentários";
	        break;     
		}

	} /// END ERROR cttresponsecod ///

} /// END CLASS ///
 
?>
