<div class="modal fade" id="endOfDayModal" tabindex="-1" role="dialog" aria-labelledby="endOfDayModal" aria-hidden="true" style="z-index: 2000;">
  	<div class="modal-dialog modal-lg" role="document">
	    <div class="modal-content">

		    <div class="modal-header">
		        <h6 class="modal-title">End of the Day / Generate Manifest</h6>
		        <button type="button" class="close" data-dismiss="modal" aria-label="Close"> 
		          <span aria-hidden="true">&times;</span>
		        </button>
		    </div>


		    <div class="modal-body">
		    	<div class="alert text-center" v-if="manifestNumber !== ''">
	    			<div v-if="displayLoadManifestSpinner == 1">
	    				<div class="fa fa-spinner fa-spin fa-4x"></div>
	    			</div>
	    			Getting a PDF manifest summarizing the shipments and charges related to the manifest number.
		    	</div>

				<div class="alert alert-success" v-if="manifestNumber !== ''">
					Manifest has been created. Number: <b>{{ manifestNumber }}</b>
					
					<p v-if="manifest !== ''">
						<a :href="manifest">Download Manifest</a>
					</p>					
				</div>

		    	<div v-if="manifestNumber === ''">
					<div class="text-center" v-if="displayEndOfDaySpinner == 1">
		    			<div class="fa fa-spinner fa-spin fa-4x"></div>
		    		</div>

		    		<div class="alert" v-else>
		    			This action generates a manifest file that will be sent to Canpar for billing and tracking purposes.  
		    		</div>
		    	</div> 

		    	<div class="alert alert-danger" v-for="error in errors">
					{{ error }}<br/>
				</div>
		    </div>


		   	<div class="modal-footer" v-if="manifest === ''">
		   		<button type="button" class="btn btn-info" v-on:click="endOfDay">Confirm</button>
	    	</div>

	    </div>
  	</div>
</div>