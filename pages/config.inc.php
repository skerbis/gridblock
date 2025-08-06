<?php
/*
	Redaxo-Addon Gridblock
	Verwaltung: Einstellungen (config)
	v1.1.15
	by Falko Müller @ 2021-2025 (based on 0.1.0-dev von bloep)
*/

/** RexStan: Vars vom Check ausschließen */
/** @var rex_addon $this */
/** @var array $config */
/** @var string $func */
/** @var string $page */
/** @var string $subpage */


//Variablen deklarieren
$form_error = 0;


//Formular dieser Seite verarbeiten
if ($func == "save" && isset($_POST['submit'])):

	//Modulauswahl aufbereiten
	$mods = rex_post('modules');
	$mods = (is_array($mods)) ? implode("#", rex_post('modules')) : '';

	//Konfig speichern
	$res = $this->setConfig('config', [
		'modulesmode'				=> rex_post('modulesmode'),
		'modules'					=> '#'.$mods.'#',
		'previewtabnames'			=> rex_post('previewtabnames'),
		'showtemplatetitles'		=> rex_post('showtemplatetitles'),
		'hidepreviewcoltitles'		=> rex_post('hidepreviewcoltitles'),
		'hideinfotexts'				=> rex_post('hideinfotexts'),
		'showcontentsettingsbe'		=> rex_post('showcontentsettingsbe'),
		'plusbuttonfornewblock'		=> rex_post('plusbuttonfornewblock'),
		'alwaysallowdelete'			=> rex_post('alwaysallowdelete'),
	]);

	//Rückmeldung
	echo ($res) ? rex_view::info($this->i18n('a1620_settings_saved')) : rex_view::warning($this->i18n('a1620_error'));

	//reload Konfig
	$config = $this->getConfig('config');
endif;


//Formular ausgeben
?>


<script>setTimeout(function() { jQuery('.alert-info').fadeOut(); }, 5000);</script>


<form action="index.php?page=<?php echo $page; ?>" method="post" enctype="multipart/form-data">
<input type="hidden" name="subpage" value="<?php echo $subpage; ?>" />
<input type="hidden" name="func" value="save" />

