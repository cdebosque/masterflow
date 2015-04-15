<?php
/**
 * Configuration pour log4php
 * Copier et modifier ce fichier dans répertoire /etc/../logging.php d'interface activer la surcharge de configuration
 * Permet d'utiliser les librairies autoloadées et les fonctions natives PHP
 *
 * @see http://logging.apache.org/log4php/docs/configuration.html - format PHP
 *
 * @author tbondois
 */
$loggingParam = array(
		'rootLogger' => array( //@see http://logging.apache.org/log4php/docs/loggers.html
				'level'     => 'TRACE',	//niveau à partir duquel on va prendre en compte le log. TRACE < DEBUG < INFO < WARN < ERROR < FATAL
				'appenders' => array(
 						//'mail', //pour désactiver l'envoi par mail, commenter cette ligne
						'echo',	//pour désactiver l'affichage en sortie, commenter cette ligne
						'file', //pour désactiver l'écriture dans un fichier, commenter cette ligne
				),
		),//end root
		'appenders' => array(	//@see http://logging.apache.org/log4php/docs/appenders.html
				'echo' => array(
						'class'  => 'LoggerAppenderEcho',
						'layout' => array(
								'class' => 'LoggerLayoutTTCC',
						),
				),//end appender echo
				'file' => array(
						'class'  => 'LoggerAppenderFile',
						'layout' => array(
								'class' => 'LoggerLayoutTTCC',
						),
						'params' => array(
								//'file' => Esb::WORKBASE.Esb::registry('identifier')."/logs/".date('Y-m-d_H.i.s')."_".getmypid().".log",
								'file' => "var\\".Esb::registry('identifier')."\\logs\\".date('Y-m-d_H.i.s')."_".getmypid().".log",
								'append' => true
						),
				),//end appender file
				'mail' => array(
						'class'  => 'LoggerAppenderMailEvent',
						'layout' => array(
								'class' => 'LoggerLayoutTTCC',
						),
						'params' => array(
								'from'    => 'cdebosque@mexmsolutions.com',
								'to'      => 'cdebosque@mexmsolutions.com',
								'subject' => "[OCP/ESB] Log report ".Esb::registry('identifier')." ".datetime()." PID:".getmypid() //pour un sujet auto, commenter cette ligne
						),
				),//end appender mail
		),//end appenders
);//end array

// if(Esb::isCli()) {
//     $loggingParam['rootLogger']['appenders']['echo']= null;
// }

return $loggingParam;
?>