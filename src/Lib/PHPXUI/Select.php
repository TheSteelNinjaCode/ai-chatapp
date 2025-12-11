<?php

declare(strict_types=1);

namespace Lib\PHPXUI;

use PP\MainLayout;
use PP\PHPX\PHPX;
use PP\StateManager;
use Lib\PHPXUI\Slot;
use Lib\PHPXUI\Portal;
use Lib\PPIcons\{Check, ChevronDown, ChevronUp};

class Select extends PHPX
{
    /** @property string|bool|null $open = {false}|{true}|* */
    public string|bool|null $open = '{false}';
    public ?string $onOpenChange = null;
    public ?string $value = '{null}';
    public ?string $onValueChange = null;
    public ?string $name = '';

    public ?string $class = '';
    public ?string $id    = null;
    public mixed $children = null;

    private string $portalId;

    public function __construct(array $props = [])
    {
        parent::__construct($props);
        $this->portalId = uniqid('select-portal-');
        $this->id = $this->props['id'] ?? uniqid('select-');
        StateManager::setState("phpxui.Select", [
            'portalId' => $this->portalId,
        ]);
    }

    public function render(): string
    {
        $attributes = $this->getAttributes([
            'data-slot'  => 'select',
            'data-state' => '{open ? "open" : "closed"}',
            'id'         => $this->id,
        ], ['name']);
        $class = $this->getMergeClasses($this->class);
        $selectHiddenInput = uniqid('select-hidden-input-');

        return <<<HTML
        <div class="{$class}" {$attributes}>
            <input type="hidden" id="{$selectHiddenInput}" name="{$this->name}" />
            {$this->children}

            <script>
                const [openValue, setOpenValue]   = pp.state(false);
                const [valueValue, setValueValue] = pp.state(null);

                const select  = document.getElementById('{$this->id}');
                const trigger = select.querySelector('[data-slot="select-trigger"]');
                const hidden  = select.querySelector('[data-slot="select-hidden"]');
                const valueEl = select.querySelector('[data-slot="select-value"]');

                const portal   = document.getElementById('{$this->portalId}');
                const content  = () => portal ? portal.querySelector('[data-slot="select-content"]') : null;
                const scrollEl = () => { const c = content(); return c ? c.querySelector('[data-slot="select-scroll"]') : null; };
                const upBtn    = () => { const c = content(); return c ? c.querySelector('[data-slot="select-scroll-up-button"]') : null; };
                const downBtn  = () => { const c = content(); return c ? c.querySelector('[data-slot="select-scroll-down-button"]') : null; };

                const selectOpenAttribute = select.getAttribute('open');

                const readOpen  = () => (selectOpenAttribute !== null) ? !!open : !!openValue;
                const writeOpen = (v) => {
                    if (selectOpenAttribute !== null) {
                        if (typeof onOpenChange !== 'undefined') onOpenChange(!!v);
                    } else {
                        setOpenValue(!!v);
                    }
                };

                const normalizeValue = (v) => {
                    if (v === '' || v === undefined) return null;
                    return v ?? null;
                };

                const readValue  = () => {
                    if (typeof value !== 'undefined' && !(value instanceof Element)) {
                        return normalizeValue(value); // 🔹
                    }
                    return normalizeValue(valueValue); // 🔹
                };

                const writeValue = (v) => {
                    const nv = normalizeValue(v); // 🔹
                    if (hidden) hidden.value = (nv == null ? '' : String(nv));
                    if (typeof onValueChange !== 'undefined') onValueChange(nv);
                    else setValueValue(nv);
                };

                let activeIndex = -1;

                const itemsAll = () => {
                    const c = content(); if (!c) return [];
                    return Array.from(c.querySelectorAll('[data-slot="select-item"]'));
                };
                const isDisabled = (el) => (el?.getAttribute('data-disabled') === 'true' || el?.getAttribute('aria-disabled') === 'true');

                const indexOfItem = (el) => itemsAll().indexOf(el);
                const indexOfEnabledByValue = (v) => {
                    const list = itemsAll();
                    for (let i=0;i<list.length;i++) {
                        const el = list[i];
                        if (isDisabled(el)) continue;
                        if (String(el.getAttribute('data-value')) === String(v)) return i;
                    }
                    return -1;
                };
                const firstEnabledIndex = () => {
                    const list = itemsAll();
                    for (let i=0;i<list.length;i++) if (!isDisabled(list[i])) return i;
                    return -1;
                };
                const lastEnabledIndex = () => {
                    const list = itemsAll();
                    for (let i=list.length-1;i>=0;i--) if (!isDisabled(list[i])) return i;
                    return -1;
                };

                const clamp = (v, a, b) => Math.max(a, Math.min(v, b));

                const resolveTransformOrigin = (side, align) => {
                    let x = 'center', y = 'center';
                    if (side === 'top')    y = 'bottom';
                    if (side === 'bottom') y = 'top';
                    if (side === 'left')   x = 'right';
                    if (side === 'right')  x = 'left';

                    if (side === 'top' || side === 'bottom') {
                        if (align === 'start') x = 'left';
                        if (align === 'end')   x = 'right';
                    } else {
                        if (align === 'start') y = 'top';
                        if (align === 'end')   y = 'bottom';
                    }
                    return y + ' ' + x;
                };

                const restartDirectionalAnimation = (el) => {
                    const prevAnim = el.style.animation;
                    const prevTran = el.style.transition;
                    el.style.animation = 'none';
                    el.style.transition = 'none';
                    void el.offsetWidth;
                    el.style.animation = prevAnim || '';
                    el.style.transition = prevTran || '';
                };

                const ensureItemVisible = (el) => {
                    const s = scrollEl(); if (!s || !el) return;
                    const sRect = s.getBoundingClientRect();
                    const iRect = el.getBoundingClientRect();

                    const upH = (upBtn() && upBtn().offsetHeight) || 0;
                    const dnH = (downBtn() && downBtn().offsetHeight) || 0;
                    const gap = 4;

                    const topLimit = sRect.top + upH + gap;
                    const botLimit = sRect.bottom - dnH - gap;

                    if (iRect.top < topLimit) {
                        s.scrollTop -= (topLimit - iRect.top);
                    } else if (iRect.bottom > botLimit) {
                        s.scrollTop += (iRect.bottom - botLimit);
                    }
                    updateScrollButtons();

                    const idx = indexOfItem(el);
                    const firstIdx = firstEnabledIndex();
                    const lastIdx  = lastEnabledIndex();
                    const maxTop   = Math.max(0, s.scrollHeight - s.clientHeight);
                    if (idx === firstIdx) {
                        s.scrollTop = 0;
                        const up = upBtn(); if (up) { up.style.opacity = '0'; up.style.pointerEvents = 'none'; }
                    } else if (idx === lastIdx) {
                        s.scrollTop = maxTop;
                        const dn = downBtn(); if (dn) { dn.style.opacity = '0'; dn.style.pointerEvents = 'none'; }
                    }
                };

                const clearHighlights = () => {
                    itemsAll().forEach(it => it.setAttribute('data-highlighted','false'));
                };

                const setActiveIndex = (i, forceScroll=true) => {
                    const list = itemsAll();
                    if (!list.length) { activeIndex = -1; return; }
                    i = clamp(i, 0, list.length - 1);

                    const dir = (activeIndex >= 0 && i < activeIndex) ? -1 : 1;
                    let j = i;
                    while (j >= 0 && j < list.length && isDisabled(list[j])) j += dir;
                    if (j < 0 || j >= list.length) {
                        j = i;
                        const dir2 = -dir;
                        while (j >= 0 && j < list.length && isDisabled(list[j])) j += dir2;
                        if (j < 0 || j >= list.length) { return; }
                    }

                    activeIndex = j;
                    clearHighlights();
                    const el = list[activeIndex];
                    el.setAttribute('data-highlighted','true');
                    if (forceScroll) ensureItemVisible(el);
                };

                const moveActive = (delta) => {
                    const list = itemsAll(); if (!list.length) return;
                    let i = (activeIndex < 0) ? firstEnabledIndex() : activeIndex + delta;
                    setActiveIndex(i, true);
                };

                const selectActive = () => {
                    const list = itemsAll(); if (activeIndex < 0 || activeIndex >= list.length) return;
                    const el = list[activeIndex];
                    if (isDisabled(el)) return;
                    const raw = el.getAttribute('data-value');
                    const val = (raw === '' || raw === 'null' || raw === null) ? null : raw;
                    writeValue(val);
                    syncSelectionUI();
                    renderTriggerValue(val);
                    const idx = indexOfItem(el);
                    if (idx >= 0) setActiveIndex(idx, true);
                    writeOpen(false);
                };

                const readPlacement = (el, fallbackOffset = 4) => {
                    if (!el) {
                        return { side: 'bottom', align: 'start', offset: fallbackOffset };
                    }
                    if (!el.dataset.prefSide)  el.dataset.prefSide  = el.getAttribute('data-side')  || 'bottom';
                    if (!el.dataset.prefAlign) el.dataset.prefAlign = el.getAttribute('data-align') || 'start';
                    const off    = Number.parseFloat(el.getAttribute('data-offset') || '');
                    const offset = Number.isFinite(off) ? off : fallbackOffset;
                    return { side: el.dataset.prefSide, align: el.dataset.prefAlign, offset };
                };

                const positionToAnchor = (floating, anchor, fallbackOffset = 4) => {
                    if (!floating || !anchor) return;
                    if (floating.getAttribute('data-state') !== 'open') return;

                    const rect = anchor.getBoundingClientRect();

                    const vv = window.visualViewport;
                    const vw = vv ? vv.width  : document.documentElement.clientWidth;
                    const vh = vv ? vv.height : document.documentElement.clientHeight;
                    const vx = vv ? vv.offsetLeft : 0;
                    const vy = vv ? vv.offsetTop  : 0;

                    const { side:prefSide, align:prefAlign, offset } = readPlacement(floating, fallbackOffset);

                    const prevSide  = floating.getAttribute('data-side');
                    const prevAlign = floating.getAttribute('data-align');

                    const sEl = scrollEl();
                    if (sEl) {
                        sEl.style.minWidth = Math.floor(rect.width) + 'px';
                        sEl.style.maxHeight = 'none';
                        sEl.style.height = 'auto';
                        sEl.style.overflowY = 'visible';
                        sEl.style.webkitOverflowScrolling = '';
                    }
                    floating.style.overflowX = 'hidden';
                    floating.style.overflowY = 'visible';

                    const naturalH = floating.scrollHeight;
                    const cw = floating.offsetWidth;
                    let ch = naturalH;

                    const spaceBelow = Math.max(0, (vy + vh) - rect.bottom - offset - 4);
                    const spaceAbove = Math.max(0, rect.top - vy - offset - 4);
                    const needsScrollBottom = naturalH > spaceBelow;
                    const needsScrollTop    = naturalH > spaceAbove;
                    const needsScroll       = needsScrollBottom || needsScrollTop;

                    let side = prefSide;

                    if (sEl && (prefSide === 'bottom' || prefSide === 'top') && needsScroll) {
                        const triggerMid = rect.top + rect.height / 2;
                        const screenMid  = vy + vh / 2;
                        const hysteresis = 8;
                        side = (triggerMid < (screenMid - hysteresis)) ? 'bottom' : 'top';
                    } else {
                        const vwSpaceRight = (vx + vw) - rect.right;
                        if (prefSide === 'bottom' && ((vy + vh) - rect.bottom) < ch + offset && (rect.top - vy) >= ch + offset) side = 'top';
                        if (prefSide === 'top'    && (rect.top - vy) < ch + offset && ((vy + vh) - rect.bottom) >= ch + offset) side = 'bottom';
                        if (prefSide === 'right'  && vwSpaceRight < cw + offset && (rect.left - vx)   >= cw + offset) side = 'left';
                        if (prefSide === 'left'   && (rect.left - vx)   < cw + offset && vwSpaceRight >= cw + offset) side = 'right';
                    }

                    const availableH = (side === 'bottom')
                        ? Math.max(0, (vy + vh) - rect.bottom - offset - 4)
                        : (side === 'top')
                            ? Math.max(0, (rect.top - vy) - offset - 4)
                            : Math.max(Math.max(0, (rect.top - vy) - offset - 4) + rect.height + Math.max(0, (vy + vh) - rect.bottom - offset - 4), vh - 8);

                    if (naturalH > availableH) {
                        ch = availableH;
                        if (sEl) {
                            sEl.style.maxHeight = ch + 'px';
                            sEl.style.overflowY = 'auto';
                            sEl.style.webkitOverflowScrolling = 'touch';
                        } else {
                            floating.style.height = ch + 'px';
                            floating.style.overflowY = 'auto';
                        }
                    } else {
                        if (sEl) {
                            sEl.style.maxHeight = 'none';
                            sEl.style.overflowY = 'hidden';
                            sEl.style.webkitOverflowScrolling = '';
                        } else {
                            floating.style.height = 'auto';
                            floating.style.overflowY = 'hidden';
                        }
                    }

                    let top = 0, left = 0;
                    if (side === 'top')    top  = rect.top    - ch - offset;
                    if (side === 'bottom') top  = rect.bottom + offset;
                    if (side === 'left')   left = rect.left   - cw - offset;
                    if (side === 'right')  left = rect.right  + offset;

                    if (side === 'top' || side === 'bottom') {
                        if      (prefAlign === 'start') left = rect.left;
                        else if (prefAlign === 'end')   left = rect.right - cw;
                        else                            left = rect.left + rect.width/2 - cw/2;
                    } else {
                        if      (prefAlign === 'start') top = rect.top;
                        else if (prefAlign === 'end')   top = rect.bottom - ch;
                        else                            top = rect.top + rect.height / 2 - ch / 2;
                    }

                    const minL = vx + 4, maxL = vx + vw - cw - 4;
                    const minT = vy + 4, maxT = vy + vh - ch - 4;
                    left = clamp(left, minL, Math.max(minL, maxL));
                    top  = clamp(top,  minT, Math.max(minT, maxT));

                    floating.style.position = 'fixed';
                    floating.style.left = Math.round(left) + 'px';
                    floating.style.top  = Math.round(top)  + 'px';

                    floating.setAttribute('data-resolved-side', side);
                    floating.setAttribute('data-resolved-align', prefAlign);
                    floating.setAttribute('data-side', side);
                    floating.setAttribute('data-align', prefAlign);
                    floating.style.transformOrigin = resolveTransformOrigin(side, prefAlign);

                    if (select.getAttribute('data-state') === 'open' && (prevSide !== side || prevAlign !== prefAlign)) {
                        restartDirectionalAnimation(floating);
                    }
                };

                const primeScrollButtons = () => {
                    const up = upBtn(), dn = downBtn();
                    [up, dn].forEach(btn => {
                        if (!btn) return;
                        const prevTr = btn.style.transition;
                        const prevPE = btn.style.pointerEvents;
                        const prevOp = btn.style.opacity;
                        btn.style.transition = 'none';
                        btn.style.pointerEvents = 'none';
                        btn.style.opacity = '0.001';
                        void btn.offsetHeight;
                        btn.style.opacity = prevOp || '0';
                        btn.style.transition = prevTr || '';
                        btn.style.pointerEvents = prevPE || '';
                    });
                };

                // ==== SCROLL LOCK GLOBAL (MISMO PATRÓN QUE DROPDOWN) ====
                (function initScrollLock(){
                    if (window.__ppScrollLock) return;
                    window.__ppScrollLock = {
                        locks: 0,
                        scrollTop: 0,
                        initialPadRight: '',
                    };
                })();

                function getScrollbarWidth() {
                    const docEl = document.documentElement;
                    return window.innerWidth - docEl.clientWidth;
                }

                function lockScroll() {
                    const state = window.__ppScrollLock;
                    if (state.locks === 0) {
                        state.scrollTop = window.scrollY || document.documentElement.scrollTop || 0;

                        const docEl = document.documentElement;
                        state.initialPadRight = docEl.style.paddingRight || '';
                        const sw = getScrollbarWidth();
                        if (sw > 0) docEl.style.paddingRight = `\${sw}px`;

                        docEl.style.overflow = 'hidden';
                        document.body.style.position = 'fixed';
                        document.body.style.top = `-\${state.scrollTop}px`;
                        document.body.style.left = '0';
                        document.body.style.right = '0';
                        document.body.style.width = '100%';
                    }
                    state.locks++;
                }

                function unlockScroll() {
                    const state = window.__ppScrollLock;
                    if (!state || state.locks === 0) return;
                    state.locks--;
                    if (state.locks === 0) {
                        const docEl = document.documentElement;
                        docEl.style.overflow = '';
                        docEl.style.paddingRight = state.initialPadRight || '';
                        document.body.style.position = '';
                        document.body.style.top = '';
                        document.body.style.left = '';
                        document.body.style.right = '';
                        document.body.style.width = '';
                        window.scrollTo(0, state.scrollTop || 0);
                    }
                }

                let wheelBlocker = null;
                let touchBlocker = null;

                function isEventInsideSelect(target) {
                    if (!target) return false;
                    const c = content();
                    if (!c) return false;
                    const root = target.closest?.('[data-slot="select-content"], [data-slot="select-scroll"]');
                    return !!root;
                }

                function enableScrollEventBlockers() {
                    if (wheelBlocker || touchBlocker) return;

                    wheelBlocker = (e) => {
                        if (!isEventInsideSelect(e.target)) {
                            e.preventDefault();
                        }
                    };

                    touchBlocker = (e) => {
                        if (!isEventInsideSelect(e.target)) {
                            e.preventDefault();
                        }
                    };

                    document.addEventListener('wheel', wheelBlocker, { passive: false });
                    document.addEventListener('touchmove', touchBlocker, { passive: false });
                }

                function disableScrollEventBlockers() {
                    if (wheelBlocker) {
                        document.removeEventListener('wheel', wheelBlocker);
                        wheelBlocker = null;
                    }
                    if (touchBlocker) {
                        document.removeEventListener('touchmove', touchBlocker);
                        touchBlocker = null;
                    }
                }

                let isClosing = false;

                const openSelect = () => {
                    isClosing = false;
                    select.setAttribute('data-state','open');
                    const c = content();
                    if (portal) portal.hidden = false;
                    if (c) {
                        c.style.display = 'block';
                        c.setAttribute('data-state','open');
                        c.style.pointerEvents = 'auto';
                        c.style.willChange = '';
                        c.style.backfaceVisibility = '';
                        c.style.transform = '';
                    }
                    requestAnimationFrame(() => {
                        const c2 = content();
                        if (c2) {
                            c2.style.display = '';
                            positionToAnchor(c2, trigger, 4);
                            primeScrollButtons();
                            syncSelectionUI();
                            updateScrollButtons();
                            const v = readValue();
                            let idx = (v != null) ? indexOfEnabledByValue(v) : firstEnabledIndex();
                            if (idx < 0) idx = firstEnabledIndex();
                            setActiveIndex(idx, true);
                            bindItemHover();
                        }
                        renderTriggerValue(readValue());
                    });
                    bindLive();
                    lockScroll();
                    enableScrollEventBlockers();
                };

                const closeSelect = () => {
                    if (isClosing) return;
                    isClosing = true;

                    select.setAttribute('data-state','closed');
                    const c = content();
                    const s = scrollEl();
                    if (s) { s.style.overflowY = 'hidden'; }
                    const up = upBtn(), dn = downBtn();
                    if (up) { up.style.opacity = '0'; up.style.pointerEvents = 'none'; }
                    if (dn) { dn.style.opacity = '0'; dn.style.pointerEvents = 'none'; }

                    if (!c) {
                        if (portal) portal.hidden = true;
                        if (s) s.style.overflowY = '';
                        clearHighlights();
                        activeIndex = -1;
                        isClosing = false;
                        unlockScroll();
                        disableScrollEventBlockers();
                        return;
                    }

                    c.style.display = 'block';
                    c.style.pointerEvents = 'none';
                    c.setAttribute('data-state','closed');

                    c.style.willChange = 'transform, opacity';
                    c.style.backfaceVisibility = 'hidden';
                    c.style.transform = 'translateZ(0)';

                    void c.offsetHeight;

                    let done = false;
                    const finish = () => {
                        if (done) return; done = true;
                        c.removeEventListener('animationend', finish);
                        c.removeEventListener('transitionend', finish);

                        const stillOpen = (select.getAttribute('data-state') === 'open');

                        c.style.display = '';
                        c.style.pointerEvents = '';
                        c.style.willChange = '';
                        c.style.backfaceVisibility = '';
                        c.style.transform = '';
                        if (s) s.style.overflowY = '';

                        unlockScroll();
                        disableScrollEventBlockers();

                        if (!stillOpen) {
                            if (portal) portal.hidden = true;
                            clearHighlights();
                            activeIndex = -1;
                        }

                        isClosing = false;
                    };
                    c.addEventListener('animationend', finish, { once:true });
                    c.addEventListener('transitionend', finish, { once:true });
                    setTimeout(finish, 220);

                    unbindLive();
                };

                const onOutside = (e) => {
                    if (select.getAttribute('data-state') !== 'open') return;
                    const t = e.target;
                    const c = content();
                    const insideTrigger = trigger && t && trigger.contains(t);
                    const insideContent = c && t && c.contains(t);
                    if (!insideTrigger && !insideContent) writeOpen(false);
                };

                if (trigger) {
                    trigger.addEventListener('click', () => {
                        if (selectOpenAttribute !== null) {
                            if (typeof onOpenChange !== 'undefined') onOpenChange(!open);
                        } else {
                            setOpenValue(v => !v);
                        }
                    });
                }

                const onPortalClick = (e) => {
                    const item = e.target.closest ? e.target.closest('[data-slot="select-item"]') : null;
                    if (!item || isDisabled(item)) return;
                    const raw = item.getAttribute('data-value');
                    const val = (raw === '' || raw === 'null' || raw === null) ? null : raw;
                    writeValue(val);
                    syncSelectionUI();
                    renderTriggerValue(val);
                    const idx = indexOfItem(item);
                    if (idx >= 0) setActiveIndex(idx, true);
                    writeOpen(false);
                };

                const findItemByValue = (v) => {
                    if (v == null) return null;
                    const q = `[data-slot="select-item"][data-value="\${String(v)}"]`;
                    let el = portal ? portal.querySelector(q) : null;
                    if (!el) el = select ? select.querySelector(q) : null;
                    return el;
                };
                const getItemLabel = (item) => {
                    const node = item ? (item.querySelector('[data-slot="select-item-text"]') || item) : null;
                    if (!node) return { html:null, text:null };
                    const rich = !!(node.innerHTML && node.innerHTML.indexOf('<') !== -1);
                    return rich ? { html: node.innerHTML, text: null } : { html: null, text: (node.textContent || '').trim() };
                };
                
                const renderTriggerValue = (v) => {
                    if (!valueEl) return;
                    
                    if (v == null || v === '') {
                        const ph = valueEl.getAttribute('data-placeholder') || '';
                        valueEl.textContent = ph;
                        if (trigger) trigger.removeAttribute('data-label');
                        return;
                    }

                    const it = findItemByValue(v);
                    if (it) {
                        const label = getItemLabel(it);
                        if (label.html !== null) { valueEl.innerHTML = label.html; if (trigger) trigger.setAttribute('data-label', label.html); }
                        else { valueEl.textContent = label.text || String(v); if (trigger) trigger.setAttribute('data-label', label.text || String(v)); }
                    } else {
                        const cached = trigger ? trigger.getAttribute('data-label') : null;
                        if (cached) { if (cached.indexOf('<') !== -1) valueEl.innerHTML = cached; else valueEl.textContent = cached; }
                        else valueEl.textContent = String(v);
                    }
                };

                const syncSelectionUI = () => {
                    const v = readValue();
                    const c = content();
                    if (!c) return;
                    const items = Array.from(c.querySelectorAll('[data-slot="select-item"]'));
                    items.forEach(it => {
                        const iv = it.getAttribute('data-value');
                        const isSel = (v != null && v !== '' && iv === String(v));
                        it.setAttribute('aria-selected', isSel ? 'true' : 'false');
                        it.setAttribute('data-selected', isSel ? 'true' : 'false');
                        const indicator = it.querySelector('[data-slot="select-item-indicator"]');
                        if (indicator) indicator.style.opacity = isSel ? '1' : '0';
                    });
                };

                const bindItemHover = () => {
                    const list = itemsAll();
                    list.forEach((el, idx) => {
                        if (el._phpxuiHover) el.removeEventListener('mouseenter', el._phpxuiHover);
                        el._phpxuiHover = () => setActiveIndex(idx, false);
                        el.addEventListener('mouseenter', el._phpxuiHover);
                    });
                };

                let hoverDir = null, autoRAF = 0, sbBound = false;

                const setBtnVisible = (btn, visible, instant=false) => {
                    if (!btn) return;
                    if (instant) {
                        const prev = btn.style.transition;
                        btn.style.transition = 'none';
                        btn.style.opacity = visible ? '1' : '0';
                        btn.style.pointerEvents = visible ? 'auto' : 'none';
                        void btn.offsetHeight;
                        btn.style.transition = prev || '';
                        return;
                    }
                    btn.style.opacity = visible ? '1' : '0';
                    btn.style.pointerEvents = visible ? 'auto' : 'none';
                };

                const isAtTop = (s) => s.scrollTop <= 1;
                const isAtBottom = (s) => {
                    const maxTop = Math.max(0, s.scrollHeight - s.clientHeight);
                    return Math.abs(s.scrollTop - maxTop) <= 1;
                };

                const updateScrollButtons = () => {
                    const s = scrollEl(); if (!s) return;
                    const up = upBtn(), dn = downBtn();
                    const atTop = isAtTop(s);
                    const atBottom = isAtBottom(s);
                    setBtnVisible(up, !atTop, atTop);
                    setBtnVisible(dn, !atBottom, atBottom);
                };

                const stopAutoScroll = () => {
                    hoverDir = null;
                    if (autoRAF) cancelAnimationFrame(autoRAF);
                    autoRAF = 0;
                    updateScrollButtons();
                };

                const startAutoScroll = (dir) => {
                    const s = scrollEl(); if (!s) return;
                    stopAutoScroll();
                    hoverDir = dir;
                    const STEP = 9;
                    const tick = () => {
                        const maxTop = Math.max(0, s.scrollHeight - s.clientHeight);
                        if (dir === 'down') s.scrollTop = Math.min(maxTop, s.scrollTop + STEP);
                        else s.scrollTop = Math.max(0, s.scrollTop - STEP);
                        const atEnd = (dir === 'down') ? isAtBottom(s) : isAtTop(s);
                        if (atEnd) {
                            stopAutoScroll();
                            const btn = (dir === 'down') ? downBtn() : upBtn();
                            setBtnVisible(btn, false, true);
                            return;
                        }
                        updateScrollButtons();
                        autoRAF = requestAnimationFrame(tick);
                    };
                    updateScrollButtons();
                    autoRAF = requestAnimationFrame(tick);
                };

                const bindScrollButtons = () => {
                    if (sbBound) return; sbBound = true;
                    const s = scrollEl(); const up = upBtn(); const dn = downBtn();
                    if (s) {
                        s.addEventListener('scroll', updateScrollButtons, { passive:true });
                        s.addEventListener('mouseenter', updateScrollButtons);
                        s.addEventListener('mouseleave', () => { stopAutoScroll(); updateScrollButtons(); });
                    }
                    if (up) {
                        up.addEventListener('mouseenter', () => startAutoScroll('up'));
                        up.addEventListener('mouseleave', stopAutoScroll);
                        up.addEventListener('touchstart', (e) => { e.preventDefault(); startAutoScroll('up'); }, { passive:false });
                        up.addEventListener('touchend', stopAutoScroll);
                    }
                    if (dn) {
                        dn.addEventListener('mouseenter', () => startAutoScroll('down'));
                        dn.addEventListener('mouseleave', stopAutoScroll);
                        dn.addEventListener('touchstart', (e) => { e.preventDefault(); startAutoScroll('down'); }, { passive:false });
                        dn.addEventListener('touchend', stopAutoScroll);
                    }
                };
                const unbindScrollButtons = () => {
                    stopAutoScroll();
                    sbBound = false;
                };

                let bound = false, raf = 0;
                const resched = () => {
                    if (raf) cancelAnimationFrame(raf);
                    raf = requestAnimationFrame(() => {
                        const c = content();
                        if (c && select.getAttribute('data-state') === 'open') {
                            positionToAnchor(c, trigger, 4);
                            updateScrollButtons();
                            const list = itemsAll();
                            if (activeIndex >= 0 && activeIndex < list.length) ensureItemVisible(list[activeIndex]);
                            bindItemHover();
                        }
                    });
                };
                const bindLive = () => {
                    if (bound) return; bound = true;
                    document.addEventListener('pointerdown', onOutside, true);
                    if (portal) portal.addEventListener('click', onPortalClick);
                    window.addEventListener('resize', resched);
                    window.addEventListener('scroll', resched, { passive:true });
                    if (window.visualViewport) {
                        window.visualViewport.addEventListener('resize', resched);
                        window.visualViewport.addEventListener('scroll', resched, { passive:true });
                    }
                    bindScrollButtons();
                };
                const unbindLive = () => {
                    if (!bound) return; bound = false;
                    document.removeEventListener('pointerdown', onOutside, true);
                    if (portal) portal.removeEventListener('click', onPortalClick);
                    window.removeEventListener('resize', resched);
                    window.removeEventListener('scroll', resched);
                    if (window.visualViewport) {
                        window.visualViewport.removeEventListener('resize', resched);
                        window.visualViewport.removeEventListener('scroll', resched);
                    }
                    unbindScrollButtons();
                };

                select.addEventListener('keydown', (e) => {
                    const isOpen = (select.getAttribute('data-state') === 'open');
                    if (e.key === 'Escape' && isOpen) {
                        e.preventDefault();
                        writeOpen(false);
                        return;
                    }
                    if (!isOpen) return;

                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        if (activeIndex < 0) setActiveIndex(firstEnabledIndex(), true);
                        else moveActive(1);
                        return;
                    }
                    if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        if (activeIndex < 0) setActiveIndex(lastEnabledIndex(), true);
                        else moveActive(-1);
                        return;
                    }
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        selectActive();
                        return;
                    }
                    if (e.key === 'Home') {
                        e.preventDefault();
                        setActiveIndex(firstEnabledIndex(), true);
                        return;
                    }
                    if (e.key === 'End') {
                        e.preventDefault();
                        setActiveIndex(lastEnabledIndex(), true);
                        return;
                    }
                });

                const initFromHidden = () => {
                    const hv = hidden ? hidden.value : null;
                    if (typeof value === 'undefined' && hv && hv !== 'null') {
                        setValueValue(hv);
                    }
                    syncSelectionUI();
                    renderTriggerValue(readValue());
                };
                initFromHidden();

                function setHiddenInput() {
                    const hiddenInput = document.getElementById('{$selectHiddenInput}');
                    if (hiddenInput) {
                        hiddenInput.value = hidden ? hidden.value : '';
                    }
                }

                pp.effect(() => {
                    if (selectOpenAttribute !== null) {
                        if (open) { openSelect(); } else { closeSelect(); }
                    } else {
                        if (openValue) { openSelect(); } else { closeSelect(); }
                    }

                    setHiddenInput();
                }, [open, openValue]);

                pp.effect(() => {
                    const v = readValue();
                    if (hidden) hidden.value = (v == null ? '' : String(v));
                    syncSelectionUI();
                    renderTriggerValue(v);

                    if (select.getAttribute('data-state') === 'open') {
                        let idx = (v != null) ? indexOfEnabledByValue(v) : firstEnabledIndex();
                        if (idx < 0) idx = firstEnabledIndex();
                        setActiveIndex(idx, true);
                        bindItemHover();
                    }

                    setHiddenInput();
                }, [value, valueValue]);
            </script>
        </div>
        HTML;
    }
}

