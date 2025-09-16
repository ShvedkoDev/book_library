/* ==========================================
   MTDL - Main JavaScript
   Pure JS for Laravel/Livewire Integration
   ========================================== */

(function() {
  'use strict';

  // ==========================================
  // Core Utilities
  // ==========================================
  const Utils = {
    // DOM Ready
    ready: function(fn) {
      if (document.readyState != 'loading') {
        fn();
      } else {
        document.addEventListener('DOMContentLoaded', fn);
      }
    },

    // Query Selector Wrapper
    $: function(selector, context = document) {
      return context.querySelector(selector);
    },

    // Query Selector All Wrapper
    $$: function(selector, context = document) {
      return Array.from(context.querySelectorAll(selector));
    },

    // Add Event Listener
    on: function(element, event, handler) {
      if (element) {
        element.addEventListener(event, handler);
      }
    },

    // LocalStorage Wrapper
    storage: {
      get: function(key) {
        try {
          return JSON.parse(localStorage.getItem(key));
        } catch {
          return localStorage.getItem(key);
        }
      },
      set: function(key, value) {
        localStorage.setItem(key, typeof value === 'string' ? value : JSON.stringify(value));
      },
      remove: function(key) {
        localStorage.removeItem(key);
      }
    }
  };

  // ==========================================
  // Mobile Menu Handler
  // ==========================================
  const MobileMenu = {
    init: function() {
      const toggle = Utils.$('.menu-toggle');
      const menu = Utils.$('.nav-menu');

      Utils.on(toggle, 'click', function() {
        menu.classList.toggle('active');
        toggle.setAttribute('aria-expanded', menu.classList.contains('active'));
      });

      // Close menu when clicking outside
      Utils.on(document, 'click', function(e) {
        if (!e.target.closest('.nav-primary') && menu.classList.contains('active')) {
          menu.classList.remove('active');
          toggle.setAttribute('aria-expanded', 'false');
        }
      });
    }
  };

  // ==========================================
  // Module Toggle (Guide/Library)
  // ==========================================
  const ModuleToggle = {
    init: function() {
      const toggleOptions = Utils.$$('.toggle-option');

      toggleOptions.forEach(option => {
        Utils.on(option, 'click', function() {
          const target = this.dataset.module;

          if (target === 'library') {
            // Check if terms accepted
            if (!TermsModal.hasAcceptedTerms()) {
              TermsModal.show();
            } else {
              window.location.href = 'library.html';
            }
          } else if (target === 'guide') {
            window.location.href = 'index.html';
          }
        });
      });

      // Set active state based on current page
      this.setActiveState();
    },

    setActiveState: function() {
      const currentPage = window.location.pathname.split('/').pop() || 'index.html';
      const toggleOptions = Utils.$$('.toggle-option');

      toggleOptions.forEach(option => {
        option.classList.remove('active');
        if ((currentPage === 'index.html' && option.dataset.module === 'guide') ||
            (currentPage === 'library.html' && option.dataset.module === 'library')) {
          option.classList.add('active');
        }
      });
    }
  };

  // ==========================================
  // Terms Modal Handler
  // ==========================================
  const TermsModal = {
    STORAGE_KEY: 'mtdl_terms_accepted',

    init: function() {
      this.modal = Utils.$('#termsModal');
      if (!this.modal) return;

      this.closeBtn = Utils.$('.modal-close', this.modal);
      this.acceptBtn = Utils.$('.btn-accept', this.modal);
      this.declineBtn = Utils.$('.btn-decline', this.modal);

      // Event listeners
      Utils.on(this.closeBtn, 'click', () => this.hide());
      Utils.on(this.declineBtn, 'click', () => this.hide());
      Utils.on(this.acceptBtn, 'click', () => this.accept());

      // Close on backdrop click
      Utils.on(this.modal, 'click', (e) => {
        if (e.target === this.modal) {
          this.hide();
        }
      });

      // Close on ESC key
      Utils.on(document, 'keydown', (e) => {
        if (e.key === 'Escape' && this.modal.classList.contains('active')) {
          this.hide();
        }
      });
    },

    hasAcceptedTerms: function() {
      return Utils.storage.get(this.STORAGE_KEY) === 'true';
    },

    show: function() {
      if (this.modal) {
        this.modal.classList.add('active');
        document.body.style.overflow = 'hidden';
      }
    },

    hide: function() {
      if (this.modal) {
        this.modal.classList.remove('active');
        document.body.style.overflow = '';
      }
    },

    accept: function() {
      Utils.storage.set(this.STORAGE_KEY, 'true');
      this.hide();
      window.location.href = 'library.html';
    }
  };

  // ==========================================
  // Library Entry Points Handler
  // ==========================================
  const LibraryEntry = {
    init: function() {
      // Get all library entry triggers
      const triggers = [
        '.library-entry-btn',
        '.sidebar-library-entry',
        '.bottom-library-entry',
        '.library-entry-sidebar-btn'
      ];

      triggers.forEach(selector => {
        const elements = Utils.$$(selector);
        elements.forEach(element => {
          Utils.on(element, 'click', (e) => {
            e.preventDefault();
            this.handleEntry();
          });
        });
      });
    },

    handleEntry: function() {
      if (TermsModal.hasAcceptedTerms()) {
        window.location.href = 'library.html';
      } else {
        TermsModal.show();
      }
    }
  };

  // ==========================================
  // Smooth Scroll Handler
  // ==========================================
  const SmoothScroll = {
    init: function() {
      const links = Utils.$$('a[href^="#"]');

      links.forEach(link => {
        Utils.on(link, 'click', function(e) {
          const targetId = this.getAttribute('href');
          if (targetId === '#') return;

          const target = Utils.$(targetId);
          if (target) {
            e.preventDefault();
            target.scrollIntoView({
              behavior: 'smooth',
              block: 'start'
            });
          }
        });
      });
    }
  };

  // ==========================================
  // Search Functionality
  // ==========================================
  const Search = {
    init: function() {
      const searchForm = Utils.$('.search-form');
      const searchInput = Utils.$('.search-input');
      const searchToggle = Utils.$('.search-toggle');

      if (searchToggle) {
        Utils.on(searchToggle, 'click', function(e) {
          e.preventDefault();
          const searchContainer = Utils.$('.search-container');
          searchContainer.classList.toggle('active');
          if (searchContainer.classList.contains('active')) {
            searchInput.focus();
          }
        });
      }

      if (searchForm) {
        Utils.on(searchForm, 'submit', function(e) {
          e.preventDefault();
          const query = searchInput.value.trim();
          if (query) {
            // In Laravel/Livewire, this would trigger a Livewire method
            console.log('Search query:', query);
            // window.location.href = `/search?q=${encodeURIComponent(query)}`;
          }
        });
      }
    }
  };

  // ==========================================
  // Language Selector
  // ==========================================
  const LanguageSelector = {
    init: function() {
      const selector = Utils.$('.language-selector');
      if (!selector) return;

      Utils.on(selector, 'click', function(e) {
        e.preventDefault();
        const dropdown = this.nextElementSibling;
        if (dropdown) {
          dropdown.classList.toggle('active');
        }
      });

      // Handle language selection
      const options = Utils.$$('.language-options a');
      options.forEach(option => {
        Utils.on(option, 'click', function(e) {
          e.preventDefault();
          const lang = this.textContent.trim();
          console.log('Selected language:', lang);
          // In Laravel, this would trigger a language change
          // window.location.href = `/locale/${lang.toLowerCase()}`;
        });
      });
    }
  };

  // ==========================================
  // Sidebar Sticky Position
  // ==========================================
  const StickySidebar = {
    init: function() {
      const sidebar = Utils.$('.sidebar');
      if (!sidebar) return;

      const header = Utils.$('.site-header');
      if (header) {
        const headerHeight = header.offsetHeight;
        sidebar.style.top = `${headerHeight + 20}px`;
      }
    }
  };

  // ==========================================
  // Initialize All Modules
  // ==========================================
  const App = {
    init: function() {
      MobileMenu.init();
      ModuleToggle.init();
      TermsModal.init();
      LibraryEntry.init();
      SmoothScroll.init();
      Search.init();
      LanguageSelector.init();
      StickySidebar.init();

      // Add loaded class to body
      document.body.classList.add('loaded');

      console.log('MTDL App initialized');
    }
  };

  // Start the app when DOM is ready
  Utils.ready(() => App.init());

  // Export for use in Laravel/Livewire if needed
  window.MTDL = {
    Utils,
    TermsModal,
    LibraryEntry,
    App
  };

})();