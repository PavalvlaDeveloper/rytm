document.addEventListener('DOMContentLoaded', function() {
    // ===== Toggle password (показ только при зажатой кнопке) =====
    document.querySelectorAll('.toggle-password').forEach(btn => {
        const input = document.getElementById(btn.getAttribute('data-target'));

        const showPassword = () => {
            if (input) {
                input.type = 'text';
                btn.setAttribute('data-show', 'true');
            }
        };
        const hidePassword = () => {
            if (input) {
                input.type = 'password';
                btn.setAttribute('data-show', 'false');
            }
        };

        btn.addEventListener('mousedown', showPassword);
        btn.addEventListener('mouseup', hidePassword);
        btn.addEventListener('mouseleave', hidePassword);
        btn.addEventListener('touchstart', showPassword);
        btn.addEventListener('touchend', hidePassword);
        btn.addEventListener('touchcancel', hidePassword);
    });

    // ===== Автоматический переход между полями кода (6 цифр) =====
    const codeInputs = document.querySelectorAll('.code-digit');
    if (codeInputs.length) {
        codeInputs.forEach((input, idx) => {
            input.addEventListener('input', function() {
                if (this.value.length === 1 && idx < 5) {
                    codeInputs[idx + 1].focus();
                }
                if (idx === 5 && this.value.length === 1) {
                    document.getElementById('confirm-form').dispatchEvent(new Event('submit'));
                }
            });
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Backspace' && !this.value.length && idx > 0) {
                    codeInputs[idx - 1].focus();
                }
            });
            input.addEventListener('paste', function(e) {
                e.preventDefault();
                const paste = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '');
                if (paste.length === 6) {
                    paste.split('').forEach((ch, i) => {
                        if (codeInputs[i]) codeInputs[i].value = ch;
                    });
                    codeInputs[5].focus();
                    document.getElementById('confirm-form').dispatchEvent(new Event('submit'));
                }
            });
        });
    }

    // ===== Отправка формы подтверждения через AJAX =====
    const confirmForm = document.getElementById('confirm-form');
    if (confirmForm) {
        confirmForm.addEventListener('submit', function(e) {
            e.preventDefault();

            let code = '';
            const inputs = this.querySelectorAll('.code-digit');
            inputs.forEach(input => {
                code += input.value;
            });

            if (code.length < 6) {
                alert('Пожалуйста, введите все 6 цифр кода');
                return;
            }

            fetch('/register/step2', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    code: code,
                    csrf_token: this.querySelector('input[name="csrf_token"]').value
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = data.redirect || '/register/step3';
                } else {
                    alert(data.message || 'Неверный код подтверждения');
                    inputs.forEach(input => input.value = '');
                    inputs[0].focus();
                }
            })
            .catch(err => {
                alert('Ошибка соединения. Попробуйте позже.');
            });
        });
    }

    // ===== Повторная отправка кода с таймером 60 секунд =====
    const resendBtn = document.getElementById('resend-code');
    if (resendBtn) {
        let seconds = 60;
        let interval = null;
        let timeoutId = null;

        const timerSpan = document.createElement('span');
        timerSpan.id = 'resend-timer';

        const setButtonTextWithTimer = () => {
            timerSpan.textContent = seconds;
            resendBtn.innerHTML = '';
            resendBtn.appendChild(document.createTextNode('Выслать код повторно ('));
            resendBtn.appendChild(timerSpan);
            resendBtn.appendChild(document.createTextNode(' сек)'));
        };

        const startTimer = () => {
            resendBtn.disabled = true;
            seconds = 60;
            setButtonTextWithTimer();

            if (interval) clearInterval(interval);
            interval = setInterval(() => {
                seconds--;
                timerSpan.textContent = seconds;
                if (seconds <= 0) {
                    clearInterval(interval);
                    interval = null;
                    resendBtn.disabled = false;
                    timerSpan.textContent = '0';
                }
            }, 1000);
        };

        startTimer();

        resendBtn.addEventListener('click', function() {
            if (this.disabled) return;

            this.disabled = true;
            this.innerHTML = 'Код повторно отправлен!';

            fetch('/register/resend', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    csrf_token: document.querySelector('input[name="csrf_token"]').value
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    timeoutId = setTimeout(() => {
                        startTimer();
                    }, 500);
                } else {
                    alert('Ошибка: ' + data.error);
                    startTimer();
                }
            })
            .catch(err => {
                alert('Ошибка сети');
                startTimer();
            });
        });
    }

    // ===== Предпросмотр аватарки (шаг 3) =====
    const avatarInput = document.getElementById('avatar-input');
    const preview = document.getElementById('avatar-preview');
    if (avatarInput && preview) {
        preview.addEventListener('click', function() {
            avatarInput.click();
        });

        avatarInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = `<img src="${e.target.result}" alt="Avatar preview" style="max-width:100%; max-height:200px;">`;
                };
                reader.readAsDataURL(file);
            } else {
                preview.innerHTML = `<span class="placeholder">Загрузите изображение</span>`;
            }
        });
    }
});