class SelectTrigger extends PHPX
{
    /** @property ?bool $asChild = {false}|{true} */
    public ?bool $asChild = false;
    public ?string $name  = null;

    /** @property ?string $size = default|sm|lg|icon */
    public ?string $size = 'default';
    public ?string $class = '';
    public mixed $children = null;

    public function render(): string
    {
        $attributes = $this->getAttributes([
            'data-slot'     => 'select-trigger',
            'type'          => 'button',
            'role'          => 'combobox',
            'aria-haspopup' => 'listbox',
            'data-size'     => $this->size,
        ]);
        $class = $this->getMergeClasses("border-input data-[placeholder]:text-muted-foreground [&_svg:not([class*='text-'])]:text-muted-foreground focus-visible:border-ring focus-visible:ring-ring/50 aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive dark:bg-input/30 dark:hover:bg-input/50 flex w-fit items-center justify-between gap-2 rounded-md border bg-transparent px-3 py-2 text-sm whitespace-nowrap shadow-xs transition-[color,box-shadow] outline-none focus-visible:ring-[3px] disabled:cursor-not-allowed disabled:opacity-50 data-[size=default]:h-9 data-[size=sm]:h-8 [&_svg]:pointer-events-none [&_svg]:shrink-0 [&_svg:not([class*='size-'])]:size-4", $this->class);

        $hiddenName = (string)($this->name ?? '');

        if ($this->asChild) {
            $slot = new Slot([
                'class'     => $class,
                'data-slot' => 'select-trigger',
                'asChild'   => true,
                ...$this->attributesArray,
            ]);
            $slot->children = $this->children;
            return $slot->render();
        }

        return <<<HTML
        <div>
            <input data-slot="select-hidden" type="hidden" name="{$hiddenName}" value="" />
            <button {$attributes} class="{$class}">
                {$this->children}
                <ChevronDown class="size-4 opacity-50 transition-transform data-[state=open]:rotate-180" />
            </button>
        </div>
        HTML;
    }
}

