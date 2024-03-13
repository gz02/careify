let currentStep = 1;

function showStep(step) {
	document.querySelectorAll('.step').forEach(s => s.style.display = 'none');
	document.getElementById(`step${step}`).style.display = 'block';
}

function nextStep(next) {
	currentStep = next;
	showStep(next);
}

function prevStep(prev) {
	currentStep = prev;
	showStep(prev);
}

document.addEventListener("DOMContentLoaded", function () {
	showStep(currentStep);
});

function markAsDone(id) {
	// Get the parent element of the button, which is the todo item
	const apiUrl = "/api?completed-todo";
	const requestData = {
		id: id
	};

	fetch(apiUrl, {
		method: 'POST',
		headers: {
			'Content-Type': 'application/json',
		},
		body: JSON.stringify(requestData),
	})
	.catch(error => {
		console.error('Error during API call:', error);
	});

	// Add a class to apply the strike-through and transparency styles
	//todoItem.classList.add('done');

	// Optionally, you can remove the todo item after marking it as done
	// todoItem.remove();
}

// Sets the Date to current date.
document.addEventListener("DOMContentLoaded", function () {
	var currentDate = new Date().toISOString().split("T")[0];
	document.getElementById("todo-date").value = currentDate;
});


// load todo list elements

function loadTodoList() {
	fetch("/api?all-todo")
	.then(ret => ret.text())
	.then(ret => { document.querySelector(".step#step1 .todo-items-list").innerHTML = ret; })
}

document.addEventListener("DOMContentLoaded", function () {
	loadTodoList();
	
	document.addEventListener("click", function (event) {
		const nodes = event.target.closest('.todo-item').childNodes;
			
		for (let i = 0; i < nodes.length; i++) {
			if (nodes[i].id == "todo-item-id") {
				const id = nodes[i].innerText;
				if (event.target.classList.contains("btn-done")) {
					markAsDone(id);
					loadTodoList();
				}
				else if (event.target.classList.contains("btn-delete")) {
					deleteTodoItem(id); // Remove the todo item
					loadTodoList();
				}
			}
		}
	});
});

function saveTodoItem(time, date, title) {
	const apiUrl = "/api?save-todo";
	const requestData = {
		time: time,
		date: date,
		title: title
	};

	fetch(apiUrl, {
		method: 'POST',
		headers: {
			'Content-Type': 'application/json',
		},
		body: JSON.stringify(requestData),
	})
	.catch(error => {
		console.error('Error during API call:', error);
	});
}

function deleteTodoItem(id) {
	const apiUrl = "/api?delete-todo";
	const requestData = {
		id: id
	};

	fetch(apiUrl, {
		method: 'POST',
		headers: {
			'Content-Type': 'application/json',
		},
		body: JSON.stringify(requestData),
	})
	.catch(error => {
		console.error('Error during API call:', error);
	});
}

// JavaScript function to handle the click event on the "ADD" button
document.addEventListener("DOMContentLoaded", function () {
	// Get the "ADD" button by class name
	var addButton = document.querySelector(".step#step2 .button.nextbutton");

	// Add a click event listener to the "ADD" button
	addButton.addEventListener("click", function () {
		// Get the values from the input fields
		const todoTime = document.getElementById("todo-time").value;
		const todoDate = document.getElementById("todo-date").value;
		const todoTitle = document.querySelector(".step#step2 .input-text").value;

		const formattedDate = new Date(todoDate).toLocaleDateString('en-US', {
			weekday: 'long',
			month: 'long',
			day: 'numeric'
		});

		saveTodoItem(todoTime, todoDate, todoTitle);
		loadTodoList();

		// Navigate back to step 1
		nextStep(1);
	});
});
