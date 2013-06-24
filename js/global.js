var URL_AJAX = 'http://' + DOMAIN + '/ajax/main.php',
    REGEXP_MONEY = /^[0-9]+$/,
    showError = function(msg, callback, selector) {
        var callback = callback || function () {}
            selector = selector || $('.error');
        selector
            .stop()
            .html(msg)
            .css('opacity', 1)
            .show()
            .css('display','inline')
            .fadeOut(4000, callback);
    },
    cursorWait = function() { $('body').css('cursor','wait');},
    cursorDefault = function() { $('body').css('cursor','default');},
    setCookie = function(name, value) {
        var exdate = new Date();
        exdate.setDate(exdate.getDate()+1);
        document.cookie = name + "=" + value + "; path=/; expires=" + exdate.toGMTString();
    },
    delCookie = function(name) {
        var exdate = new Date();
        exdate.setDate(exdate.getDate() - 1);
        document.cookie = name + "=; path=/; expires=" + exdate.toGMTString();
    },
    getCookie = function(name) {
        var arr1 = document.cookie.split(name);
        if(arr1.length > 1) {
            var arr2 = arr1[1].split(/;/);
            var arr3 = arr2[0].split(/=/);
            return arr3[0] ? arr3[0] : arr3[1];
        }
        return null;
    },
    showUploadedImages = function() {
        var img = '';
        for(var n = 0; n < IMAGES.length; n++) {
            var im = IMAGES[n];
            img += '<div class="zakaz_im">' +
                '<img src="' + im.link + '" height="90" />' +
                ' <a class="zakaz_image_delete" val="' + n + '">Удалить</a>' +
                '</div>';
        }
        $('#zakaz_images').html(img);
    };

$(document).ready(function () {
    if($('#zakaz_edit').length > 0)
        showUploadedImages();
    $('#work_about').autosize();
});

$.fn.buttonWait = function() {
    $(this).addClass('busy');
    cursorWait();
};
$.fn.buttonCancel = function() {
    $(this).removeClass('busy');
    cursorDefault();
};

$(document)
    .on('click', '#login_button', function() {
        if($(this).hasClass('busy'))
            return;
        var but = $(this),
            send = {
                op:'login',
                pass:$('#pass').val()
            };
        if(!send.pass)
            return;
        but.buttonWait();
        $.post(URL_AJAX, send, function(data) {
            but.buttonCancel();
            if(data.error == 1)
                showError(data.text);
            else
                location.reload();
        }, 'json');
    })
    .on('keydown', '#pass', function(e) {
        if(e.keyCode == 13)
            $('#login_button').trigger('click');
    });

$(document)
    .on('click', '#client_add_button', function() {
        if($(this).hasClass('busy'))
            return;
        var but = $(this),
            send = {
                op:'client_add',
                fio:$.trim($('#fio').val()),
                telefon:$.trim($('#telefon').val()),
                adres:$.trim($('#adres').val())
            };
        if(!send.fio) {
            showError('Необходимо обязательно указать ФИО.');
            return;
        }
        but.buttonWait();
        $.post(URL_AJAX, send, function(data) {
            but.buttonCancel();
            if(data.error == 1)
                showError(data.text);
            else if(data.success == 1)
                location.href = URL + '/client/' + data.id;
        }, 'json');
    })
    .on('click', '#client_edit_button', function() {
        if($(this).hasClass('busy'))
            return;
        var but = $(this),
            send = {
                op:'client_edit',
                id:$('#client_id').val(),
                fio:$.trim($('#fio').val()),
                telefon:$.trim($('#telefon').val()),
                adres:$.trim($('#adres').val())
            };
        if(!send.fio) {
            showError('Необходимо обязательно указать ФИО.');
            return;
        }
        but.buttonWait();
        $.post(URL_AJAX, send, function(data) {
            but.buttonCancel();
            if(data.error == 1)
                showError(data.text);
            else if(data.success == 1)
                location.href = URL + '/client/' + send.id;
        }, 'json');
    })
    .on('keyup', '#client_search', function() {
        if($(this).hasClass('busy'))
            return;
        var inp = $(this),
            spisok = $('#client_spisok'),
            send = {
                op:'client_search',
                val:inp.val()
            };
        inp.addClass('busy');
        spisok.css('opacity', 0.2);
        $.post(URL_AJAX, send, function(data) {
            inp.removeClass('busy');
            spisok.css('opacity', 1);
            if(data.success == 1)
                spisok.html(data.spisok);
        }, 'json');
    })
    .on('click', '#client_next', function() {
        if($(this).hasClass('busy'))
            return;
        var next = $(this),
            spisok = $('#client_spisok'),
            send = {
                op:'client_next',
                val:$('#client_search').val(),
                page:next.attr('val')
            };
        next.addClass('busy');
        $.post(URL_AJAX, send, function(data) {
            next.remove();
            if(data.success == 1)
                spisok.append(data.spisok);
        }, 'json');
    });

