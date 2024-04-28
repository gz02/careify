var modal = document.getElementById("addTaskModal");

var addTaskBtn = document.querySelector(".addTask");


var closeBtn = document.querySelector(".close");


addTaskBtn.onclick = function() {
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


document.getElementById("addTaskForm").addEventListener("submit", function(event) {
    event.preventDefault(); 

    var medicationType = document.getElementById("medicationType").value;
    var frequency = document.getElementById("frequency").value;
    var dosage = document.getElementById("dosage").value;
    var time = document.getElementById("time").value;


    addMedicationItem(medicationType, frequency, dosage, time, false);

    modal.style.display = "none";
});

function deleteMedication(iconElement) {
    var medicationItem = iconElement.closest('.medication-item');
    medicationItem.remove();
}


function getTodayDate() {
        var today = new Date();
        var dd = String(today.getDate()).padStart(2, '0');
        var mm = String(today.getMonth() + 1).padStart(2, '0'); 
        var yyyy = today.getFullYear();
        return yyyy + '-' + mm + '-' + dd;
    }

function addMedicationItem(name, frequency, dosage, time, taken) {
    var medicationList = document.querySelector(".medication-list");

    var medicationItem = document.createElement("div");
    medicationItem.classList.add("medication-item");
    medicationItem.innerHTML = `
        <div class="medication-icons">
            <span class="delete-icon" onclick="deleteMedication(this)">✖</span>
        </div>
        <div class="medication-box">
            <span class="medication-label">Name:</span>
            <span class="medication-info medication-name">${name}</span>
        </div>
        <div class="medication-box">
            <span class="medication-label">Date:</span>
            <span class="medication-info medication-date">${getTodayDate()}</span>
        </div>
        <div class="medication-box">
            <span class="medication-label">Time:</span>
            <span class="medication-info medication-time">${time}</span>
        </div>
        <div class="medication-box">
            <span class="medication-label">Frequency:</span>
            <span class="medication-info medication-frequency">${frequency}</span>
        </div>
        <div class="medication-box">
            <span class="medication-label">Dosage:</span>
            <span class="medication-info medication-dosage">${dosage}</span>
        </div>
        <div class="medication-box">
            <span class="medication-label">Taken:</span>
            <div class="taken-select-container">
                <select id="medicine-taken" class="medication-info medication-taken">
                    <option value="Yes">Yes</option>
                    <option value="No">No</option>
                </select>
            </div>
        </div>
    `;
    medicationList.appendChild(medicationItem);
    
    
    
}