

$(() => {

        // Autofocus
        $('form :input:not(button):first').focus();
        $('.err:first').prev().focus();
        $('.err:first').prev().find(':input:first').focus();


        // Initiate GET request
        $('[data-get]').on('click', e => {
            e.preventDefault();
            const url = e.target.dataset.get;
            location = url || location;
        });
    
        // Initiate POST request
        $('[data-post]').on('click', e => {
            e.preventDefault();
            const url = e.target.dataset.post;
            const f = $('<form>').appendTo(document.body)[0];
            f.method = 'POST';
            f.action = url || location;
            f.submit();
        });

    $('label.upload input[type=file]').on('change', e => {
        const f = e.target.files[0];
        const img = $(e.target).siblings('img')[0];

        if (!img) return;

        img.dataset.src ??= img.src;

        if (f?.type.startsWith('image/')) {
            img.src = URL.createObjectURL(f);
        }
        else {
            img.src = img.dataset.src;
            e.target.value = '';
        }
    });

 


});

