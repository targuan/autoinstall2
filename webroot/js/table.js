$(document).ready(function () {
    $('.checkall').on('click', function (event) {
        value = $(this).prop("checked")
        $(this).closest('table').find("td :checkbox").each(function () {
            $(this).prop("checked", value)
        })
    })
})