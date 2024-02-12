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
  const color = document.getElementById('color').value;
  const firstname = document.getElementById('firstname').value;
  const lastname = document.getElementById('lastname').value;
  const phone = document.getElementById('phone').value;
  const emfirstname = document.getElementById('emfirstname').value;
  const emlastname = document.getElementById('emlastname').value;
  const emphone = document.getElementById('emphone').value;
  const email = document.getElementById('email').value;
  const password = document.getElementById('password').value;
  const hypertension = document.getElementById('hypertension').value;
  const arthritis = document.getElementById('arthritis').value;
  const heartdisease = document.getElementById('heartdisease').value;
  const dementia = document.getElementById('dementia').value;
  const osteoporosis = document.getElementById('osteoporosis').value;

  const carename = document.getElementById('carename').value;

  // fetch API placeholder below
  fetch('/api/register', {
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
      hypertension,
      arthritis,
      heartdisease,
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