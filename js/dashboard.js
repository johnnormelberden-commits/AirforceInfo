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

   DOES NOT CONTROL:
   - Chart.js
   - DataTables
   - Page-specific forms
   - Page-specific modals
   - Database operations
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
         * Select normal sidebar navigation links.
         *
         * We intentionally do NOT include submenu links
         * in this selector.
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
                     * Close dropdown first.
                     */

                    copyFileMenu.classList.remove(
                        "show"
                    );

                    copyFileToggle.classList.remove(
                        "open"
                    );


                    /*
                     * Open only if it was previously closed.
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
           ACTIVE SIDEBAR LINK
        ================================================= */

        function getCurrentPage() {

            let pathname =
                window.location.pathname;


            /*
             * Remove trailing slash.
             */

            pathname =
                pathname.replace(
                    /\/+$/,
                    ""
                );


            /*
             * Get filename.
             */

            let page =
                pathname
                    .split("/")
                    .pop()
                    .toLowerCase();


            /*
             * If there is no filename,
             * assume index.php.
             */

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
                     * Ignore pure anchor links.
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


                    /*
                     * Compare actual PHP page.
                     */

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


        /*
         * Set active navigation item
         * immediately when the page loads.
         */

        setActiveSidebarLink();


        /* =================================================
           SIDEBAR NAVIGATION
        ================================================= */

        sidebarLinks.forEach(
            function (link) {

                link.addEventListener(
                    "click",
                    function () {

                        /*
                         * Set active state immediately.
                         *
                         * The browser will still perform
                         * normal navigation.
                         */

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
                         * On mobile, close the sidebar
                         * before navigating.
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

                            /*
                             * Close mobile sidebar.
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

        }


        /* =================================================
           RESPONSIVE SIDEBAR
        ================================================= */

        function handleResize() {

            /*
             * Desktop:
             *
             * Sidebar is fixed by CSS.
             * Remove temporary mobile state.
             */

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


        /*
         * Run once when page loads.
         */

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


        /*
         * Watch sidebar class changes.
         */

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


            /*
             * No hash.
             */

            if (
                hashIndex === -1
            ) {

                return;

            }


            const hash =
                href.substring(
                    hashIndex
                );


            /*
             * Ignore empty hash.
             */

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
             * Only handle hashes belonging
             * to the current PHP page.
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


            /*
             * Prevent normal jump.
             */

            event.preventDefault();


            /*
             * Calculate topbar offset.
             */

            const topbar =
                document.querySelector(
                    ".topbar"
                );


            const offset =
                topbar
                    ? topbar.offsetHeight + 20
                    : 20;


            /*
             * Calculate target position.
             */

            const targetPosition =
                target.getBoundingClientRect().top +
                window.scrollY -
                offset;


            /*
             * Smooth scroll.
             */

            window.scrollTo({

                top: targetPosition,

                behavior: "smooth"

            });


            /*
             * Update browser URL.
             */

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


            /*
             * Close mobile sidebar.
             */

            if (
                window.innerWidth <= 992
            ) {

                closeSidebar();

            }

        }


        /*
         * Attach hash navigation only to
         * same-page links.
         */

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


            /*
             * Small delay so the page layout
             * is fully rendered.
             */

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

                        top: position,

                        behavior: "smooth"

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
                 * Nothing to reset.
                 *
                 * Navigation state is reconstructed
                 * automatically when the page loads.
                 */

            }
        );


    });

})();
