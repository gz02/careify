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

function submitForm() {
  // data collected for each element.
  const textSize = gFontSize;
  const themeSet = gThemeSet;
  const color = document.getElementById('colorOptions').value;
  const firstname = document.getElementById('firstname').value;
  const lastname = document.getElementById('lastname').value;
  const DateOfBirth = document.getElementById('DOB').value;
  const phone = document.getElementById('phone').value;
  const emfirstname = document.getElementById('emfirstname').value;
  const emlastname = document.getElementById('emlastname').value;
  const emphone = document.getElementById('emphone').value;
  const email = document.getElementById('email').value;
  const pin = document.getElementById('pin').value;
  const carename = document.getElementById('carename').value;
  
  const allergies = {
	"Pollen": document.getElementById('Pollen').checked,
	"Latex": document.getElementById('Latex').checked,
	"Penicillin": document.getElementById('Penicillin').checked,
	"Dust": document.getElementById('Dust').checked,
	"Plasters": document.getElementById('Plasters').checked
  };
  
  const medical_conditions = {
	"Hypertension": document.getElementById('Hypertension').checked,
	"Cardiovascular": document.getElementById('Cardiovascular').checked,
	"Diabetes": document.getElementById('Diabetes').checked,
	"Alzheimer's Disease": document.getElementById("Alzheimer's Disease").checked
  };
  
  const medication = {
	"Paracetamol": document.getElementById('Paracetamol').checked,
	"Ibuprofen": document.getElementById('Ibuprofen').checked,
	"Naproxen": document.getElementById('Naproxen').checked,
	"Statins": document.getElementById('Statins').checked,
	"ACE-Inhibitors": document.getElementById('ACE Inhibitors').checked,
	"Antiplatelet": document.getElementById('Antiplatelet').checked,
	"Metformin": document.getElementById('Metformin').checked,
	"Sulfonylureas": document.getElementById('Sulfonylureas').checked,
	"Insulin": document.getElementById('Insulin').checked,
	"DPP-4": document.getElementById('DPP-4').checked,
	"Z-drugs": document.getElementById('Z-drugs').checked,
	"Benzodiazepines": document.getElementById('Benzodiazepines').checked
  };

  // fetch API placeholder below
  fetch('/api?register', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      textSize,
	  themeSet,
      color,  
      firstname,
      lastname,
	  DateOfBirth,
      phone,
      emfirstname,
      emlastname,
      emphone,
      email,
      pin,
	  carename,
	  
      allergies,
      medical_conditions,
      medication
    }),
  })
  .then(ret => { 
	if (ret.status == 201) { window.location.href = '/index?user-interface'; }
	else { alert("Something went wrong, try again."); }
  })
  .catch(error => {
    console.error('Error:', error);
  });
}

$("#textSize").on("input", function () {
    const textSizeValue = $(this).val();
    console.log("Text Size:", textSizeValue);
    $('#scroller').css("font-size", textSizeValue + "px");
});

$(".accordion-trigger").click(function(){
  $(this).next(".accordion-panel").slideToggle(300).siblings(".accordion-panel:visible").slideUp(300);
});

$(".label-wrap label").click(function(){
  $(this).next('input[type="checkbox"]').trigger('click');
});

document.addEventListener("DOMContentLoaded", function () {
	showStep(currentStep);
  // Check if there's a user first name stored
  const storedFirstName = sessionStorage.getItem("userFirstName");

  if (storedFirstName) {
      // If the user first name is stored, display the greeting
      displayGreeting(storedFirstName);
  }
});

function displayGreeting(firstName) {
  //const greetingElement = document.getElementById("greeting");

  // Update the content of the greeting element
  //greetingElement.innerHTML = `<h2>Hello, ${firstName}!!</h2>`;
}
