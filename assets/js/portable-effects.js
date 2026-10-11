/* Embedded in exported Elementor templates. No jQuery, Elementor Pro or plugin activation required. */
(function (config) {
    'use strict';
    // Older imported templates can own the motion API first. Accordion states use
    // their own portable registry, so they still initialize with those templates.
    if (config.some(definition => definition.effects.accordion)) {
        if (!window.SuiteEdgePortableAccordion) {
            const definitions = new Map(), initialized = new WeakSet();
            let scheduled = false;
            function refreshAccordion() {
                scheduled = false;
                if (!window.elementorFrontend || !window.jQuery) return;
                for (const definition of definitions.values()) {
                    for (const el of document.querySelectorAll('.elementor-element.' + definition.scope)) {
                        if (initialized.has(el)) continue;
                        const titles = [...el.querySelectorAll('.elementor-tab-title')];
                        // Wait for native initialization, then adjust its state once.
                        if (!titles.some(title => title.getAttribute('aria-expanded') === 'true')) continue;
                        initialized.add(el);
                        const desired = definition.effects.accordion.initial;
                        if (desired === null) {
                            const open = titles.find(title => title.getAttribute('aria-expanded') === 'true');
                            if (open) open.click();
                        } else if (titles[desired] && titles[desired].getAttribute('aria-expanded') !== 'true') {
                            titles[desired].click();
                        }
                    }
                }
            }
            function scheduleAccordion() {
                if (!scheduled) { scheduled = true; requestAnimationFrame(refreshAccordion); }
            }
            window.SuiteEdgePortableAccordion = {register(value) {
                for (const definition of value) {
                    if (definition.effects.accordion) definitions.set(definition.scope, definition);
                }
                scheduleAccordion();
            }};
            addEventListener('load', scheduleAccordion, {once: true});
            new MutationObserver(scheduleAccordion).observe(document.documentElement, {
                childList: true, subtree: true, attributes: true, attributeFilter: ['class', 'aria-expanded']
            });
        }
        window.SuiteEdgePortableAccordion.register(config);
    }
    if (window.SuiteEdgePortableEffects) { window.SuiteEdgePortableEffects.register(config); return; }
    const definitions = new Map(), records = new Map();
    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');
    let frame = 0, refreshFrame = 0;
    const sizes = typeof ResizeObserver === 'function' ? new ResizeObserver(schedule) : null;
    const own = (el, property) => [el.style.getPropertyValue(property), el.style.getPropertyPriority(property)];
    const restore = (el, property, value) => value[0] ? el.style.setProperty(property, value[0], value[1]) : el.style.removeProperty(property);
    function background(record, mode) {
        const el = record.el;
        if (record.clip) { record.clip.remove(); record.clip = null; }
        restore(el, 'background-image', record.image); restore(el, 'isolation', record.isolation);
        if (!mode || reduced.matches || (mode === 'fixed' && innerWidth <= record.definition.breakpoints.mobile)) { return; }
        const image = getComputedStyle(el).backgroundImage;
        if (!image || image === 'none') { return; }
        const clip = document.createElement('span'), layer = document.createElement('span');
        clip.className = 'suiteedge-parallax-clip'; clip.setAttribute('aria-hidden', 'true');
        clip.style.cssText = 'position:absolute;inset:0;overflow:hidden;pointer-events:none;z-index:-1;border-radius:inherit;';
        layer.className = 'suiteedge-parallax-layer';
        layer.style.cssText = 'position:absolute;bottom:0;left:0;width:100%;height:100%;background-size:cover;background-position:top center;background-repeat:no-repeat;pointer-events:none;';
        layer.style.backgroundImage = image;
        if (mode === 'fixed') { layer.style.backgroundAttachment = 'fixed'; }
        clip.appendChild(layer); el.prepend(clip);
        el.style.setProperty('background-image', 'none', 'important'); el.style.setProperty('isolation', 'isolate');
        record.clip = clip; record.layer = layer;
    }
    function device(definition) {
        return innerWidth <= definition.breakpoints.mobile ? 'mobile' : innerWidth <= definition.breakpoints.tablet ? 'tablet' : 'desktop';
    }
    function refresh() {
        refreshFrame = 0;
        for (const [el, record] of records) {
            if (!el.isConnected) { restore(el, 'background-image', record.image); restore(el, 'isolation', record.isolation); restore(el, 'translate', record.translate); if (record.clip) record.clip.remove(); if (sizes) sizes.unobserve(el); records.delete(el); }
        }
        for (const definition of definitions.values()) {
            for (const el of document.querySelectorAll('.elementor-element.' + definition.scope)) {
                let record = records.get(el);
                if (!record) {
                    record = {el, definition, image: own(el, 'background-image'), isolation: own(el, 'isolation'), translate: own(el, 'translate')};
                    records.set(el, record);
                    if (sizes) sizes.observe(el);
                }
                const effects = definition.effects, active = device(definition);
                if (effects.image_title) { const img = el.querySelector('img'); if (img) img.title = effects.image_title; }
                const mode = effects.parallax && effects.parallax[active];
                // Rebuild only for a breakpoint/preference/resize transition, not for our own DOM changes.
                const signature = String(mode) + ':' + reduced.matches + ':' + innerWidth + ':' + innerHeight;
                if (record.signature !== signature || (!record.clip && mode && !reduced.matches)) { record.signature = signature; background(record, mode); }
                record.mode = mode; record.curve = effects.horizontal && effects.horizontal[active];
            }
        }
        schedule();
    }
    function interpolate(curve, progress) {
        const [start, middleStart, middleEnd, end, first, middle, last] = curve;
        if (progress <= start) return first;
        if (progress >= end) return last;
        if (progress < middleStart) return first + (middle - first) * (progress - start) / (middleStart - start);
        if (progress <= middleEnd) return middle;
        return middle + (last - middle) * (progress - middleEnd) / (end - middleEnd);
    }
    function update() {
        frame = 0;
        for (const record of records.values()) {
            const el = record.el, rect = el.getBoundingClientRect();
            if (!rect.width || !rect.height) { restore(el, 'translate', record.translate); continue; }
            if (record.clip && record.mode === 'true') {
                record.layer.style.height = (el.clientHeight + innerHeight * 0.3) + 'px';
                record.layer.style.transform = 'translateY(' + (0.3 * (innerHeight - rect.top)) + 'px)';
            }
            if (record.curve && !reduced.matches && CSS.supports('translate', '1px 0')) {
                const trigger = record.definition.effects.trigger;
                const point = rect.top + (trigger === 'top' ? 0 : trigger === 'bottom' ? rect.height : rect.height / 2);
                const progress = Math.max(0, Math.min(100, 100 - point / innerHeight * 100));
                el.style.translate = Math.round(100 * interpolate(record.curve, progress)) + 'px 0';
            } else { restore(el, 'translate', record.translate); }
        }
    }
    function schedule() { if (!frame) frame = requestAnimationFrame(update); }
    function scheduleRefresh() { if (!refreshFrame) refreshFrame = requestAnimationFrame(refresh); }
    function register(value) {
        for (const definition of value) { definitions.set(definition.scope, definition); }
        scheduleRefresh();
    }
    window.SuiteEdgePortableEffects = {register};
    addEventListener('scroll', schedule, {passive: true}); addEventListener('resize', scheduleRefresh, {passive: true});
    addEventListener('load', scheduleRefresh, {once: true});
    if (reduced.addEventListener) reduced.addEventListener('change', scheduleRefresh); else reduced.addListener(scheduleRefresh);
    // Elementor reveals lazy backgrounds by adding a class after initial rendering.
    new MutationObserver(scheduleRefresh).observe(document.documentElement, {childList: true, subtree: true, attributes: true, attributeFilter: ['class']});
    register(config);
})
