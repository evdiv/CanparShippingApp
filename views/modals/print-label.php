

	<div class="modal fade" id="printLabelModal" tabindex="-1" role="dialog" aria-labelledby="printLabelModal" aria-hidden="true" style="z-index: 1800;">
	  	<div class="modal-dialog" role="document">
		    <div class="modal-content">

			    <div class="modal-header">
			        <h6 class="modal-title">
			        	Tracking Number:
		        		<a target="_blank" :href="'https://www.canpar.com/en/track/TrackingAction.do?locale=en&type=0&reference=' + shipmentPin">
		        			{{ shipmentPin }}
		        		</a>
		        	</h6>

			        <button type="button" class="close" 
			        		@click="{ errors = []; }"
			        		data-dismiss="modal"
			        		aria-label="Close"> 
			          <span aria-hidden="true">&times;</span>
			        </button>
			    </div>

			    <div class="modal-body">
			    	<div class="alert alert-danger alert-narrow" v-for="error in errors">
						{{ error }}<br/>
					</div>

		        	
		        	<div v-if="storedLabel !== ''">
						<img style="max-width: 100%; margin-bottom: 50px;" :src="storedLabel" alt="Shipping Label" />
	
						<button type="button" class="btn btn-primary btn-block"  
			    				v-on:click="printLabel(storedLabel)">Print {{ storedLabel }}</button>
					</div>

			    </div>

		    </div>
	  	</div>
	</div>