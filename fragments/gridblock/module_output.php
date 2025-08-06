<?php
/*
	Redaxo-Addon Gridblock
	Fragment für Modulausgabe (FE/BE)
	v1.1.15dev
	by Falko Müller @ 2021-2025 (based on 0.1.0-dev von bloep)

	
	genutzte VALUES:
	1-16	Variableninhalte der Inhaltsmodule (je Spalte)
	17		Templateauswahl & -optionen
	18		weitere Optionen (ehemals [13])
	19		ausgewählte Inhaltsmodule aller Spalten
	20		reserviert für Plugin contentsettings
*/

//Vorgaben
$config = rex_addon::get('gridblock')->getConfig('config');

$template =	isset($this->values[17]) ? rex_var::toArray($this->values[17]) : array();								//liefert kein Array zurück wenn leer / REX_INPUT_VALUE[17][name]
$options = 	isset($this->values[18]) ? rex_var::toArray($this->values[18]) : array();								//liefert kein Array zurück wenn leer / REX_INPUT_VALUE[18][name]
$modules = 	isset($this->values[19]) ? rex_var::toArray($this->values[19]) : array();								//liefert kein Array zurück wenn leer / REX_INPUT_VALUE[19][name]
$settings = isset($this->values[20]) ? rex_var::toArray($this->values[20]) : array();								//liefert kein Array zurück wenn leer / REX_INPUT_VALUE[20][name]

$rexVars = $this->rexVars;

$selTemplate = intval(@$template['selectedTemplate']);																//gespeichertes Template einladen
$selColumns = 0; $selPreview = "";


$useSettingPlugin = ( rex_plugin::get('gridblock', 'contentsettings')->isAvailable() ) ? true : false;

//Contentsettings abrufen
if ($useSettingPlugin):
	$oSettings = new GridblockContentSettings;
	$contentsettings = $oSettings->parseGridblockContentSettings($settings, $selTemplate);
else:
	$contentsettings = $settings;
endif;


/*
echo "<br>options:<br>";
dump($options);
echo "<br>template:<br>";
dump($template);
echo "<br>modules:<br>";
dump($modules);

echo "<br><hr><br>";
*/


//Template holen und Ausgaben aufbereiten
$db = rex_sql::factory();
$db->setQuery("SELECT template, columns, preview FROM ".rex::getTable('1620_gridtemplates')." WHERE id = '".$selTemplate."'");