$(document)
    .on('click', '#zakaz_add_button', function() {
        if($(this).hasClass('busy'))
            return;
        var images = [];
        for(var n = 0; n < IMAGES.length; n++) {
            var im = IMAGES[n];
            images.push('{"x":"' + im.x + '","y":"' + im.y + '","link":"' + im.link + '"}');
        }
        var but = $(this),
            send = {
                op:'zakaz_add',
                client_id:$('#client_id').val(),
                work_about:$.trim($('#work_about').val()),
                work_adres:$.trim($('#work_adres').val()),
                responsible:$.trim($('#responsible').val()),
                date_exec:$.trim($('#date_exec').val()),
                images:images.join(','),
                cost_osmotr:$.trim($('#cost_osmotr').val()),
                oplata_osmotr:$('input[name="oplata_osmotr"]:checked').val(),
                comment:$.trim($('#comment').val())
            };
        if(send.cost_osmotr && !REGEXP_MONEY.test(send.cost_osmotr)) {
            showError('Некорректно введена стоимость осмотра.', null, $('#zakaz_error'));
            $('#cost_osmotr').focus();
            return;
        }
        if(send.cost_osmotr.length > 0 && !send.oplata_osmotr) {
            showError('Укажите, оплачена ли стоимость осмотра.', null, $('#zakaz_error'));
            return;
        }
        but.buttonWait();
        $.post(URL_AJAX, send, function(data) {
            if(data.success == 1)
                location.href = URL + '/zakaz/' + data.id;
            but.buttonCancel();
        }, 'json');
    })
    .on('click', '#accrualAdd_show', function() {
        $('#zakaz_accrual_insert').toggle();
        $('#zakaz_money_insert').hide();
        $('#accrual_sum').val('');
        $('#accrual_about').val('');
    })
    .on('click', '#moneyAdd_show', function() {
        $('#zakaz_money_insert').toggle();
        $('#zakaz_accrual_insert').hide();
        $('#money_sum').val('');
        $('#money_about').val('');
    })
    .on('click', '#accrual_insert_button', function() {
        if($(this).hasClass('busy'))
            return;
        var but = $(this),
            error = $('#accrual_error'),
            send = {
                op:'accrual_insert',
                zakaz_id:$('#zakaz_id').val(),
                client_id:$('#client_id').val(),
                sum: $.trim($('#accrual_sum').val()),
                about:$.trim($('#accrual_about').val())
            };
        if(!send.sum || !REGEXP_MONEY.test(send.sum)) {
            showError('Некорректно введена сумма.', null, error);
            $('#accrual_sum').focus();
            return;
        }
        but.buttonWait();
        $.post(URL_AJAX, send, function(data) {
            but.buttonCancel();
            if(data.success == 1) {
                $('#zakaz_accrual_insert').hide();
                $('#zakaz_moneyAccrual').html(data.spisok);
            } else if(data.error == 1)
                showError(data.text, null, error);
        }, 'json');
    })
    .on('click', '#money_insert_button', function() {
        if($(this).hasClass('busy'))
            return;
        var but = $(this),
            error = $('#money_error'),
            send = {
                op:'money_insert',
                zakaz_id:$('#zakaz_id').val(),
                client_id:$('#client_id').val(),
                sum: $.trim($('#money_sum').val()),
                about:$.trim($('#money_about').val())
            };
        if(!send.sum || !REGEXP_MONEY.test(send.sum)) {
            showError('Некорректно введена сумма.', null, error);
            $('#money_sum').focus();
            return;
        }
        but.buttonWait();
        $.post(URL_AJAX, send, function(data) {
            but.buttonCancel();
            if(data.success == 1) {
                $('#zakaz_money_insert').hide();
                $('#zakaz_moneyAccrual').html(data.spisok);
            } else if(data.error == 1)
                showError(data.text, null, error);
        }, 'json');
    })
    .on('keyup', '#cost_osmotr', function() {
        var vis = ($.trim($(this).val()).length > 0);
        $('#oplata_osmotr_span')[vis ? 'show' : 'hide']();
    })
    .on('click', '#zakaz_edit_button', function() {
        if($(this).hasClass('busy'))
            return;
        var images = [];
        for(var n = 0; n < IMAGES.length; n++) {
            var im = IMAGES[n];
            images.push('{"x":"' + im.x + '","y":"' + im.y + '","link":"' + im.link + '"}');
        }
        var but = $(this),
            send = {
                op:'zakaz_edit',
                id:$('#zakaz_id').val(),
                work_about:$.trim($('#work_about').val()),
                work_adres:$.trim($('#work_adres').val()),
                responsible:$.trim($('#responsible').val()),
                date_exec:$.trim($('#date_exec').val()),
                images:images.join(','),
                status:$('#zakaz_status').val()
            };
        but.buttonWait();
        $.post(URL_AJAX, send, function(data) {
            but.buttonCancel();
            if(data.success == 1)
                location.href = URL + '/zakaz/' + send.id;
        }, 'json');
    })
    .on('click', '.zakaz_unit', function() {
        var id = $(this).attr('val');
        location.href = URL + '/zakaz/' + id;
    })
    .on('click', '#zakaz #cond input[name="status"]', function() {
        var spisok = $('#zakaz_spisok');
        if(spisok.hasClass('busy'))
            return;
        var send = {
                op:'zakaz_search',
                status:$(this).val()
            };
        spisok.addClass('busy');
        $.post(URL_AJAX, send, function(data) {
            spisok.removeClass('busy');
            if(data.success == 1)
                spisok.html(data.spisok);
        }, 'json');
    })
    .on('click', '#zakaz_next', function() {
        if($(this).hasClass('busy'))
            return;
        var next = $(this),
            spisok = $('#zakaz_spisok'),
            send = {
                op:'zakaz_next',
                page:next.attr('val'),
                client_id:$('#zakaz_client_id').length > 0 ? $('#zakaz_client_id').val() : 0,
                status:$('input[name="status"]').length > 0 ? $('input[name="status"]:checked').val() : 0
            };
        next.addClass('busy');
        $.post(URL_AJAX, send, function(data) {
            next.remove();
            if(data.success == 1)
                spisok.append(data.spisok);
        }, 'json');
    })
    .on('keydown', '#zakaz_comment_add', function(e) {
        if(e.keyCode != 13)
            return;
        var inp = $(this),
            spisok = $('#zakaz_comments'),
            send = {
                op:'zakaz_comment_add',
                zakaz_id:$('#zakaz_id').val(),
                txt: $.trim(inp.val())
            };
        if(!send.txt)
            return;
        inp.attr('disabled', 'disabled');
        cursorWait();
        $.post(URL_AJAX, send, function(data) {
            inp.removeAttr('disabled');
            cursorDefault();
            if(data.success == 1) {
                spisok.append(data.html);
                inp.val('');
            }
        }, 'json');
    });


