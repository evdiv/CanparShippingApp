
<div class="modal fade" id="voidShipmentModal" tabindex="-1" role="dialog" aria-labelledby="voidShipmentModal" aria-hidden="true" style="z-index: 1600;">
  	<div class="modal-dialog" role="document">
	    <div class="modal-content">

		    <div class="modal-header">
		        <h6 class="modal-title">Do you want to Void Shipment?</h6>
		        <button type="button" class="close" 
		        		@click="{ errors = []; confirmation = ''; }"
		        		data-dismiss="modal" 
		        		aria-label="Close"><span aria-hidden="true">&times;</span>
		        </button>
		    </div>

		    <div class="modal-body">

			    <div class="alert alert-danger" v-for="error in errors">
					{{ error }}<br/>
				</div>

				<div class="alert alert-success" v-if="confirmation !== ''">
					{{ confirmation }}<br/>
				</div>

				<center>
					<div v-if="displayVoidShipmentSpinner == 1" class="fa fa-spinner fa-spin fa-3x"></div>
				</center>

		    </div>

		    <div class="modal-footer" v-if="confirmation === ''">
		    	<button type="button" class="btn btn-danger btn-block" v-on:click="VoidShipment">Void Shipment</button>
		    </div>

	    </div>
  	</div>
</div>