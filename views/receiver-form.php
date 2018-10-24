<div class="tab-pane fade show active" id="receiver" role="tabpanel" aria-labelledby="receiver-tab">

	<div class="alert alert-info" role="alert">

		<div class="form-group row">
		    <label class="col-sm-4 col-form-label">Order ID</label>
		    <div class="col-sm-8">
		    	<div class="input-group input-group-sm">
		    		<input type="hidden" id="incomingOrderId" value="<?= $ordersID ?>"/>
		      		<input type="text" class="form-control form-control-sm" placeholder="Enter Order ID" v-model="orderId" v-on:keyup.enter="searchShipmentByOrderId">

			      	<div class="input-group-append">

			      		<button class="btn btn-success" type="button" 
			      			v-on:click="searchShipmentByOrderId"><i class="fa fa-search" aria-hidden="true"></i> Search</button>
				  	</div>
				</div>
		    </div>
		</div><!-- /.form-group row-->


	  	<div class="form-group row">
	    	<label class="col-sm-4 col-form-label">Customer Code</label>
	    	<div class="col-sm-8">
	      		<input type="text" class="form-control form-control-sm" v-model="receiverCode">
	    	</div>
	  	</div><!-- /.form-group row-->


		<div class="form-group row">
		    <label class="col-sm-4 col-form-label" style="margin-bottom: 3px;">Country</label>
		    <div class="col-sm-8">
		    	<select class="form-control form-control-sm" v-model="receiverCountry">
			      	<option value="CA">Canada</option>
			      	<option value="US">United States</option>
			    </select>
		    </div>
		</div><!-- /.form-group row-->	


		<div class="form-group row">
		    <label class="col-sm-4 col-form-label">Company / Name</label>
		    <div class="col-sm-8">
		      <input type="text" class="form-control form-control-sm" v-model="receiverName">
		    </div>
		</div><!-- /.form-group row-->


		<div class="form-group row">
		    <label class="col-sm-4 col-form-label">Attention To</label>
		    <div class="col-sm-8">
		      <input type="text" class="form-control form-control-sm" v-model="receiverAttentionTo">
		    </div>
		</div><!-- /.form-group row-->


		<div class="form-group row">
		    <label class="col-sm-4 col-form-label">Address 1</label>
		    <div class="col-sm-8">
				<div class="form-row">

				    <div class="col-4">
				       	<input type="text" class="form-control form-control-sm" v-model="receiverStreetNumber">
				    </div>

				    <div class="col-8">
				       	<input type="text" class="form-control form-control-sm" v-model="receiverStreetName">
				    </div>

				</div>
			</div>
		</div><!-- /.form-group row-->


		<div class="form-group row">
		    <label class="col-sm-4 col-form-label">Address 2</label>
		    <div class="col-sm-8">
		      	<input type="text" class="form-control form-control-sm" v-model="receiverAddress2">
		    </div>
		</div><!-- /.form-group row-->


		<div class="form-group row">
		    <label class="col-sm-4 col-form-label">Address 3</label>
		    <div class="col-sm-8">
		      	<input type="text" class="form-control form-control-sm" v-model="receiverAddress3">
		    </div>
		</div><!-- /.form-group row-->


		<div class="form-group row">
		    <label class="col-sm-4 col-form-label">City</label>
		    <div class="col-sm-8">
		      	<input type="text" class="form-control form-control-sm" v-model="receiverCity">
		    </div>
		</div><!-- /.form-group row-->


		<div class="form-group row">
		    <label class="col-sm-4 col-form-label" style="margin-bottom: 3px;">Province</label>
		    <div class="col-sm-8">
		    	<select class="form-control form-control-sm" v-model="receiverProvince">
					<option value="">Province</option>
					<option value="AB">Alberta</option>
					<option value="BC">British Columbia</option>
					<option value="MB">Manitoba</option>
					<option value="NB">New Brunswick</option>
					<option value="NL">Newfoundland</option>
					<option value="NT">Northwest Territories</option>
					<option value="NS">Nova Scotia</option>
					<option value="NU">Nunavut</option>
					<option value="ON">Ontario</option>
					<option value="PE">Prince Edward Island</option>
					<option value="QC">Quebec</option>
					<option value="SK">Saskatchewan</option>
					<option value="YT">Yukon</option>
				</select>
		    </div>
		</div><!-- /.form-group row-->	


		<div class="form-group row">
		    <label class="col-sm-4 col-form-label">Postal Code</label>
		    <div class="col-sm-8">
		      	<input type="text" class="form-control form-control-sm" v-model="receiverPostalCode">
		    </div>
		</div><!-- /.form-group row--> 


		<div class="form-group row">
		    <label class="col-sm-4 col-form-label">Phone </label>
		    <div class="col-sm-8">
				<div class="form-row">
					<div class="col-4">
				       	<input type="text" class="form-control form-control-sm" v-model="receiverPhoneAreaCode">
				    </div>

				    <div class="col-8">
				       	<input type="text" class="form-control form-control-sm" v-model="receiverPhone">
				    </div>
				</div>
			</div>
		</div><!-- /.form-group row-->


		<div class="form-group row">
		    <label class="col-sm-4 col-form-label">Fax Number</label>
		    <div class="col-sm-8">
		      	<input type="text" class="form-control form-control-sm" v-model="receiverFaxNumber">
		    </div>
		</div><!-- /.form-group row-->


		<div class="form-group row">
		    <label class="col-sm-4 col-form-label">Email</label>
		    <div class="col-sm-8">
		      	<input type="text" class="form-control form-control-sm" v-model="receiverEmail">
		    </div>
		</div><!-- /.form-group row-->


		<div class="form-group row">
		    <label class="col-sm-4 col-form-label">Signature Required</label>
		    <div class="col-sm-8">
		    	<div class="form-check" style="margin-top: 5px;">
		      		<input class="form-check-input" type="checkbox" v-model="sigRequired">
		      	</div>
		    </div>
		</div><!-- /.form-group row-->


		<div class="form-group row">
		    <label class="col-sm-4 col-form-label">Notes</label>
		    <div class="col-sm-8">
		    	<div class="input-group input-group-sm">
					<textarea class="form-control" v-model="specialInstructions"></textarea>
				</div>
		    </div>
		</div><!-- /.form-group row-->


	</div>
</div>