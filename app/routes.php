<?php

// Les infos et controlleurs de connexion sont g�r�s via la librairie simpleuser dans vendor
$app->mount('/user', $userServiceProvider);

// Redirige les utilisateurs identifi�s vers les bons espaces ( pas besoin de restriction )
$app->mount('/login/redirect', new Masterflow\Controller\LoginRedirect());

// D�finition des type d'utilisateur
$app->get('/roles', "Masterflow\Controller\RoleController::indexAction")->bind('list-roles')->secure('ROLE_DEV')->secure(array());

// Ajout d'un type utilisateur 
$app->match('/role/add', "Masterflow\Controller\RoleController::createRoleAction")->bind('add-role')->secure(array());

// Edition des types utilisateurs
$app->match('/role/{id}/edit', "Masterflow\Controller\RoleController::createRoleAction")->bind('edit-role')->secure(array());

// D�finition de l'association role-route
$app->get('/roles-route', "Masterflow\Controller\RoleController::listeRolesRouteAction")->bind('roles-route')->secure(array());

// Edition de l'association role-route
$app->match('/roles-route/{id}/edit', "Masterflow\Controller\RoleController::editRolesRouteAction")->bind('edit-roleroute')->secure(array());

// Home page
$app->get('/', "Masterflow\Controller\HomeController::indexAction")->bind('dashboard')->secure(array());

// Admin zone
$app->get('/admin', "Masterflow\Controller\AdminController::indexAction")->bind('admin')->secure(array());



// Page d'accueil de la gestion des dataflows
$app->get('/dataflow', "Masterflow\Controller\DataflowController::indexAction")->bind('dataflow')->secure(array());
	
// Affichage d�taill�e d'un dataflow
$app->match('/dataflow/{id}', "Masterflow\Controller\DataflowController::editAction")->bind('edit-dataflow')->secure(array());