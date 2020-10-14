
        {code js([
            'templates/back/assets/libs/jquery/dist/jquery.min.js',
            'https://code.jquery.com/ui/1.11.4/jquery-ui.js',
            'templates/back/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js',
            'templates/back/assets/libs/@shopify/draggable/lib/es5/draggable.bundle.legacy.js',
            'templates/back/assets/libs/autosize/dist/autosize.min.js',
            'templates/back/assets/libs/chart.js/dist/Chart.min.js',
            'templates/back/assets/libs/dropzone/dist/min/dropzone.min.js',
            'templates/back/assets/libs/flatpickr/dist/flatpickr.min.js',
            'templates/back/assets/libs/highlightjs/highlight.pack.min.js',
            'templates/back/assets/libs/jquery-mask-plugin/dist/jquery.mask.min.js',
            'templates/back/assets/libs/list.js/dist/list.min.js',
            'templates/back/assets/libs/quill/dist/quill.min.js',
            'templates/back/assets/libs/select2/dist/js/select2.min.js',
            'templates/back/assets/libs/chart.js/Chart.extension.min.js',
            'templates/back/assets/js/theme.min.js',
            'templates/back/assets/js/all.js',
        ])}

        {if #cookie.notice}<script>notice('{#cookie.notice}');</script>{/if}
        {if #cookie.warning}<script>warning('{#cookie.warning}');</script>{/if}

    </body>

</html>
