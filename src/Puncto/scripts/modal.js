document.addEventListener('DOMContentLoaded', function() {
  const container = document.getElementsByClassName('modal-container')[0];
  const inner = container.getElementsByClassName('modal-inner')[0];
  const triggers = document.querySelectorAll('[data-trigger]');
  const targets = [];

  function closeAllModals() {
    for (let i = 0; i < targets.length; i++) {
      targets[i].classList.remove('visible');
    }

    setTimeout(function() {
      container.classList.remove('visible');
    }, 150);
  }

  for (let i = 0; i < triggers.length; i++) {
    const trigger = triggers[i];
    const targetSelector = trigger.getAttribute('data-target');
    const target = document.querySelector(targetSelector);
    const closeButtons = target.querySelectorAll('[data-close]');

    targets.push(target);

    trigger.addEventListener('click', function() {
      container.classList.add('visible');
      setTimeout(function() {
        target.classList.add('visible');
      }, 150);
    });

    for (let j = 0; j < closeButtons.length; j++) {
      closeButtons[j].addEventListener('click', closeAllModals);
    }
  }

  inner.addEventListener('click', function(event) {
    if (event.target !== inner) return;

    closeAllModals();
  });

  document.addEventListener('keyup', function(event) {
    const code = event.keyCode || event.which;

    if (code === 27) {
      closeAllModals();
    }
  });
});
