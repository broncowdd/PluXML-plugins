<?php /**
* Plugin buddies_links - config page
*
* @package	PLX
* @version	1.0
* @date	16/09/16
* @author Bronco
**/
if(!defined("PLX_ROOT")) exit; ?>
<?php 
	if(!empty($_POST)) {
		$names 			=implode('|',$_POST['name']);
		$links 			=implode('|',$_POST['link']);
		$descriptions 	=implode('|',$_POST['description']);

		$plxPlugin->setParam("desc_in_title", plxUtils::strCheck($_POST['desc_in_title']), "string");
		$plxPlugin->setParam("name", plxUtils::strCheck($names), "string");
		$plxPlugin->setParam("link", plxUtils::strCheck($links), "string");
		$plxPlugin->setParam("description", plxUtils::strCheck($descriptions), "string");

		$plxPlugin->saveParams();
		header("Location: parametres_plugin.php?p=buddies_links");
		exit;
	}
?>
<script>

 function removeme(obj){
	if (obj.length==0){return;}
	parent=obj.parentNode;
	console.log(parent);
	parent.remove();
}
 function addnew(obj){
	if (obj.length==0){return;}
	// clone the completed one & add it after
	fullone=obj.parentNode.cloneNode(true);
	obj.parentNode.parentNode.appendChild(fullone);

	// reset the add_new form
	obj.parentNode.removeAttribute('id');
	obj.parentNode.childNodes.forEach(function(object){
		object.value='';
	});
	fullone.lastElementChild.removeAttribute('class');
	fullone.lastElementChild.previousElementSibling.style.display='none';
	
}

</script>
<style> 
	.hidden{display:none;}
	fieldset{padding:10px;margin-bottom:20px;border-bottom:1px solid rgba(0,0,0,0.5);}
	.remove a{color:red;}
</style>

<h2><?php $plxPlugin->lang("L_TITLE") ?></h2>
<p><?php $plxPlugin->lang("L_DESCRIPTION") ?></p>
<form action="parametres_plugin.php?p=buddies_links" method="post" >
	<div>
		<select style="width:100%;" name="desc_in_title" value="<?php echo $plxPlugin->getParam("desc_in_title");?>"><option value="0" <?php echo $plxPlugin->getParam("desc_in_title")=="0" ? 'selected="true"' : ''?>><?php echo $plxPlugin->lang("L_DESCRIPTION_ON_HOVER");?></option>
		<option value="1" <?php echo $plxPlugin->getParam("desc_in_title")=="1" ? 'selected="true"' : 'no'?>><?php echo $plxPlugin->lang("L_DESCRIPTION_VISIBLE");?></option></select>

	</div>
	<hr/>
	<div>
	<div id="add">
		<fieldset id="clone_this">
			<input type="text" style="width:100%;" name="name[]" value="" placeholder="<?php $plxPlugin->lang("L_NAME") ?>"/>

			<input type="url" style="width:100%;" name="link[]" value=""  placeholder="<?php $plxPlugin->lang("L_URL_EXAMPLE") ?>"/>

			<input type="text" style="width:100%;" name="description[]" placeholder="" value=""/>

			<a onclick="addnew(this);" id="add_link">&#8853; <?php $plxPlugin->lang("L_ADD_NEW") ?> &#9660;</a>
			<a onclick="removeme(this);" class="hidden">&#10006; <?php $plxPlugin->lang("L_REMOVE");?></a>
		</fieldset>
	</div>
<?php
	$names 			=array_filter(explode('|',$plxPlugin->getParam("name")));
	$links 			=array_filter(explode('|',$plxPlugin->getParam("link")));
	$descriptions 	=array_filter(explode('|',$plxPlugin->getParam("description")));

	foreach ($names as $key => $value) {
		echo '
		<div>
			<fieldset class="remove">
				<input type="text" style="width:100%;" name="name[]" value="'.$value.'" placeholder="'.$plxPlugin->getlang("L_NAME").'"/>

				<input type="url" style="width:100%;" name="link[]" value="'.$links[$key].'"  placeholder="'.$plxPlugin->getlang("L_URL_EXAMPLE").'"/>

				<input type="text" style="width:100%;" name="description[]" placeholder="'.$plxPlugin->getlang("L_DESC").'" value="'.$descriptions[$key].'"/>

				<a onclick="removeme(this)">&#10006; '.$plxPlugin->getlang("L_REMOVE").' '.$value.'</a>
			</fieldset>
		</div>
		';
	}
?>


	</div>
	<p class="right"></p>
	<br />
	<input type="submit" name="submit" value="Enregistrer"/>
</form>
