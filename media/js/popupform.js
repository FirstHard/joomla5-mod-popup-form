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

        // ГЛОБАЛЬНЫЙ обработчик отправки всех форм модуля
        document.addEventListener('submit', function (e) {
            const form = e.target.closest('.mpf-form');
            if (!form) {
                return;
            }

            const mod = form.closest('.mod-popup-form');
            if (!mod) {
                return;
            }

            e.preventDefault(); // Гарантированно блокируем стандартный submit

            handleFormSubmit(mod, form);
        });
    });

    /**
     * Инициализация конкретного экземпляра модуля:
     * - режим отображения (popup / inline)
     * - обработка кликов по ссылкам для popup
     * - обработка закрытия popup
     */
    function initPopupModule(mod) {
        const displayMode     = mod.dataset.displayMode || 'popup';
        const anchorHash      = mod.dataset.anchorHash || 'callback';
        const overlay         = mod.querySelector('.mpf-overlay');
        const popup           = mod.querySelector('.mpf-popup');
        const closeButtons    = mod.querySelectorAll('[data-mpf-close]');

        let lastClickX = window.innerWidth / 2;
        let lastClickY = window.innerHeight / 2;

        // POPUP-режим: отслеживаем клики по ссылкам с нужным хешем
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

        // Закрытие попапа (в статичном режиме элементов может не быть — это ок)
        closeButtons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                closePopup(mod, popup, overlay);
            });
        });
    }

    /**
     * Общий обработчик submit для всех форм модуля.
     * mod — корневой контейнер .mod-popup-form
     * form — текущая форма .mpf-form
     */
    function handleFormSubmit(mod, form) {
        const ajaxUrl         = mod.dataset.ajaxUrl || '';
        const submitLabel     = mod.dataset.submitLabel || 'Send';
        const submittingLabel = mod.dataset.submittingLabel || 'Sending...';
        const moduleId        = mod.dataset.moduleId;

        const alertBox   = mod.querySelector('.mpf-alert');
        const successBox = mod.querySelector('.mpf-success');
        const submitBtn  = form.querySelector('.mpf-submit-btn');

        // Сбрасываем алерт и предыдущие ошибки
        if (alertBox) {
            alertBox.classList.add('d-none');
            alertBox.textContent = '';
        }

        const inputs = form.querySelectorAll('input, textarea');
        let hasError = false;

        inputs.forEach(function (input) {
            const value         = (input.value || '').trim();
            const isRequired    = input.hasAttribute('required');
            const dataType      = input.dataset.type || input.type || 'text';
            const emailValidate = input.dataset.emailValidate === '1';

            let fieldHasError = false;

            // Сброс класса ошибки
            input.classList.remove('is-invalid');

            // Обязательность
            if (isRequired && !value) {
                fieldHasError = true;
            }

            // Email-валидация
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
            // Валидация не пройдена — не отправляем
            return;
        }

        // Если ajaxUrl не задан — не отправляем, но и не перезагружаем страницу
        if (!ajaxUrl) {
            if (alertBox) {
                alertBox.textContent = 'Configuration error: AJAX URL is not defined for this form.';
                alertBox.classList.remove('d-none');
            }
            console.error('mod_popup_form: ajaxUrl is not defined for module', moduleId);
            return;
        }

        // AJAX-отправка
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
                // response — объект вида { success, message, data }

                // Сбрасываем старый алерт
                if (alertBox) {
                    alertBox.classList.add('d-none');
                    alertBox.textContent = '';
                }

                // Если com_ajax вернул ошибку (исключение в PHP)
                if (!response.success) {
                    const message = response.message || 'An error occurred while sending the form.';

                    if (alertBox) {
                        alertBox.textContent = message;
                        alertBox.classList.remove('d-none');
                    }

                    return;
                }

                const data = response.data || {};

                // Ошибки валидации полей (сервер)
                if (data.errors && form) {
                    const errors = data.errors;

                    Object.keys(errors).forEach(function (field) {
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
                            : 'Please check the form fields.';
                        alertBox.classList.remove('d-none');
                    }

                    return;
                }

                // Успех
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

        // Изначально позиционируем попап в точке клика
        const popupRect = popup.getBoundingClientRect();

        const startLeft = clickX - popupRect.width / 2;
        const startTop  = clickY - popupRect.height / 2;

        // Показываем контейнер
        mod.classList.add('mpf-visible');

        // Стартовые значения
        popup.style.transition = 'none';
        popup.style.opacity    = '0';
        popup.style.left       = startLeft + 'px';
        popup.style.top        = startTop + 'px';
        popup.style.transform  = 'translate(0, 0)';

        overlay.classList.add('mpf-overlay-visible');

        // Принудительный reflow, чтобы анимация сработала
        void popup.offsetWidth;

        // Анимация к центру
        popup.style.transition = 'all 0.25s ease-out';
        popup.style.left       = '50%';
        popup.style.top        = '50%';
        popup.style.transform  = 'translate(-50%, -50%)';
        popup.style.opacity    = '1';
    }

    function closePopup(mod, popup, overlay) {
        if (!popup || !overlay) {
            return;
        }

        // Анимация исчезновения
        popup.style.transition = 'all 0.2s ease-in';
        popup.style.opacity    = '0';
        popup.style.transform  = 'translate(-50%, -50%) scale(0.95)';

        // После анимации убираем классы и возвращаем в безопасное состояние
        setTimeout(function () {
            mod.classList.remove('mpf-visible');
            overlay.classList.remove('mpf-overlay-visible');

            popup.style.transition = '';
            popup.style.left       = '50%';
            popup.style.top        = '50%';
            popup.style.transform  = 'translate(-50%, -50%)';
        }, 200);
    }
})();
