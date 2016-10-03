<?php
/**
* Plugin AutoEcho
*
* @package	PLX
* @version	1.0
* @date	03/10/16
* @author Bronco
**/
class AutoEcho extends plxPlugin {
	public function __construct($default_lang) {
		# appel du constructeur de la classe plxPlugin (obligatoire)
		parent::__construct($default_lang);

		
		
		# limite l'acces a l'ecran de configuration du plugin
		# PROFIL_ADMIN , PROFIL_MANAGER , PROFIL_MODERATOR , PROFIL_EDITOR , PROFIL_WRITER
		$this->setConfigProfil(PROFIL_ADMIN);
		
		
		# Declaration d'un hook (existant ou nouveau)
		$this->addHook('plxAdminEditArticle','plxAdminEditArticle');

		
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
	# plxAdminEditArticle
	########################################
	# Description:
	public function plxAdminEditArticle(){
		$file=$this->getParam("shaarli_db_path");global $plxAdmin;
		echo '<?php
		$PHPPREFIX=\'<?php /* \'; // Prefix to encapsulate data in php code.
		$PHPSUFFIX=\' */ ?>\'; // Suffix to encapsulate data in php code.
		# get shaarli db content
		$links=(file_exists("'.$file.'") ? unserialize(gzinflate(base64_decode(substr(file_get_contents("'.$file.'"),strlen($PHPPREFIX),-strlen($PHPSUFFIX))))) : array() );
			if (!empty($content["url_link"])){$_URL=$content["url_link"];}else{$_URL=$url;}
			if (!empty($content["chapo"])){$_DESC=$content["chapo"];}else{$_DESC=$content["content"];}
			if (!empty($content["thumbnail"])){$_IMG="![titre]('.$plxAdmin->aConf['racine'].'".$content["thumbnail"].") \n";}else{$_IMG="";}
            $link = array(
             	"title"=>$content["title"],
             	"url"=>$_URL,
             	"description"=>$_IMG.$_DESC."\n\n*repost automatique de mon site...*",
             	"private"=>isset($content["draft"]),
             	"linkdate"=>$content["date_publication_year"].$content["date_publication_month"].$content["date_publication_day"]."_".str_replace(":","",$content["date_publication_time"])."00",
             	"tags"=>str_replace(","," ",$content["tags"])
            );
            $links[$link["linkdate"]] = $link;
            file_put_contents("'.$file.'", $PHPPREFIX.base64_encode(gzdeflate(serialize($links))).$PHPSUFFIX); // Write database to disk
            
            ?>';
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