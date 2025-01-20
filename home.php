<html>

<head>
	<title>Random</title>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
	<script>
		function sendData() {
			const store = document.getElementById("store").value;
			const paymentOption = document.getElementById("payment_option").value;

			const responseDiv = document.getElementById("response");

			// Show loading text
			responseDiv.innerHTML = "Loading..."; // Display "Loading..." message
			responseDiv.style.display = 'block'; // Ensure the response div is visible

			// Send the data to the PHP file using AJAX
			$.ajax({
				url: 'index.php', // PHP file to handle the request
				type: 'POST',
				data: {
					store: store,
					paymentOption: paymentOption
				},
				success: function(response) {
					// Once the response is received, update the div with the response
					responseDiv.innerHTML = response;
					responseDiv.style.padding = '15px';
					responseDiv.style.border = '2px solid #007BFF';
					responseDiv.style.borderRadius = '5px';
					responseDiv.style.backgroundColor = '#f1f8ff';
					responseDiv.style.color = '#333';
					responseDiv.style.fontFamily = 'Arial, sans-serif';
					responseDiv.style.fontSize = '16px';
				},
				error: function() {
					// Handle error case
					responseDiv.innerHTML = "Error sending data.";
					responseDiv.style.display = 'block'; // Make the response visible
					responseDiv.style.padding = '15px';
					responseDiv.style.border = '2px solid #FF5733';
					responseDiv.style.borderRadius = '5px';
					responseDiv.style.backgroundColor = '#ffebe6';
					responseDiv.style.color = '#d9534f';
					responseDiv.style.fontFamily = 'Arial, sans-serif';
					responseDiv.style.fontSize = '16px';
				}
			});
		}
	</script>
</head>

<body>
	<div>
		<label for="store">Store</label>
		<select id="store">
			<option value=""></option>
			<option value="7">Test Store</option>
		</select>
	</div>
	<div>
		<label for="paymentOption">Payment Option</label>
		<select id="payment_option" onchange="sendData()">
			<option value=""></option>
			<option value="Virtual Account Payment">Virtual Account Payment</option>
		</select>
	</div>

	<div id="response"></div>
</body>

</html>