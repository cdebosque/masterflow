<div class="row">
	<div class="col-lg-12">
    	<h2 class="page-header">Filters</h2>
	</div>
	<!-- /.col-lg-12 -->
</div>
<h4>Brands</h4>
<form role="form">
    <div class="form-group">
        <label>Select a brand to disable</label>
        <select class="form-control">
            <option>Seb</option>
            <option>Kitchen Aid</option>
            <option>...</option>
            <option>...</option>
            <option>...</option>
        </select>
    </div>
    <button type="submit" class="btn btn-default">Disable brand</button>
            <p class="help-block">Warning, all product of this brand will be disable for this channel.</p>
</form>
<div class="panel panel-default">
<div class="panel-heading">
    Disabled brands
</div>
<p>Click on icon to enable a brand</p>
<!-- /.panel-heading -->
<div class="panel-body">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Brands</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Samsung</td>
                    <td><button type="button" class="btn btn-danger btn-circle"><i class="fa fa-times"></i></button></td>
                </tr>
                <tr>
                    <td>De Buyer</td>
                    <td><button type="button" class="btn btn-danger btn-circle"><i class="fa fa-times"></i></button></td>
				</tr>
                <tr>
                    <td>Sony</td>
                    <td><button type="button" class="btn btn-danger btn-circle"><i class="fa fa-times"></i></button></td>
                 </tr>
                <tr>
                    <td>Apple</td>
                    <td><button type="button" class="btn btn-danger btn-circle"><i class="fa fa-times"></i></button></td>
               	</tr>
			</tbody>
        </table>
    </div>
    <!-- /.table-responsive -->
</div>
<!-- /.panel-body -->
</div>
<h4>Stocks</h4>
<div class="panel panel-default">
<div class="panel-heading">
   Stock statuts 
</div>
<p>Click on icon to enable or disable a stock statut</p>
<!-- /.panel-heading -->
<div class="panel-body">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Stock statut</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>In stock</td>
                    <td><button type="button" class="btn btn-success btn-circle"><i class="fa fa-check"></i></button></td>
                </tr>
                <tr>
                    <td>Fin de stock</td>
                    <td><button type="button" class="btn btn-success btn-circle"><i class="fa fa-check"></i></button></td>
				</tr>
                <tr>
                    <td>Ne plus vendre</td>
                    <td><button type="button" class="btn btn-danger btn-circle"><i class="fa fa-times"></i></button></td>
                 </tr>
			</tbody>
        </table>
    </div>
    <!-- /.table-responsive -->
</div>
<!-- /.panel-body -->
</div>
<div class="panel panel-default">
        	<div class="panel-heading">
            	Stock quantity
			</div>
            <!-- /.panel-heading -->
            <div class="panel-body">
            	<div class="dataTable_wrapper">
                	<table class="table table-striped table-bordered table-hover" id="dataTables-example">
                        <tbody>
                        	<tr class="odd gradeX">
                        		<td>Physical stock</td>
                            	<td><select class="form-control">
                                		<option>></option>
                                		<option>>=</option>
                                	</select>
                                <td>
                                	<input class="form-control">
                                </td>
							</tr>
                        	<tr class="even gradeC">
                        		<td>Suppliers stock</td>
                            	<td><select class="form-control">
                                		<option>></option>
                                		<option>>=</option>
                                	</select>
                                <td>
                                	<input class="form-control">
                                </td>
                            </tr>
                        	<tr class="odd gradeA">
                        		<td>Quantity</td>
                            	<td><select class="form-control">
                                		<option>></option>
                                		<option>>=</option>
                                	</select>
                                <td>
                                	<input class="form-control">
                                </td>
							</tr>
							
						</tbody>
					</table>
					<button type="submit" class="btn btn-default">Save change</button>
				</div>
				<!-- /.table-responsive -->
			</div>
			<!-- /.panel-body -->
		</div>
		<!-- /.panel -->                                