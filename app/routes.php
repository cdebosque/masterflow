<?php

// Page d'accueil de la gestion des dataflows
$app->get('/dataflow', function () use ($app) {
	$dataflows = $app['masterflow.masterflow.dao.dataflow']->findAll();

	return $app['twig']->render('dataflow/dataflow.phtml', array('dataflows' => $dataflows));
});
	
// Affichage détaillée d'un dataflow
$app->get('/dataflow/{id}', function ($id) use ($app) {
	$dataflow = $app['masterflow.masterflow.dao.dataflow']->find($id);

	return $app['twig']->render('dataflow/dataflow_detail.phtml', array('dataflow' => $dataflow));
});