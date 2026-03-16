const html = String.raw;

function Icon(name, size = 16, cssClass = '') {
    return `<i data-lucide="${name}" class="${cssClass}" style="width:${size}px; height:${size}px"></i>`;
}

function triggerIcon(provider, size = 14) {
    const p = (provider || '').toLowerCase();
    // Match GTM's own icon scheme from the screenshot
    if (p.includes('page view') || p.includes('all pages') || p.includes('dom ready') || p.includes('window loaded'))
        return `<span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-slate-200">${Icon('eye', size, 'text-slate-500')}</span>`;
    if (p.includes('click - just links') || p.includes('link click'))
        return `<span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-cyan-100">${Icon('link-2', size, 'text-cyan-600')}</span>`;
    if (p.includes('click') || p.includes('all elements'))
        return `<span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-blue-500">${Icon('mouse-pointer-2', size, 'text-white')}</span>`;
    if (p.includes('form') || p.includes('submit'))
        return `<span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-green-500">${Icon('eye', size, 'text-white')}</span>`;
    if (p.includes('element visibility'))
        return `<span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-emerald-100">${Icon('scan-eye', size, 'text-emerald-600')}</span>`;
    if (p.includes('trigger group'))
        return `<span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-slate-300">${Icon('rotate-cw', size, 'text-slate-600')}</span>`;
    if (p.includes('scroll'))
        return `<span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-purple-100">${Icon('arrow-down-to-line', size, 'text-purple-600')}</span>`;
    if (p.includes('timer'))
        return `<span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-yellow-100">${Icon('clock', size, 'text-yellow-600')}</span>`;
    if (p.includes('youtube') || p.includes('video'))
        return `<span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-red-100">${Icon('play-circle', size, 'text-red-600')}</span>`;
    if (p.includes('history'))
        return `<span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-indigo-100">${Icon('history', size, 'text-indigo-600')}</span>`;
    if (p.includes('javascript error') || p.includes('js error'))
        return `<span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-red-200">${Icon('bug', size, 'text-red-700')}</span>`;
    // Default: Custom Event → orange code brackets
    return `<span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-orange-500">${Icon('code-2', size, 'text-white')}</span>`;
}

class GTMApp {
    constructor(data, containerInfo) {
        this.tags = data.tags || [];
        this.triggers = data.triggers || [];
        this.variables = data.variables || [];
        this.templates = data.templates || [];
        this.insights = data.insights || { ga4_dimensions: [], measurement_ids: [], transport_urls: [], consent_overview: {} };
        this.containerInfo = containerInfo || { id: 'GTM-Analyzer', version: 'N/A' };

        // Build id → item lookup maps for O(1) access and to handle duplicate display_names
        this._tagById = Object.fromEntries(this.tags.map(t => [t.id || t.display_name, t]));
        this._triggerById = Object.fromEntries(this.triggers.map(t => [t.id || t.display_name, t]));
        this._varById = Object.fromEntries(this.variables.map(v => [v.id || v.display_name, v]));

        this.tags.forEach(t => t.used_variables = []);
        this.triggers.forEach(tr => tr.used_variables = []);

        this.variables.forEach(v => {
            (v.used_in_tags || []).forEach(tagRef => {
                // tagRef is now a tag id (tag_N)
                const tag = this._tagById[tagRef]
                    || this.tags.find(t => t.display_name === tagRef);
                if (tag) {
                    const param = (v.tag_param_usage || {})[tag.id || tagRef] || '';
                    const entry = { name: v.display_name, id: v.id || v.display_name, param };
                    if (!tag.used_variables.find(e => e.name === v.display_name))
                        tag.used_variables.push(entry);
                }
            });
            (v.used_in_triggers || []).forEach(trigRef => {
                // trigRef is now a unique id
                const trigger = this._triggerById[trigRef]
                    || this.triggers.find(t => t.display_name === trigRef);
                if (trigger && !trigger.used_variables.includes(v.display_name))
                    trigger.used_variables.push(v.display_name);
            });
        });

        this.state = {
            activeTab: 'tags',
            panelStack: [],
            tagFilters: [],
            triggerFilters: [],
            variableFilters: [],
            searchQuery: ''
        };

        this.root = document.getElementById('gtm-app');
        this.render();
    }

    setState(updates) {
        this.state = { ...this.state, ...updates };
        this.render();
    }

    toggleFilter(opt, stateKey) {
        let current = [...this.state[stateKey]];
        if (current.includes(opt)) {
            current = current.filter(x => x !== opt);
        } else {
            current.push(opt);
        }
        this.setState({ [stateKey]: current });
    }

    pushPanel(type, idOrName) {
        let data = null;
        if (type === 'tag') data = this._tagById[idOrName] || this.tags.find(t => t.display_name === idOrName);
        if (type === 'trigger') data = this._triggerById[idOrName] || this.triggers.find(t => t.display_name === idOrName);
        if (type === 'variable') data = this._varById[idOrName] || this.variables.find(v => v.display_name === idOrName);
        if (data) this.setState({ panelStack: [...this.state.panelStack, { type, data }] });
    }

    popPanel() {
        this.setState({ panelStack: this.state.panelStack.slice(0, -1) });
    }

    closeAllPanels() {
        this.setState({ panelStack: [] });
    }

    render() {
        const s = this.state;

        // Save focus state
        const activeElementId = document.activeElement ? document.activeElement.id : null;
        let selectionStart = null, selectionEnd = null;
        if (activeElementId === 'searchQuery' && document.activeElement) {
            selectionStart = document.activeElement.selectionStart;
            selectionEnd = document.activeElement.selectionEnd;
        }

        // Dynamic Filter Options
        const tagTypes = [...new Set(this.tags.map(t => t.detected_provider))].sort();
        const triggerTypes = [...new Set(this.triggers.map(t => t.detected_provider))].sort();
        const varTypes = [...new Set(this.variables.map(v => v.detected_provider))].sort();

        // Search Matcher
        const matchSearch = (item) => {
            if (!s.searchQuery) return true;
            const q = s.searchQuery.toLowerCase();
            return (item.display_name && item.display_name.toLowerCase().includes(q)) ||
                (item.detected_provider && item.detected_provider.toLowerCase().includes(q)) ||
                (item.value && item.value.toLowerCase().includes(q)) ||
                (item.technical_summary && item.technical_summary.toLowerCase().includes(q));
        };

        // Filtered Lists
        const filteredTags = this.tags.filter(t => (s.tagFilters.length === 0 || s.tagFilters.includes(t.detected_provider)) && matchSearch(t));
        const filteredTriggers = this.triggers.filter(t => (s.triggerFilters.length === 0 || s.triggerFilters.includes(t.detected_provider)) && matchSearch(t));
        const filteredVars = this.variables.filter(v => (s.variableFilters.length === 0 || s.variableFilters.includes(v.detected_provider)) && matchSearch(v));

        this.root.innerHTML = html`
            <div class="max-w-6xl mx-auto">
                <!-- Navigation Tabs -->
                <nav class="flex gap-2 mb-6 bg-slate-200/50 p-1 rounded-xl w-fit">
                    ${this.renderTabBtn('tags', 'tag', this.tags.length)}
                    ${this.renderTabBtn('triggers', 'zap', this.triggers.length)}
                    ${this.renderTabBtn('variables', 'variable', this.variables.length)}
                    ${this.renderTabBtn('domains', 'globe', this.getDomainData().count)}
                    ${this.renderTabBtn('insights', 'bar-chart-2', this.insights.ga4_dimensions.length + this.insights.measurement_ids.length)}
                </nav>

                <!-- Main Content Area -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden min-h-[500px] flex flex-col">
                    ${s.activeTab === 'tags' ? this.renderTagsPanel(filteredTags, tagTypes) : ''}
                    ${s.activeTab === 'triggers' ? this.renderTriggersPanel(filteredTriggers, triggerTypes) : ''}
                    ${s.activeTab === 'variables' ? this.renderVariablesPanel(filteredVars, varTypes) : ''}
                    ${s.activeTab === 'domains' ? this.renderDomainsPanel() : ''}
                    ${s.activeTab === 'insights' ? this.renderInsightsPanel() : ''}
                </div>
            </div>

            <!-- Slide-over Overlays -->
            ${this.renderSlideOver()}
        `;

        if (window.lucide) window.lucide.createIcons();

        // Restore focus state
        if (activeElementId === 'searchQuery') {
            const input = document.getElementById('searchQuery');
            if (input) {
                input.focus();
                if (selectionStart !== null) {
                    input.setSelectionRange(selectionStart, selectionEnd);
                }
            }
        }
    }

