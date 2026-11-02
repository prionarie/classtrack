const toggle = document.getElementById('accountToggle');
const dropdown = document.getElementById('accountDropdown');

toggle.addEventListener('click', function(e) {
  e.stopPropagation();
  dropdown.classList.toggle('show');
});

window.addEventListener('click', function() {
  dropdown.classList.remove('show');
});

dropdown.addEventListener('click', function(e) {
  e.stopPropagation();
});