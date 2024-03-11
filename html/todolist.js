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

showStep(currentStep);
function markAsDone(button) {
  // Get the parent element of the button, which is the todo item
  var todoItem = button.closest('.todo-item');
  console.log("Click done.");

  // Add a class to apply the strike-through and transparency styles
  todoItem.classList.add('done');

  // Optionally, you can remove the todo item after marking it as done
  // todoItem.remove();
}

// Sets the Date to current date.
document.addEventListener("DOMContentLoaded", function () {
  var currentDate = new Date().toISOString().split("T")[0];
  document.getElementById("todo-date").value = currentDate;
});


// JavaScript function to handle the click event on the "ADD" button
document.addEventListener("DOMContentLoaded", function () {
  // Get the "ADD" button by class name
  var addButton = document.querySelector(".step#step2 .button.nextbutton");

  // Add a click event listener to the "ADD" button
  addButton.addEventListener("click", function () {
      // Get the values from the input fields
      var todoTime = document.getElementById("todo-time").value;
      var todoDate = document.getElementById("todo-date").value;
      var todoTitle = document.querySelector(".step#step2 .input-text").value;

      var formattedDate = new Date(todoDate).toLocaleDateString('en-US', {
        weekday: 'long',
        month: 'long',
        day: 'numeric'
      });

      save(todoTime, todoDate, todoTitle);

      // Create a new todo item HTML
      var newTodoItemHTML = `
          <div class="todo-item">
              <time class="todo-item-date">${formattedDate}</time>
              <h3 class="todo-item-title">${todoTitle}</h3>
              <time class="todo-item-time">${todoTime}</time>
              <div class="todo-item-actions">
                  <button class="btn-delete"></button>
                  <button class="btn-done">Done</button>
              </div>
          </div>
      `;

      // Append the new todo item to the todo list
      var todoList = document.querySelector(".step#step1 .todo-items-list");
      todoList.innerHTML += newTodoItemHTML;

      // Navigate back to step 1
      nextStep(1);

      function save(time, date, title) {
        
        var apiUrl = "https://your-api-endpoint.com/save-todo"; //NEED API INFO HERE
        var requestData = {
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
        .then(response => response.json())
        .then(data => {
            console.log('API response:', data);
        })
        .catch(error => {
            console.error('Error during API call:', error);
        });
    }

      document.addEventListener("click", function (event) {
        if (event.target.classList.contains("btn-done")) {
          markAsDone(event.target);
        }
      });

      var deleteButtons = document.querySelectorAll(".btn-delete");
        deleteButtons.forEach(function (deleteButton) {
            deleteButton.addEventListener("click", function (event) {
                // Get the parent element of the delete button, which is the todo item
                var todoItemToDelete = event.target.closest('.todo-item');

                // Remove the todo item
                todoItemToDelete.remove();
            });
        });

  });
});
