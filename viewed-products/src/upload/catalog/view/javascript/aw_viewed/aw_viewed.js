(function () {
    'use strict';

    function post(url, body, onText) {
        var params = new URLSearchParams(body).toString();

        fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            credentials: 'same-origin',
            body: params
        }).then(function (r) { return r.text(); }).then(onText);
    }

    function loadWidget(widget) {
        var target = widget.querySelector('[data-aw-viewed-target]');

        post(widget.dataset.url, {
            limit: widget.dataset.limit,
            width: widget.dataset.width,
            height: widget.dataset.height,
            product_id: widget.dataset.productId
        }, function (html) {
            target.innerHTML = html;

            if (!html.trim()) {
                widget.style.display = 'none';
            }
        });
    }

    function loadPage(page, pageNum) {
        var target = page.querySelector('[data-aw-viewed-target]');

        post(page.dataset.url, {
            limit: page.dataset.limit,
            page: pageNum || 1
        }, function (html) {
            target.innerHTML = html;
        });
    }

    function bindDelete() {
        document.addEventListener('click', function (e) {
            var btn = e.target.closest('.aw-viewed-delete');

            if (!btn) {
                return;
            }

            e.preventDefault();

            var container = btn.closest('[data-aw-viewed-widget], [data-aw-viewed-page]');

            if (!container || !container.dataset.deleteUrl) {
                return;
            }

            post(container.dataset.deleteUrl, { product_id: btn.dataset.productId }, function () {
                var item = btn.closest('.product-layout');

                if (item && item.parentNode) {
                    item.parentNode.removeChild(item);
                }
            });
        });
    }

    function bindPagination() {
        document.addEventListener('click', function (e) {
            var link = e.target.closest('#account-viewed .pagination a');

            if (!link) {
                return;
            }

            var page = document.querySelector('[data-aw-viewed-page]');

            if (!page) {
                return;
            }

            e.preventDefault();
            var match = link.href.match(/page=(\d+)/);
            loadPage(page, match ? match[1] : 1);
            window.scrollTo({ top: page.getBoundingClientRect().top + window.pageYOffset - 50, behavior: 'smooth' });
        });
    }

    function init() {
        document.querySelectorAll('[data-aw-viewed-widget]').forEach(loadWidget);
        document.querySelectorAll('[data-aw-viewed-page]').forEach(function (p) { loadPage(p, 1); });
        bindPagination();
        bindDelete();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
