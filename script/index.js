    const searchForm = document.querySelector("form");
    const textField = document.querySelector("form textarea");

    searchForm.addEventListener('submit', function (event) {
        if(textField.value.length <= 3)
            event.preventDefault();
    })