    renderTabBtn(id, iconName, count) {
        const isActive = this.state.activeTab === id;
        const btnClass = isActive ? 'bg-white shadow-sm text-indigo-600' : 'text-slate-600 hover:text-slate-900';
        const badgeClass = isActive ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-200 text-slate-500';
        return html`
            <button onclick="app.setState({ activeTab: '${id}' })" class="flex items-center gap-2 px-6 py-2 rounded-lg text-sm font-medium transition-all capitalize ${btnClass}">
                ${Icon(iconName, 16)}
                ${id}
                <span class="ml-1 text-[10px] px-1.5 py-0.5 rounded-full ${badgeClass}">${count}</span>
            </button>
        `;
    }

    getRootDomain(hostname) {
        const multiLevelTlds = new Set([
            'co.uk','org.uk','me.uk','ac.uk',
            'com.au','net.au','org.au',
            'co.nz','net.nz','org.nz',
            'co.jp','or.jp','ne.jp',
            'co.kr','or.kr',
            'com.br','net.br','org.br',
            'co.in','net.in','org.in',
            'co.za',
            'com.mx',
            'co.id','or.id',
            'com.tr','org.tr',
            'com.pl','net.pl',
            'co.il',
            'com.ar',
            'com.sg','org.sg',
            'com.hk',
            'co.th',
        ]);
        const parts = hostname.split('.');
        if (parts.length <= 2) return hostname;
        const last2 = parts.slice(-2).join('.');
        if (multiLevelTlds.has(last2) && parts.length > 2) {
            return parts.slice(-3).join('.');
        }
        return last2;
    }

    getDomainData() {
        const hostnameMap = {};

        const collect = (list, key) => {
            list.forEach(item => {
                (item.domains || []).forEach(domain => {
                    if (!hostnameMap[domain]) hostnameMap[domain] = { tags: [], triggers: [], variables: [] };
                    if (!hostnameMap[domain][key].find(i => i.display_name === item.display_name))
                        hostnameMap[domain][key].push(item);
                });
            });
        };

        collect(this.tags, 'tags');
        collect(this.triggers, 'triggers');
        collect(this.variables, 'variables');

        // Group full hostnames by root domain
        const rootMap = {};
        for (const [hostname, usage] of Object.entries(hostnameMap)) {
            const root = this.getRootDomain(hostname);
            if (!rootMap[root]) rootMap[root] = { subdomains: new Set(), tags: new Set(), triggers: new Set(), variables: new Set() };
            rootMap[root].subdomains.add(hostname);
            usage.tags.forEach(i => rootMap[root].tags.add(i.display_name));
            usage.triggers.forEach(i => rootMap[root].triggers.add(i.display_name));
            usage.variables.forEach(i => rootMap[root].variables.add(i.display_name));
        }

        const rows = Object.entries(rootMap).map(([root, data]) => ({
            root,
            subdomains: Array.from(data.subdomains).filter(s => s !== root).sort(),
            tags: Array.from(data.tags).sort(),
            triggers: Array.from(data.triggers).sort(),
            variables: Array.from(data.variables).sort(),
        })).sort((a, b) => a.root.localeCompare(b.root));

        return { rows, count: rows.length };
    }

