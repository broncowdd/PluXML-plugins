<?php /**
* Plugin AutoEcho - config page
*
* @package	PLX
* @version	1.0
* @date	03/10/16
* @author Bronco
**/
if(!defined("PLX_ROOT")) exit; ?>
<?php 
	if(!empty($_POST)) {
		$plxPlugin->setParam("shaarli_db_path", plxUtils::strCheck($_POST["shaarli_db_path"]), "string");

		$plxPlugin->saveParams();
		header("Location: parametres_plugin.php?p=AutoEcho");
		exit;
	}
?>
<h2><?php $plxPlugin->lang("L_TITLE") ?></h2>
<p><?php $plxPlugin->lang("L_DESCRIPTION") ?></p>
<form action="parametres_plugin.php?p=AutoEcho" method="post" >
	<li>
		<label>shaarli_db_path : 
			<input type="text" style="width:100%;" name="shaarli_db_path" value="<?php echo $plxPlugin->getParam("shaarli_db_path");?>"/>
		</label>
	</li>

	<br />
	<input type="submit" name="submit" value="Enregistrer"/>
</form>