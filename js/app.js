var app = new Vue({
  				el: '#app', 
				data: {

					//New Shipment Sender
					locations: [],
					senderLocationCode: '',
					senderId: '',
					senderName: '',
					senderCompany: '',
					senderStreetNumber: '',
					senderStreetName: '',
					senderCity: '',
					senderAddress: '',
					senderPostalCode: '',
					senderPhoneCountryCode: '1',
					senderPhoneAreaCode: '', 
					senderPhone: '',
					senderProvince: '',
					senderCountry: 'CA',

					//New Shipment Receiver
					incomingOrderId: $('#incomingOrderId').val(),
					orderId: '',
					receiverCode: '',
					receiverCountry: 'CA',
					receiverName: '',
					receiverAttentionTo: '',
					receiverStreetNumber: '',
					receiverStreetName: '',			
					receiverAddress2: '',
					receiverAddress3: '',
					receiverCity: '',
					receiverProvince: '',
					receiverPostalCode: '',
					receiverPhoneAreaCode: '',
					receiverPhone: '',
					receiverPhoneExtension: '',
					receiverFaxNumber: '',
					receiverEmail: '',
					receiverEmailBody: 'Message to Customer',
					specialInstructions: 'No Redirects Permitted', 
					sigRequired: true,

					packages: [],
					services: [],
					selectedService: '',
					boxes: [],
					selectedBoxId: '',
					orders: [],
					ordersDate: '',
					labels: '',

					//Completed Shipment Details
					pins: [],
					storedLabel: '',
					pdfLabels: [],
					shipmentPin: '',
					shipmentCreated: '',
					shipmentService: '',
					shipmentAdminName: '',
					shipmentAdminId: '',
					shipmentOrderId: '',
					shipmentSenderAddress: '',
					shipmentSenderCity: '',
					shipmentSenderId: '',
					shipmentSenderPostalCode: '',
					shipmentVoided: 0,

					//Manifest
					manifest: '',
					manifestNumber: '',
					manifestDate: '',
					manifestType: '',
					manifestDescription: '',	
					manifestStatus: '',					

					areRatesVisible: 0,
					displayLoadServicesSpinner: 0,
					displayLoadShipmentSpinner: 0,
					displayVoidShipmentSpinner: 0,
					displayLoadManifestSpinner: 0,
					displayEndOfDaySpinner: 0,
					emailToCustomerSent: 0,
					voidShipmentPin: '',
					errors: [],
					confirmation: '',
					width: '',
					length: '',
					height: '',
					weight: '',
					reference: '',
					note: '',
				    message: 'You loaded this page on ' + new Date().toLocaleString()
				},


				computed: {
					shipmentLoader: function() {
						return (this.isShipmentLoaderVisible > 0 && this.shipmentPin.length !== '') ? true : false;
					},

					getDateForHumans: function() {
						return moment(this.ordersDate).format("LL");
					},

					widthInches: function() {
						return this.convertCmToInches(this.width);
					},

					lengthInches: function() {
						return this.convertCmToInches(this.length);
					},

					heightInches: function() {
						return this.convertCmToInches(this.height);
					},

					weightLbs: function() {
						return this.convertKgToLbs(this.weight);
					},

					label64Base: function() {
						if(this.labels.length > 1) {
							return "data:image/png;base64, " + this.labels;
						}
					}
				},


				mounted: function() {

					this.getSenderLocation();
					this.getLocations();
					this.getOrders(); 
					this.activateDatePicker();
					this.setCurrentDate();
					this.getShippingBoxes();

					if(this.incomingOrderId !== '0') {
						this.orderId = this.incomingOrderId;
						this.searchShipmentByOrderId();
					} 
				},


				methods: {
					
					getSelectedDate: function() {
						this.ordersDate = this.$refs.dateField.value;
						return moment(this.ordersDate);
					},


					setCurrentDate: function() {
						this.ordersDate = moment().format('L');
					},


					getTotalWeight: function() {
						var totalWeight = 0;
						this.packages.forEach(function(package) {
							totalWeight += parseFloat(package.weight);
						});

						return totalWeight;
					},


					convertCmToInches: function(cm) {
						var inches = (cm > 0) ? (cm * 1)/2.54 : 0;
						return inches.toFixed(2);
					},


					convertKgToLbs: function(kg) {
						var lb = (kg > 0) ? 2.2 * kg : 0;
						return lb.toFixed(2); 
					},


					getTotalPieces: function() {

						return this.packages.length;
					},					


					isEmpty: function(string) {

						return (string === '' || string === 0) ? true : false;
					},


					setPackageSizesBySelectedBox: function() {
						var self = this;

						this.boxes.forEach(function(box) {
							if(box.id === self.selectedBoxId) {
								self.length = box.length;
								self.width = box.width;
								self.height = box.height;
							}
						});
					},


					addPackage: function() {

						if(this.weight === '' || this.length === '' || this.width === '' || this.height === '') {
							return;
						}

						this.packages.push({
							'weight': this.weight,
							'length': this.length,
							'width': this.width,
							'height': this.height,
							'reference': this.reference,
							'note': this.note
						});

						this.clearPackageForm();
						this.getAvailableServices();
					},


					removePackage: function(index) {
						this.packages.splice(index, 1);

						this.getAvailableServices();
					},


					clearPackageForm: function() {
						this.weight = '';
						this.length = ''; 
						this.width = '';
						this.height = '';
						this.reference = '';
						this.note = '';
					},


					getLocations: function() {
						var self = this;

						axios.post("api.php", {
							action: "getLocations"
						
						}).then(function (response) {
							self.locations = response.data;
  						});
					},


					getSenderLocation: function() {
						var self = this;

						this.resetAvailableServices();

						axios.post("api.php", {
							action: "getSenderLocation",
							Id: this.senderId
						
						}).then(function (response) {
    						self.populateSender(response.data.sender);
  						});
					},


					searchShipmentByOrderId: function() {

						this.getReceiverByOrderId();
						this.getSenderByOrderId();
					},


					getReceiverByOrderId: function() {
						var self = this;

						axios.post("api.php", {
							action: "getReceiverByOrderId",
							orderID: this.orderId
						
						}).then(function (response) {
    						self.populateReceiver(response.data.receiver);
  						});
					},


					getSenderByOrderId: function() {
						var self = this;

						axios.post("api.php", {
							action: "getSenderByOrderId",
							orderID: this.orderId
						
						}).then(function (response) {
							self.populateSender(response.data.sender);
  						});
					},


					getShippingBoxes: function() { 
						var self = this;

						axios.post("api.php", {
							action: "getShippingBoxes"
						
						}).then(function (response) {

							if( response.data.boxes.length > 0) {

    							self.boxes = response.data.boxes;
							}
  						});						

					},


                    populateSender: function(sender) {

						if(!sender || typeof sender == "undefined" || sender.Id === '') {
							return;
						}

                    	this.senderId = sender.Id;
                    	this.senderLocationCode = sender.LocationCode;
						this.senderName = sender.Name;
						this.senderCompany = sender.Company;
						this.senderStreetNumber = sender.StreetNumber;
						this.senderStreetName = sender.StreetName;
						this.senderCity = sender.City;
						this.senderPostalCode = sender.PostalCode;
						this.senderPhoneAreaCode = sender.PhoneAreaCode;
						this.senderPhone = sender.Phone;
						this.senderProvince = sender.Province;
                    }, 


					populateReceiver: function(receiver) {

						if(!receiver || typeof receiver == "undefined") {
							return;
						}

						this.receiverCode = receiver.CustomerCode;
						this.receiverCountry = receiver.Country;
						this.receiverName = receiver.ShippingName;
						this.receiverAttentionTo = receiver.ShippingName;
						this.receiverStreetNumber = receiver.StreetNumber;
						this.receiverStreetName = receiver.StreetName;
						this.receiverAddress2 = receiver.AddressLine2;
						this.receiverAddress3 = receiver.AddressLine3;
						this.receiverCity = receiver.City;
						this.receiverProvince = receiver.ProvinceCode;
						this.receiverPostalCode = receiver.PostalCode;
						this.receiverPhoneAreaCode = receiver.PhoneAreaCode;
						this.receiverPhone = receiver.Phone;
						this.receiverEmail = receiver.Email;
					},


					displayRates: function() {
						this.getAvailableServices();
						this.areRatesVisible = 1;
					},


					resetManifest: function() {

						this.manifestDate = "";
						this.manifestType = "";
						this.manifestDescription = "";	
						this.manifestStatus = "";					
					},


					resetAvailableServices: function() {
						this.services =  [];
						this.selectedService = '';
						this.errors = [];
					},


					getAvailableServicesFormValidation: function() {
						this.errors = [];

						if(this.senderPostalCode === '') { this.errors.push("Sender Postal Code is required"); }
						if(this.receiverCity === '') { this.errors.push("Customer City is required"); }
						if(this.receiverProvince === '') { this.errors.push("Customer Province is required"); }
						if(this.receiverPostalCode === '') { this.errors.push("Customer Postal Code is required"); }
						if(this.getTotalPieces() === 0) { this.errors.push("At least one Package is required"); }

						return (this.errors.length === 0) ? true : false;
					},


					getAvailableServices: function() {
						var self = this;

						if(!this.getAvailableServicesFormValidation()) {
							return false;
						}

						this.displayLoadServicesSpinner = 1;
						this.resetAvailableServices();	

						axios.post("api.php", {
		
							action: "getAvalableServices",

							//Sender
							senderStreetNumber: this.senderStreetNumber, 
							senderStreetName: this.senderStreetName, 
							senderCity: this.senderCity, 
							senderProvince: this.senderProvince, 
							senderPostalCode: this.senderPostalCode, 
							senderPhoneAreaCode: this.senderPhoneAreaCode, 
							senderPhone: this.senderPhone,  

							//Receiver
							receiverName: this.receiverName, 
							receiverStreetNumber: this.receiverStreetNumber, 
							receiverStreetName: this.receiverStreetName, 
							receiverPhoneAreaCode: this.receiverPhoneAreaCode,   
							receiverPhone: this.receiverPhone,   
							receiverCity: this.receiverCity, 
							receiverProvince: this.receiverProvince, 
							receiverPostalCode: this.receiverPostalCode,  

							//Packages
							packages: this.packages,
							totalWeight: this.getTotalWeight(),
							totalPieces: this.getTotalPieces()
						
						}).then(function (response) {

							if( response.data.services.length > 0) {

								self.services = response.data.services;
								self.selectedService = response.data.services[0].service_type;
								self.displayLoadServicesSpinner = 0;
								
								return true;
							}

							self.handleErrors(response.data.errors);
							self.displayLoadServicesSpinner = 2;

  						});
					},


					resetShipmentDetails: function() {

						this.createShipmentFormValidation();

						this.pins = [];
						this.shipmentPin = '';
					},


					createShipmentFormValidation: function() {
						this.errors = [];

						if(this.senderName === '') { this.errors.push("Store Name is required"); }
						if(this.senderPostalCode === '') { this.errors.push("Store Postal Code is required"); }
						if(this.senderStreetNumber === '') { this.errors.push("Store Street Number is required"); }
						if(this.senderStreetName === '') { this.errors.push("Store Street Name is required"); }
						if(this.senderCity === '') { this.errors.push("Store City is required"); }
						if(this.senderProvince === '') { this.errors.push("Store Province is required"); }
						if(this.senderPhoneAreaCode === '') { this.errors.push("Store Phone Area is required"); }						
						if(this.senderPhone === '') { this.errors.push("Store Phone is required"); }		


						if(this.receiverName === '') { this.errors.push("Customer Name is required"); }
						if(this.receiverCity === '') { this.errors.push("Customer City is required"); }
						if(this.receiverStreetNumber === '') { this.errors.push("Customer Street Number is required"); }
						if(this.receiverStreetName === '') { this.errors.push("Customer Street Name is required"); }
						if(this.receiverProvince === '') { this.errors.push("Customer Province is required"); }
						if(this.receiverPhoneAreaCode === '') { this.errors.push("Customer Phone Area is required"); }						
						if(this.receiverPhone === '') { this.errors.push("Customer Phone is required"); }	

						if(this.receiverPostalCode === '') { this.errors.push("Customer Postal Code is required"); }
						if(this.services.length === 0) { this.errors.push("At least one Purolator Service should be selected");}
						if(this.getTotalPieces() === 0) { this.errors.push("At least one Package is required"); }

						return (this.errors.length === 0) ? true : false;
					},


					createShipment: function() {
						var self = this;

						if(!this.createShipmentFormValidation()) {
							return false;
						}

						this.pins = [];
						this.displayLoadShipmentSpinner = 1;

						axios.post("api.php", {
		
							action: "createShipment",

							//Sender
							senderLocationCode: this.senderLocationCode,
							senderName: this.senderName,
							senderCompany: this.senderCompany,
							senderStreetNumber: this.senderStreetNumber, 
							senderStreetName: this.senderStreetName, 
							senderCity: this.senderCity, 
							senderProvince: this.senderProvince, 
							senderCountry: this.senderCountry, 
							senderPostalCode: this.senderPostalCode, 
							senderPhoneAreaCode: this.senderPhoneAreaCode, 
							senderPhone: this.senderPhone,  

							//Receiver
							receiverCode: this.receiverCode, 
							receiverName: this.receiverName, 
							receiverCompany: this.receiverName, 
							receiverStreetNumber: this.receiverStreetNumber, 
							receiverStreetName: this.receiverStreetName, 
							receiverAddress2: this.receiverAddress2, 
							receiverAddress3: this.receiverAddress3, 
							receiverPhoneAreaCode: this.receiverPhoneAreaCode,   
							receiverPhone: this.receiverPhone,   
							receiverPhoneExtension: this.receiverPhoneExtension,   
							receiverFaxNumber: this.receiverFaxNumber, 
							receiverEmail: this.receiverEmail,  	
							receiverCity: this.receiverCity, 
							receiverProvince: this.receiverProvince, 
							receiverCountry: this.receiverCountry,
							receiverPostalCode: this.receiverPostalCode,  
							specialInstructions: this.specialInstructions,

							//Packages
							packages: this.packages,
							totalWeight: this.getTotalWeight(),
							totalPieces: this.getTotalPieces(),

							//Order ID
							orderID: this.orderId,

							//Selected Service
							serviceID: this.selectedService

						}).then(function (response) {

							self.displayLoadShipmentSpinner = 0;

							if( response.data.errors.length === 0) {
								self.labels = response.data.labels;

								self.getOrders();
								self.activateTab('history');

								return true;
							}

							self.handleErrors(response.data.errors); 
  						});
					},


					getCompletedShipment: function(shipmentPin) {
						var self = this;

						this.errors = [];
						this.shipmentPin = shipmentPin;

						axios.post("api.php", {
							action: "getShipmentDetails",
							pin: this.shipmentPin,
						})
						.then(function (response) {

							if( response.data.errors.length === 0) { 

								self.shipmentCreated = response.data.shipment.date;
								self.shipmentService = response.data.shipment.service || '';
								self.shipmentAdminName = response.data.shipment.adminName || '';
								self.shipmentAdminId = response.data.shipment.adminId;
								self.shipmentOrderId = response.data.shipment.orderId;
								self.shipmentSenderAddress = response.data.shipment.senderAddress;
								self.shipmentSenderCity = response.data.shipment.senderCity;
								self.shipmentSenderId = response.data.shipment.senderLocationId;
								self.shipmentSenderPostalCode = response.data.shipment.senderPostalCode;
								self.shipmentVoided = response.data.shipment.voided;
								self.storedLabel = response.data.shipment.storedLabel;
								self.displayLoadServicesSpinner = 0;

								return true;
							}

							self.handleErrors(response.data.errors);

  						});
					},


					shipAgain: function(orderId, locationId) {
						this.orderId = orderId;
						this.senderId = locationId;

						this.getReceiverByOrderId();
						this.getSenderLocation();

						this.packages = [];
						this.services = [];
						this.shipmentPin = '';

						this.activateTab('shipment');
					},


					printLabel: function(label) {

						label = label || this.labels;

    					var labelImage = window.open(label);
    					labelImage.window.print();

    					return false;
					},

					endOfDay: function() {

						var self = this;
						this.errors = [];
						this.displayEndOfDaySpinner = 1;
						this.manifestNumber = '';


						axios.post("api.php", {
							action: "endOfDay"

						}).then(function(response) {

							self.displayEndOfDaySpinner = 0;

							if( response.data.errors.length === 0) {
								self.manifestNumber = response.data.manifestNumber;
								self.getManifest(self.manifestNumber);
							}
							
							self.handleErrors(response.data.errors); 
						});
					},


					getManifest: function(manifestNumber) {

						var self = this;
						this.errors = [];		
						this.displayLoadManifestSpinner = 1;
						this.manifest = '';

						manifestNumber = manifestNumber || this.manifestNumber;

						axios.post("api.php", {
							action: "getManifest",
							manifestNumber: manifestNumber,
						
						}).then(function (response) {

							self.displayLoadManifestSpinner = 0;
							if( response.data.errors.length === 0) {

								self.manifest = response.data.manifest;
								return true;
							}

							self.handleErrors(response.data.errors); 

  						});
					},


					handleErrors: function(errors) {
						this.errors = [];

						if(typeof errors === "undefined") {
							return;
						}

						if(errors.constructor === Array) {
							this.errors = errors;
							return true;
						}

						this.errors.push(errors);
					},


					getOrders: function(date) {
						var self = this;

						this.orders = [];
						this.ordersDate = this.getSelectedDate().format("YYYY-MM-DD");

						if(date === 'today') {
							this.setCurrentDate();
						} 


						axios.post("api.php", {
							action: "getShipmentsByDate",
							date: this.ordersDate
						})
						.then(function (response) {

							if( response.data.errors.length === 0) {
								self.orders = response.data.shipments;

								return true;
							}
							
							self.handleErrors(response.data.errors); 

  						});
					},


					getShippingManifest: function() {
						return false;
					},


					VoidShipment: function() {
						var self = this;
						this.confirmation = '';
						this.displayVoidShipmentSpinner = 1;

						axios.post("api.php", {
							action: "voidShipment",
							id: this.voidShipmentPin,
						})
						.then(function (response) {

							self.displayVoidShipmentSpinner = 0;

							if(response.data.voided !== '') {

								self.getOrders();
								self.confirmation = "Shipment has been voided";
								return true;
							}

							self.handleErrors(response.data.errors); 
  						});
					},


					sendEmailToCustomer: function() {
						var self = this;

						axios.post("api.php", {
							action: "sendEmail",
							receiverName: this.receiverName,
							receiverEmail: this.receiverEmail,
							receiverEmailBody: this.receiverEmailBody,
							pdfLabels: self.pdfLabels,
							pins: self.pins
						})
						.then(function (response) {

							if(response.data.sent === true) {

								self.emailToCustomerSent = 1;
								return true;
							}

							self.emailToCustomerSent = 2;
  						});
					},


					activateTab: function(id) {
 
						$('.nav-tabs a[href="#' + id + '"]').tab('show');
						window.scrollTo(0, 0);
					},


					activateDatePicker: function() {
						$('#datetimepicker').datetimepicker({
		            		format: 'L'
		        		});
					}
				}
			});