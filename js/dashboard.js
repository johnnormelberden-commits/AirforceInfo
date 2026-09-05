/* =========================================================
   CMO TRAINING SQUADRON
   DASHBOARD.JS
   SHARED SIDEBAR / TOPBAR JAVASCRIPT

   RESPONSIBILITIES:
   - Mobile sidebar
   - Sidebar overlay
   - Copy File dropdown
   - Active navigation item
   - Responsive sidebar behavior
   - Same-page hash navigation
   - DataTables on Dashboard
   - Export Excel
   - Export PDF
   - Print Table
   - Delete confirmation modal

   IMPORTANT:
   - DataTables is only initialized when #personnelTable exists.
   - Statistics page will not be affected.
   ========================================================= */


(function () {

    "use strict";


    /* =====================================================
       DOM READY
    ===================================================== */

    document.addEventListener("DOMContentLoaded", function () {


        /* =================================================
           ELEMENTS
        ================================================= */

        const sidebar =
            document.getElementById("sidebar");

        const sidebarOverlay =
            document.getElementById("sidebarOverlay");

        const mobileMenuBtn =
            document.getElementById("mobileMenuBtn");

        const copyFileToggle =
            document.getElementById("copyFileToggle");

        const copyFileMenu =
            document.getElementById("copyFileMenu");


        /*
         * Normal sidebar navigation links.
         */

        const sidebarLinks =
            document.querySelectorAll(
                ".sidebar-nav > a.nav-item"
            );


        /* =================================================
           SIDEBAR FUNCTIONS
        ================================================= */

        function openSidebar() {

            if (!sidebar) {
                return;
            }

            sidebar.classList.add("show");

            if (sidebarOverlay) {

                sidebarOverlay.classList.add("show");

            }

            document.body.classList.add(
                "sidebar-open"
            );

        }


        function closeSidebar() {

            if (!sidebar) {
                return;
            }

            sidebar.classList.remove("show");

            if (sidebarOverlay) {

                sidebarOverlay.classList.remove("show");

            }

            document.body.classList.remove(
                "sidebar-open"
            );

        }


        function toggleSidebar() {

            if (!sidebar) {
                return;
            }

            if (
                sidebar.classList.contains("show")
            ) {

                closeSidebar();

            } else {

                openSidebar();

            }

        }


        /* =================================================
           MOBILE MENU BUTTON
        ================================================= */

        if (mobileMenuBtn) {

            mobileMenuBtn.addEventListener(
                "click",
                function (event) {

                    event.preventDefault();

                    event.stopPropagation();

                    toggleSidebar();

                }
            );

        }


        /* =================================================
           SIDEBAR OVERLAY
        ================================================= */

        if (sidebarOverlay) {

            sidebarOverlay.addEventListener(
                "click",
                function () {

                    closeSidebar();

                }
            );

        }


        /* =================================================
           ESCAPE KEY
        ================================================= */

        document.addEventListener(
            "keydown",
            function (event) {

                if (event.key === "Escape") {

                    closeSidebar();

                }

            }
        );


        /* =================================================
           COPY FILE DROPDOWN
        ================================================= */

        if (
            copyFileToggle &&
            copyFileMenu
        ) {

            copyFileToggle.addEventListener(
                "click",
                function (event) {

                    event.preventDefault();

                    event.stopPropagation();


                    const isOpen =
                        copyFileMenu.classList.contains(
                            "show"
                        );


                    /*
                     * Close first.
                     */

                    copyFileMenu.classList.remove(
                        "show"
                    );

                    copyFileToggle.classList.remove(
                        "open"
                    );


                    /*
                     * Open only when it was closed.
                     */

                    if (!isOpen) {

                        copyFileMenu.classList.add(
                            "show"
                        );

                        copyFileToggle.classList.add(
                            "open"
                        );

                    }

                }
            );

        }


        /* =================================================
           CLOSE COPY FILE DROPDOWN WHEN CLICKING OUTSIDE
        ================================================= */

        document.addEventListener(
            "click",
            function (event) {

                if (
                    !copyFileToggle ||
                    !copyFileMenu
                ) {

                    return;

                }


                if (
                    !copyFileToggle.contains(
                        event.target
                    ) &&
                    !copyFileMenu.contains(
                        event.target
                    )
                ) {

                    copyFileMenu.classList.remove(
                        "show"
                    );

                    copyFileToggle.classList.remove(
                        "open"
                    );

                }

            }
        );


        /* =================================================
           DATATABLES
           
           IMPORTANT:
           Only initialize this on pages that have
           #personnelTable.
        ================================================= */

        let personnelTable = null;


        if (
            document.getElementById(
                "personnelTable"
            )
        ) {

            /*
             * Make sure jQuery exists.
             */

            if (
                typeof window.jQuery !== "undefined"
            ) {

                const $ =
                    window.jQuery;


                /*
                 * Make sure DataTables exists.
                 */

                if (
                    $.fn.DataTable
                ) {


                    /* =====================================
                       INITIALIZE PERSONNEL TABLE
                    ===================================== */

                    personnelTable =
                        $("#personnelTable").DataTable({

                            /*
                             * Hide the DataTables Buttons.
                             *
                             * We use the custom
                             * "Copy File" sidebar instead.
                             */

                            dom:
                                '<"datatable-top"lf>' +
                                'rt' +
                                '<"datatable-bottom"ip>',


                            pageLength: 10,


                            lengthMenu: [
                                [10, 25, 50, 100, -1],
                                [
                                    10,
                                    25,
                                    50,
                                    100,
                                    "All"
                                ]
                            ],


                            ordering: true,


                            searching: true,


                            responsive: false,


                            autoWidth: false,


                            language: {

                                search: "",

                                searchPlaceholder:
                                    "Search personnel...",

                                lengthMenu:
                                    "Show _MENU_ entries",

                                info:
                                    "Showing _START_ to _END_ of _TOTAL_ personnel",

                                infoEmpty:
                                    "Showing 0 to 0 of 0 personnel",

                                zeroRecords:
                                    "No matching personnel found",

                                emptyTable:
                                    "No personnel records available",

                                paginate: {

                                    first: "First",

                                    last: "Last",

                                    next: "Next",

                                    previous: "Previous"

                                }

                            },


                            /* =================================
                               DATATABLE BUTTONS
                            ================================= */

                            buttons: [

                                /*
                                 * EXCEL
                                 */

                                {
                                    extend:
                                        "excelHtml5",

                                    title:
                                        "CMO Training Squadron - Military Personnel",

                                    filename:
                                        "CMO_Training_Squadron_Personnel",

                                    exportOptions: {

                                        /*
                                         * Exclude Action column.
                                         *
                                         * The Action column is
                                         * the last column.
                                         */

                                        columns:
                                            ":not(:last-child)"

                                    }

                                },


                                /*
                                 * PDF
                                 */

                                {
                                    extend:
                                        "pdfHtml5",

                                    title:
                                        "CMO Training Squadron - Military Personnel",

                                    filename:
                                        "CMO_Training_Squadron_Personnel",

                                    orientation:
                                        "landscape",

                                    pageSize:
                                        "A4",

                                    exportOptions: {

                                        columns:
                                            ":not(:last-child)"

                                    },


                                    customize:
                                        function (doc) {

                                            doc.defaultStyle.fontSize =
                                                7;

                                            doc.styles.tableHeader.fontSize =
                                                7;

                                            doc.styles.tableHeader.bold =
                                                true;


                                            doc.styles.title = {

                                                fontSize:
                                                    14,

                                                bold:
                                                    true,

                                                color:
                                                    "#1e3a5f",

                                                alignment:
                                                    "center",

                                                margin:
                                                    [
                                                        0,
                                                        0,
                                                        0,
                                                        10
                                                    ]

                                            };

                                        }

                                },


                                /*
                                 * PRINT
                                 */

                                {
                                    extend:
                                        "print",

                                    title:
                                        "CMO Training Squadron - Military Personnel",

                                    exportOptions: {

                                        columns:
                                            ":not(:last-child)"

                                    },


                                    customize:
                                        function (win) {

                                            $(win.document.body)
                                                .css(
                                                    "font-size",
                                                    "10pt"
                                                );


                                            $(win.document.body)
                                                .find("h1")
                                                .css({

                                                    "text-align":
                                                        "center",

                                                    "font-size":
                                                        "18pt",

                                                    "margin-bottom":
                                                        "20px",

                                                    "color":
                                                        "#1e3a5f"

                                                });


                                            $(win.document.body)
                                                .find("table")
                                                .css({

                                                    "width":
                                                        "100%",

                                                    "border-collapse":
                                                        "collapse"

                                                });


                                            $(win.document.body)
                                                .find(
                                                    "th, td"
                                                )
                                                .css({

                                                    "border":
                                                        "1px solid #ccc",

                                                    "padding":
                                                        "6px",

                                                    "font-size":
                                                        "9pt"

                                                });

                                        }

                                }

                            ]

                        });


                    /* =====================================
                       EXPORT TO EXCEL
                    ===================================== */

                    const exportExcel =
                        document.getElementById(
                            "exportExcel"
                        );


                    if (exportExcel) {

                        exportExcel.addEventListener(
                            "click",
                            function (event) {

                                event.preventDefault();

                                event.stopPropagation();


                                /*
                                 * Close Copy File menu.
                                 */

                                if (copyFileMenu) {

                                    copyFileMenu.classList.remove(
                                        "show"
                                    );

                                }

                                if (copyFileToggle) {

                                    copyFileToggle.classList.remove(
                                        "open"
                                    );

                                }


                                /*
                                 * Trigger DataTables Excel.
                                 */

                                personnelTable
                                    .button(
                                        ".buttons-excel"
                                    )
                                    .trigger();

                            }
                        );

                    }


                    /* =====================================
                       EXPORT TO PDF
                    ===================================== */

                    const exportPDF =
                        document.getElementById(
                            "exportPDF"
                        );


                    if (exportPDF) {

                        exportPDF.addEventListener(
                            "click",
                            function (event) {

                                event.preventDefault();

                                event.stopPropagation();


                                /*
                                 * Close Copy File menu.
                                 */

                                if (copyFileMenu) {

                                    copyFileMenu.classList.remove(
                                        "show"
                                    );

                                }

                                if (copyFileToggle) {

                                    copyFileToggle.classList.remove(
                                        "open"
                                    );

                                }


                                /*
                                 * Trigger DataTables PDF.
                                 */

                                personnelTable
                                    .button(
                                        ".buttons-pdf"
                                    )
                                    .trigger();

                            }
                        );

                    }


                    /* =====================================
                       PRINT TABLE
                    ===================================== */

                    const exportPrint =
                        document.getElementById(
                            "exportPrint"
                        );


                    if (exportPrint) {

                        exportPrint.addEventListener(
                            "click",
                            function (event) {

                                event.preventDefault();

                                event.stopPropagation();


                                /*
                                 * Close Copy File menu.
                                 */

                                if (copyFileMenu) {

                                    copyFileMenu.classList.remove(
                                        "show"
                                    );

                                }

                                if (copyFileToggle) {

                                    copyFileToggle.classList.remove(
                                        "open"
                                    );

                                }


                                /*
                                 * Trigger DataTables Print.
                                 */

                                personnelTable
                                    .button(
                                        ".buttons-print"
                                    )
                                    .trigger();

                            }
                        );

                    }

                }

            }

        }


        /* =================================================
           ACTIVE SIDEBAR LINK
        ================================================= */

        function getCurrentPage() {

            let pathname =
                window.location.pathname;


            pathname =
                pathname.replace(
                    /\/+$/,
                    ""
                );


            let page =
                pathname
                    .split("/")
                    .pop()
                    .toLowerCase();


            if (
                !page ||
                page === ""
            ) {

                page = "index.php";

            }


            return page;

        }


        function cleanPageName(href) {

            if (!href) {

                return "";

            }


            try {

                const url =
                    new URL(
                        href,
                        window.location.href
                    );


                let pathname =
                    url.pathname;


                pathname =
                    pathname.replace(
                        /\/+$/,
                        ""
                    );


                let page =
                    pathname
                        .split("/")
                        .pop()
                        .toLowerCase();


                if (
                    !page ||
                    page === ""
                ) {

                    page = "index.php";

                }


                return page;

            } catch (error) {

                return "";

            }

        }


        function setActiveSidebarLink() {

            const currentPage =
                getCurrentPage();


            sidebarLinks.forEach(
                function (link) {

                    const href =
                        link.getAttribute(
                            "href"
                        );


                    if (!href) {

                        return;

                    }


                    /*
                     * Ignore pure hash links.
                     */

                    if (
                        href.trim().startsWith("#")
                    ) {

                        return;

                    }


                    const linkPage =
                        cleanPageName(
                            href
                        );


                    if (
                        linkPage &&
                        linkPage === currentPage
                    ) {

                        link.classList.add(
                            "active"
                        );

                    } else {

                        link.classList.remove(
                            "active"
                        );

                    }

                }
            );

        }


        setActiveSidebarLink();


        /* =================================================
           SIDEBAR NAVIGATION
        ================================================= */

        sidebarLinks.forEach(
            function (link) {

                link.addEventListener(
                    "click",
                    function () {

                        sidebarLinks.forEach(
                            function (item) {

                                item.classList.remove(
                                    "active"
                                );

                            }
                        );


                        this.classList.add(
                            "active"
                        );


                        /*
                         * Close sidebar on mobile.
                         */

                        if (
                            window.innerWidth <= 992
                        ) {

                            closeSidebar();

                        }

                    }
                );

            }
        );


        /* =================================================
           SUBMENU LINK HANDLING
        ================================================= */

        if (copyFileMenu) {

            const submenuLinks =
                copyFileMenu.querySelectorAll(
                    "a"
                );


            submenuLinks.forEach(
                function (link) {

                    link.addEventListener(
                        "click",
                        function () {

                            if (
                                window.innerWidth <= 992
                            ) {

                                closeSidebar();

                            }

                        }
                    );

                }
            );

        }


        /* =================================================
           RESPONSIVE SIDEBAR
        ================================================= */

        function handleResize() {

            if (
                window.innerWidth > 992
            ) {

                if (sidebar) {

                    sidebar.classList.remove(
                        "show"
                    );

                }


                if (sidebarOverlay) {

                    sidebarOverlay.classList.remove(
                        "show"
                    );

                }


                document.body.classList.remove(
                    "sidebar-open"
                );

            }

        }


        window.addEventListener(
            "resize",
            handleResize
        );


        handleResize();


        /* =================================================
           BODY SCROLL CONTROL
        ================================================= */

        function updateBodyScroll() {

            if (
                window.innerWidth <= 992 &&
                sidebar &&
                sidebar.classList.contains(
                    "show"
                )
            ) {

                document.body.classList.add(
                    "sidebar-open"
                );

            } else {

                document.body.classList.remove(
                    "sidebar-open"
                );

            }

        }


        if (sidebar) {

            const sidebarObserver =
                new MutationObserver(
                    function () {

                        updateBodyScroll();

                    }
                );


            sidebarObserver.observe(
                sidebar,
                {
                    attributes: true,
                    attributeFilter: [
                        "class"
                    ]
                }
            );

        }


        updateBodyScroll();


        /* =================================================
           SAME-PAGE HASH NAVIGATION
        ================================================= */

        function handleHashNavigation(
            event,
            link
        ) {

            const href =
                link.getAttribute(
                    "href"
                );


            if (!href) {

                return;

            }


            const hashIndex =
                href.indexOf("#");


            if (
                hashIndex === -1
            ) {

                return;

            }


            const hash =
                href.substring(
                    hashIndex
                );


            if (
                hash === "#" ||
                hash.length <= 1
            ) {

                return;

            }


            let linkUrl;
            let currentUrl;


            try {

                linkUrl =
                    new URL(
                        link.href,
                        window.location.href
                    );


                currentUrl =
                    new URL(
                        window.location.href
                    );

            } catch (error) {

                return;

            }


            /*
             * Only handle same-page hashes.
             */

            if (
                linkUrl.pathname !==
                currentUrl.pathname
            ) {

                return;

            }


            let target;


            try {

                target =
                    document.querySelector(
                        hash
                    );

            } catch (error) {

                return;

            }


            if (!target) {

                return;

            }


            event.preventDefault();


            const topbar =
                document.querySelector(
                    ".topbar"
                );


            const offset =
                topbar
                    ? topbar.offsetHeight + 20
                    : 20;


            const targetPosition =
                target.getBoundingClientRect().top +
                window.scrollY -
                offset;


            window.scrollTo({

                top:
                    targetPosition,

                behavior:
                    "smooth"

            });


            if (
                window.history &&
                window.history.pushState
            ) {

                window.history.pushState(
                    null,
                    "",
                    hash
                );

            }


            if (
                window.innerWidth <= 992
            ) {

                closeSidebar();

            }

        }


        const hashLinks =
            document.querySelectorAll(
                'a[href*="#"]'
            );


        hashLinks.forEach(
            function (link) {

                link.addEventListener(
                    "click",
                    function (event) {

                        handleHashNavigation(
                            event,
                            link
                        );

                    }
                );

            }
        );


        /* =================================================
           OPEN HASH SECTION ON PAGE LOAD
        ================================================= */

        function scrollToCurrentHash() {

            const hash =
                window.location.hash;


            if (!hash) {

                return;

            }


            let target;


            try {

                target =
                    document.querySelector(
                        hash
                    );

            } catch (error) {

                return;

            }


            if (!target) {

                return;

            }


            setTimeout(
                function () {

                    const topbar =
                        document.querySelector(
                            ".topbar"
                        );


                    const offset =
                        topbar
                            ? topbar.offsetHeight + 20
                            : 20;


                    const position =
                        target.getBoundingClientRect().top +
                        window.scrollY -
                        offset;


                    window.scrollTo({

                        top:
                            position,

                        behavior:
                            "smooth"

                    });

                },
                100
            );

        }


        window.addEventListener(
            "load",
            scrollToCurrentHash
        );


        /* =================================================
           BROWSER BACK / FORWARD
        ================================================= */

        window.addEventListener(
            "popstate",
            function () {

                scrollToCurrentHash();

            }
        );


        /* =================================================
           PAGE VISIBILITY
        ================================================= */

        document.addEventListener(
            "visibilitychange",
            function () {

                /*
                 * Nothing required here.
                 */

            }
        );


    });

})();
