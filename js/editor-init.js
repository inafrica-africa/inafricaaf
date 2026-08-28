// Shared Summernote setup for the Post Content editor (add-post.php + manage-posts.php).
// Adds a custom "Caption" button to the image popover so admins can attach a
// caption to any image without needing the (removed) raw HTML code view.
(function ($) {
    if (!$.summernote || !$.summernote.plugins || $.summernote.plugins.imageCaption) {
        return;
    }

    $.extend($.summernote.plugins, {
        imageCaption: function (context) {
            var ui = $.summernote.ui;

            context.memo('button.imageCaption', function () {
                return ui.button({
                    contents: '<i class="note-icon-picture"></i>',
                    tooltip: 'Add / edit caption',
                    click: function () {
                        var $image = $(context.invoke('restoreTarget'));
                        if (!$image.length || !$image.is('img')) {
                            return;
                        }

                        var $figure = $image.closest('figure.captioned-image');
                        var existing = $figure.length ? $figure.find('figcaption').text() : '';
                        var caption = window.prompt('Caption for this image (leave blank to remove):', existing || '');
                        if (caption === null) {
                            return;
                        }

                        if (!$figure.length) {
                            $image.wrap('<figure class="captioned-image"></figure>');
                            $figure = $image.closest('figure.captioned-image');
                        }

                        $figure.find('figcaption').remove();
                        var trimmed = caption.trim();
                        if (trimmed !== '') {
                            $figure.append($('<figcaption></figcaption>').text(trimmed));
                        } else if ($figure.length) {
                            // No caption left: unwrap the figure, keep just the image.
                            $image.unwrap();
                        }

                        context.invoke('editor.afterCommand');
                    }
                }).render();
            });
        }
    });
})(jQuery);

function initPostEditor(selector) {
    $(selector).summernote({
        height: 300,
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'italic', 'underline', 'strikethrough', 'superscript', 'subscript', 'clear']],
            ['fontname', ['fontname']],
            ['fontsize', ['fontsize']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph', 'height']],
            ['table', ['table']],
            ['insert', ['link', 'picture', 'video', 'hr']],
            ['view', ['fullscreen', 'help']]
        ],
        popover: {
            image: [
                ['custom', ['imageCaption']],
                ['float', ['floatLeft', 'floatRight', 'floatNone']],
                ['resize', ['resizeFull', 'resizeHalf', 'resizeQuarter', 'resizeNone']],
                ['remove', ['removeMedia']]
            ]
        },
        callbacks: {
            // Without this, Summernote's default behavior for a picked/pasted/
            // dropped image is to embed it inline as a base64 data: URI — which
            // both bloats PostDetails with binary-as-text and, at a few images,
            // is exactly what was blowing past the upload size limit (413) on
            // posts with inline images. Upload each file to a real folder
            // instead and insert the resulting URL, same as every other image
            // field in the admin already does.
            onImageUpload: function (files) {
                var $editor = $(this);
                var csrfToken = $editor.closest('form').find('input[name="csrf_token"]').val() || '';

                Array.prototype.forEach.call(files, function (file) {
                    var formData = new FormData();
                    formData.append('file', file);
                    formData.append('csrf_token', csrfToken);

                    fetch('/admin/upload-content-image.php', {
                        method: 'POST',
                        body: formData,
                        credentials: 'same-origin'
                    })
                        .then(function (response) {
                            return response.json().then(function (data) {
                                if (!response.ok) {
                                    throw new Error(data.error || 'Upload failed.');
                                }
                                return data;
                            });
                        })
                        .then(function (data) {
                            $editor.summernote('insertImage', data.url);
                        })
                        .catch(function (err) {
                            window.alert(err.message || 'Could not upload image.');
                        });
                });
            }
        }
    });
}
