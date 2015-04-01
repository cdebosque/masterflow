<div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header"> Colissimo <button type="button" class="btn btn-success btn-circle"><i class="fa fa-check"></i></h1>
                </div>
                <!-- /.col-lg-12 -->
            </div>
            
            <!-- /.row -->
            <div class="row">
                        <!-- /.panel-heading -->
                        <div class="panel-body">
                            <!-- Nav tabs -->
                            <ul class="nav nav-tabs">
                                <li class="active"><a href="#informations" data-toggle="tab">Informations</a>
                                </li>
                                <li><a href="#channel" data-toggle="tab">Channel</a>
                                </li>
                            </ul>
                            <!-- Tab panes -->
                            <div class="tab-content">
                                <div class="tab-pane fade in active" id="informations">
                                <?php if(file_exists("../" . SP_FOLDER . "/blocks/shipping_informations.php")) include_once "../" . SP_FOLDER . "/blocks/shipping_informations.php";?>
                               </div>
                               <div class="tab-pane fade" id="channel">
                               		<?php if(file_exists("../" . SP_FOLDER . "/blocks/shipping_channel.php")) include_once "../" . SP_FOLDER . "/blocks/shipping_channel.php";?>
                               </div>
                            </div>
                        </div>
                        <!-- /.panel-body -->

            </div>
            <!-- /.row -->