class SelectContent extends PHPX
{
    /** @property ?string $side = top|right|bottom|left */
    public ?string $side = 'bottom';
    /** @property ?string $align = start|center|end */
    public ?string $align = 'start';
    /** @property int|string|null $sideOffset = px */
    public int|string|null $sideOffset = 4;

    /** @property ?bool $disablePortal = {false}|{true} */
    public ?bool $disablePortal = false;
    public ?string $class = '';
    public mixed $children = null;

    public function render(): string
    {
        $attributes = $this->getAttributes([
            'data-slot'   => 'select-content',
            'role'        => 'listbox',
            'tabindex'    => '-1',
            'data-state'  => 'closed',
            'data-side'   => $this->side,
            'data-align'  => $this->align,
            'data-offset' => $this->sideOffset,
        ]);
        $class = $this->getMergeClasses("fixed z-50 bg-popover text-popover-foreground hidden pointer-events-none data-[state=open]:pointer-events-auto data-[state=open]:block data-[state=closed]:hidden data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95 data-[side=bottom]:slide-in-from-top-2 data-[side=left]:slide-in-from-right-2 data-[side=right]:slide-in-from-left-2 data-[side=top]:slide-in-from-bottom-2 rounded-md border shadow-md min-w-[8rem]", $this->class);

        $children = $this->children;
        $patternUp   = '/<div[^>]*data-slot="select-scroll-up-button"[^>]*>.*?<\/div>/si';
        $patternDown = '/<div[^>]*data-slot="select-scroll-down-button"[^>]*>.*?<\/div>/si';

        $customUp = null;
        if (preg_match($patternUp, $children, $m)) {
            $customUp = $m[0];
            $children = preg_replace($patternUp, '', $children, 1);
        }
        $customDown = null;
        if (preg_match($patternDown, $children, $m)) {
            $customDown = $m[0];
            $children = preg_replace($patternDown, '', $children, 1);
        }

        $up   = $customUp  ?? (new SelectScrollUpButton())->render();
        $down = $customDown ?? (new SelectScrollDownButton())->render();

        $inner = <<<HTML
        <div>
            <div class="{$class}" {$attributes} style="outline:none; will-change: transform, opacity; backface-visibility: hidden; transform: translateZ(0);">
                {$up}
                <div data-slot="select-scroll" class="p-1 scroll-my-1 overflow-y-auto [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden">
                    {$children}
                </div>
                {$down}
            </div>
        </div>
        HTML;

        if ($this->props['disablePortal'] ?? false) {
            return $inner;
        }

        $selectProps = StateManager::getState("phpxui.Select");
        $portalId    = $selectProps['portalId'] ?? uniqid('select-portal-');

        $portal = new Portal([
            'to'       => ($this->props['portalTo'] ?? 'body'),
            'children' => $inner,
            'id'       => $portalId,
            'hidden'   => 'true',
        ]);

        return $portal->render();
    }
}

