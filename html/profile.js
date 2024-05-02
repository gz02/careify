document.addEventListener("DOMContentLoaded", function () {
	fetch("/api?profile")
	.then(ret => ret.json())
	.then(ret => {
		document.getElementById("firstName").innerText = ret.first_name;
		document.getElementById("lastName").innerText = ret.last_name;
		document.getElementById("dob").innerText = ret.date_of_birth;
		document.getElementById("age").innerText = ret.age;
		document.getElementById("telephone").innerText = ret.phone_number;
		document.getElementById("email").innerText = ret.email;
		document.getElementById("carer-name").innerText = ret.carer_name;
		document.getElementById("emg-cont").innerText = ret.emergency_name;
	});
});