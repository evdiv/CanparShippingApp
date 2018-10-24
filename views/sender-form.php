<div class="tab-pane fade" id="sender" role="tabpanel" aria-labelledby="sender-tab">


	<div class="alert alert-secondary" role="alert">
	
		<div class="form-group row" >
		    <label class="col-sm-4 col-form-label">Select Location</label> 
		   	<div class="col-sm-8">
			    <select class="form-control form-control-sm" v-model="senderId" @change="getSenderLocation"> 
			      	<option v-for="location in locations" :value="location.Id">{{ location.city }}</option>
			    </select>
			</div>
	  	</div><!-- /.form-group row-->


		<div class="form-group row">
		    <label class="col-sm-4 col-form-label">Name</label>
		    <div class="col-sm-8">
		      <input type="text" class="form-control form-control-sm" v-model="senderName">
		    </div>
		</div><!-- /.form-group row-->


		<div class="form-group row">
		    <label class="col-sm-4 col-form-label">Company</label>
		    <div class="col-sm-8">
		      <input type="text" class="form-control form-control-sm" v-model="senderCompany">
		    </div>
		</div><!-- /.form-group row-->



		<div class="form-group row">
		    <label class="col-sm-4 col-form-label">Address</label>
		    <div class="col-sm-8">
				<div class="form-row">
				    <div class="col-4">
				      	<input type="text" class="form-control form-control-sm" v-model="senderStreetNumber">
				    </div>
				    <div class="col-8">
				       	<input type="text" class="form-control form-control-sm" v-model="senderStreetName">
				    </div>
				</div>
			</div>
		</div><!-- /.form-group row-->


		<div class="form-group row">
		    <label class="col-sm-4 col-form-label">City</label>
		    <div class="col-sm-8">
		      <input type="text" class="form-control form-control-sm" v-model="senderCity">
		    </div>
		</div><!-- /.form-group row-->


		<div class="form-group row">
		    <label class="col-sm-4 col-form-label">Postal Code</label>
		    <div class="col-sm-8">
		      <input type="text" class="form-control form-control-sm" v-model="senderPostalCode">
		    </div>
		</div><!-- /.form-group row--> 


		<div class="form-group row">
		    <label class="col-sm-4 col-form-label">Phone</label>
		    <div class="col-sm-8">
				<div class="form-row">
				    <div class="col-2">
				      	<input type="text" class="form-control form-control-sm" v-model="senderPhoneCountryCode">
				    </div>
				    <div class="col-5">
				       	<input type="text" class="form-control form-control-sm" v-model="senderPhoneAreaCode">
				    </div>

				    <div class="col-5">
				       	<input type="text" class="form-control form-control-sm" v-model="senderPhone">
				    </div>
				</div>
			</div>
		</div><!-- /.form-group row-->


		<div class="form-group row">
		    <label class="col-sm-4 col-form-label" style="margin-bottom: 3px;">Province</label>
		    <div class="col-sm-8">
		    	<select class="form-control form-control-sm" v-model="senderProvince">
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
		    <label class="col-sm-4 col-form-label">Country</label>
		    <div class="col-sm-8">
		    	<select class="form-control form-control-sm" v-model="senderCountry">
			      	<option value="CA">Canada</option>
			    </select>
		    </div>
		</div><!-- /.form-group row-->								  

	</div>
</div>