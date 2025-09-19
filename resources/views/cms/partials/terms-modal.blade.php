<!-- Terms of Use Modal -->
<div id="terms-modal" class="terms-modal fixed inset-0 z-50 hidden bg-black bg-opacity-50 flex items-center justify-center p-4" role="dialog" aria-labelledby="terms-modal-title" aria-hidden="true">
    <div class="terms-modal-content bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-hidden">
        <div class="terms-modal-header bg-blue-600 text-white p-6 flex justify-between items-center">
            <h2 id="terms-modal-title" class="text-2xl font-bold">Terms of Use - Micronesian Teachers Digital Library</h2>
            <button type="button" class="terms-modal-close text-white hover:text-gray-200 text-2xl" aria-label="Close modal">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="terms-modal-body p-6 overflow-y-auto max-h-[calc(90vh-200px)]">
            <div class="prose prose-lg max-w-none">
                <h3>Welcome to the Micronesian Teachers Digital Library</h3>
                <p>By accessing and using this digital library, you agree to the following terms and conditions:</p>

                <h4>1. Purpose and Scope</h4>
                <p>The Micronesian Teachers Digital Library (MTDL) is designed to support educators across Micronesia by providing access to over 2,000 educational books and resources in local languages. This collection serves educational institutions, teachers, students, and families throughout the Pacific Islands region.</p>

                <h4>2. Acceptable Use</h4>
                <ul>
                    <li><strong>Educational Purpose:</strong> Resources are intended for educational, teaching, and learning purposes only</li>
                    <li><strong>Non-Commercial Use:</strong> Materials may not be used for commercial purposes without explicit permission</li>
                    <li><strong>Attribution:</strong> Proper attribution must be given to authors and publishers when using materials</li>
                    <li><strong>Respect for Copyright:</strong> Users must respect all copyright notices and licensing terms</li>
                </ul>

                <h4>3. Access Levels</h4>
                <p>The library provides different levels of access to materials:</p>
                <ul>
                    <li><strong>Full Access:</strong> Complete viewing and download capabilities</li>
                    <li><strong>Limited Access:</strong> Preview only with restricted download</li>
                    <li><strong>Restricted:</strong> Access may require registration or institutional affiliation</li>
                </ul>

                <h4>4. User Responsibilities</h4>
                <ul>
                    <li>Maintain the confidentiality of your account credentials</li>
                    <li>Use resources responsibly and in accordance with their intended purpose</li>
                    <li>Report any technical issues or content concerns to library administrators</li>
                    <li>Respect the intellectual property rights of all content creators</li>
                </ul>

                <h4>5. Content Guidelines</h4>
                <p>All materials in the library are:</p>
                <ul>
                    <li>Reviewed for educational appropriateness</li>
                    <li>Culturally sensitive and relevant to Micronesian communities</li>
                    <li>Aligned with educational standards and best practices</li>
                    <li>Regularly updated and maintained for accuracy</li>
                </ul>

                <h4>6. Privacy and Data Protection</h4>
                <p>We are committed to protecting your privacy:</p>
                <ul>
                    <li>Personal information is collected only for library services</li>
                    <li>Usage data helps improve our services and resources</li>
                    <li>Information is not shared with third parties without consent</li>
                    <li>Users may request access to or deletion of their personal data</li>
                </ul>

                <h4>7. Technical Requirements</h4>
                <p>To ensure optimal access:</p>
                <ul>
                    <li>Use a modern web browser with JavaScript enabled</li>
                    <li>Maintain a stable internet connection for downloads</li>
                    <li>Install appropriate software for viewing different file formats</li>
                    <li>Keep your device and browser updated for security</li>
                </ul>

                <h4>8. Limitation of Liability</h4>
                <p>While we strive to provide accurate and high-quality resources, the MTDL is provided "as is" without warranties. Users assume responsibility for their use of the materials and services.</p>

                <h4>9. Modifications and Updates</h4>
                <p>These terms may be updated periodically to reflect changes in our services or legal requirements. Users will be notified of significant changes, and continued use constitutes acceptance of updated terms.</p>

                <h4>10. Contact Information</h4>
                <p>For questions about these terms or the library services:</p>
                <ul>
                    <li><strong>Email:</strong> <a href="mailto:info@mtdl.edu">info@mtdl.edu</a></li>
                    <li><strong>Phone:</strong> <a href="tel:+1-000-000-0000">(000) 000-0000</a></li>
                    <li><strong>Address:</strong> Educational Resource Center, Pacific Islands Region, Micronesia</li>
                </ul>

                <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mt-6">
                    <p class="text-blue-700 font-medium">
                        <i class="fas fa-info-circle mr-2"></i>
                        By clicking "I Agree" below, you acknowledge that you have read, understood, and agree to be bound by these Terms of Use.
                    </p>
                </div>
            </div>
        </div>

        <div class="terms-modal-footer bg-gray-50 p-6 flex justify-end space-x-4">
            <button type="button" class="terms-modal-decline bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded-lg font-medium transition-colors">
                Decline
            </button>
            <button type="button" class="terms-modal-accept bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                I Agree
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('terms-modal');
    const acceptBtn = modal.querySelector('.terms-modal-accept');
    const declineBtn = modal.querySelector('.terms-modal-decline');
    const closeBtn = modal.querySelector('.terms-modal-close');

    // Check if user has already accepted terms
    const termsAccepted = localStorage.getItem('mtdl_terms_accepted');
    const termsVersion = '1.0'; // Update this when terms change
    const acceptedVersion = localStorage.getItem('mtdl_terms_version');

    // Show modal if terms not accepted or version has changed
    if (!termsAccepted || acceptedVersion !== termsVersion) {
        showModal();
    }

    function showModal() {
        modal.classList.remove('hidden');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';

        // Focus on the modal for accessibility
        modal.focus();
    }

    function hideModal() {
        modal.classList.add('hidden');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }

    // Accept terms
    acceptBtn.addEventListener('click', function() {
        localStorage.setItem('mtdl_terms_accepted', 'true');
        localStorage.setItem('mtdl_terms_version', termsVersion);
        localStorage.setItem('mtdl_terms_accepted_date', new Date().toISOString());
        hideModal();

        // Optional: Track acceptance analytics
        if (typeof gtag !== 'undefined') {
            gtag('event', 'terms_accepted', {
                event_category: 'Legal',
                event_label: 'Terms of Use v' + termsVersion
            });
        }
    });

    // Decline terms
    declineBtn.addEventListener('click', function() {
        // Redirect to external site or show alternative message
        if (confirm('You must accept the Terms of Use to access the digital library. Would you like to leave this site?')) {
            window.location.href = 'https://www.google.com';
        }
    });

    // Close modal (same as decline)
    closeBtn.addEventListener('click', function() {
        declineBtn.click();
    });

    // Close modal on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
            declineBtn.click();
        }
    });

    // Prevent closing modal by clicking outside
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            // Don't close - user must actively accept or decline
            acceptBtn.focus();
        }
    });

    // Add function to show terms modal manually (for footer link, etc.)
    window.showTermsModal = showModal;
});
</script>

