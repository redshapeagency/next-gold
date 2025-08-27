import './bootstrap';
import Alpine from 'alpinejs';

// Registra Alpine.js globalmente
window.Alpine = Alpine;

// Avvia Alpine.js
Alpine.start();

// Utilities globali
window.nextGold = {
  // Formatters
  formatCurrency: (amount, currency = 'EUR') => {
    return new Intl.NumberFormat('it-IT', {
      style: 'currency',
      currency: currency
    }).format(amount);
  },

  formatWeight: (grams) => {
    return new Intl.NumberFormat('it-IT', {
      minimumFractionDigits: 3,
      maximumFractionDigits: 3
    }).format(grams) + ' g';
  },

  formatDate: (date) => {
    return new Date(date).toLocaleDateString('it-IT');
  },

  formatDateTime: (date) => {
    return new Date(date).toLocaleString('it-IT');
  },

  // Notifications
  showToast: (message, type = 'info') => {
    // Semplice implementazione toast
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 z-50 p-4 rounded-md shadow-lg max-w-sm ${getToastClasses(type)}`;
    toast.innerHTML = `
      <div class="flex items-center">
        <div class="flex-shrink-0">
          ${getToastIcon(type)}
        </div>
        <div class="ml-3">
          <p class="text-sm font-medium">${message}</p>
        </div>
        <div class="ml-auto pl-3">
          <button onclick="this.parentElement.parentElement.parentElement.remove()" 
                  class="inline-flex rounded-md p-1.5 hover:bg-opacity-20 focus:outline-none">
            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
            </svg>
          </button>
        </div>
      </div>
    `;
    
    document.body.appendChild(toast);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
      if (toast.parentElement) {
        toast.remove();
      }
    }, 5000);
  },

  // Form helpers
  confirmDelete: (message = 'Sei sicuro di voler eliminare questo elemento?') => {
    return confirm(message);
  },

  // Loading states
  showLoading: (element) => {
    element.disabled = true;
    const originalText = element.innerHTML;
    element.innerHTML = `
      <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
      </svg>
      Caricamento...
    `;
    element.dataset.originalText = originalText;
  },

  hideLoading: (element) => {
    element.disabled = false;
    element.innerHTML = element.dataset.originalText || 'Invia';
  },

  // AJAX helpers
  makeRequest: async (url, options = {}) => {
    const defaultOptions = {
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
      }
    };

    const mergedOptions = {
      ...defaultOptions,
      ...options,
      headers: {
        ...defaultOptions.headers,
        ...options.headers
      }
    };

    try {
      const response = await fetch(url, mergedOptions);
      
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      
      return await response.json();
    } catch (error) {
      console.error('Request failed:', error);
      throw error;
    }
  }
};

// Helper functions for toast
function getToastClasses(type) {
  const classes = {
    success: 'bg-green-50 border border-green-200 text-green-800',
    error: 'bg-red-50 border border-red-200 text-red-800',
    warning: 'bg-yellow-50 border border-yellow-200 text-yellow-800',
    info: 'bg-blue-50 border border-blue-200 text-blue-800'
  };
  return classes[type] || classes.info;
}

function getToastIcon(type) {
  const icons = {
    success: '<svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>',
    error: '<svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>',
    warning: '<svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>',
    info: '<svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>'
  };
  return icons[type] || icons.info;
}

// DOM Ready
document.addEventListener('DOMContentLoaded', function() {
  // Auto-dismiss alerts after 5 seconds
  setTimeout(() => {
    document.querySelectorAll('[data-auto-dismiss]').forEach(el => {
      if (el && typeof el.style !== 'undefined') {
        el.style.display = 'none';
      }
    });
  }, 5000);

  // Confirm delete forms
  document.querySelectorAll('form[data-confirm-delete]').forEach(form => {
    form.addEventListener('submit', function(e) {
      const message = this.dataset.confirmDelete || 'Sei sicuro di voler eliminare questo elemento?';
      if (!nextGold.confirmDelete(message)) {
        e.preventDefault();
      }
    });
  });

  // Loading state for forms
  document.querySelectorAll('form[data-loading]').forEach(form => {
    form.addEventListener('submit', function(e) {
      const submitButton = this.querySelector('button[type="submit"]');
      if (submitButton) {
        nextGold.showLoading(submitButton);
      }
    });
  });

  // Auto-focus first input with autofocus
  const autofocusElement = document.querySelector('[autofocus]');
  if (autofocusElement) {
    autofocusElement.focus();
  }
});
