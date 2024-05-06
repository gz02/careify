/*function loadMoods() {
	fetch("/api?all-mood")
	.then(ret => ret.text())
	.then(ret => { document.getElementById("usersMood").innerHTML = ret; });
}

document.addEventListener("DOMContentLoaded", function () {
	loadMoods();
}); */

var modal = document.getElementById("addTaskModal");
var addTaskBtn = document.querySelector(".button.morebutton");
var closeBtn = document.querySelector(".closebutton");

addTaskBtn.onclick = function() {
	console.log("hello");
    modal.style.display = "block";
}

closeBtn.onclick = function() {
    modal.style.display = "none";
}

window.onclick = function(event) {
    if (event.target == modal) {
        modal.style.display = "none";
    }
}