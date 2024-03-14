document.addEventListener("DOMContentLoaded", function () {
	document.getElementById("userlogin").addEventListener("click", function() {
		const email = document.getElementById('email').value;
		const pin = document.getElementById('pin').value;

		// fetch API placeholder below
		fetch('/api?user-login', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
			},
			body: JSON.stringify({
				email,
				pin
			}),
		})
		.then(ret => {
			if (ret.status == 200) { window.location.href = '/index?user-interface'; }
			else { alert("Login details incorrect"); }
		})
		.catch(error => {
			console.error('Error:', error);
		});
	});
	
	document.getElementById("userregister").addEventListener("click", function() {
		window.location.href = '/index?signup';
	});
});
