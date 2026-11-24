{{-- Rich text editor for blog content. Expects $value (initial HTML or plain text). --}}
@php
    $initial = \App\Support\Html::render($value ?? '');
@endphp

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/quill/1.3.7/quill.snow.min.css">
<style>
    .blog-editor .ql-toolbar.ql-snow { border-radius: 8px 8px 0 0; border-color: var(--border); background: #f9fafb; }
    .blog-editor .ql-container.ql-snow { border-radius: 0 0 8px 8px; border-color: var(--border); font-family: 'Outfit', sans-serif; font-size: 15px; }
    .blog-editor .ql-editor { min-height: 340px; line-height: 1.7; }
    .blog-editor .ql-editor h1 { font-size: 2em; font-weight: 700; margin: .6em 0 .4em; }
    .blog-editor .ql-editor h2 { font-size: 1.6em; font-weight: 700; margin: .6em 0 .4em; }
    .blog-editor .ql-editor h3 { font-size: 1.3em; font-weight: 700; margin: .6em 0 .4em; }
    .blog-editor .ql-editor h4 { font-size: 1.1em; font-weight: 700; }
    .blog-editor .ql-editor h5 { font-size: 1em; font-weight: 700; }
    .blog-editor .ql-editor h6 { font-size: .9em; font-weight: 700; }
    .blog-editor.has-error .ql-container, .blog-editor.has-error .ql-toolbar { border-color: #dc2626; }
</style>

<div class="blog-editor" id="blogEditorWrap">
    <div id="blogEditor">{!! $initial !!}</div>
    <textarea name="content" id="blogContent" hidden>{{ $value ?? '' }}</textarea>
</div>
<p class="adm-hint">Use the <strong>Heading</strong> dropdown in the toolbar to choose H1–H6. Select text first, then pick a heading level.</p>

<script src="https://cdnjs.cloudflare.com/ajax/libs/quill/1.3.7/quill.min.js"></script>
<script>
    (function () {
        var quill = new Quill('#blogEditor', {
            theme: 'snow',
            placeholder: 'Write your article content here...',
            modules: {
                toolbar: [
                    [{ header: [1, 2, 3, 4, 5, 6, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ color: [] }, { background: [] }],
                    [{ script: 'sub' }, { script: 'super' }],
                    [{ list: 'ordered' }, { list: 'bullet' }],
                    [{ align: [] }],
                    ['blockquote', 'link'],
                    ['clean']
                ]
            }
        });

        var form = document.getElementById('blogEditorWrap').closest('form');
        var field = document.getElementById('blogContent');
        var wrap = document.getElementById('blogEditorWrap');

        form.addEventListener('submit', function (e) {
            if (quill.getText().trim().length === 0) {
                e.preventDefault();
                wrap.classList.add('has-error');
                quill.focus();
                alert('Please write the article content.');
                return;
            }
            field.value = quill.root.innerHTML;
        });
    })();
</script>