class SelectGroup extends PHPX
{
    public ?string $class = '';
    public mixed $children = null;

    public function render(): string
    {
        $attributes = $this->getAttributes([
            'data-slot' => 'select-group'
        ]);
        $class = $this->getMergeClasses('py-1', $this->class);

        return <<<HTML
        <div class="{$class}" {$attributes}>
            {$this->children}
        </div>
        HTML;
    }
}

class SelectLabel extends PHPX
{
    public ?string $class = '';
    public mixed $children = null;

    public function render(): string
    {
        $attributes = $this->getAttributes([
            'data-slot' => 'select-label'
        ]);
        $class = $this->getMergeClasses('text-muted-foreground px-2 py-1.5 text-xs', $this->class);

        return <<<HTML
        <div class="{$class}" {$attributes}>
            {$this->children}
        </div>
        HTML;
    }
}

class SelectItem extends PHPX
{
    public ?string $value = '';
    /** @property ?bool $disabled = false|true */
    public ?bool $disabled = false;
    public ?string $class = '';
    public mixed $children = null;

    public function render(): string
    {
        $disabledFlag = $this->disabled ? 'true' : 'false';

        $attributes = $this->getAttributes([
            'data-slot'     => 'select-item',
            'data-value'    => $this->value,
            'data-disabled' => $disabledFlag,
            'role'          => 'option',
            'aria-selected' => 'false',
        ]);
        $class = $this->getMergeClasses("hover:bg-accent hover:text-accent-foreground focus:bg-accent focus:text-accent-foreground data-[highlighted=true]:bg-accent data-[highlighted=true]:text-accent-foreground [&_svg:not([class*='text-'])]:text-muted-foreground relative flex w-full cursor-default items-center gap-2 rounded-sm py-1.5 pr-8 pl-2 text-sm outline-hidden select-none data-[disabled=true]:pointer-events-none data-[disabled=true]:opacity-50 [&_svg]:pointer-events-none [&_svg]:shrink-0 [&_svg:not([class*='size-'])]:size-4", $this->class);

        $text = trim($this->children) !== '' ? $this->children : '';

        return <<<HTML
        <div class="{$class}" {$attributes}>
            <span data-slot="select-item-text" class="min-w-0 inline-flex items-center gap-2 truncate align-middle">{$text}</span>
            <span data-slot="select-item-indicator" class="absolute right-2 flex size-3.5 items-center justify-center opacity-0 transition-opacity">
                <Check class="size-4" />
            </span>
        </div>
        HTML;
    }
}

