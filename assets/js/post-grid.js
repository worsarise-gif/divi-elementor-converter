/* Preserve DOM reading order while packing variable-height core Blog cards. */
(() => {
    'use strict';
    const initialized = new WeakSet();
    function setup(root = document) {
        root.querySelectorAll('.ace-post-layout-masonry').forEach(grid => {
            if (initialized.has(grid) || !window.ResizeObserver) return;
            initialized.add(grid);
            const cards = [...grid.children].filter(el => el.classList.contains('ace-post-card'));
            if (!cards.length) return;
            grid.classList.add('ace-masonry-ready');
            let frame = 0;
            const arrange = () => {
                if (frame) return;
                frame = requestAnimationFrame(() => {
                    frame = 0;
                    if (!grid.isConnected) { observer.disconnect(); return; }
                    // Old explicit placements create implicit columns after a breakpoint
                    // change. Reset all placements before measuring the new source grid;
                    // otherwise computed columns include those stale implicit tracks.
                    cards.forEach(card => {
                        card.style.gridColumnStart = '';
                        card.style.gridRowStart = '';
                        card.style.gridRowEnd = '';
                    });
                    const style = getComputedStyle(grid), rawGap = style.columnGap;
                    const contentWidth = grid.clientWidth - (parseFloat(style.paddingLeft) || 0) - (parseFloat(style.paddingRight) || 0);
                    const gap = rawGap.endsWith('%') ? (parseFloat(rawGap) || 0) * contentWidth / 100 : parseFloat(rawGap) || 0;
                    const columns = style.gridTemplateColumns.split(/\s+/).length;
                    const starts = Array(columns).fill(1);
                    cards.forEach((card, index) => {
                        const column = index % columns;
                        const span = Math.max(1, Math.ceil(card.getBoundingClientRect().height + gap));
                        const value = 'span ' + span;
                        if (card.style.gridRowEnd !== value) card.style.gridRowEnd = value;
                        card.style.gridColumnStart = String(column + 1);
                        card.style.gridRowStart = String(starts[column]);
                        starts[column] += span;
                    });
                });
            };
            const observer = new ResizeObserver(arrange);
            observer.observe(grid); cards.forEach(card => observer.observe(card));
            grid.querySelectorAll('img').forEach(img => img.addEventListener('load', arrange, { once: true }));
            if (document.fonts) document.fonts.ready.then(arrange);
            arrange();
        });
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', () => setup(), { once: true });
    else setup();
    const bind = () => {
        if (window.elementorFrontend?.hooks) window.elementorFrontend.hooks.addAction('frontend/element_ready/ace-post-grid.default', scope => setup(scope[0]));
    };
    if (window.elementorFrontend) bind();
    else if (window.jQuery) window.jQuery(window).on('elementor/frontend/init', bind);
})();
