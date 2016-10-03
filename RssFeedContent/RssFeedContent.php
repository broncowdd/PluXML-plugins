<?php
/**
* Plugin RssFeedContent
*
* @package	PLX
* @version	1.0
* @date	23/09/16
* @author Bronco
**/
class RssFeedContent extends plxPlugin {
	public function __construct($default_lang) {
		# appel du constructeur de la classe plxPlugin (obligatoire)
		parent::__construct($default_lang);


		
		# Declaration d'un hook (existant ou nouveau)
		$this->addHook('showRssFeedContent','showRssFeedContent');

		
	}

	# Activation / desactivation
	public function OnActivate() {
		# code à executer à l’activation du plugin
	}
	public function OnDeactivate() {
		# code à executer à la désactivation du plugin
	}

	private function feed2array($feed,$load=true){
		if ($load){if (!$feed_content=$this->file_curl_contents($feed)){return false;}}else{$feed_content=$feed;}
		$flux=array('infos'=>array(),'items'=>array());
		if(preg_match('~<rss(.*)</rss>~si', $feed_content)){$type='RSS';}//RSS ?
		elseif(preg_match('~<feed(.*)</feed>~si', $feed_content)){$type='ATOM';}//ATOM ?
		else return false;//if the feed isn't rss or atom

		$flux['infos']['type']=$type;
		ini_set('display_errors', '0'); 
		$feed_content=trim($feed_content," \t\n\r\0\x0B");
		try{$feed_obj = new SimpleXMLElement($feed_content, LIBXML_NOCDATA);}
		catch (Exception $e){return false;}	
		ini_set('display_errors', '1');
			$flux['infos']['version']=$feed_obj->attributes()->version;
			if (!empty($feed_obj->attributes()->version)){	$flux['infos']['version']=(string)$feed_obj->attributes()->version;}
			if (!empty($feed_obj->channel->title)){			$flux['infos']['title']=(string)$feed_obj->channel->title;}
			if (!empty($feed_obj->channel->subtitle)){		$flux['infos']['subtitle']=(string)$feed_obj->channel->subtitle;}
			if (!empty($feed_obj->channel->link)){			$flux['infos']['link']=(string)$feed_obj->channel->link;}
			if (!empty($feed_obj->channel->description)){	$flux['infos']['description']=(string)$feed_obj->channel->description;}
			if (!empty($feed_obj->channel->language)){		$flux['infos']['language']=(string)$feed_obj->channel->language;}
			if (!empty($feed_obj->channel->copyright)){		$flux['infos']['copyright']=(string)$feed_obj->channel->copyright;}

			if (!empty($feed_obj->title)){					$flux['infos']['title']=(string)$feed_obj->title;}
			if (!empty($feed_obj->subtitle)){				$flux['infos']['subtitle']=(string)$feed_obj->subtitle;}
			if (!empty($feed_obj->link)){					$flux['infos']['link']=(string)$feed_obj->link;}
			if (!empty($feed_obj->description)){			$flux['infos']['description']=(string)$feed_obj->description;}
			if (!empty($feed_obj->language)){				$flux['infos']['language']=(string)$feed_obj->language;}
			if (!empty($feed_obj->copyright)){				$flux['infos']['copyright']=(string)$feed_obj->copyright;}
			
			if (!empty($feed_obj->channel->item)){	$items=$feed_obj->channel->item;}
			if (!empty($feed_obj->entry)){	$items=$feed_obj->entry;}

			if (empty($items)){return false;}

			
			foreach ($items as $item){
				$c=count($flux['items']);
				if(!empty($item->title)){		 	$flux['items'][$c]['title'] 	  =	(string)$item->title;}
				if(!empty($item->logo)){		 	$flux['items'][$c]['titleImage']  =	(string)$item->logo;}
				if(!empty($item->icon)){		 	$flux['items'][$c]['icon'] 		  =	(string)$item->icon;}
				if(!empty($item->link['href'])){ 	$flux['items'][$c]['link']		  = (string)$item->link['href'];}
				if(!empty($item->language)){		$flux['items'][$c]['language']	  = (string)$item->language;}
				if(!empty($item->author->name)){ 	$flux['items'][$c]['author']	  =	(string)$item->author->name;}
				if(!empty($item->author->email)){	$flux['items'][$c]['email'] 	  = (string)$item->author->email;}
				if(!empty($item->updated)){			$flux['items'][$c]['last'] 		  = (string)$item->updated;}
				if(!empty($item->rights)){			$flux['items'][$c]['copyright']	  = (string)$item->rights;}
				if(!empty($item->generator)){		$flux['items'][$c]['generator']	  = (string)$item->generator;}
				if(!empty($item->guid)){			$flux['items'][$c]['guid']	 	  = (string)$item->guid;}
				if(!empty($item->pubDate)){			$flux['items'][$c]['pubDate']	  = (string)$item->pubDate;$flux['items'][$c]['date'] =(string)$item->pubDate;}
				if(!empty($item->published)){		$flux['items'][$c]['date']		  = (string)$item->published;}
				if(!empty($item->update)){			$flux['items'][$c]['update'] 	  = (string)$item->update;}
				if(!empty($item->link)){			$flux['items'][$c]['link'] 	  	  = (string)$item->link;}
				if(!empty($item->summary)){			$flux['items'][$c]['description'] = (string)$item->summary;}
				if(!empty($item->subtitle)){ 		$flux['items'][$c]['description'] = (string)$item->subtitle;}
				if(!empty($item->description)){		$flux['items'][$c]['description'] = (string)$item->description;}
				if(!empty($item->content)){			$flux['items'][$c]['description'] = (string)$item->content;}
			}	
			
		return $flux;
	}
	private function fileage($file){
		if (!is_file($file)){return false;}
		if ($t=@date('U',filemtime($file))){return @date('U')-$t;}
		else{return false;}
	}
	# curlContent
	###########################
	## $url as string
	## Return : string
	private function file_curl_contents($url){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept-Charset: UTF-8'));
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,  FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, $url);
		if (!ini_get("safe_mode") && !ini_get('open_basedir') ) {curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);}
		curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Linux x86_64; rv:40.0) Gecko/20100101 Firefox/40.0');    
		curl_setopt($ch, CURLOPT_REFERER, 'http://noreferer.com');
		$data = curl_exec($ch);
		$response_headers = curl_getinfo($ch);
		curl_close($ch);
		return $data;
	}
	########################################
	# HOOKS
	########################################


	########################################
	# showRssFeedContent
	########################################
	# Description:
	public function showRssFeedContent($array){
		if (empty($array)){return;}
		$feed_url            =$array[0];
		$description_visible =$array[1];
		$nb                  =$array[2];
		$obsolete_time       =$array[3];
		if (empty($feed_url)){echo 'No feed url given !';}
		$c=0;
		$file=PLX_PLUGINS.'RssFeedContent/cache/'.sha1($feed_url);
		$age=$this->fileage($file);

		if ($age && $age<$obsolete_time){
			echo file_get_contents($file);
		}else{

			$feed_content=$this->feed2array($feed_url,true);
			if (empty($feed_content)){echo 'Feed content empty !';}
			$echo = '<ul class="rssFeedContent">';
			foreach ($feed_content['items'] as $item) {
				
				if ($c==$nb){
					$echo.= '</ul>';
					break;
				}
				$echo.= '<li>';
					if ($description_visible){
						$echo.= '<span class="item-title"><a href="'.$item['link'].'" title="'.$item['date'].'">'.$item['title'].'</a></span>';
						$echo.= '<span class="item-description">'.$item['description'].'</span>';
					}else{
						$echo.= '<span class="item-title"><a href="'.$item['link'].'" title="'.$item['description'].'">'.$item['title'].'</a></span>';
					}
				$echo.= '</li>';
				$c++;
			}
			$echo.= '</ul>';
			echo $echo;
			file_put_contents($file,$echo);
		}
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