$(document)
    .on('change', '#zakaz_img_upload', function () {
        cursorWait();
        $("#upload_form").submit();
        $(this).attr('disabled', 'disabled');
        setCookie('fotoUpload', 'process');
        var timer = setInterval(uploadStart, 400);

        function uploadStart() {
            var cookie = getCookie('fotoUpload');
            $('#upload_input_file').append('.');
            if (cookie != 'process') {
                clearInterval(timer);
                var arr = cookie.split('_');
                switch (arr[0]) {
                    case 'uploaded': fotoUploadLastImage(); break;
                    case 'error': fotoUploadErrorPrint(arr[1]); break;
                    default: fotoUploadErrorPrint(-1);
                }
            }
        }

        function fotoUploadLastImage() {
            cursorDefault();
            var img = getCookie('fotoJson').split(/%2C/g);
            IMAGES.push({
                x:img[0],
                y:img[1],
                link:URL + '/files/images/' + img[2]
            });
            showUploadedImages();
            $('#upload_input_file').html('<input type="file" name="file_name" id="zakaz_img_upload">');
        }

        function fotoUploadErrorPrint(id) {
            var msg = 'неизвестная ошибка';
            switch(parseInt(id)) {
                case 1: msg = 'файл не является избражением'; break;
                case 2: msg = 'размер изображения слишком маленький'; break;
            }
            showError('Избражение не загружено: ' + msg, null, $('#image_error'));
            $('#upload_input_file').html('<input type="file" name="file_name" id="zakaz_img_upload">');
            cursorDefault();
        }
    })
    .on('click', '.zakaz_image_delete', function() {
        var id = $(this).attr('val');
        IMAGES.splice(id, 1);
        showUploadedImages();
    });
