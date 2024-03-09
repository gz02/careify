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
  const textSize = document.getElementById('textSize').value;
  const color = document.getElementById('colorOptions').value;
  const firstname = document.getElementById('firstname').value;
  const lastname = document.getElementById('lastname').value;
  const phone = document.getElementById('phone').value;
  const emfirstname = document.getElementById('emfirstname').value;
  const emlastname = document.getElementById('emlastname').value;
  const emphone = document.getElementById('emphone').value;
  const email = document.getElementById('email').value;
  const password = document.getElementById('password').value;
  const pollen=document.getElementById('pollen').value;
  const latex=document.getElementById('latex').value;
  const penicillin=document.getElementById('penicillin').value;
  const dust=document.getElementById('dust').value;
  const plasters=document.getElementById('plasters').value;
  const hypertension = document.getElementById('hypertension').checked ;
  const arthritis = document.getElementById('arthritis').checked ;
  const diabetes = document.getElementById('heartdisease').checked ;
  const dementia = document.getElementById('dementia').checked ;
  const osteoporosis = document.getElementById('osteoporosis').checked ;

  const carename = document.getElementById('carename').value;

  // fetch API placeholder below
  fetch('/api?register', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      textSize,
      color,  
      firstname,
      lastname,
      phone,
      emfirstname,
      emlastname,
      emphone,
      email,
      password,
      pollen,
      latex,
      penicillin,
      dust,
      plasters,
      hypertension,
      arthritis,
      diabetes,
      dementia,
      osteoporosis,
      
      carename,
    }),
  })
  .then(response => response.json())
  .then(data => {
    console.log(data);
  })
  .catch(error => {
    console.error('Error:', error);
  });
}

showStep(currentStep);

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
  // Check if there's a user first name stored
  const storedFirstName = sessionStorage.getItem("userFirstName");

  if (storedFirstName) {
      // If the user first name is stored, display the greeting
      displayGreeting(storedFirstName);
  }
});

function displayGreeting(firstName) {
  const greetingElement = document.getElementById("greeting");

  // Update the content of the greeting element
  greetingElement.innerHTML = `<h2>Hello, ${firstName}!!</h2>`;
}
