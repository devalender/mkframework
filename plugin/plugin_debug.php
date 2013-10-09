<?php
class plugin_debug{
	
	private $iStartMicrotime;
	private $sHtml;
	
	private static $tSpy;
	
	public function __construct($iMicrotime){
		$this->iStartMicrotime=$iMicrotime;
		
		$iDiff=(microtime()-$this->iStartMicrotime);
		
		$this->add('Time',$iDiff);
		
		$this->addComplex('$_GET',print_r($_GET,1));
		
		if(isset($_POST)){
			$this->addComplex('$_POST',print_r($_POST,1));
		}
		
		if(isset($_SESSION)){
			$this->addComplex('$_SESSION',print_r($_SESSION,1));
		}
		
		if(isset($_SERVER)){
			$this->addComplex('$_SERVER',print_r($_SERVER,1));
		}
		
		$oRequest=_root::getRequest();
		
		$this->add('Module',$oRequest->getModule());
		$this->add('Action',$oRequest->getAction());
		
		$oFileLog=new _file(_root::getConfigVar('path.log','data/log/').date('Y-m-d').'_log.csv');
		if($oFileLog->exist()){ 
			$oFileLog->load();
			$sContentLog=$oFileLog->getContent();
			$this->addFileLog('File log',$sContentLog);
		}
		
		$sVarIniConfig=_root::getConfigVar('model.ini.var','db');
		$tClassSgbd=_root::getConfigVar($sVarIniConfig);
		$this->addComplexIni('Connexions',array($sVarIniConfig=>$tClassSgbd));
		
		$tConfigSection=array(
			'path' ,
			'cache' ,
			'language',
			'auth',
			'acl',
			'navigation',
			'urlrewriting',
			'security',
			'log',
			'check',
			'path',
			'model',
		);
		$tConfig=array();
		foreach($tConfigSection as $sSection){
			$tConfig[$sSection]=_root::getConfigVar($sSection);
		}
		
		$this->addComplexIni('Config',$tConfig);
		
		if(self::$tSpy){
			$this->addComplexSpy('Spy variables',self::$tSpy);
		}
	}
	
	public static function addSpy($uLabel,$uVar){
		self::$tSpy[][$uLabel]=$uVar;
	}
	
	public function display(){
		echo '<script>
		var activePopup=\'\';
		function openPopupDebug(id){
				closePopup();
				var a=getById(id);
				if(a){
					a.style.display="block";
					activePopup=id;
				}
			}
			function closePopup(){
				if(activePopup){
					var b=getById(activePopup);
					if(b){
						b.style.display="none";
					}
				}
			}
			</script>';
		echo '<div style="position:absolute;border:2px solid #444;background:#ddd;bottom:0px;left:0px;width:80%">';
		echo $this->sHtml;
		echo '</div>';
	}
	
	private function addComplex($key,$value){
		$this->addHtml('<input type="button" value="'.$key.'" onclick="openPopupDebug(\'popupDebug'.$key.'\')" />');
		$this->addSep();
		
		$this->addPopupPrintr($key,$value);
	}
	private function addComplexIni($key,$value){
		$this->addHtml('<input type="button" value="'.$key.'" onclick="openPopupDebug(\'popupDebug'.$key.'\')" />');
		$this->addSep();
		
		$value=$this->parseIni($value);
		
		$this->addPopup($key,$value);
	}
	private function addComplexSpy($key,$value){
		$this->addHtml('<input type="button" value="'.$key.'" onclick="openPopupDebug(\'popupDebug'.$key.'\')" />');
		$this->addSep();
		
		$sValue=$this->parseSpy($value);
		
		$this->addPopup($key,$sValue);
	}
	
	private function addFileLog($key,$value){
		$this->addHtml('<input type="button" value="'.$key.'" onclick="openPopupDebug(\'popupDebug'.$key.'\')" />');
		$this->addSep();
		
		$value=$this->parseLog($value);
		
		$this->addPopup($key,$value);
	}
	
