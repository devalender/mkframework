<?php $tEnable=_root::getParam('tEnable')?>
<h1>Cr&eacute;er un module Lecture seule Bootstrap</h1>
<p>Choisissez une classe mod&egrave;le</p>
<div class="smenu">
<ul>
<?php if($this->tFile)?>
<?php foreach($this->tFile as $sFile):?>
	<?php if(_root::getParam('class')==$sFile):?>
		<li class="selectionne"><?php echo $sFile?></li>
	<?php else:?>
		<li><a href="<?php echo _root::getLink(_root::getRequest()->getParamNav(),array(
									'id' => _root::getParam('id'),
									'action' => 'crudreadonlyWithBootstrap',
									'class'=> $sFile
							))?>#editcrud"><?php echo $sFile?></a></li>
	<?php endif;?>
<?php endforeach;?>
</ul>
</div>
<br />

<?php if(_root::getParam('class') !=''):?>
<a id="editcrud" name="editcrud"></a>
<div class="table">
	<?php if($this->tColumn)?>
	<form action="" method="POST">
	<p>Nom du module &agrave cr&eacute;er <input type="text" name="moduleToCreate" value="<?php echo _root::getParam('moduleToCreate',$this->sModuleToCreate)?>"/></>
	<?php if(!_root::getParam('moduleToCreate') and file_exists(_root::getConfigVar('path.generation')._root::getParam('id').'/module/'.$this->sModuleToCreate)):?>
		<p class="error">Le module module/<?php echo $this->sModuleToCreate?> existe d&eacute;j&agrave;, veuillez indiquer un autre nom </p>
	<?php endif;?>

	<input type="hidden" name="sClass" value="<?php echo $this->sClass?>" />
	<table>
		<tr>
			<th></th>
			<th>Champ</th>
			<th>Type</th>
		</tr>
	<?php foreach($this->tColumn as $sColumn):?>
		<tr>
			<td><input type="checkbox" name="tEnable[]" value="<?php echo $sColumn?>" <?php if(!is_array($tEnable)):?>checked="checked"<?php elseif(in_array($sColumn,$tEnable)):?>checked="checked"<?php endif;?> /></td>
			<td><?php echo $sColumn?><input type="hidden" name="tColumn[]" value="<?php echo $sColumn?>" /></td>
			<td><select name="tType[]">
				<option value="text">text</option>
				
				<?php foreach($this->tRowMethodes as $sRowMethod => $sLabel):?>
					<option value="select;<?php echo $sRowMethod?>">Select en utilisant <?php echo $sLabel?></option>
				<?php endforeach;?>
			</select></td>
		</tr>
	<?php endforeach;?>
	</table>
	
	<input type="submit" value="cr&eacute;er" />
	
	</form>
</div>
<?php endif;?>

<p class="msg"><?php echo $this->msg?></p>
<p class="detail"><?php echo $this->detail?></p>
