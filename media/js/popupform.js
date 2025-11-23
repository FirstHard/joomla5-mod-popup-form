(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        const modules = document.querySelectorAll('.mod-popup-form');

        if (!modules.length) {
            console.log('Popup module(s) not initialized');
            return;
        }

        modules.forEach(function (mod) {
            initPopupModule(mod);
        });

        document.addEventListener('submit', function (e) {
            const form = e.target.closest('.mpf-form');
            if (!form) {
                return;
            }

            const mod = form.closest('.mod-popup-form');
            if (!mod) {
                return;
            }

            e.preventDefault();

            handleFormSubmit(mod, form);
        });
    });

    function initPopupModule(mod) {
        const displayMode = mod.dataset.displayMode || 'popup';
        const anchorHash = mod.dataset.anchorHash || 'callback';
        const overlay = mod.querySelector('.mpf-overlay');
        const popup = mod.querySelector('.mpf-popup');
        const closeButtons = mod.querySelectorAll('[data-mpf-close]');

        let lastClickX = window.innerWidth / 2;
        let lastClickY = window.innerHeight / 2;

        if (displayMode === 'popup') {
            document.addEventListener('click', function (e) {
                const link = e.target.closest('a[href]');
                if (!link) {
                    return;
                }

                const href = link.getAttribute('href') || '';
                const urlHash = href.split('#')[1];

                if (urlHash && urlHash === anchorHash) {
                    e.preventDefault();

                    lastClickX = e.clientX;
                    lastClickY = e.clientY;

                    openPopup(mod, popup, overlay, lastClickX, lastClickY);
                }
            });
        }

        closeButtons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                closePopup(mod, popup, overlay);
            });
        });
    }

    function handleFormSubmit(mod, form) {
        const ajaxUrl = mod.dataset.ajaxUrl || '';
        const submitLabel = mod.dataset.submitLabel || 'Send';
        const submittingLabel = mod.dataset.submittingLabel || 'Sending...';
        const moduleId = mod.dataset.moduleId;

        const alertBox = mod.querySelector('.mpf-alert');
        const successBox = mod.querySelector('.mpf-success');
        const submitBtn = form.querySelector('.mpf-submit-btn');

        if (alertBox) {
            alertBox.classList.add('d-none');
            alertBox.textContent = '';
        }

        const inputs = form.querySelectorAll('input, textarea');
        let hasError = false;

        inputs.forEach(function (input) {
            const value = (input.value || '').trim();
            const isRequired = input.hasAttribute('required');
            const dataType = input.dataset.type || input.type || 'text';
            const emailValidate = input.dataset.emailValidate === '1';

            let fieldHasError = false;

            input.classList.remove('is-invalid');

            if (isRequired && !value) {
                fieldHasError = true;
            }

            if (!fieldHasError && dataType === 'email' && emailValidate) {
                const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (value && !emailPattern.test(value)) {
                    fieldHasError = true;
                }
            }

            if (fieldHasError) {
                input.classList.add('is-invalid');
                hasError = true;
            }
        });

        if (hasError) {
            return;
        }

        if (!ajaxUrl) {
            if (alertBox) {
                alertBox.textContent = 'Configuration error: AJAX URL is not defined for this form.';
                alertBox.classList.remove('d-none');
            }
            console.error('mod_popup_form: ajaxUrl is not defined for module', moduleId);
            return;
        }

        const formData = new FormData(form);
        formData.append('module_id', moduleId);

        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = submittingLabel;
        }

        fetch(ajaxUrl, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(function (response) {
                return response.json();
            })
            .then(function (response) {
                if (alertBox) {
                    alertBox.classList.add('d-none');
                    alertBox.textContent = '';
                }

                if (!response.success) {
                    const message = response.message || 'An error occurred while sending the form.';

                    if (alertBox) {
                        alertBox.textContent = message;
                        alertBox.classList.remove('d-none');
                    }

                    return;
                }

                const data = response.data || {};

                if (data.errors && form) {
                    const errors = data.errors;

                    Object.keys(errors).forEach(function (field) {
                        if (field === 'captcha') {
                            const captchaField = form.querySelector('.mpf-field--captcha');
                            if (captchaField) {
                                captchaField.classList.add('is-invalid');
                                const feedback = captchaField.querySelector('.invalid-feedback');
                                if (feedback) {
                                    feedback.textContent = errors[field];
                                }
                            }
                            return;
                        }

                        const input = form.querySelector('[name="' + field + '"]');
                        if (input) {
                            input.classList.add('is-invalid');
                            const feedback = input.parentElement.querySelector('.invalid-feedback');
                            if (feedback) {
                                feedback.textContent = errors[field];
                            }
                        }
                    });

                    if (alertBox) {
                        alertBox.textContent = (window.Joomla && Joomla.Text)
                            ? Joomla.Text._('MOD_POPUP_FORM_ERROR_VALIDATION')
                            : 'Please check that the form is filled out correctly.';
                        alertBox.classList.remove('d-none');
                    }

                    return;
                }

                if (data.success) {
                    if (form) {
                        form.classList.add('d-none');
                    }
                    if (successBox) {
                        successBox.classList.remove('d-none');
                    }
                }
            })
            .catch(function (error) {
                if (alertBox) {
                    alertBox.textContent = 'Connection error. Please try again later.';
                    alertBox.classList.remove('d-none');
                }
                console.error('mod_popup_form AJAX error:', error);
            })
            .finally(function () {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = submitLabel;
                }
            });
    }

    function openPopup(mod, popup, overlay, clickX, clickY) {
        if (!popup || !overlay) {
            return;
        }

        const popupRect = popup.getBoundingClientRect();

        const startLeft = clickX - popupRect.width / 2;
        const startTop = clickY - popupRect.height / 2;

        mod.classList.add('mpf-visible');

        popup.style.transition = 'none';
        popup.style.opacity = '0';
        popup.style.left = startLeft + 'px';
        popup.style.top = startTop + 'px';
        popup.style.transform = 'translate(0, 0)';

        overlay.classList.add('mpf-overlay-visible');

        void popup.offsetWidth;

        popup.style.transition = 'all 0.25s ease-out';
        popup.style.left = '50%';
        popup.style.top = '50%';
        popup.style.transform = 'translate(-50%, -50%)';
        popup.style.opacity = '1';
    }

    function closePopup(mod, popup, overlay) {
        if (!popup || !overlay) {
            return;
        }

        popup.style.transition = 'all 0.2s ease-in';
        popup.style.opacity = '0';
        popup.style.transform = 'translate(-50%, -50%) scale(0.95)';

        setTimeout(function () {
            mod.classList.remove('mpf-visible');
            overlay.classList.remove('mpf-overlay-visible');

            popup.style.transition = '';
            popup.style.left = '50%';
            popup.style.top = '50%';
            popup.style.transform = 'translate(-50%, -50%)';
        }, 200);
    }

    document.addEventListener('DOMContentLoaded', function () {
        const observer = new MutationObserver(function () {
            const badge = document.querySelector('.grecaptcha-badge');
            if (badge) {
                badge.style.right = 'auto';
                badge.style.left = '0';
                badge.style.bottom = '0';
                badge.style.zIndex = '999999';
            }
        });

        observer.observe(document.body, { childList: true, subtree: true });
    });
})();