if ($db->getRows() > 0):
	$selColumns = $db->getValue('columns', 'int');
	$selPreview = $db->getValue('preview');
		$selPreview = (!empty($selPreview)) ? str_replace(array("\n\r", "\n", "\r"), " ", $selPreview) : $selPreview;
		$selPreview = preg_replace("/\s+/", " ", $selPreview);
		

	//Template mit Spaltenausgaben holen & GRID-Vars/Rex-Vars ersetzen
	$op = $db->getValue('template');
	
	$op = preg_replace('/REX_ARTICLE_ID/', 				$rexVars['artID'], $op);
	$op = preg_replace('/REX_CLANG_ID/', 				$rexVars['clangID'], $op);
	$op = preg_replace('/REX_CTYPE_ID/', 				$rexVars['ctypeID'], $op);
	$op = preg_replace('/REX_SLICE_ID/', 				$rexVars['sliceID'], $op);
	
	$op = preg_replace('/REX_GRID_TEMPLATE_ID/', 		$selTemplate, $op);				//GRID: Template ID
	$op = preg_replace('/REX_GRID_TEMPLATE_PREVIEW/', 	$selPreview, $op);				//GRID: Template Preview-JSON als array()
	$op = preg_replace('/REX_GRID_TEMPLATE_COLUMNS/', 	$selColumns, $op);				//GRID: Template Spaltenanzahl
	
	
	//globale Settingsvariable setzen
	$gridSettings = array(
		"template" => array(
			"id"		=> $selTemplate,
			"preview"	=> json_decode($selPreview, true),
			"columns"	=> $selColumns
		)
	);
	$gridContentSettings = array("contentsettings" => json_decode( json_encode($contentsettings), true));
	
	
	//alle Spalten durchlaufen und Inhalte holen/setzen
	for ($i = 1; $i <= $selColumns; ++$i):
		//alle Inhalte der Spalte holen
		$modOP = '';
		$moduleIDs = @$modules[$i] ?? null;
		
		
		/*
		echo "Spalte $i:";
		dump($moduleIDs);
		*/

		
		//Inhaltsmodule durchlaufen und ausgeben
		if (!empty($moduleIDs)):
			foreach ($moduleIDs as $uID => $moduleID):
				$uID = str_replace("'", "", $uID);
				$moduleStatus = 1;
				
				//prüfen ob alte Speicherart (1.0-beta) oder neue Art
				if (!is_array($moduleID)):
					//alt (1.0-beta)
					$moduleID = intval($moduleID);
				else:
					//neu
					$moduleStatus = intval(@$moduleID['status']);
					$moduleID = intval(@$moduleID['id']);
				endif;
				

				//Modulausgabe übergehen, wenn kein Modul gewählt oder Modul offline
				if ((!$moduleStatus && !rex::isBackend()) || empty($moduleID)) { continue; }				
				
				
				//BE-Ausgabe aufbereiten
				if (rex::isBackend()):
					$css = (!$moduleStatus) ? 'gridblock-panel-offline' : '';
					$moduleNAME = '';
						$db = rex_sql::factory();
						$db->setQuery("SELECT name FROM ".rex::getTable('module')." WHERE id = '".$moduleID."'");
						if ($db->getRows() > 0):
							$moduleNAME = $db->getValue('name');
						endif;
					
					$modOP .= '<div class="gridblock-panel '.$css.'">';
					$modOP .= '<header class="gridblock-panel-header">'.$moduleNAME;
						$modOP .= '<i class="gridblock-panel-offline-icon rex-icon fa-eye-slash rex-offline"></i>';
					$modOP .= '</header>';
					$modOP .= '<div class="gridblock-panel-body">';
				endif;				
				
				
				//Inhaltsmodul laden und ausgeben        
				if ($moduleID && $uID):
					$editor = new rex_article_content_gridblock();
					
					//Values der Spalte wählen
					$values = rex_var::toArray($this->values[$i]);
					$values = @$values[$uID];
					
					unset($modcol);
					$values ?? null;

					//REX-MODULE-VARS erweitern					
					//$rexVars = $this->rexVars;
					
					$rexVars['grid_tmplID'] 	= $selTemplate;							//GRID: Template ID
					$rexVars['grid_tmplPREV'] 	= $selPreview;							//GRID: Template Preview-JSON als array()
					$rexVars['grid_tmplCOLS']	= $selColumns;							//GRID: Template Spaltenanzahl
					$rexVars['grid_colNR'] 		= $i;									//GRID: Spaltennummer
					
					//Modul-Settingsvariable setzen & bereitstellen
					$gridSettingsMod = array(
						"column" => array(
							"number"	=> $i
						)
					);
					$gridSettingsMod = array_merge($gridSettings, $gridSettingsMod, $gridContentSettings);
					
					
					//Ausgaben des Moduls holen
					rex_addon::get('gridblock')->setProperty('REX_GRID_SETTINGS', $gridSettingsMod);
					$editor->setValues($values, $uID);
					$modOP .= $editor->getModuleOutput($moduleID, $uID, $rexVars);
					rex_addon::get('gridblock')->removeProperty('REX_GRID_SETTINGS');
				endif;
				
				
				//BE-Ausgabe aufbereiten
				if (rex::isBackend()):
					$modOP .= '</div></div>';
				endif;				
			endforeach;
		endif;
		
		
		//Abstand zwischen den Columns im BE ausgeben
		if (rex::isBackend() && !empty($modOP)):
			$modOP = '<div class="gridblock-panel-columnspacer gridblock-panel-columnspacer'.$i.'"></div>'.$modOP;
		endif;
		

		//GRID-Spaltenplatzhalter ersetzen
		$modOP = str_replace('$', '\$', $modOP);											//$-Zeichen in Modulcontent maskieren, da diese sonst durch preg_replace als Backreference ausgeführt werden könnten
		$op = preg_replace("/REX_GRID\[(\s)*(id=)?".$i."(\s)*\]/", $modOP, $op);			//GRID: Platzhalter mit Spaltencontent ersetzen
	endfor;
	
	
	
	//globale Settingsvariable ändern & bereitstellen
	$gridSettings = array_merge($gridSettings, $gridContentSettings);
	
	//PHP-Code des Templates ausführen und Rückgabe verwerten
	rex_addon::get('gridblock')->setProperty('REX_GRID_SETTINGS', $gridSettings);
	ob_start();
	try {
		ob_implicit_flush(0);
		$sandbox = function() use ($op, $selTemplate, $contentsettings) {
			require rex_stream::factory('rex_gridblock/template/'.$selTemplate, $op);								//führt PHP-Code des Templates aus und gibt Ausgabe zurück (1. Parameter ist nur für Fehlerhinweis = virtueller Pfad)
		};
		$sandbox();
	} finally {
		$CONTENT = ob_get_clean();
	}
	$op = $CONTENT;
	rex_addon::get('gridblock')->removeProperty('REX_GRID_SETTINGS');
	
	
	//Settingübersicht im BE zeigen
	if ($useSettingPlugin):
		if (@$config['showcontentsettingsbe'] == "checked") {
			$contentsettings = (is_array($contentsettings)) ? (object) $contentsettings : $contentsettings;
			
			if (rex::isBackend()) {
				$op .= '<br>';
				$op .= $oSettings->getBackendSummary($contentsettings->data_with_labels,$selTemplate);
			}
		}
	endif ;
	
	
	//alles ausgeben
	echo $op;
	unset($op);
	
else:
	echo (rex::isBackend()) ? rex_i18n::msg('a1620_mod_template_notselected') : '';
endif;
?>