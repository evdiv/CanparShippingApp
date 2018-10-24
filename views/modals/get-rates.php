

	<div class="modal fade" id="ratesModal" tabindex="-1" role="dialog" aria-labelledby="ratesModal" aria-hidden="true">
	  	<div class="modal-dialog" role="document">
		    <div class="modal-content">

			    <div class="modal-header">
			        <h5 class="modal-title">Available Serives with Rates</h5>
			        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
			          <span aria-hidden="true">&times;</span>
			        </button>
			    </div>

			    <div class="modal-body">

			    	<div class="alert alert-danger alert-narrow" v-for="error in errors">
						{{ error }}<br/>
					</div>
					
					<small>
						<div style="margin-bottom: 18px;" 
							v-bind:class="{'text-danger': isEmpty(senderPostalCode)}">
							Sender Postal Code:  <b>{{ senderPostalCode }}</b>
						</div>

						<div v-bind:class="{'text-danger': isEmpty(receiverCity)}">Customer City: <b>{{ receiverCity }}</b> </div>
						<div v-bind:class="{'text-danger': isEmpty(receiverProvince)}">Customer Province: <b>{{ receiverProvince }}</b></div>
						<div v-bind:class="{'text-danger': isEmpty(receiverPostalCode)}">Customer Postal Code: <b>{{ receiverPostalCode }}</b></div>

						<div v-bind:class="{'text-danger': isEmpty(getTotalWeight())}">Total Weight: <b>{{ getTotalWeight() }}kg.</b></div>
						<div v-bind:class="{'text-danger': isEmpty(getTotalPieces())}">Number of Pieces: <b>{{ getTotalPieces() }}</b></div>
					</small>

					
					<center>
						<div v-if="displayLoadServicesSpinner == 1" class="fa fa-spinner fa-spin fa-3x"></div>
	    				<div v-if="displayLoadServicesSpinner == 2">
	    					
	    					<span v-if="services.length == 0">
								<i class="fa fa-exclamation-circle fa-3x"></i> <br/>
								<i>There is no service available.</i>
							</span>	
	    				</div>
	    			</center>


		        	<ul class="list-group list-group-flush" v-if="areRatesVisible == 1" v-cloak style="margin-top: 24px;">
						<li class="list-group-item" v-for="service in services" style="padding: 0">
							<small>{{ service.service_name }}: ${{ service.charge }}</small>
						</li>
					</ul>
			    </div> 

			    <div class="modal-footer">
			        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			    </div>

		    </div>
	  	</div>
	</div>