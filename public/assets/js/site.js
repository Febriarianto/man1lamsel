const backToTop = document.getElementById('backToTop');
window.addEventListener('scroll', () => backToTop?.classList.toggle('show', window.scrollY > 500));
backToTop?.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
