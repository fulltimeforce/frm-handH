(function () {
    function qs(sel, ctx) { return (ctx || document).querySelector(sel); }
    function qsa(sel, ctx) { return Array.prototype.slice.call((ctx || document).querySelectorAll(sel)); }

    const modal = qs('#hh-cr-modal');
    if (!modal) return;

    const inputRequestId = qs('#hh-cr-request-id');
    const selectUser = qs('#hh-cr-assigned-user');
    const btnCancel = qs('#hh-cr-cancel');
    const btnSave = qs('#hh-cr-save');
    const msg = qs('#hh-cr-msg');

    function openModal(requestId) {
        inputRequestId.value = String(requestId);
        selectUser.value = "0";
        msg.style.display = 'none';
        msg.textContent = '';
        modal.style.display = 'block';
    }

    function closeModal() {
        modal.style.display = 'none';
    }

    // Open modal
    qsa('.hh-pass-in-progress').forEach(btn => {
        btn.addEventListener('click', function () {
            const requestId = parseInt(this.getAttribute('data-request-id') || '0', 10);
            if (!requestId) return;
            openModal(requestId);
        });
    });

    // Close handlers (robusto)
    const backdrop = qs('.hh-modal__backdrop', modal);
    if (backdrop) backdrop.addEventListener('click', closeModal);
    if (btnCancel) btnCancel.addEventListener('click', closeModal);

    // Save (NEW -> IN PROGRESS)
    if (btnSave) {
        btnSave.addEventListener('click', async function () {
            const requestId = parseInt(inputRequestId.value || '0', 10);
            const assignedUserId = parseInt(selectUser.value || '0', 10);

            // Si no elige usuario -> no hacemos nada (se queda en New)
            if (!assignedUserId || assignedUserId <= 0) {
                closeModal();
                return;
            }

            // Tomamos nonce desde cualquier card (todas lo tienen igual)
            const anyCard = document.querySelector('.hh-card[data-nonce]');
            const nonce = anyCard ? anyCard.getAttribute('data-nonce') : '';

            msg.style.display = 'none';
            msg.textContent = '';

            const formData = new FormData();
            formData.append('action', 'hh_condition_request_pass_in_progress');
            formData.append('nonce', nonce);
            formData.append('request_id', requestId);
            formData.append('assigned_user_id', assignedUserId);

            try {
                const res = await fetch(ajaxurl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    body: formData
                });

                const json = await res.json();

                if (!json || !json.success) {
                    msg.style.display = 'block';
                    msg.textContent = (json && json.data && json.data.message) ? json.data.message : 'Update failed.';
                    return;
                }

                // Recargamos para ver el item en la otra columna
                window.location.reload();
            } catch (e) {
                msg.style.display = 'block';
                msg.textContent = 'AJAX error: ' + (e && e.message ? e.message : e);
            }
        });
    }

    // IN PROGRESS -> COMPLETED
    document.addEventListener("click", async (e) => {
        const btn = e.target.closest(".hh-pass-completed");
        if (!btn) return;

        const card = btn.closest(".hh-card");
        const requestId = parseInt(btn.dataset.requestId || card?.dataset.requestId || "0", 10);
        const nonce = card?.dataset.nonce || "";

        if (!requestId || !nonce) return;

        btn.disabled = true;

        try {
            const body = new URLSearchParams();
            body.append("action", "hh_condition_request_pass_completed");
            body.append("nonce", nonce);
            body.append("request_id", String(requestId));

            const res = await fetch(ajaxurl, {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8" },
                body: body.toString(),
                credentials: "same-origin",
            });

            const json = await res.json();

            if (!json || !json.success) {
                alert(json?.data?.message || "Could not update.");
                btn.disabled = false;
                return;
            }

            // Opcional: si prefieres consistencia total con DB:
            window.location.reload();

            // ✅ Mover en UI a columna "completed"
            /*const completedCol = document.querySelector('.hh-col[data-col="completed"] .hh-col__list');
            if (completedCol && card) {
                completedCol.prepend(card);
            }

            // ✅ Actualizar contadores
            updateColumnCounts();*/

        } catch (err) {
            console.error(err);
            alert("Request failed.");
            btn.disabled = false;
        }
    });

    // Helper: recalcula contadores
    function updateColumnCounts() {
        document.querySelectorAll(".hh-col").forEach((col) => {
            const list = col.querySelector(".hh-col__list");
            const countEl = col.querySelector(".hh-col__count");
            if (!list || !countEl) return;
            countEl.textContent = list.querySelectorAll(".hh-card").length;
        });
    }

})();