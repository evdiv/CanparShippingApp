<div class="tab-pane fade show active" id="shipment" role="tabpanel" aria-labelledby="shipment-tab">

	<div class="alert alert-danger" role="alert">

		<div class="form-group row">
		    <label for="carrier" class="col-sm-4 col-form-label">Carrier</label>
		    <div class="col-sm-8">
		    	<div class="input-group input-group-sm">
		      		<input type="text" class="form-control form-control-sm" id="carrier" value="Canpar" readonly>
				</div>
		    </div>
		</div><!-- /.form-group row -->


		<div class="form-group row">
		    <label class="col-sm-4 col-form-label">Available Services  	
		    	<div v-if="displayLoadServicesSpinner == 1" class="fa fa-spinner fa-spin"></div>
		    	<span v-if="displayLoadServicesSpinner == 2">
		    		<span v-on:click="displayRates" 
							data-toggle="modal" 
							data-target="#ratesModal" class="fa fa-exclamation-circle"></span>
		    	</span>
		    </label>

		    <div class="col-sm-8">
		    	<div class="input-group input-group-sm" v-cloak>
			    	<select class="form-control form-control-sm" v-model="selectedService">
				      	<option v-for="service in services" :value="service.service_type">{{ service.service_name }}</option>
				    </select>

			      	<div class="input-group-append">
			      		<button class="btn btn-success" type="button" v-on:click="getAvailableServices">
			      			<i class="fa fa-refresh" aria-hidden="true"></i> Update
			      		</button>
				  	</div>
				</div>
		    </div>
		</div><!-- /.form-group row -->

	</div><!-- /.alert alert-danger -->


	<div class="alert alert-primary" role="alert">

		<div class="form-group row">
		    <label class="col-sm-4 col-form-label">Available Boxes</label>

		    <div class="col-sm-8">
		    	<div class="input-group input-group-sm" v-cloak>
			    	<select class="form-control form-control-sm" v-model="selectedBoxId" @change="setPackageSizesBySelectedBox">
				      	<option :value="box.id" v-for="box in boxes">{{ box.description }} - weight limit {{ box.weightLimit }}kg.</option>
				    </select>
				</div>
		    </div>
		</div><!-- /.form-group row -->	

		<hr/>

	  	<div class="form-group row" style="margin-bottom: 6px;">
	  		<div class="col-sm-4" style="font-size: 0.8rem;" v-cloak>{{ lengthInches }} inches</div>
	  		<div class="col-sm-4" style="font-size: 0.8rem;" v-cloak>{{ widthInches }} inches</div>
	  		<div class="col-sm-4" style="font-size: 0.8rem;" v-cloak>{{ heightInches }} inches</div>

		    <div class="col-sm-4">
		    	<div class="input-group input-group-sm">
		      		<input type="number" class="form-control form-control-sm" placeholder="Length" value="" v-model="length" min="0">
			      	<div class="input-group-append">
	    				<span class="input-group-text">cm</span>
				  	</div>
				</div>
		    </div>

		    <div class="col-sm-4">
		    	<div class="input-group input-group-sm">
		      		<input type="number" class="form-control form-control-sm" placeholder="Width" value="" v-model="width"  min="0">
			      	<div class="input-group-append">
	    				<span class="input-group-text">cm</span>
				  	</div>
				</div>
		    </div>

		    <div class="col-sm-4">
		    	<div class="input-group input-group-sm">
		      		<input type="number" class="form-control form-control-sm" placeholder="Height" value="" v-model="height"  min="0">
			      	<div class="input-group-append">
	    				<span class="input-group-text">cm</span>
				  	</div>
				</div>
		    </div>
	  	</div><!-- /.form-group row -->


		<div class="form-group row" style="margin-bottom: 6px;">
			<div class="col-sm-12" style="font-size: 0.8rem;" v-cloak>{{ weightLbs }} lbs</div>
		    <div class="col-sm-12">
		    	<div class="input-group input-group-sm">
		      		<input type="number" class="form-control form-control-sm" id="weight" placeholder="Weight" value="" v-model="weight"  min="0">
			      	<div class="input-group-append">
	    				<span class="input-group-text">kg</span>
				  	</div>
				</div>
		    </div>
		</div><!-- /.form-group row -->


	  	<div class="form-group row">
		    <label for="packageReference" class="col-sm-4 col-form-label">Package Reference</label>

		    <div class="col-sm-8">
		    	<div class="input-group input-group-sm">
		      		<input type="text" class="form-control form-control-sm" id="packageReference" placeholder="Package Reference" value="" v-model="reference">
				</div>
		    </div>
	  	</div><!-- /.form-group row -->



		<div class="form-group row" style="margin-bottom: 12px;">
		    <label for="packageNote" class="col-sm-4 col-form-label">Package Note</label>
		    <div class="col-sm-8">
		    	<div class="input-group input-group-sm">
					<textarea class="form-control" v-model="note"></textarea>
				</div>
		    </div>
		</div><!-- /.form-group row -->


		<div class="text-right">
			<button type="button" class="btn btn-success btn-sm" v-on:click="addPackage"> <i class="fa fa-plus" aria-hidden="true"></i> Add Package </button>
		</div>

	</div><!--/.alert alert-primary -->

	
	<table class="table table-hover table-sm" v-cloak>
		<thead>
		    <tr>
			    <th scope="col">ID</th>
			    <th scope="col">Weight</th>
			    <th scope="col">Reference</th>
			    <th scope="col">Special Handling</th>
			    <th scope="col">Delete</th>	
			</tr>
	  	</thead>
	  	<tbody v-for="(package, index) in packages">
		    
		    <tr>
			    <td>{{ index + 1 }}</td>
			    <td>{{ package.weight }}<small class="text-muted"> kg.</small></td>
			    <td><small class="text-secondary">{{ package.reference }}</small></td>
			    <td><small class="text-secondary">{{ package.note}}</small></td>	
			    <td><span class="active-link" v-on:click="removePackage(index)"><i class="fa fa-trash text-danger" aria-hidden="true"></i></span></td>		    
		    </tr>
		</tbody>
	</table>


	<div class="text-right" style="margin-top: 24px;">
		<? if($returnShipment): ?>

			<button type="button" class="btn btn-info btn-sm" 
						data-toggle="modal" 
						data-target="#createReturnShipmentModal" 
						v-on:click="resetShipmentDetails" 
		          		href="#"> Create Return Shipment</button>	

		<? else: ?>
			<button type="button" class="btn btn-info btn-sm" 
					data-toggle="modal" 
					data-target="#createShipmentModal"  
					v-on:click="resetShipmentDetails"> Create Shipment </button>
		<? endif;?>
	</div>

</div> 