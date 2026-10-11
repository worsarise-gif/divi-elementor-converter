/* Independent interaction adapter for the retained Classic composition DOM. */
(function () {
    'use strict';
    var root = document.currentScript && document.currentScript.closest('[data-ace-reverse-composition]');
    if (!root) return;

    root.querySelectorAll('.e-n-accordion').forEach(function (group, index) {
        var widget = group.closest('.elementor-widget-nested-accordion'), settings = {};
        try { settings = JSON.parse(widget.getAttribute('data-settings') || '{}'); } catch (error) { /* Source default: single. */ }
        var items = Array.from(group.querySelectorAll(':scope > details'));
        var titles = items.map(function (item) { return item.querySelector('summary'); });
        function focus(title) {
            titles.forEach(function (other) { other.tabIndex = other === title ? 0 : -1; });
        }
        items.forEach(function (item, i) {
            var title = titles[i];
            if (settings.max_items_expended !== 'multiple') item.name = root.classList[0] + '-accordion-' + index;
            title.addEventListener('focus', function () { focus(title); });
            function sync() {
                title.setAttribute('aria-expanded', String(item.open));
                if (item.open && settings.max_items_expended !== 'multiple') {
                    items.forEach(function (other) { if (other !== item) other.open = false; });
                }
            }
            item.addEventListener('toggle', sync);
            sync();
            title.addEventListener('keydown', function (event) {
                if (['ArrowDown', 'ArrowUp', 'Home', 'End', 'Escape'].indexOf(event.key) < 0) return;
                event.preventDefault();
                if (event.key === 'Escape') { item.open = false; return; }
                var next = event.key === 'Home' ? 0 : event.key === 'End' ? titles.length - 1 :
                    (i + (event.key === 'ArrowDown' ? 1 : -1) + titles.length) % titles.length;
                titles[next].focus();
            });
        });
    });

    root.querySelectorAll('.elementor-accordion,.elementor-toggle').forEach(function (group) {
        var titles = Array.from(group.querySelectorAll('.elementor-tab-title'));
        var single = group.classList.contains('elementor-accordion');
        function set(title, active) {
            title.classList.toggle('elementor-active', active);
            title.setAttribute('aria-expanded', String(active));
            var content = title.nextElementSibling;
            if (content) {
                content.classList.toggle('elementor-active', active);
                content.hidden = !active;
                content.style.display = active ? 'block' : 'none';
            }
        }
        titles.forEach(function (title, index) {
            title.tabIndex = 0;
            set(title, single && index === 0);
            function toggle() {
                var active = !title.classList.contains('elementor-active');
                if (single) titles.forEach(function (other) { set(other, false); });
                set(title, active);
            }
            title.addEventListener('click', toggle);
            title.addEventListener('keydown', function (event) {
                if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); toggle(); }
            });
        });
    });

    root.querySelectorAll('.elementor-tabs').forEach(function (group) {
        var titles = Array.from(group.querySelectorAll('.elementor-tab-title'));
        function select(tab) {
            group.querySelectorAll('.elementor-tab-title,.elementor-tab-content').forEach(function (node) {
                var active = node.getAttribute('data-tab') === tab;
                node.classList.toggle('elementor-active', active);
                if (node.matches('.elementor-tab-content')) {
                    node.hidden = !active;
                    node.style.display = active ? 'block' : 'none';
                } else { node.setAttribute('aria-selected', String(active)); node.tabIndex = active ? 0 : -1; }
            });
        }
        titles.forEach(function (title, index) {
            title.addEventListener('click', function () { select(title.getAttribute('data-tab')); });
            title.addEventListener('keydown', function (event) {
                if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); select(title.getAttribute('data-tab')); }
                if (['ArrowRight', 'ArrowLeft', 'Home', 'End'].indexOf(event.key) < 0) return;
                event.preventDefault();
                var next = event.key === 'Home' ? 0 : event.key === 'End' ? titles.length - 1 :
                    (index + (event.key === 'ArrowRight' ? 1 : -1) + titles.length) % titles.length;
                select(titles[next].getAttribute('data-tab'));
                titles[next].focus();
            });
        });
        if (titles.length) select(titles[0].getAttribute('data-tab'));
    });
})();
