/**
 * CampusMarket — Filters JS (Browse Page)
 */
(function ($) {
    'use strict';

    const filterForm = document.getElementById('cm-filter-form');
    const gridContainer = document.getElementById('cm-listings-container');
    const loading = document.getElementById('cm-loading');
    const resultsCount = document.getElementById('cm-filter-results-count');
    if (!filterForm || !gridContainer) return;

    function doFilter(page) {
        page = page || 1;
        const formData = new FormData(filterForm);

        loading.style.display = 'block';
        gridContainer.style.opacity = '0.5';

        $.post(cmData.ajaxUrl, {
            action: 'cm_filter_listings',
            nonce: cmData.nonce,
            search: formData.get('search') || '',
            category: formData.get('category') || '',
            listing_type: formData.get('listing_type') || '',
            condition: formData.get('condition') || '',
            min_price: formData.get('min_price') || '',
            max_price: formData.get('max_price') || '',
            sort: formData.get('sort') || 'date',
            paged: page
        }, function (response) {
            loading.style.display = 'none';
            gridContainer.style.opacity = '1';
            if (response.success) {
                gridContainer.innerHTML = response.data.html;
                if (resultsCount) {
                    resultsCount.textContent = response.data.found + ' listing(s) found';
                }
                if (window.cmScrollTo) window.cmScrollTo(gridContainer);
            }
        });
    }

    // Apply filters
    filterForm.addEventListener('submit', function (e) {
        e.preventDefault();
        doFilter(1);
    });

    // Reset
    var resetBtn = document.getElementById('cm-filter-reset');
    if (resetBtn) {
        resetBtn.addEventListener('click', function () {
            filterForm.reset();
            doFilter(1);
        });
    }

    // Auto-filter on select change
    var selects = filterForm.querySelectorAll('select');
    selects.forEach(function (sel) {
        sel.addEventListener('change', function () { doFilter(1); });
    });

})(jQuery);
