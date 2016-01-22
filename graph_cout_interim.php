<?php
	require('config.php');
	
	if (!$user->rights->mandarin->graph->ca_cumule) accessforbidden();
	$langs->load('mandarin@mandarin');
	
	$PDOdb = new TPDOdb;
	$TData = array();
	
	$year_n_1 = GETPOST('year_n_1', 'int');
	if (empty($year_n_1)) $year_n_1 = date('Y')-1;
	$year_n = GETPOST('year_n', 'int');
	if (empty($year_n)) $year_n = date('Y');
	
	// Formatage du tableau de base
	for ($i=1; $i<=53; $i++) $TData[$i] = array('week' => $i, $year_n_1 => 0, $year_n => 0);
	
	$sql_n_1 = 'SELECT WEEKOFYEAR(date_valid) AS `week`, sum(total) as total_ht
			FROM llx_facture
			WHERE YEAR(date_valid) = '.$year_n_1.'
			GROUP BY `week` 
			ORDER BY `week` ASC';
			
	$resql = $db->query($sql_n_1);
	if ($resql)
	{
		$cumul = 0;
		while ($line = $db->fetch_object($resql))
		{
			$cumul += $line->total_ht;
			$TData[$line->week][$year_n_1] = $cumul;
		}
	}
	
	
	$sql_n = 'SELECT WEEKOFYEAR(date_valid) AS `week`, sum(total) as total_ht
			FROM llx_facture
			WHERE YEAR(date_valid) = '.$year_n.'
			GROUP BY `week` 
			ORDER BY `week` ASC';
			
	$resql = $db->query($sql_n);
	if ($resql)
	{
		$cumul = 0;
		while ($line = $db->fetch_object($resql))
		{
			$cumul += $line->total_ht;
			$TData[$line->week][$year_n] = $line->total_ht;
		}
	}
	
	for ($i =1; $i < count($TData); $i++)
	{
		if ($TData[$i][$year_n_1] == 0 && isset($TData[$i-1][$year_n_1])) $TData[$i][$year_n_1] = $TData[$i-1][$year_n_1];
		if ($TData[$i][$year_n] == 0 && isset($TData[$i-1][$year_n])) $TData[$i][$year_n] = $TData[$i-1][$year_n];
	}
	
	
	// Begin of page
	llxHeader('', $langs->trans('mandarinTitleGraphCAInterim'), '');
	
	$listeview = new TListviewTBS('graphCAInterim');
	print $listeview->renderArray($PDOdb, $TData
		,array(
			'type' => 'chart'
			,'liste'=>array(
				'titre'=>$langs->transnoentitiesnoconv('titleGraphCAInterim')
			)
			,'title'=>array(
				'year' => $langs->transnoentitiesnoconv('Year')
				,'week' => $langs->transnoentitiesnoconv('Week')
			)
			,'xaxis'=>'week'
			,'hAxis'=>array('title'=>$langs->transnoentitiesnoconv('subTitleHAxisGraphCAInterim'))
			,'vAxis'=>array('title'=>$langs->transnoentitiesnoconv('subTitleVAxisGraphCAInterim'))
		)
	);
	
	// End of page
	llxFooter();