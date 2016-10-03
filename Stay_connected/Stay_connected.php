<?php
class Stay_connected extends plxPlugin {
	public function __construct($default_lang) {
		# appel du constructeur de la classe plxPlugin (obligatoire)
		parent::__construct($default_lang);

		# limite l'accès à l'écran d'administration du plugin
		# PROFIL_ADMIN , PROFIL_MANAGER , PROFIL_MODERATOR , PROFIL_EDITOR , PROFIL_WRITER
		$this->setConfigProfil(PROFIL_ADMIN);
		
		# Déclaration d'un hook (existant ou nouveau)
		$this->addHook('AdminAuth','AdminAuth');
		$this->addHook('AdminAuthPrepend','AdminAuthPrepend');

		
	}

	# Activation / désactivation
	public function OnActivate() {
		// generation du fichier salt unique (pour sécuriser le cookie)
		function generate_salt($length=256){
			$salt='';
			for($i=1;$i<=$length;$i++){
				$salt.=chr(mt_rand(35,126));
			}
			return str_replace(['"',"'"],'!',$salt);
		}

		$path_to_salt=PLX_PLUGINS.'Stay_connected/salt.php';
		if (!is_file($path_to_salt)){
			file_put_contents($path_to_salt,'<?php define("SALT_STAY_CONNECTED","'.generate_salt(512).'");?>' );
		}
	}

	
	# HOOKS
	public function AdminAuth(){
		# Ajout de la case rester connecté
		echo '
			<input type="checkbox" id="id_stay_connected" name="stay_connected" style="float:none;width:5%;vertical-align: middle;"/>
			<label for="id_stay_connected" style="float:none;width:80%;display:inline-block;vertical-align: middle;" title="Ne cochez pas sur des ordinateurs publics !">
				Rester connecté
			</label>
			<hr style="height:1px;background:#ddd;border:none"/>
		';
		
	}
	public function AdminAuthPrepend(){
		global $session_domain;
		function isLogged(){
		    if (
		        !isset($_SESSION['user']) ||
		        !isset($_SESSION['profil']) ||
		        !isset($_SESSION['hash']) ||
		        !isset($_SESSION['domain']) ||
		        !isset($_SESSION['lang'])
		    ){return false;}
		    else{return true;}
		}
		global $plxAdmin;
		#########################
		# variables spécifiques #
		#########################
		$path_to_salt=PLX_PLUGINS.'Stay_connected/salt.php';
		$cookies_path=PLX_PLUGINS.'Stay_connected/cookies_files/';
		$cookie_name=preg_replace('#[^a-zA-Z]#','',$plxAdmin->aConf["racine"]);
		
		#################################################
		# remplacement de la procédure d'identification #
		#################################################
		# Initialisation variable erreur
		$error = "";
		$msg = "";

		#########################################
		# Déconnexion (ajout: retait du cookie) #
		#########################################
		if(!empty($_GET["d"]) AND $_GET["d"]==1) {
			if (isset($_COOKIE[$cookie_name])){
				setcookie($cookie_name,'',time()-60);// Expiration forcée du cookie
				if (is_file($cookies_path.$_COOKIE[$cookie_name])){
					unlink($cookies_path.$_COOKIE[$cookie_name]);
				}
			}

			$_SESSION = array();
			session_destroy();
			header("Location: auth.php");
			exit;

			$formtoken = $_SESSION["formtoken"]; # sauvegarde du token du formulaire
			$_SESSION = array();
			session_destroy();
			session_start();
			$msg = L_LOGOUT_SUCCESSFUL;
			$_GET["p"]="";
			$_SESSION["formtoken"]=$formtoken; # restauration du token du formulaire
			unset($formtoken);
		}

		##########################################################################
		# Authentification (ajouts: vérification du cookie / création du cookie) #
		##########################################################################
		# Controle et filtrage du parametre $_GET['p']
		$redirect=$plxAdmin->aConf['racine'].'core/admin/';
		if(!empty($_GET['p'])) {
			$racine = parse_url($plxAdmin->aConf['racine']);
			$get_p = parse_url(urldecode($_GET['p']));
			$error = (!$get_p OR (isset($get_p['host']) AND $racine['host']!=$get_p['host']));
			if(!$error AND !empty($get_p['path']) AND file_exists(PLX_ROOT.'core/admin/'.basename($get_p['path']))) {
				# filtrage des parametres de l'url
				$query='';
				if(isset($get_p['query'])) {
					$query=strtok($get_p['query'],'=');
					$query=($query[0]!='d'?'?'.$get_p['query']:'');
				}
				# url de redirection
				$redirect=$get_p['path'].$query;
			}
		}

		# Ajout 1: vérification du cookie 
		# si le cookie correspond à un fichier de token autorisé -> login direct
		if (!isLogged() && isset($_COOKIE[$cookie_name]) && is_file($cookies_path.$_COOKIE[$cookie_name])){
			$user_nb=file_get_contents($cookies_path.$_COOKIE[$cookie_name]);
			$connected = false;
			foreach($plxAdmin->aUsers as $userid => $user) {
				if ($user_nb==$userid AND $user["active"] AND !$user["delete"]) {
					$_SESSION["user"] = $userid;
					$_SESSION["profil"] = $user["profil"];
					$_SESSION["hash"] = plxUtils::charAleatoire(10);
					$_SESSION["domain"] = $session_domain;
					$_SESSION["lang"] = $user["lang"];
					$connected = true;
				}
			}
			if($connected){
				
				header("Location: ".htmlentities($redirect));
				exit;
			}
		}
		else if(!isLogged() && !empty($_POST["login"]) AND !empty($_POST["password"])) {
			$connected = false;
			foreach($plxAdmin->aUsers as $userid => $user) {
				if ($_POST["login"]==$user["login"] AND sha1($user["salt"].md5($_POST["password"]))==$user["password"] AND $user["active"] AND !$user["delete"]) {
					$_SESSION["user"] = $userid;
					$_SESSION["profil"] = $user["profil"];
					$_SESSION["hash"] = plxUtils::charAleatoire(10);
					$_SESSION["domain"] = $session_domain;
					$_SESSION["lang"] = $user["lang"];
					$connected = true;
				}
			}
			if($connected) {
				# AJOUT: gestion de la case à cocher,
				# création du cookie (et du fichier user correspondant au token du cookie)
				if (!empty($_POST['stay_connected'])){
					include ($path_to_salt);
					# ici, on crée un token pour la génération du cookie
					$token_cookie=hash('sha512',SALT_STAY_CONNECTED.md5(preg_replace('#[^a-zA-Z]#','',uniqid(true))));
					setcookie($cookie_name,$token_cookie,time()+31536000);// Expiration au bout d'un an
						file_put_contents($cookies_path.$token_cookie,$_SESSION["user"]);
				}
				header("Location: ".htmlentities($redirect));
				exit;
			} else {
				$msg = L_ERR_WRONG_PASSWORD;
				$error = "error";
			}
		}
	
	}


}


?>
