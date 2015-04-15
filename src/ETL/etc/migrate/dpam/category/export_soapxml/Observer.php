<?php
class Observer extends EaiObserverProduct
{

	/**
	 * On transforme un arbre de catégories en x elements de catégories, puis en renvoie l'élement actuel.
	 * @param EaiEvent $event
	 */
	public function onSoapCall(EaiEvent $event)
	{
		$connector = $event->getObj();

		$results = $connector->getResults();

		$results = array_values(self::convertTreeToFlatArray($results));// array_values pour avoir les clés bien ordonnées, pour getRawDatasFetched. Sinon la fonction renvoie l'id comme clé de tableau.

		dump($results);

		$connector->setResults($results);
	}



}