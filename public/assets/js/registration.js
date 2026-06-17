document.addEventListener('DOMContentLoaded', function() {
    // Toggle password visibility
    document.querySelectorAll('.toggle-password').forEach(btn => {
        btn.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const input = document.getElementById(targetId);
            if (input) {
                input.type = (input.type === 'password') ? 'text' : 'password';
            }
        });
    });

    // Автоматический переход между полями кода (6 цифр)
    const codeInputs = document.querySelectorAll('.code-digit');
    if (codeInputs.length) {
        codeInputs.forEach((input, idx) => {
            input.addEventListener('input', function() {
                if (this.value.length === 1 && idx < 5) {
                    codeInputs[idx + 1].focus();
                }
                if (idx === 5 && this.value.length === 1) {
                    this.form.submit();
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
                }
            });
        });
    }

    // Повторная отправка кода (AJAX)
    const resendBtn = document.getElementById('resend-code');
    const timerSpan = document.getElementById('resend-timer');
    if (resendBtn) {
        resendBtn.addEventListener('click', function() {
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
                    alert('Новый код отправлен на вашу почту');
                    resendBtn.disabled = true;
                    let seconds = 60;
                    timerSpan.style.display = 'inline';
                    timerSpan.textContent = seconds + 'с';
                    const interval = setInterval(() => {
                        seconds--;
                        timerSpan.textContent = seconds + 'с';
                        if (seconds <= 0) {
                            clearInterval(interval);
                            timerSpan.style.display = 'none';
                            resendBtn.disabled = false;
                        }
                    }, 1000);
                } else {
                    alert('Ошибка: ' + data.error);
                }
            })
            .catch(err => alert('Ошибка сети'));
        });
    }
});