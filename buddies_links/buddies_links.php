<?php
/**
* Plugin buddies_links
*
* @package	PLX
* @version	1.0
* @date	16/09/16
* @author Bronco
**/
class buddies_links extends plxPlugin {
	public function __construct($default_lang) {
		# appel du constructeur de la classe plxPlugin (obligatoire)
		parent::__construct($default_lang);

		
		
		# limite l'acces a l'ecran de configuration du plugin
		# PROFIL_ADMIN , PROFIL_MANAGER , PROFIL_MODERATOR , PROFIL_EDITOR , PROFIL_WRITER
		$this->setConfigProfil(PROFIL_ADMIN);
		
		
		# Declaration d'un hook (existant ou nouveau)
		$this->addHook('show_buddies_links','show_buddies_links');

		
	}

	# Activation / desactivation
	public function OnActivate() {
		# code à executer à l’activation du plugin
	}
	public function OnDeactivate() {
		# code à executer à la désactivation du plugin
	}
	

	########################################
	# HOOKS
	########################################


	########################################
	# show_buddies_links
	########################################
	# Description:
	public function show_buddies_links(){
		$names 			=array_filter(explode('|',$this->getParam("name")));
		$links 			=array_filter(explode('|',$this->getParam("link")));
		$descriptions 	=array_filter(explode('|',$this->getParam("description")));
		$desc			=$this->getParam("desc_in_title");
		$ordered 		=array();
		foreach ($names as $key => $name){
			$ordered[$name]['link']=$links[$key];
			$ordered[$name]['description']=$descriptions[$key];
		}
		ksort($ordered);
		echo '<ul class="buddies_links unstyled-list">';
		foreach ($ordered as $name => $value) {
			if (empty($desc)){
				$title=$value['description'];
				$desc ='';
			}else{
				$title='';
				$desc =$value['description'];
			}
			echo '
				<li>
					<a href="'.$value['link'].'" title="'.$title.'">
						<img src="'.$this->get_favicon($value['link']).'" alt="favicon"/> '.$name.'
					</a>
					<span>'.$desc.'</span>
				</li>
			';
		}
		echo '</ul>';
	}

	private function get_favicon($url){      
        $id=sha1($url);
        $file=PLX_PLUGINS.'buddies_links/favicons/'.$id.'.png';     
        $defaultfavicon=PLX_PLUGINS.'buddies_links/favicons/default_favicon.png';     
        if ($url==''){return $defaultfavicon;}     
        if (!is_file($file)){         
	        @$header=file_get_contents($url, NULL, NULL, 0, 3000);
	        if ($header){       
		        if (preg_match('#<link +rel=["\'].*icon["\'].+href="([^"]+)|<link.+href="([^"]+).+rel=["\'].*icon["\']#i',$header,$r)>0){
			        if ($r[1]==''){$f=$r[2];}else{$f=$r[1];}                 
			        @$img=file_get_contents($f);                 
			        $url2=pathinfo($url ,PATHINFO_DIRNAME );   
			        if(!$img){@$img=file_get_contents($url.'/'.$f);}  
			        if(!$img){@$img=file_get_contents($url.$f);}    
			        if(!$img){@$img=file_get_contents($url2.'/'.$f);}  
			        if(!$img){@$img=file_get_contents($url2.$f);}  
		        } 
		       		        
		    } 
			if (empty($img)){   			        	              
				$img=file_get_contents($defaultfavicon);
			} 
		    file_put_contents($file,$img);    
	    }
	        
	    
	    return $file;
	}

}





/* Pense-bete:
 * Récuperer des parametres du fichier parameters.xml
 *	$this->getParam("<nom du parametre>")
 *	$this-> setParam ("param1", 12345, "numeric")
 *	$this->saveParams()
 *
 *	plxUtils::strCheck($string) : sanitize string
 *
 * 
 * Quelques constantes utiles: 
 * PLX_CORE
 * PLX_ROOT
 * PLX_CHARSET
 * PLX_CONFIG_PATH
 * PLX_PLUGINS
 * PLX_CONFIG_PATH
 * PLX_ADMIN (true si on est dans admin)
 * PLX_CHARSET
 * PLX_VERSION
 * PLX_FEED
 *
 * Appel de HOOK dans un thème
 *	eval($plxShow->callHook("ThemeEndHead","param1"))  ou eval($plxShow->callHook("ThemeEndHead",array("param1","param2")))
 *	ou $retour=$plxShow->callHook("ThemeEndHead","param1"));
 */