    renderDomainsPanel() {
        const q = (this.state.domainSearch || '').toLowerCase();
        const { rows, count } = this.getDomainData();

        const filtered = q
            ? rows.filter(r =>
                r.root.includes(q) ||
                r.subdomains.some(s => s.includes(q)) ||
                r.tags.some(t => t.toLowerCase().includes(q)) ||
                r.triggers.some(t => t.toLowerCase().includes(q)) ||
                r.variables.some(v => v.toLowerCase().includes(q))
            )
            : rows;

        const renderItems = (items, type) => items.length === 0
            ? '<span class="text-slate-300 text-xs">&mdash;</span>'
            : items.map(name => html`
                <button onclick="app.pushPanel('${type}', '${name.replace(/'/g, "\\'")}')" 
                    class="inline-block text-[10px] bg-white border border-slate-200 px-2 py-0.5 rounded text-slate-500 hover:bg-indigo-50 hover:text-indigo-700 hover:border-indigo-200 transition-colors mb-0.5 mr-0.5">
                    ${name}
                </button>
            `).join('');

        return html`
            <div class="px-6 py-3 bg-slate-50 border-b border-slate-200 flex items-center gap-4 shrink-0">
                <div class="relative flex-1 max-w-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        ${Icon('search', 14)}
                    </div>
                    <input type="text" id="domainSearch"
                        placeholder="Domain, Subdomain oder Element suchen…"
                        oninput="app.setState({ domainSearch: this.value })"
                        value="${this.state.domainSearch || ''}"
                        class="w-full pl-9 pr-4 py-1.5 bg-white border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 text-slate-700 shadow-sm">
                </div>
                <span class="text-xs text-slate-400 shrink-0">
                    ${filtered.length} von ${count} Root-Domain${count !== 1 ? 's' : ''}
                </span>
            </div>
            ${filtered.length === 0 ? html`
                <div class="p-12 text-center">
                    <div class="text-slate-300 mb-4 flex justify-center">${Icon('globe', 48)}</div>
                    <h3 class="text-lg font-semibold text-slate-600 mb-1">Keine Domains gefunden</h3>
                    <p class="text-slate-400 text-sm">In diesem Container wurden keine externen Hostnamen identifiziert.</p>
                </div>
            ` : html`
            <div class="flex-1 overflow-auto">
                <table class="w-full text-sm text-left border-collapse">
                    <thead class="sticky top-0 z-10">
                        <tr class="bg-slate-100 border-b border-slate-200">
                            <th class="px-6 py-3 text-[10px] font-bold text-slate-500 uppercase tracking-wider w-44">Root-Domain</th>
                            <th class="px-4 py-3 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Subdomains</th>
                            <th class="px-4 py-3 text-[10px] font-bold text-slate-500 uppercase tracking-wider w-52 flex items-center gap-1">${Icon('tag', 11)} Tags</th>
                            <th class="px-4 py-3 text-[10px] font-bold text-slate-500 uppercase tracking-wider w-52">Trigger</th>
                            <th class="px-4 py-3 text-[10px] font-bold text-slate-500 uppercase tracking-wider w-52">Variablen</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        ${filtered.map((row, i) => html`
                        <tr class="${i % 2 === 0 ? 'bg-white' : 'bg-slate-50/60'} hover:bg-indigo-50/30 transition-colors">
                            <td class="px-6 py-3 font-bold text-slate-800 font-mono text-xs align-top whitespace-nowrap">
                                <div class="flex items-center gap-1.5">
                                    ${Icon('globe', 12)} ${row.root}
                                </div>
                            </td>
                            <td class="px-4 py-3 align-top max-w-xs">
                                ${row.subdomains.length === 0
                ? '<span class="text-slate-300 text-[10px] italic">nur Root</span>'
                : row.subdomains.map(s => html`<span class="inline-block font-mono text-[10px] bg-slate-100 text-slate-500 px-1.5 py-0.5 rounded mr-1 mb-0.5">${s}</span>`).join('')
            }
                            </td>
                            <td class="px-4 py-3 align-top">${renderItems(row.tags, 'tag')}</td>
                            <td class="px-4 py-3 align-top">${renderItems(row.triggers, 'trigger')}</td>
                            <td class="px-4 py-3 align-top">${renderItems(row.variables, 'variable')}</td>
                        </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
            `}
        `;
    }

    renderFilterBar(options, currentFilters, stateKey, allItems) {
        // Build count map: how many items per detected_provider type
        const countMap = {};
        if (allItems) allItems.forEach(item => { const k = item.detected_provider; countMap[k] = (countMap[k] || 0) + 1; });
        const totalCount = allItems ? allItems.length : null;

        return html`
            <div class="p-4 bg-slate-50 border-b border-slate-200 flex flex-col gap-4">
                <!-- Search and Reset -->
                <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                    <div class="relative flex-1 max-w-md">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            ${Icon('search', 16)}
                        </div>
                        <input type="text" id="searchQuery"
                            placeholder="Suchen nach Namen, Typ, Wert..." 
                            value="${this.state.searchQuery}"
                            oninput="app.setState({ searchQuery: this.value })"
                            class="w-full pl-10 pr-10 py-2 bg-white border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition-all text-slate-700 placeholder-slate-400 shadow-sm">
                        ${this.state.searchQuery ? html`
                        <button onclick="app.setState({ searchQuery: '' })" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none">
                            ${Icon('x', 14)}
                        </button>` : ''}
                    </div>
                    
                    ${currentFilters.length > 0 || this.state.searchQuery ? html`
                    <button onclick="app.setState({ ${stateKey}: [], searchQuery: '' })" class="text-xs font-semibold text-slate-500 hover:text-indigo-600 flex items-center gap-1 transition-colors px-2 py-1">
                        ${Icon('rotate-ccw', 12)} Filter zurücksetzen
                    </button>
                    ` : ''}
                </div>

                <!-- Multi-select Types -->
                <div class="flex flex-wrap items-center gap-2">
                    <div class="text-slate-400 shrink-0 mr-1 flex items-center justify-center p-1.5 bg-white border border-slate-200 rounded-md shadow-sm" title="Nach Typ filtern">
                        ${Icon('filter', 14)}
                    </div>
                    <button onclick="app.setState({ ${stateKey}: [] })" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[11px] font-semibold transition-all duration-200 border ${currentFilters.length === 0 ? 'bg-indigo-600 text-white border-indigo-600 shadow-sm shadow-indigo-200' : 'bg-white text-slate-600 border-slate-200 hover:border-indigo-300 hover:bg-indigo-50/50 hover:text-indigo-700'}">
                        Alle Typen
                        ${totalCount !== null ? html`<span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full ${currentFilters.length === 0 ? 'bg-indigo-500 text-white' : 'bg-slate-100 text-slate-500'}">${totalCount}</span>` : ''}
                    </button>
                    
                    ${options.map(opt => {
            const isActive = currentFilters.includes(opt);
            const c = isActive ? 'bg-indigo-600 text-white border-indigo-600 shadow-sm shadow-indigo-200' : 'bg-white text-slate-600 border-slate-200 hover:border-indigo-300 hover:bg-indigo-50/50 hover:text-indigo-700';
            const badgeC = isActive ? 'bg-indigo-500 text-white' : 'bg-slate-100 text-slate-500';
            const cnt = countMap[opt] || 0;
            return html`<button onclick="app.toggleFilter('${opt}', '${stateKey}')" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[11px] font-semibold transition-all duration-200 border ${c}">${opt}<span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full ${badgeC}">${cnt}</span></button>`;
        }).join('')}
                </div>
            </div>
        `;
    }

    renderTagsPanel(tags, tagTypes) {
        return html`
            ${this.renderFilterBar(tagTypes, this.state.tagFilters, 'tagFilters', this.tags)}
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-400 text-[10px] font-bold uppercase">
                        <tr><th class="px-6 py-4">Tag Name</th><th class="px-6 py-4">Typ (Provider)</th><th class="px-6 py-4">Triggers</th><th class="px-6 py-4 text-right">Aktion</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        ${tags.map(t => {
            const firingTriggers = (t.triggers || []).map(ref => this._triggerById[ref] || this.triggers.find(x => x.display_name === ref)).filter(Boolean);
            const blockingTriggers = (t.blocking_triggers || []).map(ref => this._triggerById[ref] || this.triggers.find(x => x.display_name === ref)).filter(Boolean);
            return html`
                            <tr class="hover:bg-indigo-50/40 transition-colors group cursor-pointer" onclick="app.pushPanel('tag', '${t.display_name}')">
                                <td class="px-6 py-3">
                                    <div class="font-bold text-slate-800 flex items-center gap-2">${Icon('tag', 14, 'text-indigo-400')} ${t.display_name}</div>
                                    ${(t.external_scripts && t.external_scripts.length > 0) ? html`
                                    <div class="flex flex-wrap gap-1 mt-1.5">
                                        ${t.external_scripts.map(host => html`
                                            <span class="inline-flex items-center gap-1 text-[10px] font-mono font-semibold bg-amber-50 text-amber-700 border border-amber-200 rounded px-1.5 py-0.5">
                                                ${Icon('link', 10, 'text-amber-500')} ${host}
                                            </span>
                                        `).join('')}
                                    </div>` : ''}
                                </td>
                                <td class="px-6 py-3"><span class="text-xs bg-slate-100 text-slate-600 px-2 py-0.5 rounded border border-slate-200 cursor-help whitespace-nowrap" title="${t.description || 'Keine Beschreibung verfügbar'}">${t.detected_provider}</span></td>
                                <td class="px-6 py-3">
                                    <div class="flex flex-wrap items-center gap-1">
                                        ${firingTriggers.map(tr => html`<span class="inline-flex items-center gap-1" title="${tr.display_name}">${triggerIcon(tr.detected_provider, 12)}</span>`).join('')}
                                        ${blockingTriggers.length > 0 ? blockingTriggers.map(tr => html`<span class="inline-flex items-center gap-1 opacity-60" title="Blocking: ${tr.display_name}"><span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-red-100">${Icon('shield-off', 12, 'text-red-500')}</span></span>`).join('') : ''}
                                        ${firingTriggers.length === 0 && blockingTriggers.length === 0 ? '<span class="text-slate-300 text-xs">—</span>' : ''}
                                    </div>
                                </td>
                                <td class="px-6 py-3 text-right text-slate-300 group-hover:text-indigo-500">${Icon('chevron-right', 20)}</td>
                            </tr>`;
                        }).join('')}
                    </tbody>
                </table>
            </div>
        `;
    }

    renderTriggersPanel(triggers, types) {
        return html`
            ${this.renderFilterBar(types, this.state.triggerFilters, 'triggerFilters', this.triggers)}
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-400 text-[10px] font-bold uppercase">
                        <tr><th class="px-6 py-4">Trigger Name</th><th class="px-6 py-4">Typ</th><th class="px-6 py-4">Bedingung</th><th class="px-6 py-4 text-right">Aktion</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        ${triggers.map(t => html`
                            <tr class="hover:bg-indigo-50/40 transition-colors group cursor-pointer" onclick="app.pushPanel('trigger', '${t.id || t.display_name}')">
                                <td class="px-6 py-4"><div class="font-bold text-slate-800 flex items-center gap-2">${triggerIcon(t.detected_provider)} ${t.display_name}</div></td>
                                <td class="px-6 py-4"><span class="text-xs bg-slate-100 text-slate-600 px-2 py-0.5 rounded border border-slate-200 cursor-help" title="${t.description || 'No description available'}">${t.detected_provider}</span></td>
                                <td class="px-6 py-4"><code class="text-[11px] text-indigo-600 bg-indigo-50 px-2 py-1 rounded border border-indigo-100 block max-w-xs truncate">${t.technical_summary}</code></td>
                                <td class="px-6 py-4 text-right text-slate-300 group-hover:text-indigo-500">${Icon('chevron-right', 20)}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `;
    }

    renderVariablesPanel(variables, types) {
        return html`
            ${this.renderFilterBar(types, this.state.variableFilters, 'variableFilters', this.variables)}
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-400 text-[10px] font-bold uppercase">
                        <tr><th class="px-6 py-4">Variablen Name</th><th class="px-6 py-4">Typ</th><th class="px-6 py-4">Wert</th><th class="px-6 py-4 text-center">Verwendung</th><th class="px-6 py-4 text-right">Aktion</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        ${variables.map(v => html`
                            <tr class="hover:bg-indigo-50/40 transition-colors group cursor-pointer" onclick="app.pushPanel('variable', '${v.display_name}')">
                                <td class="px-6 py-4"><div class="font-bold text-slate-800 flex items-center gap-2">${Icon('wrap-text', 14, 'text-blue-500')} ${v.display_name}</div></td>
                                <td class="px-6 py-4"><span class="text-xs bg-slate-100 text-slate-600 px-2 py-0.5 rounded border border-slate-200 cursor-help" title="${v.description || 'Keine Beschreibung verfügbar'}">${v.detected_provider}</span></td>
                                <td class="px-6 py-4"><code class="text-[11px] font-mono font-bold text-pink-600">${v.value || v.technical_summary}</code></td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center justify-center bg-slate-100 text-slate-500 border border-slate-200 text-[10px] font-bold h-6 min-w-[24px] px-1.5 rounded-full ${v.usage_count > 0 ? 'bg-indigo-100 border-indigo-200 text-indigo-700' : ''}">${v.usage_count}x</span>
                                </td>
                                <td class="px-6 py-4 text-right text-slate-300 group-hover:text-indigo-500">${Icon('chevron-right', 20)}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `;
    }

    renderSlideOver() {
        if (this.state.panelStack.length === 0) return '';

        return html`
            <div class="fixed inset-0 z-50 flex justify-end">
                <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" onclick="app.closeAllPanels()"></div>
                <div class="relative h-full w-full max-w-4xl bg-white shadow-2xl flex flex-col animate-in slide-in-from-right duration-300">
                    ${this.state.panelStack.map((item, index) => {
            const isTop = index === this.state.panelStack.length - 1;
            if (!isTop) return '';
            return this.renderPanelContent(item, index);
        }).join('')}
                </div>
            </div>
        `;
    }

    renderPanelContent(item, index) {
        return html`
            <div class="flex flex-col h-full">
                <!-- Top Bar -->
                <div class="bg-slate-800 text-white px-6 py-4 flex items-center justify-between shadow-md">
                    <div class="flex items-center gap-4">
                        ${this.state.panelStack.length > 1
                ? html`<button onclick="app.popPanel()" class="p-1 hover:bg-white/10 rounded-lg">${Icon('arrow-left', 20)}</button>`
                : html`<button onclick="app.closeAllPanels()" class="p-1 hover:bg-white/10 rounded-lg">${Icon('x', 20)}</button>`}
                        <div>
                            <div class="text-[10px] uppercase font-bold text-slate-400 tracking-widest flex items-center gap-2">${item.type}</div>
                            <h2 class="text-xl font-bold leading-tight truncate max-w-sm">${item.data.display_name}</h2>
                        </div>
                    </div>
                </div>

                <!-- Body -->
                <div class="flex-1 overflow-y-auto p-8 space-y-8">
                    ${item.type === 'tag' ? this.renderTagDetails(item.data) : ''}
                    ${item.type === 'trigger' ? this.renderTriggerDetails(item.data) : ''}
                    ${item.type === 'variable' ? this.renderVariableDetails(item.data) : ''}
                </div>
            </div>
        `;
    }

    renderTagDetails(t) {
        return html`
            <section>
                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-2">Provider / Type</label>
                <div class="bg-indigo-50 border border-indigo-100 rounded-2xl p-5 text-indigo-900 font-bold cursor-help" title="${t.description || 'No description available'}">${t.detected_provider}</div>
            </section>


            ${t.html_content ? html`
            <section>
                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-2">Custom HTML Code</label>
                <div class="bg-slate-800 rounded-2xl p-4 overflow-x-auto">
                    <pre class="whitespace-pre-wrap break-words"><code class="text-[11px] text-emerald-400 font-mono">${t.html_content.replace(/</g, "&lt;").replace(/>/g, "&gt;")}</code></pre>
                </div>
            </section>` : ''}

            <section>
                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-3">Firing Triggers</label>
                ${(t.triggers || []).length > 0 ? t.triggers.map(trRef => {
            // trRef is either a trigger id (trigger_5) or a display_name string
            const trObj = this._triggerById[trRef] || this.triggers.find(x => x.display_name === trRef);
            const label = trObj ? trObj.display_name : trRef;
            const id = trObj ? (trObj.id || trObj.display_name) : trRef;
            const prov = trObj ? trObj.detected_provider : '';
            return html`
                    <div class="group flex items-center justify-between mb-2 p-4 bg-orange-50 border border-orange-100 rounded-xl cursor-pointer hover:border-orange-300 transition-all" onclick="app.pushPanel('trigger', '${id}')">
                        <div class="flex items-center gap-3">
                            <div class="shrink-0">${triggerIcon(prov, 14)}</div>
                            <div>
                                <div class="text-sm font-bold text-orange-900">${label}</div>
                                <div class="text-[10px] text-orange-400">Show details</div>
                            </div>
                        </div>
                        ${Icon('chevron-right', 18, 'text-orange-300 group-hover:translate-x-1 transition-transform')}
                    </div>`;
        }).join('') : '<div class="text-xs text-slate-400 italic p-4 text-center border border-dashed rounded-xl">No triggers linked</div>'}
            </section>

            ${(t.blocking_triggers || []).length > 0 ? html`
            <section>
                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-3">Blocking Triggers</label>
                ${t.blocking_triggers.map(trRef => {
            const trObj = this._triggerById[trRef] || this.triggers.find(x => x.display_name === trRef);
            const label = trObj ? trObj.display_name : trRef;
            const id = trObj ? (trObj.id || trObj.display_name) : trRef;
            const prov = trObj ? trObj.detected_provider : '';
            return html`
                    <div class="group flex items-center justify-between mb-2 p-4 bg-red-50 border border-red-100 rounded-xl cursor-pointer hover:border-red-300 transition-all" onclick="app.pushPanel('trigger', '${id}')">
                        <div class="flex items-center gap-3">
                            <div class="shrink-0"><span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-red-100">${Icon('shield-off', 14, 'text-red-500')}</span></div>
                            <div>
                                <div class="text-sm font-bold text-red-900">${label}</div>
                                <div class="text-[10px] text-red-400">${prov}</div>
                            </div>
                        </div>
                        ${Icon('chevron-right', 18, 'text-red-300 group-hover:translate-x-1 transition-transform')}
                    </div>`;
        }).join('')}
            </section>` : ''}

            ${(t.consent_required && t.consent_required.length > 0) || (t.consent_defaults && Object.keys(t.consent_defaults).length > 0) ? html`
            <section>
                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-3 flex items-center gap-1">
                    ${Icon('shield', 12)} Consent Configuration
                </label>
                ${(t.consent_required && t.consent_required.length > 0) ? html`
                <div class="mb-3">
                    <div class="text-[10px] font-semibold text-slate-500 mb-1.5">Required Consent</div>
                    <div class="flex flex-wrap gap-2">
                        ${t.consent_required.map(c => html`
                        <span class="inline-flex items-center gap-1.5 text-[11px] font-bold px-3 py-1.5 rounded-lg bg-amber-50 text-amber-700 border border-amber-200">
                            ${Icon('shield-check', 12, 'text-amber-500')} ${c}
                        </span>`).join('')}
                    </div>
                </div>` : ''}
                ${(t.consent_defaults && Object.keys(t.consent_defaults).length > 0) ? html`
                <div>
                    <div class="text-[10px] font-semibold text-slate-500 mb-1.5">Default Consent States (Initialization)</div>
                    <div class="flex flex-wrap gap-2">
                        ${Object.entries(t.consent_defaults).map(([type, state]) => html`
                        <span class="inline-flex items-center gap-1.5 text-[11px] font-bold px-3 py-1.5 rounded-lg border ${state === 'granted'
                            ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                            : 'bg-red-50 text-red-700 border-red-200'}">
                            ${Icon(state === 'granted' ? 'check-circle' : 'x-circle', 12, state === 'granted' ? 'text-emerald-500' : 'text-red-500')}
                            ${type}: ${state}
                        </span>`).join('')}
                    </div>
                </div>` : ''}
            </section>` : ''}

            ${(t.used_variables || []).length > 0 ? html`
            <section>
                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-3 flex items-center gap-1">${Icon('wrap-text', 12)} Used Variables (${t.used_variables.length})</label>
                <div class="border border-slate-200 rounded-xl overflow-hidden">
                    <table class="w-full text-left bg-white text-sm">
                        <thead class="bg-slate-50 text-[10px] uppercase font-bold text-slate-500 border-b border-slate-200">
                            <tr>
                                <th class="px-4 py-3">Variable</th>
                                <th class="px-4 py-3">Type</th>
                                <th class="px-4 py-3">Used as</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            ${t.used_variables.map(entry => {
            // entry is either {name, id, param} (new) or a plain string (legacy)
            const vName = typeof entry === 'object' ? entry.name : entry;
            const vId = typeof entry === 'object' ? (entry.id || entry.name) : entry;
            const param = typeof entry === 'object' ? entry.param : '';
            const varObj = this._varById[vId] || this.variables.find(v => v.display_name === vName);
            if (!varObj) return '';
            return html`
                                <tr class="hover:bg-indigo-50/40 cursor-pointer group transition-colors" onclick="app.pushPanel('variable', '${vId}')">
                                    <td class="px-4 py-3 font-semibold text-slate-800">
                                        <div class="flex items-center gap-2">
                                            ${Icon('wrap-text', 14, 'text-blue-500')}
                                            <span class="truncate max-w-[180px] block" title="${varObj.display_name}">${varObj.display_name}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="text-[10px] bg-slate-100 text-slate-600 px-2 py-0.5 rounded border border-slate-200">${varObj.detected_provider}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        ${param ? html`<span class="text-[11px] font-mono font-semibold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded">${param}</span>` : html`<span class="text-slate-300 text-xs">—</span>`}
                                    </td>
                                </tr>
                                `;
        }).join('')}
                        </tbody>
                    </table>
                </div>
            </section>` : ''}
        `;
    }

    renderTriggerDetails(t) {
        return html`
            <section class="bg-slate-50 p-6 rounded-2xl border border-slate-200">
                <h3 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-4">Trigger Konfiguration</h3>
                <div class="space-y-4">
                    <div class="grid grid-cols-3 gap-4 border-b border-slate-200 pb-4">
                        <span class="text-xs font-bold text-slate-500">Trigger Typ</span>
                        <span class="text-sm font-medium col-span-2 cursor-help border-b border-dashed border-slate-300 pb-0.5 max-w-max" title="${t.description || 'Keine Beschreibung verfügbar'}">${t.detected_provider}</span>
                    </div>
                    <div class="grid grid-cols-3 gap-4">
                        <span class="text-xs font-bold text-slate-500">Bedingungen</span>
                        <div class="col-span-2 space-y-2">
                            ${(t.conditions || []).map(c => html`<code class="bg-white p-2 rounded border border-slate-200 text-xs text-indigo-600 block break-all">${c}</code>`).join('')}
                        </div>
                    </div>
                </div>
            </section>

            ${(() => {
                // firing_tags contains tag display_names (from vtp_name in the backend)
                const firingTags = t.firing_tags || [];
                if (firingTags.length === 0) return '';
                return html`
                <section>
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-3 flex items-center gap-1">${Icon('tag', 12)} Feuernde Tags (${firingTags.length})</label>
                    <div class="flex flex-col gap-1">
                        ${firingTags.map(tagRef => {
                    // tagRef is now a tag id (tag_N) — resolve display_name from _tagById map
                    const tagObj = this._tagById[tagRef] || this.tags.find(t => t.display_name === tagRef);
                    const id = tagObj ? (tagObj.id || tagObj.display_name) : tagRef;
                    const label = tagObj ? tagObj.display_name : tagRef;
                    return html`
                            <div class="group flex items-center justify-between p-3 bg-indigo-50/50 border border-indigo-100 rounded-xl cursor-pointer hover:border-indigo-300 transition-all" onclick="app.pushPanel('tag', '${id}')">
                                <div class="flex items-center gap-2">
                                    ${Icon('tag', 14, 'text-indigo-400')}
                                    <span class="text-xs font-semibold text-indigo-800">${label}</span>
                                </div>
                                ${Icon('chevron-right', 14, 'text-indigo-300 group-hover:translate-x-1 transition-transform')}
                            </div>`;
                }).join('')}
                    </div>
                </section>`;
            })()}

            ${(t.used_variables || []).length > 0 ? html`
            <section>
                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-3 flex items-center gap-1">${Icon('wrap-text', 12)} Verwendete Variablen (${t.used_variables.length})</label>
                <div class="border border-slate-200 rounded-xl overflow-hidden">
                    <table class="w-full text-left bg-white text-sm">
                        <thead class="bg-slate-50 text-[10px] uppercase font-bold text-slate-500 border-b border-slate-200">
                            <tr>
                                <th class="px-4 py-3">Variable</th>
                                <th class="px-4 py-3">Typ</th>
                                <th class="px-4 py-3">Wert</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            ${t.used_variables.map(vName => {
                const varObj = this.variables.find(v => v.display_name === vName);
                if (!varObj) return '';
                return html`
                                <tr class="hover:bg-indigo-50/40 cursor-pointer group transition-colors" onclick="app.pushPanel('variable', '${vName}')">
                                    <td class="px-4 py-3 font-semibold text-slate-800 flex items-center gap-2">
                                        ${Icon('wrap-text', 14, 'text-blue-500')} 
                                        <span class="truncate max-w-[200px] block" title="${varObj.display_name}">${varObj.display_name}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="text-[10px] bg-slate-100 text-slate-600 px-2 py-0.5 rounded border border-slate-200 truncate max-w-[150px] block" title="${varObj.detected_provider}">${varObj.detected_provider}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="text-xs text-pink-600 font-mono font-bold truncate max-w-[200px] block" title="${varObj.value || varObj.technical_summary}">${varObj.value || varObj.technical_summary}</span>
                                    </td>
                                </tr>
                                `;
            }).join('')}
                        </tbody>
                    </table>
                </div>
            </section>` : ''}
        `;
    }

    renderInsightsPanel() {
        const ins = this.insights;
        const dims = ins.ga4_dimensions || [];
        const mids = ins.measurement_ids || [];
        const urls = ins.transport_urls || [];

        const EmptyNote = (msg) => html`<p class="text-xs text-slate-400 italic">${msg}</p>`;

        return html`
            <div class="p-6 space-y-8">

                <!-- Header -->
                <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                    ${Icon('bar-chart-2', 20, 'text-indigo-500')}
                    <div>
                        <h2 class="text-base font-bold text-slate-800">GA4 Insights</h2>
                        <p class="text-xs text-slate-400">Measurement IDs, Custom Dimensions and Transport URLs extracted from the container</p>
                    </div>
                </div>

                <!-- Measurement IDs -->
                <section>
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-3 flex items-center gap-2">
                        ${Icon('fingerprint', 13, 'text-violet-500')} Measurement IDs (${mids.length})
                    </label>
                    ${mids.length === 0 ? EmptyNote('No Measurement IDs found.') : html`
                    <div class="flex flex-wrap gap-3">
                        ${mids.map(id => html`
                        <div class="flex items-center gap-2 bg-violet-50 border border-violet-200 rounded-xl px-4 py-2.5 shadow-sm">
                            ${Icon('hash', 14, 'text-violet-400')}
                            <code class="text-sm font-mono font-bold text-violet-800">${id}</code>
                        </div>`).join('')}
                    </div>`}
                </section>

                <!-- Transport URLs -->
                ${urls.length > 0 ? html`
                <section>
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-3 flex items-center gap-2">
                        ${Icon('server', 13, 'text-emerald-500')} Server Container / Transport URLs (${urls.length})
                    </label>
                    <div class="flex flex-col gap-2">
                        ${urls.map(url => html`
                        <div class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3">
                            ${Icon('link', 14, 'text-emerald-500')}
                            <a href="${url}" target="_blank" rel="noopener" class="text-sm font-mono text-emerald-800 hover:text-emerald-600 hover:underline truncate">${url}</a>
                        </div>`).join('')}
                    </div>
                </section>` : ''}

                <!-- Consent Overview -->
                ${this.renderConsentOverview(ins.consent_overview || {})}

                <!-- GA4 Dimensions -->
                <section>
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-3 flex items-center gap-2">
                        ${Icon('table-2', 13, 'text-indigo-500')} Custom Dimensions / Event Parameters (${dims.length})
                    </label>
                    ${dims.length === 0 ? EmptyNote('No GA4 event parameters found. Parameters are extracted from GA4 Event tags and Event Settings Variables.') : html`
                    <div class="border border-slate-200 rounded-xl overflow-hidden">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-slate-50 border-b border-slate-200">
                                <tr>
                                    <th class="px-4 py-3 text-[10px] font-bold text-slate-500 uppercase tracking-wider w-8">#</th>
                                    <th class="px-4 py-3 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Parameter Name</th>
                                    <th class="px-4 py-3 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Used in Variables</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                ${dims.map((dim, i) => {
            // dim is either an object {name, variables} or a plain string (backwards compat)
            const paramName = typeof dim === 'object' ? dim.name : dim;
            const backendVars = (typeof dim === 'object' && dim.variables) ? dim.variables : [];
            // Find matching variable objects by display_name from the backend-provided list
            const relatedVars = backendVars.length > 0
                ? backendVars.map(vName => this.variables.find(v => v.display_name === vName)).filter(Boolean)
                : [];
            return html`
                                <tr class="${i % 2 === 0 ? 'bg-white' : 'bg-slate-50/40'} hover:bg-indigo-50/30 transition-colors">
                                    <td class="px-4 py-2.5 text-slate-300 text-xs font-mono">${i + 1}</td>
                                    <td class="px-4 py-2.5">
                                        <code class="text-sm font-mono font-bold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded">${paramName}</code>
                                    </td>
                                    <td class="px-4 py-2.5">
                                        ${relatedVars.length > 0
                    ? relatedVars.map(v => html`<span class="inline-flex items-center gap-1 text-[11px] bg-slate-100 text-slate-600 rounded px-2 py-0.5 mr-1 cursor-pointer hover:bg-indigo-100 hover:text-indigo-700 transition-colors" onclick="app.pushPanel('variable','${v.id || v.display_name}')">${v.display_name}</span>`).join('')
                    : (backendVars.length > 0
                        ? backendVars.map(n => html`<span class="inline-flex items-center gap-1 text-[11px] bg-slate-100 text-slate-600 rounded px-2 py-0.5 mr-1">${n}</span>`).join('')
                        : '<span class="text-slate-300 text-xs">—</span>')}
                                    </td>
                                </tr>`;
        }).join('')}
                            </tbody>
                        </table>
                    </div>`}
                </section>


            </div>
        `;
    }

    renderConsentOverview(co) {
        if (!co || (!co.consent_types || Object.keys(co.consent_types).length === 0) && !co.init_tag) return '';
        const types = co.consent_types || {};
        const defaults = co.default_states || {};
        const totalTypes = Object.keys(types).length;

        return html`
            <section>
                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-3 flex items-center gap-2">
                    ${Icon('shield', 13, 'text-amber-500')} Consent Configuration
                </label>

                ${co.init_tag ? html`
                <div class="mb-4 p-3 bg-slate-50 border border-slate-200 rounded-xl">
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Consent Initialization Tag</div>
                    <div class="text-sm font-semibold text-slate-700 flex items-center gap-2">
                        ${Icon('tag', 14, 'text-indigo-400')} ${co.init_tag}
                    </div>
                    ${Object.keys(defaults).length > 0 ? html`
                    <div class="flex flex-wrap gap-1.5 mt-2">
                        ${Object.entries(defaults).map(([type, state]) => html`
                        <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-1 rounded-lg border ${state === 'granted'
                            ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                            : 'bg-red-50 text-red-700 border-red-200'}">
                            ${Icon(state === 'granted' ? 'check' : 'x', 10, state === 'granted' ? 'text-emerald-500' : 'text-red-400')}
                            ${type}
                        </span>`).join('')}
                    </div>` : ''}
                </div>` : ''}

                ${totalTypes > 0 ? html`
                <div class="border border-slate-200 rounded-xl overflow-hidden">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="px-4 py-3 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Consent Type</th>
                                <th class="px-4 py-3 text-[10px] font-bold text-slate-500 uppercase tracking-wider text-center">Default</th>
                                <th class="px-4 py-3 text-[10px] font-bold text-slate-500 uppercase tracking-wider text-center">Tags</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            ${Object.entries(types).map(([type, info], i) => {
            const defaultState = defaults[type] || null;
            return html`
                            <tr class="${i % 2 === 0 ? 'bg-white' : 'bg-slate-50/40'} hover:bg-amber-50/30 transition-colors">
                                <td class="px-4 py-2.5">
                                    <div class="flex items-center gap-2">
                                        ${Icon('shield', 13, 'text-amber-400')}
                                        <code class="text-sm font-mono font-bold text-slate-700">${type}</code>
                                    </div>
                                </td>
                                <td class="px-4 py-2.5 text-center">
                                    ${defaultState ? html`<span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded ${defaultState === 'granted' ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700'}">${defaultState}</span>` : '<span class="text-slate-300 text-xs">—</span>'}
                                </td>
                                <td class="px-4 py-2.5 text-center">
                                    <span class="inline-flex items-center justify-center bg-amber-100 text-amber-700 border border-amber-200 text-[10px] font-bold h-6 min-w-[24px] px-1.5 rounded-full">${info.required_count}</span>
                                </td>
                            </tr>`;
        }).join('')}
                        </tbody>
                    </table>
                </div>` : ''}
            </section>
        `;
    }

    renderGaSettingsDetails(gs) {
        const KeyVal = (label, value, icon) => value ? html`
            <div class="flex items-start gap-3 py-2">
                <span class="text-slate-400 shrink-0 mt-0.5">${Icon(icon, 14)}</span>
                <div>
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">${label}</div>
                    <code class="text-sm font-mono font-semibold text-slate-700">${value}</code>
                </div>
            </div>` : '';

        const TableSection = (title, icon, headers, rows) => rows.length === 0 ? '' : html`
            <div class="mt-4">
                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-2 flex items-center gap-1">
                    ${Icon(icon, 12)} ${title} (${rows.length})
                </label>
                <div class="rounded-xl border border-slate-200 overflow-hidden">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>${headers.map(h => html`<th class="px-4 py-2 text-[10px] font-bold text-slate-500 uppercase tracking-wider">${h}</th>`).join('')}</tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            ${rows.map((row, i) => html`
                            <tr class="${i % 2 === 0 ? '' : 'bg-slate-50/50'}">
                                ${row.map(cell => html`<td class="px-4 py-2 font-mono text-sm text-slate-700">${cell}</td>`).join('')}
                            </tr>`).join('')}
                        </tbody>
                    </table>
                </div>
            </div>`;

        return html`
            <section>
                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-3 flex items-center gap-1">
                    ${Icon('settings', 12)} Google Analytics Settings
                </label>
                <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 space-y-1">
                    ${KeyVal('Tracking ID', gs.tracking_id, 'hash')}
                    ${KeyVal('Cookie Domain', gs.cookie_domain, 'cookie')}
                    ${KeyVal('Transport URL', gs.transport_url, 'server')}

                    ${TableSection('Fields to Set', 'sliders-horizontal',
                        ['Field Name', 'Value'],
                        (gs.fields_to_set || []).map(f => [f.field, f.value])
                    )}

                    ${TableSection('Custom Dimensions', 'ruler',
                        ['Index', 'Value'],
                        (gs.dimensions || []).map(d => ['dimension' + d.index, d.value])
                    )}

                    ${TableSection('Custom Metrics', 'bar-chart',
                        ['Index', 'Value'],
                        (gs.metrics || []).map(m => ['metric' + m.index, m.value])
                    )}

                    ${(gs.flags || []).length > 0 ? html`
                    <div class="mt-4">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-2 flex items-center gap-1">
                            ${Icon('toggle-left', 12)} Settings Flags
                        </label>
                        <div class="flex flex-wrap gap-2">
                            ${gs.flags.map(f => html`
                            <span class="inline-flex items-center gap-1.5 text-[11px] px-2.5 py-1 rounded-lg border ${f.enabled
                                ? 'bg-emerald-50 border-emerald-200 text-emerald-700'
                                : 'bg-slate-50 border-slate-200 text-slate-400'}">
                                ${Icon(f.enabled ? 'check' : 'x', 11, f.enabled ? 'text-emerald-500' : 'text-slate-300')}
                                ${f.label}
                            </span>`).join('')}
                        </div>
                    </div>` : ''}
                </div>
            </section>
        `;
    }

    renderVariableDetails(v) {
        return html`
            <section>
                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-2">Provider / Type</label>
                <div class="bg-indigo-50 border border-indigo-100 rounded-2xl p-5 text-indigo-900 font-bold cursor-help" title="${v.description || 'No description available'}">${v.detected_provider}</div>
            </section>

            <section>
                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-2">Value / Key</label>
                <code class="text-lg font-mono font-bold text-pink-600">${v.value || '—'}</code>
            </section>

            ${(v.lookup_rows && v.lookup_rows.length > 0) ? html`
            <section>
                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-2 flex items-center gap-1">
                    ${Icon('table', 12)} ${v.detected_provider.includes('Regex') ? 'Regex Table' : 'Lookup Table'}
                    ${v.lookup_input ? html`<span class="ml-2 text-[10px] text-slate-500 font-normal normal-case">Input: <code class="font-mono text-indigo-600">${v.lookup_input}</code></span>` : ''}
                </label>
                <div class="rounded-xl border border-slate-200 overflow-hidden">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="px-4 py-2.5 text-[10px] font-bold text-slate-500 uppercase tracking-wider w-1/2">Input (Key)</th>
                                <th class="px-4 py-2.5 text-[10px] font-bold text-slate-500 uppercase tracking-wider w-1/2">Output (Value)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            ${v.lookup_rows.map((row, i) => html`
                            <tr class="${i % 2 === 0 ? '' : 'bg-slate-50/50'}">
                                <td class="px-4 py-2.5 font-mono text-sm text-slate-700 font-semibold">${row.key}</td>
                                <td class="px-4 py-2.5 font-mono text-sm text-pink-600 font-bold">${row.value || '<span class="text-slate-400 font-normal italic">(empty)</span>'}</td>
                            </tr>`).join('')}
                        </tbody>
                    </table>
                </div>
            </section>` : ''}

            ${v.js_code ? html`
            <section>
                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-2 flex items-center gap-1">
                    ${Icon('code', 12)} JavaScript Code
                </label>
                <pre class="bg-slate-900 text-slate-100 rounded-xl p-5 text-sm font-mono overflow-x-auto whitespace-pre-wrap break-words leading-relaxed">${v.js_code.replace(/;(?!\s*[\n\r}])/g, ';\n').replace(/\{(?!\s*[\n\r])/g, '{\n').replace(/\}(?!\s*[;\n\r,)])/g, '}\n')}</pre>
            </section>` : ''}

            ${v.ga_settings ? this.renderGaSettingsDetails(v.ga_settings) : ''}

            ${(v.used_in_tags || []).length > 0 ? html`
            <section>
                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-3 flex items-center gap-1">${Icon('tag', 12)} Used in Tags (${v.used_in_tags.length})</label>
                <div class="flex flex-col gap-1">
                    ${v.used_in_tags.map(tagRef => {
            const tagObj = this._tagById[tagRef] || this.tags.find(t => t.display_name === tagRef);
            const label = tagObj ? tagObj.display_name : tagRef;
            const id = tagObj ? (tagObj.id || tagObj.display_name) : tagRef;
            const prov = tagObj ? tagObj.detected_provider : '';
            return html`
                        <div class="group flex items-center justify-between p-3 bg-indigo-50/50 border border-indigo-100 rounded-xl cursor-pointer hover:border-indigo-300 transition-all" onclick="app.pushPanel('tag', '${id}')">
                            <div class="flex items-center gap-2">
                                ${Icon('tag', 14, 'text-indigo-400')}
                                <div>
                                    <div class="text-xs font-bold text-indigo-800">${label}</div>
                                    ${prov ? html`<div class="text-[10px] text-indigo-400">${prov}</div>` : ''}
                                </div>
                            </div>
                            ${Icon('chevron-right', 14, 'text-indigo-300 group-hover:translate-x-1 transition-transform')}
                        </div>`;
        }).join('')}
                </div>
            </section>` : ''}

            ${(v.used_in_triggers || []).length > 0 ? html`
            <section>
                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-3 flex items-center gap-1">${Icon('zap', 12)} Used in Triggers (${v.used_in_triggers.length})</label>
                <div class="flex flex-col gap-1">
                    ${v.used_in_triggers.map(tRef => {
            const trObj = this._triggerById[tRef] || this.triggers.find(x => x.display_name === tRef);
            const label = trObj ? trObj.display_name : tRef;
            const id = trObj ? (trObj.id || trObj.display_name) : tRef;
            const prov = trObj ? trObj.detected_provider : '';
            return html`
                        <div class="group flex items-center justify-between p-3 bg-orange-50 border border-orange-100 rounded-xl cursor-pointer hover:border-orange-300 transition-all" onclick="app.pushPanel('trigger', '${id}')">
                            <div class="flex items-center gap-3">
                                <div class="shrink-0">${triggerIcon(prov, 14)}</div>
                                <div>
                                    <div class="text-xs font-bold text-orange-800">${label}</div>
                                    ${prov ? html`<div class="text-[10px] text-orange-400">${prov}</div>` : ''}
                                </div>
                            </div>
                            ${Icon('chevron-right', 14, 'text-orange-300 group-hover:translate-x-1 transition-transform')}
                        </div>`;
        }).join('')}
                </div>
            </section>` : ''}

            ${(v.used_in_variables || []).length > 0 ? html`
            <section>
                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-3 flex items-center gap-1">${Icon('wrap-text', 12)} Used in Variables (${v.used_in_variables.length})</label>
                <div class="flex flex-col gap-1">
                    ${v.used_in_variables.map(vName => {
            const varObj = this._varById[vName] || this.variables.find(x => x.display_name === vName);
            const label = varObj ? varObj.display_name : vName;
            const id = varObj ? (varObj.id || varObj.display_name) : vName;
            return html`
                        <div class="group flex items-center justify-between p-3 bg-blue-50 border border-blue-100 rounded-xl cursor-pointer hover:border-blue-300 transition-all" onclick="app.pushPanel('variable', '${id}')">
                            <div class="flex items-center gap-2">
                                ${Icon('wrap-text', 14, 'text-blue-400')}
                                <span class="text-xs font-bold text-blue-800">${label}</span>
                            </div>
                            ${Icon('chevron-right', 14, 'text-blue-300 group-hover:translate-x-1 transition-transform')}
                        </div>`;
        }).join('')}
                </div>
            </section>` : ''}
        `;
    }
}
