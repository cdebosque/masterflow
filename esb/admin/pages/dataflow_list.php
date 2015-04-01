            <?php 
            $listDatasSorted = array();
            $listDatasSorted = $dataflow->showList();
			?>
						
			
		<div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">Dataflow List</h1>
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            DataTables Advanced Tables
                        </div>
                        <div class="panel-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Libellé</th>
                                            <th>État</th>
                                            <th>Type</th>
                                            <th>Entrée</th>
                                            <th>Sortie</th>
                                            <th>Dernier lancement</th> 
                                            <th>Vue</th>
                                            <th>Hist</th>
                                         </tr>
                                    </thead>
                                    <tbody>
                                    <?php 
									foreach ($listDatasSorted as $datas) {
										$style_line = '';
										if(!empty($datas['status'])){
											if($datas['status'] == 'fatal' | $datas['status'] == 'error') $style_line = "danger";
											if($datas['status'] == 'info') $style_line = "success";
											if($datas['status'] == 'warning') $style_line = "warning";
											if($datas['status'] == 'processing') $style_line = "info";
										}
										echo ' <tr class='.$style_line.'>' . PHP_EOL;
										echo '  <td>' . $datas['id'] . '</td>' . PHP_EOL;
										echo '  <td class="name">' . $datas['name'] . '</td>' . PHP_EOL;
										echo '  <td' . ($datas['enable'] == 1 ? ' class="lvl1">activée' : ' class="lvl4">désactivée') . '</td>' . PHP_EOL;
										echo '  <td>' . $datas['type'] . '</td>' . PHP_EOL;
										echo '  <td>' . $datas['in_connection_type'] . '</td>' . PHP_EOL;
										echo '  <td>' . $datas['out_connection_type'] . '</td>' . PHP_EOL;
										echo '  <td>' . Dataflow::mysql2Datetime($datas['date_start'])
										  //. (!empty($datas['status']) ? '&nbsp;<img class="icon" src="images/status/' . $datas['status'] . '.png" alt="' . $datas['status'] . '" />' : '')
										  //. (!empty($datas['logfile']) ? '&nbsp;<a href="' . $datas['logfile'].'"><img class="icon" src="images/favicons/launch.png" alt="Afficher le fichier de logs pour ce lancement" /></a>' : '')
										  . '</td>' . PHP_EOL;
										echo '  <td><a href="?action=info&amp;id='.$datas['id'].'"><img class="icon" src="images/favicons/info.png" alt="Afficher la fiche de l\'interface" /></a></td>' . PHP_EOL;
										echo '  <td><a href="?action=history&amp;id='.$datas['id'].'"><img class="icon" src="images/favicons/history.png" alt="Afficher l\'historique de l\'interface" /></a></td>' . PHP_EOL;
										echo ' </tr>' . PHP_EOL;
									}
									?>                                    
                                    
                                    </tbody>
                                </table>
                            </div>
                            <!-- /.table-responsive -->
                        </div>
                        <!-- /.panel-body -->
                        
                    </div>
                    <!-- /.panel -->
                </div>
                <!-- /.col-lg-6 -->
            </div>
            <!-- /.row -->
