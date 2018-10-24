<div class="tab-pane fade" id="history" role="tabpanel" aria-labelledby="history-tab">

	<!-- Date Picker Form -->
    <div class="row justify-content-end" style="padding: 24px 0">
		<div class="col-sm-6"><small>Canpar Shipments for <b>{{ getDateForHumans }}</b> </small></div>


        <div class="col-sm-4">
            <div class="form-group">
                <div class="input-group input-group-sm date" id="datetimepicker" data-target-input="nearest">
                    <input type="text" 
                    	class="form-control datetimepicker-input"
                    	data-target="#datetimepicker"
                    	placeholder="Enter Date"
                    	ref="dateField" 
                    	:value="ordersDate"/>

                    <div class="input-group-append" data-target="#datetimepicker" data-toggle="datetimepicker">
                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-2">
			<button type="button" class="btn btn-success btn-sm" @click="getOrders()"><i class="fa fa-refresh"></i> Update </button>
        </div>
    </div>
	<!--/ Date Picker Form -->
	

	<!-- Shipments Table -->
	<table class="table table-hover table-sm" v-cloak>
		
		<thead>
		    <tr>
			    <th scope="col">Order ID</th>
			    <th scope="col">Tracking #</th>
			    <th scope="col"></th>
			    <th scope="col"></th>
			   	<th scope="col"></th>			    
			</tr>
	  	</thead>

	  	<tbody v-for="(order, index) in orders" style="line-height: 1.1; font-size: 0.8rem;">
		    
		    <tr :class="{'table-danger': order.void == 1}">

			    <td><a href="#" 
			    	data-toggle="modal" 
			    	data-target="#showShipmentModal" 
			    	@click="getCompletedShipment(order.pin)">{{ order.orderId }}</a></td>


			    <td><a target="_blank" :href="'https://www.canpar.com/en/track/TrackingAction.do?locale=en&type=0&reference=' + order.pin">{{ order.pin }}</a></td>

			    <td><button type="button"
			    		@click="shipAgain(order.orderId, order.locationId)" 
			    		class="btn btn-warning btn-sm"
			    		style="padding: .2rem .5rem; line-height: 1.2; font-size: 0.6rem;">Ship Again</button></td>


				<td><button v-if="order.void == 0"
						type="button" 
			    		class="btn btn-secondary btn-sm"
						data-toggle="modal" 
						data-target="#printLabelModal"  
						@click="getCompletedShipment(order.pin)" 
			    		style="padding: .2rem .5rem; line-height: 1.2; font-size: 0.6rem;">Print Label</button>

			    	<div v-else style="padding: .2rem .5rem; line-height: 1.2; font-size: 0.6rem; text-align: left;">Voided</div>
			    </td>


			    <td><button v-if="order.void == 0"
			    		type="button" 
			    		class="btn btn-danger btn-sm" 
			    		data-toggle="modal" 
			    		data-target="#voidShipmentModal"
			    		@click="{ voidShipmentPin = order.canparShipmentId; errors = []; }" 
			    		style="padding: .2rem .5rem; line-height: 1.2; font-size: 0.6rem;">Void Shipment</button>

			    	<div v-else style="padding: .2rem .5rem; line-height: 1.2; font-size: 0.6rem; text-align: left;">Voided</div>
			    </td>

			</tr>

		</tbody>
	</table>
	<!-- Shipments Table -->
	
</div>