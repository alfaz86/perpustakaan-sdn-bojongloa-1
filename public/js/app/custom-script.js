// Jalankan setelah DOM siap
document.addEventListener('DOMContentLoaded', () => {
  // Untuk icon di sidebar (semua svg yang match)
  var iconsSidebars = document.querySelectorAll(
    'button.fi-icon-btn svg.fi-icon-btn-icon.h-6.w-6'
  );

  // Untuk icon filter table (semua svg yang match)
  var iconFilters = document.querySelectorAll(
    '.fi-icon-btn-badge-ctn'
  );

  iconsSidebars.forEach(function (icon) {
    icon.style.stroke = 'white';
  });

  iconFilters.forEach(function (icon) {
    icon.style.zIndex = 0;
  });
});

(function () {
  const updateZIndex = () => {
    document.querySelectorAll('.fi-btn-badge-ctn').forEach((el) => {
      el.style.zIndex = '0';
    });
  };

  // Pantau perubahan DOM
  const observeBadgeContainer = () => {
    const observer = new MutationObserver(() => {
      updateZIndex();
    });

    observer.observe(document.body, {
      childList: true,
      subtree: true,
    });
  };

  // Intercept XMLHttpRequest
  const originalOpen = XMLHttpRequest.prototype.open;
  XMLHttpRequest.prototype.open = function (method, url) {
    if (url.includes('/livewire/update')) {
      this.addEventListener('load', function () {
        window.dispatchEvent(new CustomEvent('livewire-updated'));
      });
    }
    return originalOpen.apply(this, arguments);
  };

  // Intercept fetch
  const originalFetch = window.fetch;
  window.fetch = async function (...args) {
    const response = await originalFetch.apply(this, args);
    if (args[0].includes('/livewire/update')) {
      window.dispatchEvent(new CustomEvent('livewire-updated'));
    }
    return response;
  };

  // Mulai saat halaman dimuat
  document.addEventListener('DOMContentLoaded', () => {
    updateZIndex();       // jalankan pertama kali
    observeBadgeContainer(); // mulai observasi DOM
  });
})();
