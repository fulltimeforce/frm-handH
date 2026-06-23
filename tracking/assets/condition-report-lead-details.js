(function () {
    function qs(sel, ctx) { return (ctx || document).querySelector(sel); }

    const modal = qs('#hh-cr-lead-details-modal');
    if (!modal) return;

    const backdrop = qs('.hh-modal__backdrop', modal);
    const closeBtn = qs('#hh-cr-lead-details-close');
    const body = qs('#hh-cr-lead-details-body');

    function escHtml(s) {
        return String(s || '')
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

    function openModal(card) {
        const make = card.dataset.lotMake || '—';
        const model = card.dataset.lotModel || '—';
        const year = card.dataset.lotYear || '—';
        const assigned = card.dataset.assignedUser || '—';
        const createdAt = fmtDate(card.dataset.createdAt || '');

        const rows = [
            ['Lot Make', make],
            ['Lot Model', model],
            ['Lot Year', year],
            ['Assigned user', assigned],
            ['Created at', createdAt],
        ];

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

    function closeModal() {
        modal.style.display = 'none';
    }

    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.hh-lead-details-btn');
        if (!btn) return;

        const card = btn.closest('.hh-card');
        if (!card) return;

        openModal(card);
    });

    if (backdrop) backdrop.addEventListener('click', closeModal);
    if (closeBtn) closeBtn.addEventListener('click', closeModal);

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal.style.display === 'block') closeModal();
    });
})();