
MikeYatou2468, Connecté

















































Accueil · JS
// ===========================================================
// 3iL FootBook — diaporama de la page d'accueil
// Fait défiler automatiquement les images d'arrière-plan du hero
// ===========================================================
 
document.addEventListener('DOMContentLoaded', function () {
  const slides = document.querySelectorAll('.hero-slide');
  const points = document.querySelectorAll('.hero-points button');
 
  if (slides.length === 0) return;
 
  let indexActuel = 0;
  const DELAI = 5000; // 5 secondes entre chaque photo
  let minuteur = null;
 
  function afficherSlide(index) {
    slides.forEach((slide, i) => slide.classList.toggle('actif', i === index));
    points.forEach((point, i) => point.classList.toggle('actif', i === index));
    indexActuel = index;
  }
 
  function slideSuivante() {
    const prochainIndex = (indexActuel + 1) % slides.length;
    afficherSlide(prochainIndex);
  }
 
  function demarrerDefilement() {
    minuteur = setInterval(slideSuivante, DELAI);
  }
 
  function redemarrerDefilement() {
    clearInterval(minuteur);
    demarrerDefilement();
  }
 
  // clic manuel sur les points en bas du hero
  points.forEach((point, i) => {
    point.addEventListener('click', () => {
      afficherSlide(i);
      redemarrerDefilement();
    });
  });
 
  afficherSlide(0);
  demarrerDefilement();
});
 
