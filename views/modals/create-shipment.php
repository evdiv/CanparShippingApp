

	<div class="modal fade" id="createShipmentModal" tabindex="-1" role="dialog" aria-labelledby="createShipmentModal" aria-hidden="true">
	  	<div class="modal-dialog" role="document">
		    <div class="modal-content">

			    <div class="modal-header">
			        <h5 class="modal-title">Create Shipment <small>(check customer details)</small></h5>

			        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
			          <span aria-hidden="true">&times;</span>
			        </button>
			    </div>

			    <div class="modal-body">

					<div class="alert alert-danger alert-narrow" v-for="error in errors">
						{{ error }}<br/>
					</div>


					<div v-for="(pin, index) in pins">
						<div class="shipment-pin">
							<h6>Shipment PIN: {{ pin }} </h6> 
						</div>
					</div>
					

					<div v-if="pins.length > 0">
						<button type="button" class="btn btn-outline-info btn-sm btn-show-pdf" 
									onclick="$('#shipment-labels').toggle();">Show / Hide label</button>

						<div class="pdf-label" id="shipment-labels">
							<center><div class="fa fa-spinner fa-spin fa-3x"></div></center>
						</div>
					</div>


					<div v-if="labels !== ''">
						<img style="max-width: 100%; margin-bottom: 30px;" :src="labels" alt="Shipping Label" />
	
						<button type="button" class="btn btn-primary btn-block"  
			    				v-on:click="printLabel(labels)">Print</button>
					</div>


					<!-- Receiver Details--> 
					<small v-if="labels === ''">

						<h6>TO: Customer</h6> 

						<div>Tracking Reference: <b>{{ orderId }}</b></div>

						<div v-bind:class="{'text-danger': isEmpty(receiverName)}">Receiver Name: <b>{{ receiverName }}</b></div>
						<div v-bind:class="{'text-danger': isEmpty(receiverStreetName)}">Receiver Address: <b>{{ receiverStreetNumber }} {{ receiverStreetName }}</b></div>

						<div v-if="receiverAddress2">Receiver Address 2: <b>{{ receiverAddress2 }} </b></div>
						<div v-if="receiverAddress3">Receiver Address 3: <b>{{ receiverAddress3 }} </b></div>

						<div v-bind:class="{'text-danger': isEmpty(receiverCity)}">Receiver City: <b>{{ receiverCity }}</b></div>
						<div v-bind:class="{'text-danger': isEmpty(receiverProvince)}">Receiver Province Code: <b>{{ receiverProvince }}</b></div>
						<div v-bind:class="{'text-danger': isEmpty(receiverPostalCode)}">Receiver Postal Code: <b>{{ receiverPostalCode }}</b></div>

						<div v-bind:class="{'text-danger': isEmpty(receiverPhone)}">
							Receiver Phone: <b>{{ receiverPhoneAreaCode }}-{{ receiverPhone }}
							<span v-if="receiverPhoneExtension">ext.{{ receiverPhoneExtension }}</span></b>
						</div>

						<div v-if="receiverFaxNumber">Receiver Fax: <b>{{ receiverFaxNumber }}</b></div>
						<div v-if="receiverEmail">Email: <b>{{ receiverEmail }}</b></div>
						<div v-if="specialInstructions">Note: <b>{{ specialInstructions }}</b></div>
						<br/>

						<div v-bind:class="{'text-danger': isEmpty(getTotalPieces())}">Total Pieces: <b>{{ getTotalPieces() }}</b></div>
						<div v-bind:class="{'text-danger': isEmpty(getTotalWeight())}">Total Weight: <b>{{ getTotalWeight() }}kg.</b></div>

					</small>
					<!--/ Receiver Details --> 

					<center>
						<div v-if="displayLoadShipmentSpinner == 1" class="fa fa-spinner fa-spin fa-3x"></div>
	    			</center>


			    </div>

			    <div class="modal-footer">
			    	<button type="button" class="btn btn-success" 
			    		v-on:click="createShipment" v-if="labels.length === 0">Create Shipment</button>
			    </div>

		    </div>
	  	</div>
	</div>