class SelectSeparator extends PHPX
{
    public ?string $class = '';

    public function render(): string
    {
        $attributes = $this->getAttributes([
            'data-slot' => 'select-separator'
        ]);
        $class = $this->getMergeClasses('bg-border pointer-events-none -mx-1 my-1 h-px', $this->class);

        return <<<HTML
        <div class="{$class}" {$attributes}></div>
        HTML;
    }
}

class SelectScrollUpButton extends PHPX
{
    public ?string $class = '';

    public function render(): string
    {
        $attributes = $this->getAttributes([
            'data-slot' => 'select-scroll-up-button'
        ]);
        $class = $this->getMergeClasses('absolute top-0 left-0 right-0 h-6 z-10 flex cursor-default items-center justify-center py-1 bg-popover rounded-t-md pointer-events-none opacity-0 transition-opacity', $this->class);

        return <<<HTML
        <div class="{$class}" {$attributes}>
            <ChevronUp class="size-4" />
        </div>
        HTML;
    }
}

class SelectScrollDownButton extends PHPX
{
    public ?string $class = '';

    public function render(): string
    {
        $attributes = $this->getAttributes([
            'data-slot' => 'select-scroll-down-button'
        ]);
        $class = $this->getMergeClasses('absolute bottom-0 left-0 right-0 h-6 z-10 flex cursor-default items-center justify-center py-1 bg-popover rounded-b-md pointer-events-none opacity-0 transition-opacity', $this->class);

        return <<<HTML
        <div class="{$class}" {$attributes}>
            <ChevronDown class="size-4" />
        </div>
        HTML;
    }
}

class SelectValue extends PHPX
{
    public ?string $placeholder = '';
    public ?string $class = '';

    public function render(): string
    {
        $attributes = $this->getAttributes([
            'data-slot'        => 'select-value',
            'data-placeholder' => $this->placeholder ?? ''
        ]);
        $class = $this->getMergeClasses("truncate text-left w-full flex items-center gap-2 leading-normal", $this->class);

        return <<<HTML
        <span class="{$class}" {$attributes}></span>
        HTML;
    }
}
