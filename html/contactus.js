let slideIndex = 0;

function showSlide(index) {
  const faqSlides = document.getElementsByClassName("faq-slide");
  if (index >= faqSlides.length) {
    slideIndex = 0;
  } else if (index < 0) {
    slideIndex = faqSlides.length - 1;
  }
  for (let i = 0; i < faqSlides.length; i++) {
    faqSlides[i].style.display = "none";
  }
  faqSlides[slideIndex].style.display = "block";
}

function changeSlide(n) {
  showSlide(slideIndex += n);
}

showSlide(slideIndex);

document.addEventListener("DOMContentLoaded", function() {
	document.getElementById("emailSend").addEventListener("click", function() {
		const fullname = document.getElementById('fullname').value;
		const phoneNumber = document.getElementById('phoneNumber').value;
		const email = document.getElementById('email').value;
		const emailMessage = document.getElementById('emailMessage').value;

		// fetch API placeholder below
		fetch('/api?contact', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
			},
			body: JSON.stringify({
				fullname,
				phoneNumber,
				email,
				emailMessage
			}),
		})
		.then(ret => ret.text())
		.then(ret => {
			if (ret.status == 200) { window.location.href = '/index?carer-interface'; }
			else { alert(ret); }
		})
		.catch(error => {
			console.error('Error:', error);
		});
	});
});