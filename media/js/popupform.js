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
    });

    function initPopupModule(mod) {
        const anchorHash = mod.dataset.anchorHash || 'callback'; // без "#"
        const ajaxUrl = mod.dataset.ajaxUrl;
        const submitLabel = mod.dataset.submitLabel || 'Send';
        const submittingLabel = mod.dataset.submittingLabel || 'Sending...';
        const successText = mod.dataset.successText || '';
        const moduleId = mod.dataset.moduleId;

        const overlay = mod.querySelector('.mpf-overlay');
        const popup = mod.querySelector('.mpf-popup');
        const form = mod.querySelector('.mpf-form');
        const successBox = mod.querySelector('.mpf-success');
        const submitBtn = mod.querySelector('.mpf-submit-btn');
        const closeButtons = mod.querySelectorAll('[data-mpf-close]');
        const alertBox = mod.querySelector('.mpf-alert');

        let lastClickX = window.innerWidth / 2;
        let lastClickY = window.innerHeight / 2;

        // Отслеживаем клики по ссылкам с нужным хешем
        document.addEventListener('click', function (e) {
            const link = e.target.closest('a[href]');
            if (!link) {
                return;
            }

            const href = link.getAttribute('href') || '';
            const urlHash = href.split('#')[1];

            if (urlHash && urlHash === anchorHash) {
                e.preventDefault();

                const clickEvent = e;
                lastClickX = clickEvent.clientX;
                lastClickY = clickEvent.clientY;

                openPopup(mod, popup, overlay, lastClickX, lastClickY);
            }
        });

        // Закрытие по клику на оверлей или кнопку
        closeButtons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                closePopup(mod, popup, overlay);
            });
        });

        // Обработка формы
        if (form && ajaxUrl) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();

                // Простая клиентская валидация Bootstrap 5 style
                const inputs = form.querySelectorAll('input, textarea');
                let hasError = false;

                inputs.forEach(function (input) {
                    const value = (input.value || '').trim();
                    const isRequired = input.hasAttribute('required');
                    const dataType = input.dataset.type || input.type || 'text';
                    const emailValidate = input.dataset.emailValidate === '1';

                    let fieldHasError = false;

                    // Сбрасываем предыдущее состояние
                    input.classList.remove('is-invalid');

                    if (isRequired && !value) {
                        fieldHasError = true;
                    }

                    // Простая проверка email при включённой валидации
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

                // AJAX-отправка
                const formData = new FormData(form);
                formData.append('module_id', moduleId);

                submitBtn.disabled = true;
                submitBtn.textContent = submittingLabel;

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
                        // response — это объект вида { success, message, data }

                        // Сбрасываем старое сообщение об ошибке
                        if (alertBox) {
                            alertBox.classList.add('d-none');
                            alertBox.textContent = '';
                        }

                        // Если com_ajax сообщил об ошибке (исключение в PHP)
                        if (!response.success) {
                            const message = response.message || 'Произошла ошибка при отправке формы.';

                            if (alertBox) {
                                alertBox.textContent = message;
                                alertBox.classList.remove('d-none');
                            }

                            return;
                        }

                        const data = response.data || {};

                        // Ошибки валидации полей
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

                            // Общая подпись
                            if (alertBox) {
                                alertBox.textContent = (window.Joomla && Joomla.Text)
                                    ? Joomla.Text._('MOD_POPUP_FORM_ERROR_VALIDATION')
                                    : 'Проверьте правильность заполнения формы.';
                                alertBox.classList.remove('d-none');
                            }

                            return;
                        }

                        // Успех (data.success === true)
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
                        // Сетевые / JSON ошибки
                        if (alertBox) {
                            alertBox.textContent = 'Ошибка связи с сервером. Попробуйте позже.';
                            alertBox.classList.remove('d-none');
                        }
                        console.error(error);
                    })
                    .finally(function () {
                        submitBtn.disabled = false;
                        submitBtn.textContent = submitLabel;
                    });
            });
        }
    }

    function openPopup(mod, popup, overlay, clickX, clickY) {
        if (!popup || !overlay) {
            return;
        }

        // Изначально позиционируем попап в точке клика
        const popupRect = popup.getBoundingClientRect();

        const startLeft = clickX - popupRect.width / 2;
        const startTop = clickY - popupRect.height / 2;

        // Показываем контейнер
        mod.classList.add('mpf-visible');

        // Стартовые значения
        popup.style.transition = 'none';
        popup.style.opacity = '0';
        popup.style.left = startLeft + 'px';
        popup.style.top = startTop + 'px';
        popup.style.transform = 'translate(0, 0)';

        overlay.classList.add('mpf-overlay-visible');

        // Принудительный reflow, чтобы анимация сработала
        void popup.offsetWidth;

        // Анимация к центру
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

        // Анимация исчезновения
        popup.style.transition = 'all 0.2s ease-in';
        popup.style.opacity = '0';
        popup.style.transform = 'translate(-50%, -50%) scale(0.95)';

        // После анимации убираем классы и возвращаем в безопасное состояние
        setTimeout(function () {
            mod.classList.remove('mpf-visible');
            overlay.classList.remove('mpf-overlay-visible');

            popup.style.transition = '';
            popup.style.left = '50%';
            popup.style.top = '50%';
            popup.style.transform = 'translate(-50%, -50%)';
        }, 200);
    }
})();
