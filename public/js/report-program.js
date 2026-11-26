document.querySelectorAll('form[data-confirm-delete]').forEach(form => {
    form.addEventListener('submit', event => {
        if (!window.confirm('هل تريد حذف هذا التقرير؟')) event.preventDefault();
    });
});
