(function () {

    function qs(sel, ctx) {
        return (ctx || document).querySelector(sel);
    }

    function qsa(sel, ctx) {
        return Array.prototype.slice.call((ctx || document).querySelectorAll(sel));
    }

    // =========================
    // Helpers
    // =========================
    function getNonceFromAnyCard() {
        const anyCard = document.querySelector('.hh-card[data-nonce]');
        return anyCard ? anyCard.getAttribute('data-nonce') : '';
    }

    // =====================================================
    // 1) NEW -> CLIENT_CONTACTED
    // =====================================================
    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('.hh-eval-pass-client-contacted');
        if (!btn) return;

        const card = btn.closest('.hh-card');
        const requestId = parseInt(
            btn.dataset.requestId || card?.dataset.requestId || '0',
            10
        );
        const nonce = card?.dataset.nonce || getNonceFromAnyCard();

        if (!requestId || !nonce) return;

        btn.disabled = true;

        const formData = new FormData();
        formData.append('action', 'hh_eval_request_pass_client_contacted');
        formData.append('nonce', nonce);
        formData.append('request_id', requestId);

        try {
            const res = await fetch(ajaxurl, {
                method: 'POST',
                credentials: 'same-origin',
                body: formData
            });

            const json = await res.json();

            if (!json || !json.success) {
                alert(json?.data?.message || 'Update failed.');
                btn.disabled = false;
                return;
            }

            window.location.reload();

        } catch (err) {
            console.error(err);
            alert('Server error.');
            btn.disabled = false;
        }
    });

    // =====================================================
    // 2) CLIENT_CONTACTED -> ASSIGNED (modal)
    // =====================================================
    const modal = qs('#hh-eval-modal');
    if (!modal) return;

    const inputRequestId = qs('#hh-eval-request-id');
    const selectUser = qs('#hh-eval-assigned-user');
    const btnCancel = qs('#hh-eval-cancel');
    const btnSave = qs('#hh-eval-save');
    const msg = qs('#hh-eval-msg');

    function openModal(requestId) {
        inputRequestId.value = requestId;
        selectUser.value = '0';
        msg.style.display = 'none';
        msg.textContent = '';
        modal.style.display = 'block';
    }

    function closeModal() {
        modal.style.display = 'none';
    }

    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.hh-eval-pass-assigned');
        if (!btn) return;

        const requestId = parseInt(btn.dataset.requestId || '0', 10);
        if (!requestId) return;

        openModal(requestId);
    });

    qs('.hh-modal__backdrop', modal).addEventListener('click', closeModal);
    btnCancel.addEventListener('click', closeModal);

    btnSave.addEventListener('click', async () => {
        const requestId = parseInt(inputRequestId.value || '0', 10);
        const assignedUserId = parseInt(selectUser.value || '0', 10);

        // Si no selecciona usuario → no hacemos nada
        if (!assignedUserId || assignedUserId <= 0) {
            closeModal();
            return;
        }

        const nonce = getNonceFromAnyCard();
        if (!nonce || !requestId) return;

        msg.style.display = 'none';
        msg.textContent = '';

        const formData = new FormData();
        formData.append('action', 'hh_eval_request_pass_assigned');
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
                msg.textContent = json?.data?.message || 'Update failed.';
                return;
            }

            window.location.reload();

        } catch (err) {
            console.error(err);
            msg.style.display = 'block';
            msg.textContent = 'Server error.';
        }
    });

    // =====================================================
    // 3) ASSIGNED -> UNDER_REVIEW (solo status)
    // =====================================================
    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('.hh-eval-pass-under-review');
        if (!btn) return;

        const card = btn.closest('.hh-card');
        const requestId = parseInt(
            btn.dataset.requestId || card?.dataset.requestId || '0',
            10
        );
        const nonce = card?.dataset.nonce || getNonceFromAnyCard();

        if (!requestId || !nonce) return;

        btn.disabled = true;

        const formData = new FormData();
        formData.append('action', 'hh_eval_request_pass_under_review');
        formData.append('nonce', nonce);
        formData.append('request_id', requestId);

        try {
            const res = await fetch(ajaxurl, {
                method: 'POST',
                credentials: 'same-origin',
                body: formData
            });

            const json = await res.json();

            if (!json || !json.success) {
                alert(json?.data?.message || 'Update failed.');
                btn.disabled = false;
                return;
            }

            window.location.reload();

        } catch (err) {
            console.error(err);
            alert('Server error.');
            btn.disabled = false;
        }
    });

})();