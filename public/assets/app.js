document.addEventListener('DOMContentLoaded', () => {
  const openDrawer = (id) => {
    const drawer = document.getElementById(id);
    if (!drawer) return;
    drawer.hidden = false;
    document.body.classList.add('drawer-open');
  };

  const closeDrawer = (id) => {
    const drawer = document.getElementById(id);
    if (!drawer) return;
    drawer.hidden = true;
    document.body.classList.remove('drawer-open');
  };

  document.querySelectorAll('[data-drawer-open]').forEach((button) => {
    button.addEventListener('click', () => openDrawer(button.dataset.drawerOpen));
  });

  document.querySelectorAll('[data-drawer-close]').forEach((button) => {
    button.addEventListener('click', () => closeDrawer(button.dataset.drawerClose));
  });

  document.querySelectorAll('[data-scroll-target]').forEach((button) => {
    button.addEventListener('click', () => {
      const target = document.getElementById(button.dataset.scrollTarget);
      if (target) {
        target.scrollIntoView({ behavior: 'smooth', block: 'center' });
        target.classList.add('pulse-focus');
        window.setTimeout(() => target.classList.remove('pulse-focus'), 1600);
      }
    });
  });

  const shell = document.querySelector('[data-calendar-shell]');
  if (shell) {
    const buttons = shell.querySelectorAll('[data-view-toggle]');
    const views = shell.querySelectorAll('[data-calendar-view]');

    buttons.forEach((button) => {
      button.addEventListener('click', () => {
        buttons.forEach((item) => {
          item.classList.toggle('is-selected', item === button);
          item.classList.toggle('button-secondary', item === button);
          item.classList.toggle('button-ghost', item !== button);
        });

        views.forEach((view) => {
          view.hidden = view.dataset.calendarView !== button.dataset.viewToggle;
        });
      });
    });
  }
});
