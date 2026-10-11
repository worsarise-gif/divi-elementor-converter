/* Native WordPress menu markup; independent of older imported motion runtimes. */
(function (config) {
    'use strict';
    if (window.SuiteEdgePortableNavigation) { window.SuiteEdgePortableNavigation.register(config); return; }
    const definitions = new Map(), initialized = new WeakSet(), records = new Set();
    let scheduled = false, sequence = 0;
    function placeSubmenu(record, submenu) {
        submenu.style.removeProperty('left');
        if (record.mobile || getComputedStyle(submenu).display==='none') return;
        const bounds=submenu.getBoundingClientRect(), parent=submenu.parentElement.getBoundingClientRect();
        if (bounds.right>innerWidth-10) {
            const nested=submenu.parentElement.parentElement.classList.contains('sub-menu');
            submenu.style.left=(nested ? -bounds.width : innerWidth-10-bounds.width-parent.left)+'px';
        }
        const adjusted=submenu.getBoundingClientRect();
        if (adjusted.left<10) submenu.style.left=(parseFloat(getComputedStyle(submenu).left)+10-adjusted.left)+'px';
    }
    function closed(record) {
        record.panel.classList.remove('suiteedge-menu-open'); record.toggle.setAttribute('aria-expanded','false');
        for (const button of record.ul.querySelectorAll('.suiteedge-submenu-toggle')) {
            button.setAttribute('aria-expanded','false'); button.parentElement.classList.remove('suiteedge-submenu-open');
        }
        for (const submenu of record.ul.querySelectorAll('.sub-menu')) submenu.style.removeProperty('left');
    }
    function button(label, className) {
        const el = document.createElement('button'); el.type='button'; el.className=className;
        el.setAttribute('aria-label',label); el.setAttribute('aria-expanded','false'); return el;
    }
    function refresh() {
        scheduled=false;
        for (const record of records) {
            if (!record.ul.isConnected) { records.delete(record); continue; }
            const mobile=innerWidth<=record.breakpoint;
            if (mobile!==record.mobile) { closed(record); record.mobile=mobile; record.panel.classList.toggle('suiteedge-mobile-mode',mobile); }
            const outer=record.wrapper.closest('.suiteedge-menu-module');
            if (outer) {const bounds=outer.getBoundingClientRect(),panel=record.panel.getBoundingClientRect();record.panel.style.setProperty('--suiteedge-menu-left',(bounds.left-panel.left)+'px');record.panel.style.setProperty('--suiteedge-menu-width',bounds.width+'px');}
            for (const submenu of record.ul.querySelectorAll('.sub-menu')) placeSubmenu(record,submenu);
        }
        for (const definition of definitions.values()) {
            for (const wrapper of document.querySelectorAll('.elementor-element.'+definition.scope)) {
                const ul=wrapper.querySelector('ul.menu'); if (!ul||initialized.has(ul)) continue;
                initialized.add(ul); const panel=ul.parentElement;
                const toggle=button('Toggle navigation','suiteedge-menu-toggle'); toggle.textContent='☰';
                ul.id='suiteedge-navigation-'+(++sequence); toggle.setAttribute('aria-controls',ul.id);
                panel.insertBefore(toggle,ul); panel.classList.add('suiteedge-menu-ready');
                wrapper.closest('.suiteedge-menu-module')?.classList.add('suiteedge-navigation-initialized');
                if (panel.tagName!=='NAV') panel.setAttribute('role','navigation');
                panel.setAttribute('aria-label',definition.effects.navigation.label||'Main navigation');
                const record={panel,ul,toggle,wrapper,breakpoint:definition.effects.navigation.breakpoint,mobile:innerWidth<=definition.effects.navigation.breakpoint}; records.add(record);
                panel.classList.toggle('suiteedge-mobile-mode',record.mobile);
                toggle.addEventListener('click',()=>{const open=toggle.getAttribute('aria-expanded')!=='true';toggle.setAttribute('aria-expanded',String(open));panel.classList.toggle('suiteedge-menu-open',open);});
                for (const li of ul.querySelectorAll('li')) {
                    if (li.id) li.id='suiteedge-navigation-item-'+(++sequence);
                    const submenu=[...li.children].find(e=>e.tagName==='UL'),link=[...li.children].find(e=>e.tagName==='A');
                    if (!submenu||!link) continue;
                    const expand=button('Expand submenu: '+link.textContent.trim(),'suiteedge-submenu-toggle');expand.textContent='▾';
                    submenu.id='suiteedge-navigation-'+(++sequence); expand.setAttribute('aria-controls',submenu.id); li.insertBefore(expand,submenu);
                    const setOpen=open=>{expand.setAttribute('aria-expanded',String(open));li.classList.toggle('suiteedge-submenu-open',open);if(open)placeSubmenu(record,submenu);};
                    expand.addEventListener('click',()=>{li.classList.remove('suiteedge-menu-dismissed');setOpen(expand.getAttribute('aria-expanded')!=='true');});
                    li.addEventListener('pointerenter',()=>{if (!record.mobile) {li.classList.remove('suiteedge-menu-dismissed');setOpen(true);}});
                    li.addEventListener('pointerleave',()=>{if (!record.mobile&&!li.contains(document.activeElement)) setOpen(false);});
                    li.addEventListener('focusin',()=>{if (!record.mobile&&!li.classList.contains('suiteedge-menu-dismissed')) setOpen(true);});
                    li.addEventListener('focusout',()=>queueMicrotask(()=>{if (!record.mobile&&!li.contains(document.activeElement)) setOpen(false);}));
                }
                panel.addEventListener('keydown',event=>{
                    if (event.key!=='Escape') return;
                    event.preventDefault();
                    const li=event.target.closest('li.menu-item-has-children');
                    if (li) {const expand=[...li.children].find(e=>e.classList.contains('suiteedge-submenu-toggle'));li.classList.remove('suiteedge-submenu-open');li.classList.add('suiteedge-menu-dismissed');expand.setAttribute('aria-expanded','false');expand.focus();}
                    else if (record.mobile) {closed(record);toggle.focus();}
                });
                panel.addEventListener('click',event=>{if (record.mobile&&event.target.closest('a')) closed(record);});
                schedule();
            }
        }
    }
    function schedule() {if (!scheduled) {scheduled=true;requestAnimationFrame(refresh);}}
    window.SuiteEdgePortableNavigation={register(value){for (const definition of value) {if (definition.effects.navigation) definitions.set(definition.scope,definition);}schedule();}};
    addEventListener('resize',schedule,{passive:true});addEventListener('load',schedule,{once:true});
    document.addEventListener('click',event=>{for (const record of records) {if (!record.panel.contains(event.target)) closed(record);}});
    new MutationObserver(schedule).observe(document.documentElement,{childList:true,subtree:true});
    window.SuiteEdgePortableNavigation.register(config);
})
