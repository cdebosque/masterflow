<div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">Channel - Amazon</h1>
                </div>
                <!-- /.col-lg-12 -->
            </div>
            
            <!-- /.row -->
            <div class="row">
                        <!-- /.panel-heading -->
                        <div class="panel-body">
                            <!-- Nav tabs -->
                            <ul class="nav nav-tabs">
                                <li class="active"><a href="#home" data-toggle="tab">Dashboard</a>
                                </li>
                                <li><a href="#catalog" data-toggle="tab">Catalog</a>
                                <li><a href="#filters" data-toggle="tab">Filters</a>
                                <li><a href="#prices" data-toggle="tab">Prices</a>
                                </li>
                                <li><a href="#mapping" data-toggle="tab">Mapping</a>
                                </li>
                                <li><a href="#suppliers" data-toggle="tab">Suppliers</a>
                                </li>
                                <li><a href="#shipping" data-toggle="tab">Shipping</a>
                                </li>
                                <li><a href="#flow" data-toggle="tab">Flow</a>
                                <li><a href="#logs" data-toggle="tab">Logs</a>
                                <li><a href="#settings" data-toggle="tab">Settings</a>
                                </li>
                            </ul>
                            <!-- Tab panes -->
                            <div class="tab-content">
                                <div class="tab-pane fade in active" id="home">
                                <?php if(file_exists("../" . SP_FOLDER . "/blocks/channel_detail_dashboard.php")) include_once "../" . SP_FOLDER . "/blocks/channel_detail_dashboard.php";?>
                               </div>
                                <div class="tab-pane fade" id="catalog">
                               		<?php if(file_exists("../" . SP_FOLDER . "/blocks/channel_detail_catalog.php")) include_once "../" . SP_FOLDER . "/blocks/channel_detail_catalog.php";?>
                                </div>
                                <div class="tab-pane fade" id="filters">
                               		<?php if(file_exists("../" . SP_FOLDER . "/blocks/channel_detail_filters.php")) include_once "../" . SP_FOLDER . "/blocks/channel_detail_filters.php";?>
                                </div>
                                <div class="tab-pane fade" id="prices">
                               		<?php if(file_exists("../" . SP_FOLDER . "/blocks/channel_detail_prices.php")) include_once "../" . SP_FOLDER . "/blocks/channel_detail_prices.php";?>
                                </div>
                                <div class="tab-pane fade" id="mapping">
                               		<?php if(file_exists("../" . SP_FOLDER . "/blocks/channel_detail_mapping.php")) include_once "../" . SP_FOLDER . "/blocks/channel_detail_mapping.php";?>
                                </div>
                                <div class="tab-pane fade" id="suppliers">
                               		<?php if(file_exists("../" . SP_FOLDER . "/blocks/channel_detail_suppliers.php")) include_once "../" . SP_FOLDER . "/blocks/channel_detail_suppliers.php";?>
                                </div>
                                <div class="tab-pane fade" id="shipping">
                               		<?php if(file_exists("../" . SP_FOLDER . "/blocks/channel_detail_shipping.php")) include_once "../" . SP_FOLDER . "/blocks/channel_detail_shipping.php";?>
                                </div>
                                <div class="tab-pane fade" id="flow">
                               		<?php if(file_exists("../" . SP_FOLDER . "/blocks/channel_detail_flow.php")) include_once "../" . SP_FOLDER . "/blocks/channel_detail_flow.php";?>
                                </div>
                                <div class="tab-pane fade" id="logs">
                               		<?php if(file_exists("../" . SP_FOLDER . "/blocks/channel_detail_logs.php")) include_once "../" . SP_FOLDER . "/blocks/channel_detail_logs.php";?>
                                </div>
                                <div class="tab-pane fade" id="settings">
                               		<?php if(file_exists("../" . SP_FOLDER . "/blocks/channel_detail_setting.php")) include_once "../" . SP_FOLDER . "/blocks/channel_detail_setting.php";?>
                                </div>
                            </div>
                        </div>
                        <!-- /.panel-body -->

            </div>
            <!-- /.row -->