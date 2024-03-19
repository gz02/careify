function loadMoods() {
	fetch("/api?all-mood")
	.then(ret => ret.text())
	.then(ret => { document.getElementById("usersMood").innerHTML = ret; });
}

document.addEventListener("DOMContentLoaded", function () {
	loadMoods();
});