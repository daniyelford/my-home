$(function () {
    $('#month').on('change', function () {
        if ($(this).val() == 1 || $(this).val() == 2 || $(this).val() == 3 || $(this).val() == 4 || $(this).val() == 5 || $(this).val() == 6) {
            $('.everyDay').removeClass('d-none');
        } else {
            if ($(this).val() == 7 || $(this).val() == 8 || $(this).val() == 9 || $(this).val() == 10 || $(this).val() == 11) {
                if ($('#day').val() == 31) {
                    $('#day').val(30);
                }
                $('.everyDay').removeClass('d-none');
                if (!$('.everyDay').last().hasClass('d-none')) {
                    $('.everyDay').last().addClass('d-none');
                }
            } else {
                if ($(this).val() == 12) {
                    if ($('#day').val() == 31 || $('#day').val() == 30) {
                        $('#day').val(29);
                    }
                    $('.everyDay').removeClass('d-none');
                    if (!$('.everyDay').last().hasClass('d-none')) {
                        $('.everyDay').last().addClass('d-none');
                    }
                    if (!$('.everyDay').eq(-2).hasClass('d-none')) {
                        $('.everyDay').eq(-2).addClass('d-none');
                    }
                } else {
                    return false;
                }
            }
        }
    })
})