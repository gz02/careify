document.addEventListener("DOMContentLoaded", function () {
	document.getElementById("carerlogin").addEventListener("click", function() {
		const email = document.getElementById('email').value;
		const password = document.getElementById('password').value;

		// fetch API placeholder below
		fetch('/api?carer-login', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
			},
			body: JSON.stringify({
				email,
				password
			}),
		})
		.then(ret => {
			if (ret.status == 200) { window.location.href = '/index?carer-interface'; }
			else { alert("Login details incorrect"); }
		})
		.catch(error => {
			console.error('Error:', error);
		});
	});
});
