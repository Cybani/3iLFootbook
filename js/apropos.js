// ===========================================================
// 3iL FootBook — diaporama du bandeau de la page "À propos"
// ===========================================================

document.addEventListener('DOMContentLoaded', function () {
  const slides = document.querySelectorAll('.bandeau-slide');
  if (slides.length === 0) return;

  let indexActuel = 0;
  const DELAI = 5000; // 5 secondes entre chaque photo

  function afficherSlide(index) {
    slides.forEach((slide, i) => slide.classList.toggle('actif', i === index));
    indexActuel = index;
  }

  setInterval(function () {
    const prochainIndex = (indexActuel + 1) % slides.length;
    afficherSlide(prochainIndex);
  }, DELAI);
});