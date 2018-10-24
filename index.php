<?php

require_once "./config.php";

redirectIfGuest();

?>

<!doctype html>
<html lang="en">
	<head>
	    <!-- Required meta tags -->
	    <meta charset="utf-8">
	    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

	   	<title><?= APP_NAME ?></title>

	    <!-- Bootstrap CSS -->
	    <link rel="stylesheet" href="./css/vendors/bootstrap.min.css">

		<!-- Date Picker -->
  		<link rel="stylesheet" href="./css/vendors/datepicker.min.css">

	    <!-- Custom Styles-->
	    <link rel="stylesheet" href="./css/style.css">

		<!-- Font Awesome -->
	    <script src="https://use.fontawesome.com/eb8d3fb422.js"></script>

	</head>

	<body>

		<div id="app">

			<!-- Top Navigation -->
				<?php include "./views/top-navbar.php"; ?>
			<!--/ Top Navigation -->

			<!-- Modals -->
				<?php include "./views/modals/get-rates.php"; ?>
				<?php include "./views/modals/create-shipment.php"; ?>
				<?php include "./views/modals/print-label.php"; ?>
				<?php include "./views/modals/void-shipment.php"; ?>
				<?php include "./views/modals/show-shipment.php"; ?>
				<?php include "./views/modals/end-of-day.php"; ?>
			<!--/ Modals -->

			<!-- Main Content -->
				<?php include "./views/main.php"; ?>
			<!--/ Main Content -->

	  	</div>


	    <!-- jQuery first, then Bootstrap JS -->
		<script src="js/vendors/jquery-3.2.1.slim.min.js"></script>
		<script src="js/vendors/bootstrap.min.js"></script>

	    <!-- Add Vue js for manipulation with data -->
		<script src="js/vendors/vue.js"></script>
		<script src="js/vendors/axios.min.js"></script>

		<!-- Moment.Js library is required for datepicker -->
		<script src="js/vendors/moment.min.js"></script>

		<!-- Date Picker -->
		<script src="js/vendors/datepicker.min.js"></script>

		<!-- Main JS -->
		<script src="js/app.js"></script>
  	</body> 
</html>