<section class="rex-page-section">
	<div class="panel panel-edit">
	
		<header class="panel-heading"><div class="panel-title"><?php echo $this->i18n('a1620_head_config'); ?></div></header>
		
		<div class="panel-body">
        
			<dl class="rex-form-group form-group">
				<dt><label for=""><?php echo $this->i18n('a1620_config_modulesmode'); ?></label></dt>
				<dd>
                    <div class="radio toggle switch">
                        <label for="mode1">
                            <input name="modulesmode" type="radio" id="mode1" value="allow" <?php echo (@$config['modulesmode'] != 'ignore') ? 'checked' : ''; ?> /> <?php echo $this->i18n('a1620_config_modulesmode_allow'); ?>
                        </label>
                        
                        <label for="mode2">
                            <input name="modulesmode" type="radio" id="mode2" value="ignore" <?php echo (@$config['modulesmode'] == 'ignore') ? 'checked' : ''; ?> /> <?php echo $this->i18n('a1620_config_modulesmode_deny'); ?>
                        </label>
                    </div>
                </dd>
            </dl>        
        
			<dl class="rex-form-group form-group">
				<dt><label for=""><?php echo $this->i18n('a1620_config_modules'); ?></label></dt>
				<dd>
					<select name="modules[]" id="modules" size="10" multiple class="form-control">
					<?php
                    $db = rex_sql::factory();
                    $db->setQuery('SELECT id, name FROM '.rex::getTable('module').' WHERE input NOT LIKE "%/* GRID_MODULE_IDENTIFIER | DONT REMOVE */%" ORDER BY name, id');
                    
                    foreach ($db as $dbi):
						$sel = (preg_match("/#".$dbi->getValue('id')."#/i", @$config['modules'])) ? 'selected="selected"' : '';
                        echo '<option value="'.$dbi->getValue('id').'" '.$sel.'>'.aFM_maskChar($dbi->getValue('name')).'</option>';
                    endforeach;
                    ?>
					</select>
                    <span class="infoblock"><?php echo rex_i18n::rawmsg('a1620_text1'); ?></span>
				</dd>
			</dl>
            
            
            <dl class="rex-form-group form-group"><dt></dt></dl>
             
            
            <dl class="rex-form-group form-group">
                <dt><label for=""><?php echo $this->i18n('a1620_config_moresettings'); ?></label></dt>
                <dd>				
				
                    <div class="checkbox toggle">
						<label for="showtemplatetitles">
                        	<input type="checkbox" name="showtemplatetitles" id="showtemplatetitles" value="checked" <?php echo @$config['showtemplatetitles']; ?> /> <?php echo $this->i18n('a1620_config_showtemplatetitles_info'); ?>
						</label>
                    </div>
					
                    <div class="checkbox toggle">
						<label for="hidepreviewcoltitles">
                        	<input type="checkbox" name="hidepreviewcoltitles" id="hidepreviewcoltitles" value="checked" <?php echo @$config['hidepreviewcoltitles']; ?> /> <?php echo $this->i18n('a1620_config_hidepreviewcoltitles_info'); ?>
						</label>
                    </div>
					
                    <div class="checkbox toggle">
						<label for="previewtabnames">
                        	<input type="checkbox" name="previewtabnames" id="previewtabnames" value="checked" <?php echo @$config['previewtabnames']; ?> /> <?php echo $this->i18n('a1620_config_previewtabnames_info'); ?>
						</label>
                    </div>
					
                    <div class="checkbox toggle">
						<label for="hideinfotexts">
                        	<input type="checkbox" name="hideinfotexts" id="hideinfotexts" value="checked" <?php echo @$config['hideinfotexts']; ?> /> <?php echo $this->i18n('a1620_config_hideinfotexts_info'); ?>
						</label>
                    </div>
					
                    <div class="checkbox toggle">
						<label for="plusbuttonfornewblock">
                        	<input type="checkbox" name="plusbuttonfornewblock" id="plusbuttonfornewblock" value="checked" <?php echo @$config['plusbuttonfornewblock']; ?> /> <?php echo $this->i18n('a1620_config_plusbuttonfornewblock_info'); ?>
						</label>
                    </div>
					
                    <div class="checkbox toggle">
						<label for="alwaysallowdelete">
                        	<input type="checkbox" name="alwaysallowdelete" id="alwaysallowdelete" value="checked" <?php echo @$config['alwaysallowdelete']; ?> /> <?php echo $this->i18n('a1620_config_alwaysallowdelete_info'); ?>
						</label>
                    </div>
					
					<?php if (rex_plugin::get('gridblock', 'contentsettings')->isAvailable()): ?>
                    <div class="checkbox toggle">
                        <label for="showcontentsettingsbe">
                            <input type="checkbox" name="showcontentsettingsbe" id="showcontentsettingsbe" value="checked" <?php echo @$config['showcontentsettingsbe']; ?> /> <?php echo $this->i18n('a1620_config_showcontentsettingsbe_info'); ?>
                        </label>
                    </div>					
					<?php endif; ?>
					
                </dd>
            </dl>

		</div>
        
        
		<script type="text/javascript">
		$(function() {
			$('.hiddencontent').not('.checked').hide();
			
			$('input[data-opener]').change(function(){
				dst = $(this).attr('data-opener');
				
				if (dst != undefined && dst.length > 2) {
					if ($(this).is(':checked')) {
						$(dst).slideDown();
					} else {
						$(dst).slideUp();
					}
				}
			});
		});
        </script>
        
		
		<footer class="panel-footer">
			<div class="rex-form-panel-footer">
				<div class="btn-toolbar">
					<input class="btn btn-save rex-form-aligned" type="submit" name="submit" title="<?php echo $this->i18n('a1620_save'); ?>" value="<?php echo $this->i18n('a1620_save'); ?>" />
				</div>
			</div>
		</footer>
		
	</div>
</section>
	
</form>