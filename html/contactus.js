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