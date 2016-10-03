Permet d'ajouter un flux rss dans une page via un hook:
$plxShow->callHook("showRssFeedContent",array(
	feed_url,description_visible_true/false,items_nb,obsolete_time_in_seconds))