<style>
/* Additional modal styles */
.terms-modal {
    backdrop-filter: blur(2px);
}

.terms-modal-content {
    animation: modalSlideIn 0.3s ease-out;
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: translateY(-20px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.terms-modal-body {
    line-height: 1.6;
}

.terms-modal-body h3 {
    @apply text-xl font-bold text-gray-900 mt-6 mb-3 first:mt-0;
}

.terms-modal-body h4 {
    @apply text-lg font-semibold text-gray-800 mt-5 mb-2;
}

.terms-modal-body ul {
    @apply list-disc list-inside space-y-1 ml-4;
}

.terms-modal-body a {
    @apply text-blue-600 hover:text-blue-800 underline;
}

/* Focus management for accessibility */
.terms-modal:focus {
    outline: none;
}

.terms-modal-content:focus-within {
    outline: 2px solid #3b82f6;
    outline-offset: 2px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .terms-modal-content {
        @apply m-2 max-h-[95vh];
    }

    .terms-modal-header {
        @apply p-4;
    }

    .terms-modal-body {
        @apply p-4 max-h-[calc(95vh-150px)];
    }

    .terms-modal-footer {
        @apply p-4 flex-col space-x-0 space-y-2;
    }

    .terms-modal-footer button {
        @apply w-full;
    }
}
</style>