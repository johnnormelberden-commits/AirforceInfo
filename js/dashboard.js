$(document).ready(function () {

    /*
    |--------------------------------------------------------------------------
    | DATATABLE
    |--------------------------------------------------------------------------
    */

    let table = null;

    /*
     * Only initialize DataTables if the personnel table exists.
     */

    if ($('#personnelTable').length) {

        table = $('#personnelTable').DataTable({

            /*
            |--------------------------------------------------------------------------
            | DEFAULT SORTING
            |--------------------------------------------------------------------------
            */

            order: [
                [0, 'asc']
            ],


            /*
            |--------------------------------------------------------------------------
            | PAGE LENGTH
            |--------------------------------------------------------------------------
            */

            pageLength: 5,


            /*
            |--------------------------------------------------------------------------
            | LENGTH MENU
            |--------------------------------------------------------------------------
            */

            lengthMenu: [
                [5, 10, 25, 50],
                [5, 10, 25, 50]
            ],


            /*
            |--------------------------------------------------------------------------
            | DATATABLE DOM
            |--------------------------------------------------------------------------
            */

            dom: 'lBfrtip',


            /*
            |--------------------------------------------------------------------------
            | EXPORT BUTTONS
            |--------------------------------------------------------------------------
            */

            buttons: [

                /*
                |--------------------------------------------------------------------------
                | EXCEL
                |--------------------------------------------------------------------------
                */

                {
                    extend: 'excelHtml5',

                    title: 'Military Personnel Information',

                    className: 'export-excel-button'
                },


                /*
                |--------------------------------------------------------------------------
                | PDF
                |--------------------------------------------------------------------------
                */

                {
                    extend: 'pdfHtml5',

                    title: 'Military Personnel Information',

                    orientation: 'landscape',

                    pageSize: 'A4',

                    className: 'export-pdf-button'
                },


                /*
                |--------------------------------------------------------------------------
                | PRINT
                |--------------------------------------------------------------------------
                */

                {
                    extend: 'print',

                    title: 'Military Personnel Information',

                    className: 'export-print-button'
                }

            ]

        });

    }


    /*
    |--------------------------------------------------------------------------
    | COPY FILE DROPDOWN
    |--------------------------------------------------------------------------
    |
    | This controls:
    |
    | Copy File
    |     ├── Export to Excel
    |     ├── Export to PDF
    |     └── Print Table
    |
    |--------------------------------------------------------------------------
    */

    $('#copyFileToggle').on('click', function (e) {

        e.preventDefault();

        e.stopPropagation();


        /*
         * Toggle dropdown
         */

        $('#copyFileMenu').toggleClass('show');


        /*
         * Rotate chevron
         */

        $(this).toggleClass('open');

    });


    /*
    |--------------------------------------------------------------------------
    | EXPORT TO EXCEL
    |--------------------------------------------------------------------------
    */

    $('#exportExcel').on('click', function (e) {

        e.preventDefault();

        e.stopPropagation();


        /*
         * Make sure DataTables exists
         */

        if (table) {

            table
                .button('.export-excel-button')
                .trigger();

        }


        /*
         * Close dropdown
         */

        closeCopyMenu();

    });


    /*
    |--------------------------------------------------------------------------
    | EXPORT TO PDF
    |--------------------------------------------------------------------------
    */

    $('#exportPDF').on('click', function (e) {

        e.preventDefault();

        e.stopPropagation();


        /*
         * Make sure DataTables exists
         */

        if (table) {

            table
                .button('.export-pdf-button')
                .trigger();

        }


        /*
         * Close dropdown
         */

        closeCopyMenu();

    });


    /*
    |--------------------------------------------------------------------------
    | PRINT TABLE
    |--------------------------------------------------------------------------
    */

    $('#exportPrint').on('click', function (e) {

        e.preventDefault();

        e.stopPropagation();


        /*
         * Make sure DataTables exists
         */

        if (table) {

            table
                .button('.export-print-button')
                .trigger();

        }


        /*
         * Close dropdown
         */

        closeCopyMenu();

    });


    /*
    |--------------------------------------------------------------------------
    | CLOSE COPY FILE DROPDOWN
    |--------------------------------------------------------------------------
    */

    function closeCopyMenu() {

        $('#copyFileMenu').removeClass('show');

        $('#copyFileToggle').removeClass('open');

    }


    /*
    |--------------------------------------------------------------------------
    | CLOSE DROPDOWN WHEN CLICKING OUTSIDE
    |--------------------------------------------------------------------------
    */

    $(document).on('click', function (e) {

        /*
         * If the click is outside .nav-dropdown,
         * close the Copy File menu.
         */

        if (!$(e.target).closest('.nav-dropdown').length) {

            closeCopyMenu();

        }

    });


    /*
    |--------------------------------------------------------------------------
    | MOBILE SIDEBAR
    |--------------------------------------------------------------------------
    */

    $('#mobileMenuBtn').on('click', function (e) {

        e.preventDefault();

        e.stopPropagation();


        /*
         * Open/close sidebar
         */

        $('#sidebar').toggleClass('show');


        /*
         * Open/close overlay
         */

        $('#sidebarOverlay').toggleClass('show');

    });


    /*
    |--------------------------------------------------------------------------
    | CLOSE MOBILE SIDEBAR
    |--------------------------------------------------------------------------
    */

    $('#sidebarOverlay').on('click', function () {

        $('#sidebar').removeClass('show');

        $('#sidebarOverlay').removeClass('show');

    });


    /*
    |--------------------------------------------------------------------------
    | DELETE MODAL
    |--------------------------------------------------------------------------
    */

    const deleteModalElement =
        document.getElementById('deleteModal');


    /*
     * Only initialize the modal if it exists.
     */

    if (deleteModalElement) {

        const deleteModal =
            new bootstrap.Modal(deleteModalElement);


        const confirmDeleteBtn =
            document.getElementById('confirmDeleteBtn');


        const deletePersonName =
            document.getElementById('deletePersonName');


        /*
        |--------------------------------------------------------------------------
        | DELETE BUTTON
        |--------------------------------------------------------------------------
        */

        $('#personnelTable').on(
            'click',
            '.btn-delete',
            function (e) {

                e.preventDefault();


                /*
                |--------------------------------------------------------------------------
                | GET PERSONNEL ID
                |--------------------------------------------------------------------------
                */

                const id =
                    $(this).data('id');


                /*
                |--------------------------------------------------------------------------
                | GET PERSONNEL NAME
                |--------------------------------------------------------------------------
                */

                const name =
                    $(this).data('name');


                /*
                |--------------------------------------------------------------------------
                | SHOW PERSONNEL NAME
                |--------------------------------------------------------------------------
                */

                if (deletePersonName) {

                    deletePersonName.textContent =
                        name
                            ? 'Personnel: ' + name
                            : '';

                }


                /*
                |--------------------------------------------------------------------------
                | SET DELETE URL
                |--------------------------------------------------------------------------
                */

                if (confirmDeleteBtn) {

                    confirmDeleteBtn.href =
                        'delete.php?id=' +
                        encodeURIComponent(id);

                }


                /*
                |--------------------------------------------------------------------------
                | SHOW DELETE MODAL
                |--------------------------------------------------------------------------
                */

                deleteModal.show();

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | ESC KEY
    |--------------------------------------------------------------------------
    |
    | Close Copy File dropdown when pressing Escape.
    |
    |--------------------------------------------------------------------------
    */

    $(document).on('keydown', function (e) {

        if (e.key === 'Escape') {

            closeCopyMenu();

        }

    });

});
