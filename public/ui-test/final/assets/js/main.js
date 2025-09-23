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
      const menu = Utils.$('.menu-main-container');

      if (!toggle || !menu) return;

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

      this.closeBtn = Utils.$('.terms-close', this.modal);
      this.acceptBtn = Utils.$('.terms-accept-btn', this.modal);
      this.declineBtn = Utils.$('.terms-decline-btn', this.modal);

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
      const searchForm = Utils.$('.searchform');
      const searchInput = Utils.$('.search-field');
      const searchToggle = Utils.$('.search-toggle');

      if (searchToggle) {
        Utils.on(searchToggle, 'click', function(e) {
          e.preventDefault();
          const searchContainer = Utils.$('.search-container');
          if (searchContainer) {
            searchContainer.classList.toggle('active');
            if (searchContainer.classList.contains('active') && searchInput) {
              searchInput.focus();
            }
          }
        });
      }

      if (searchForm && searchInput) {
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
      this.initSelector('languageSelector', 'languageDropdown');
    },

    initSelector: function(selectorId, dropdownId) {
      const selector = Utils.$(`#${selectorId}`);
      const dropdown = Utils.$(`#${dropdownId}`);

      if (!selector || !dropdown) return;

      // Toggle dropdown
      Utils.on(selector, 'click', (e) => {
        e.preventDefault();
        e.stopPropagation();

        // Close other dropdowns
        Utils.$$('.language-dropdown').forEach(dd => {
          if (dd !== dropdown) {
            dd.classList.remove('show');
          }
        });
        Utils.$$('.language-selector').forEach(sel => {
          if (sel !== selector) {
            sel.classList.remove('active');
          }
        });

        // Toggle current dropdown
        dropdown.classList.toggle('show');
        selector.classList.toggle('active');
      });

      // Language selection
      Utils.on(dropdown, 'click', (e) => {
        e.preventDefault();
        e.stopPropagation();

        if (e.target.tagName === 'A' && !e.target.classList.contains('disabled')) {
          const languageCode = e.target.getAttribute('data-code');
          const languageName = e.target.textContent;

          // Update display
          const codeElement = Utils.$('.language-code', selector);
          if (codeElement) {
            codeElement.textContent = languageCode.toUpperCase();
          }

          // Close dropdown
          dropdown.classList.remove('show');
          selector.classList.remove('active');

          // Store selected language
          Utils.storage.set('selected_language', languageCode);

          console.log('Language selected:', languageCode, languageName);
        }
      });

      // Close dropdown when clicking outside
      Utils.on(document, 'click', (e) => {
        if (!selector.contains(e.target) && !dropdown.contains(e.target)) {
          dropdown.classList.remove('show');
          selector.classList.remove('active');
        }
      });

      // Load saved language preference
      const savedLanguage = Utils.storage.get('selected_language');
      if (savedLanguage) {
        const languageOption = Utils.$(`[data-code="${savedLanguage}"]`, dropdown);
        if (languageOption && !languageOption.classList.contains('disabled')) {
          const codeElement = Utils.$('.language-code', selector);
          if (codeElement) {
            codeElement.textContent = savedLanguage.toUpperCase();
          }
        }
      }
    }
  };

  // ==========================================
  // Sidebar Sticky Position
  // ==========================================
  const StickySidebar = {
    init: function() {
      // Only apply to sidebars that need sticky positioning, not header-image sidebars
      const sidebar = Utils.$('.sidebar:not(.header-image)');
      if (!sidebar) return;

      const header = Utils.$('.banner');
      if (header) {
        const headerHeight = header.offsetHeight;
        sidebar.style.top = `${headerHeight + 20}px`;
      }
    }
  };

  // ==========================================
  // Filter Functions (Global)
  // ==========================================

  // Toggle filter group collapse/expand
  window.toggleFilterGroup = function(toggleElement) {
    const checkboxGroup = toggleElement.nextElementSibling;
    const isCollapsed = checkboxGroup.classList.contains('collapsed');

    if (isCollapsed) {
      checkboxGroup.classList.remove('collapsed');
      checkboxGroup.classList.add('expanded');
      toggleElement.classList.add('expanded');
    } else {
      checkboxGroup.classList.remove('expanded');
      checkboxGroup.classList.add('collapsed');
      toggleElement.classList.remove('expanded');
    }
  };

  // Auto-filter when checkbox changes
  window.autoFilter = function() {
    // Get all checked checkboxes
    const checkedFilters = {};
    const checkboxes = document.querySelectorAll('.filter-section input[type="checkbox"]:checked');

    checkboxes.forEach(checkbox => {
      const filterGroup = checkbox.closest('.filter-group').querySelector('h4').textContent.trim();
      if (!checkedFilters[filterGroup]) {
        checkedFilters[filterGroup] = [];
      }
      checkedFilters[filterGroup].push(checkbox.value);
    });

    // Apply filters to the table
    filterTable(checkedFilters);

    console.log('Auto-filtering applied:', checkedFilters);
  };

  // Clear all filters
  window.clearFilters = function() {
    // Uncheck all checkboxes
    const checkboxes = document.querySelectorAll('.filter-section input[type="checkbox"]');
    checkboxes.forEach(checkbox => {
      checkbox.checked = false;
    });

    // Show all table rows
    const rows = document.querySelectorAll('#booksTableBody tr');
    rows.forEach(row => {
      row.style.display = '';
    });

    // Update results count
    updateResultsCount();

    console.log('All filters cleared');
  };

  // Filter table based on selected filters
  function filterTable(filters) {
    const rows = document.querySelectorAll('#booksTableBody tr');
    let visibleCount = 0;

    rows.forEach(row => {
      let shouldShow = true;

      // Check each filter group
      Object.keys(filters).forEach(filterGroup => {
        if (filters[filterGroup].length > 0) {
          // This is where you'd implement actual filtering logic
          // For now, we'll show all rows as the demo data doesn't have filter attributes
          // In a real implementation, you'd check row data against filter values
        }
      });

      if (shouldShow) {
        row.style.display = '';
        visibleCount++;
      } else {
        row.style.display = 'none';
      }
    });

    updateResultsCount(visibleCount);
  }

  // Update results count
  function updateResultsCount(count = null) {
    const resultsElement = document.getElementById('results-count');
    if (resultsElement) {
      if (count === null) {
        // Count visible rows
        const visibleRows = document.querySelectorAll('#booksTableBody tr:not([style*="display: none"])');
        count = visibleRows.length;
      }
      resultsElement.textContent = `Showing ${count} books and resources`;
    }
  }

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