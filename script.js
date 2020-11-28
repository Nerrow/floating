(function() {
    const forms = document.forms;

    const checkValidity = (form) => {
        const inputs = form.querySelectorAll('input');
        let isValid = true;
        const regex = new RegExp(/^((8|\+7)[\- ]?)?(\(?\d{3}\)?[\- ]?)?[\d\- ]{7,10}$/)
        Array.from(inputs).map(input => {
            if (!input.value) {
                isValid = false;
                input.parentElement.classList.add(`red`);
            }
            if (input.name === `phone`) {
                isValid = regex.test(input.value);
                if (!isValid) {
                    input.parentElement.classList.add(`red`);
                }
            }
        });

        return isValid;
    }
    
    Array.from(forms).map((form) => {
        form.addEventListener('submit', async (evt) => {
            evt.preventDefault();

            const formData = new FormData(form);
            let res = {}
            for (let [name, value] of formData) {
                res = {...res, [name]: value};
            }
            res = {...res, url: window.location.href};

            if (checkValidity(form)) {
                await fetch('https://12822bf10805.ngrok.io', {
                    method: 'POST',
                    body: JSON.stringify(res),
                  })
                  .then(() => {
                    popup_out();
                    $('.popup_overlay').fadeIn(150);
                    $('.popup__thx').fadeIn(150);
                    document.querySelector('.popup__thx').style = 'display: flex;'
                  })
                  .catch((err) => {
                      alert('Ошибка соединения')
                  });
            }
        });
    });
    
    const popup_out = () => {
        $('.popup_overlay').fadeOut(150);
        $('.popup').fadeOut(150);
        $('.popup').removeClass('activePopup');
    }
})();