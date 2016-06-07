(function($) {
    $.fn.FastImageTV = function(options) {
        var settings = $.extend( {
            'tv'   : '',
            'siteUrl' : '/',
            'classname' : '',
            'documentData' : {}
        }, options);
        var placeholder = this,
            tv = $(settings.tv),
            uploadBtn = $('.fi-upload',placeholder),
            deleteBtn = $('.fi-delete',placeholder),
            progress = $('.fi-progress',placeholder),
            upload = $('.fi-upload-input',placeholder);
        var uploader = {
            init: function() {
                uploadBtn.click(function(e){
                   e.preventDefault();
                   uploader.clear();
                   upload.trigger('click');
                });
                deleteBtn.click(function(e){
                    e.preventDefault();
                    if (!$(e.target).hasClass('disabled')) uploader.delete();
                });
                FileAPI.event.on(upload[0], 'change', function (evt){
                    var files = FileAPI.getFiles(evt); // Retrieve file list
                    FileAPI.filterFiles(files, function (file, info/**Object*/){
                        return /jpeg|gif|png$/.test(file.type);
                    }, function (files/**Array*/, rejected/**Array*/){
                        uploader.upload(files);
                    });
                });
                FileAPI.event.dnd(placeholder[0], function (over){
                    if (over) {
                        placeholder.addClass('dnd');
                    } else {
                        placeholder.removeClass('dnd');
                    }
                }, function (files){
                    FileAPI.filterFiles(files, function (file, info/**Object*/){
                        return /jpeg|gif|png$/.test(file.type);
                    }, function (files/**Array*/, rejected/**Array*/){
                        uploader.upload(files);
                    });
                });
            },
            upload: function(files) {
                if( files.length ){
                    FileAPI.upload({
                        url: settings.siteUrl+'assets/tvs/FastImage/ajax.php',
                        files: { file: files },
                        data: {
                            mode: 'upload',
                            class: settings.classname,
                            documentData: settings.documentData
                        },
                        upload: function() {
                            uploadBtn.addClass('disabled');
                            progress.show();
                        },
                        progress: function (evt){
                            var pr = evt.loaded/evt.total * 100;
                            progress.css('width',pr+'%');
                        },
                        complete: function (err, xhr){
                            progress.css('width', 0).hide();
                            uploadBtn.removeClass('disabled');
                            uploader.clear();
                            if (!err) {
                                var response = JSON.parse(xhr.response);
                                if (response.success !== undefined && response.success == true) {
                                    uploader.save(response.data);
                                } else {
                                    alert(response.message);
                                }
                            }
                        }
                    });
                }
            },
            clear: function() {
                upload.replaceWith(upload = upload.clone( true ));
            },
            delete: function() {
                file = tv.val();
                $.post(
                    settings.siteUrl+'assets/tvs/FastImage/ajax.php',
                    {
                        mode:'delete',
                        file:file,
                        class:settings.classname,
                        documentData: settings.documentData
                    },
                    function(data) {
                        data = JSON.parse(data);
                        if (data.success !== undefined && data.success == true) {
                            tv.val('');
                            $('.fi-image', placeholder).attr('src', settings.siteUrl + 'assets/tvs/FastImage/images/noimage.png');
                            deleteBtn.addClass('disabled');
                        }
                    }
                );
            },
            save: function(value) {
                tv.val(value.path + value.file);
                thumbnail = settings.siteUrl + (value.thumbnail !== undefined ? value.thumbnail : value.path + value.file);
                $('.fi-image',placeholder).attr('src',thumbnail);
                deleteBtn.removeClass('disabled');
            }
        };
        uploader.init();
    };
})(jQuery);
