'use strict';

(function () {
    const header = document.querySelector('.site-header');
    if (!header) return;

    const updateHeaderSpace = () => {
        const height = Math.ceil(header.getBoundingClientRect().height);
        document.documentElement.style.setProperty('--site-header-height', `${height}px`);
    };

    updateHeaderSpace();
    new ResizeObserver(updateHeaderSpace).observe(header);
})();
