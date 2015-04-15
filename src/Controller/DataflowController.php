<?php

namespace Masterflow\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class DataflowController {

    /**
     * Dataflow list.
     *
     * @param Application $app Silex application
     */
    public function indexAction(Application $app) {
        $dataflows = $app['masterflow.masterflow.dao.dataflow']->findAll();
var_dump(\Masterflow\ETL\Esb::ENV);
        return $app['twig']->render('dataflow/dataflow.html.twig', array('dataflows' => $dataflows));
    }

    /**
     * Dataflow list.
     *
     * @param Application $app Silex application
     */
    public function editAction(Application $app, $id) {
        $dataflow = $app['masterflow.masterflow.dao.dataflow']->find($id);

        return $app['twig']->render('dataflow/dataflow_detail.html.twig', array('dataflow' => $dataflow));
    }

}