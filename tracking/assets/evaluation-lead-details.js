(function () {
    function qs(sel, ctx) {
        return (ctx || document).querySelector(sel);
    }

    function escHtml(s) {
        return String(s ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function fmtDate(mysql) {
        if (!mysql) return '—';
        // mysql: "YYYY-MM-DD HH:MM:SS"
        const d = new Date(mysql.replace(' ', 'T'));
        if (isNaN(d.getTime())) return mysql;
        return d.toLocaleString(undefined, { year: 'numeric', month: 'short', day: '2-digit', hour: '2-digit', minute: '2-digit' });
    }

    function addRow(rows, label, value) {
        if (value === null || value === undefined) return;
        const v = String(value).trim();
        if (!v) return;
        rows.push([label, v]);
    }

    const modal = qs('#hh-lead-details-modal');
    if (!modal) return;

    const backdrop = qs('.hh-modal__backdrop', modal);
    const closeBtn = qs('#hh-lead-details-close');
    const body = qs('#hh-lead-details-body');

    function open(card, isNotConsigned) {
        const d = card.dataset || {};
        const sold = parseInt(d.sold || '0', 10) === 1;

        const rows = [];

        addRow(rows, 'Lot Make', d.lotMake);
        addRow(rows, 'Lot Model', d.lotModel);
        addRow(rows, 'Lot Year', d.lotYear);
        addRow(rows, 'Lot Valuation', d.lotValuation);

        addRow(rows, 'Sold', sold ? 'Yes' : 'No');
        if (sold) addRow(rows, 'Sold Price', d.soldPrice);

        addRow(rows, 'Assigned User', d.assignedUser);
        addRow(rows, 'Created At', fmtDate(d.createdAt));

        // addRow(rows, 'Recommended Auction ID', d.recommendedAuctionId);
        addRow(rows, 'Recommended Auction', d.recommendedAuctionTitle);

        if (isNotConsigned) {
            addRow(rows, 'Not Consigned Reason', d.notConsignedReason);
        }

        if (!rows.length) {
            body.innerHTML = '<p style="margin:0;">No details found.</p>';
            modal.style.display = 'block';
            return;
        }

        body.innerHTML = `
            <table class="widefat striped" style="width:100%;">
                <tbody>
                    ${rows.map(([k, v]) => `
                        <tr>
                            <th style="width:220px;">${escHtml(k)}</th>
                            <td>${escHtml(v)}</td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        `;

        modal.style.display = 'block';
    }

    function close() {
        modal.style.display = 'none';
        body.innerHTML = '';
    }

    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.hh-lead-details-btn');
        if (!btn) return;

        const card = btn.closest('.hh-card');
        if (!card) return;

        const col = card.closest('.hh-col')?.getAttribute('data-col') || '';
        const isNotConsigned = (col === 'not_consigned');

        open(card, isNotConsigned);
    });

    if (backdrop) backdrop.addEventListener('click', close);
    if (closeBtn) closeBtn.addEventListener('click', close);

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal.style.display === 'block') close();
    });
})();