	private function add($key,$value){
		$this->addHtml('<strong>'.$key.'</strong>:<span style="padding:2px 4px;background:#fff">'.$value.'</span>');
		$this->addSep();
	}
	
	private function addPopupPrintr($key,$value){
		$this->addHtml('<div id="popupDebug'.$key.'" style="display:none;position:absolute;left:0px;bottom:0px;border:2px solid gray;background:white">
		<p style="text-align:right;background:#ccc;margin:0px;"><a href="#" onclick="closePopup()">Fermer</a></p>
		<div style="height:350px;width:400px;overflow:auto;padding:10px;"><pre>'.customHtmlentities(print_r($value,1)).'</pre></div></div>');
	}
	
	private function addPopup($key,$value){
		$this->addHtml('<div id="popupDebug'.$key.'" style="display:none;position:absolute;left:0px;bottom:0px;border:2px solid gray;background:white">
		<p style="text-align:right;background:#ccc;margin:0px;"><a href="#" onclick="closePopup()">Fermer</a></p>
		<div style="height:350px;width:800px;overflow:auto;padding:10px;">'.$value.'</pre></div></div>');
	}
	
	private function addSep(){
		$this->addHtml('&nbsp;&nbsp;&nbsp;&nbsp;');
	}
	
	private function addHtml($sHtml){
		$this->sHtml.=$sHtml;
	}
	
	private function parseLog($value){
		$sep=' | ';
		
		$tLine=explode("\n",$value);
		$sHtml=null;
		 
		$iMax=count($tLine)-1;
		for($i=$iMax;$i>0;$i--){
			$sLine=$tLine[$i];
				
			$tCase=explode(';',$sLine,4);
			$sDate=null;if(isset($tCase[0])){ $sDate=$tCase[0]; }
			$sTime=null;if(isset($tCase[1])){ $sTime=$tCase[1]; }
			$sType=null;if(isset($tCase[2])){ $sType=$tCase[2]; }
			$sLog=null;if(isset($tCase[3])){ $sLog=$tCase[3]; }
			
			if($sDate==null){ continue;}
			
			$sHtml.='<p style="border-bottom:1px dotted gray">';
		
				$sHtml.='<span >'.$sDate.'</span> ';
				$sHtml.='<span style="font-weight:bold">'.$sTime.'</span>';
				
				$sHtml.=$sep;
				
				$sHtml.='<span style="color:';
				if($sType=='info'){ $sHtml.='gray';}
				elseif($sType=='log'){ $sHtml.='darkblue';}
				$sHtml.='">'.$sType.'</span>';
				
				$sHtml.=$sep;
				
				$sHtml.=$sLog;
			
			$sHtml.='</p>';
			
			if(preg_match('/module a appeler/',$sLog)){ 
				$sHtml.='<p>&nbsp;</p>';
			}
			
		}
		return $sHtml;
	}
	
	private function parseSpy($tValue){
		$sHtml=null;
		foreach($tValue as $tDetail){
			foreach($tDetail as $ref => $value){
				$sHtml.='<h2 style="border-bottom:1px solid black">'.$ref.'</h2>';
				$sHtml.='<p><pre>'.customHtmlentities(print_r($value,1)).'</pre></p>';
			}
		}
		
		return $sHtml;
	}
	
	private function parseIni($tValue){
		$sHtml=null;
		foreach($tValue as $sSection => $tDetail){
			$sHtml.='<h2 style="border-bottom:1px solid black">'.$sSection.'</h2>';
			foreach($tDetail as $sKey => $sValue){
				$sHtml.='<p style="margin:0px;margin-left:10px;"><strong>'.$sKey.'</strong> = <span style="color:darkgreen"> '.$sValue.'</span></p>';
			}
		}
		
		return $sHtml;
	}
	
}
