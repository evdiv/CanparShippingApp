<div class="container-fluid">
	<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
		<div class="container">

			<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
			    <span class="navbar-toggler-icon"></span></button>

			<div class="collapse navbar-collapse" id="navbarSupportedContent">

			    <ul class="navbar-nav mr-auto">
				    <li class="nav-item active">
				        <a class="nav-link" href="http://www.canpar.com/en/home.jsp" target="_blank">
							<img src="//<?= APP_URL ?>/images/logo-canpar.png" height="28px">
				        </a> 
				    </li>
			    </ul>


				<div class="my-2 my-lg-0">

					<button type="button" class="btn btn-info btn-sm" 
						v-on:click="displayRates" 
						data-toggle="modal" data-target="#ratesModal">Get Rates</button>


					<button type="button" class="btn btn-info btn-sm" 
						data-toggle="modal" 
						data-target="#createShipmentModal"  
						v-on:click="resetShipmentDetails">Create Shipment</button>


					<button type="button" class="btn btn-secondary btn-sm" 
								data-toggle="modal" 
								data-target="#endOfDayModal"  
				          		href="#"> End of the Day</button>
				          			
				</div><!--/.my-2 my-lg-0-->

	  		</div><!-- /#navbarSupportedContent-->

		</div><!-- /.container -->
	</nav>
</div>