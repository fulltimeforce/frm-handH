(function () {
    function qs(sel, ctx) { return (ctx || document).querySelector(sel); }

    function getNonceFromAnyCard() {
        const anyCard = document.querySelector('.hh-card[data-nonce]');
        return anyCard ? anyCard.getAttribute('data-nonce') : '';
    }

    function getAjaxUrl() {
        // Prefer HH_EVAL.ajaxurl if localized; fallback to WP global ajaxurl
        return (window.HH_EVAL && window.HH_EVAL.ajaxurl) ? window.HH_EVAL.ajaxurl : (window.ajaxurl || '');
    }

    function postFormData(formData) {
        return fetch(getAjaxUrl(), { method: 'POST', credentials: 'same-origin', body: formData })
            .then(r => r.json());
    }

    function showMsg(el, text) {
        if (!el) return;
        el.style.display = 'block';
        el.textContent = text || '';
    }

    function hideMsg(el) {
        if (!el) return;
        el.style.display = 'none';
        el.textContent = '';
    }

    // =========================================================
    // 1) NEW -> CLIENT_CONTACTED
    // =========================================================
    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('.hh-eval-pass-client-contacted');
        if (!btn) return;

        const card = btn.closest('.hh-card');
        const requestId = parseInt(btn.dataset.requestId || card?.dataset.requestId || '0', 10);
        const nonce = card?.dataset.nonce || getNonceFromAnyCard();
        if (!requestId || !nonce) return;

        btn.disabled = true;

        const formData = new FormData();
        formData.append('action', 'hh_eval_request_pass_client_contacted');
        formData.append('nonce', nonce);
        formData.append('request_id', requestId);

        try {
            const json = await postFormData(formData);

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

    // =========================================================
    // 2) CLIENT_CONTACTED -> ASSIGNED (modal)
    // =========================================================
    const assignModal = qs('#hh-eval-modal');
    if (!assignModal) return;

    const inputRequestId = qs('#hh-eval-request-id');
    const selectUser = qs('#hh-eval-assigned-user');
    const btnCancel = qs('#hh-eval-cancel');
    const btnSave = qs('#hh-eval-save');
    const msg = qs('#hh-eval-msg');

    function openAssignModal(requestId) {
        if (inputRequestId) inputRequestId.value = String(requestId);
        if (selectUser) selectUser.value = "0";
        hideMsg(msg);
        assignModal.style.display = 'block';
    }
    function closeAssignModal() {
        assignModal.style.display = 'none';
    }

    const assignBackdrop = qs('.hh-modal__backdrop', assignModal);
    if (assignBackdrop) assignBackdrop.addEventListener('click', closeAssignModal);
    if (btnCancel) btnCancel.addEventListener('click', closeAssignModal);

    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.hh-eval-pass-assigned');
        if (!btn) return;

        const requestId = parseInt(btn.getAttribute('data-request-id') || '0', 10);
        if (!requestId) return;

        openAssignModal(requestId);
    });

    if (btnSave) {
        btnSave.addEventListener('click', async () => {
            const requestId = parseInt(inputRequestId?.value || '0', 10);
            const assignedUserId = parseInt(selectUser?.value || '0', 10);

            if (!assignedUserId || assignedUserId <= 0) {
                closeAssignModal();
                return;
            }

            const nonce = getNonceFromAnyCard();
            if (!nonce || !requestId) return;

            hideMsg(msg);

            const formData = new FormData();
            formData.append('action', 'hh_eval_request_pass_assigned');
            formData.append('nonce', nonce);
            formData.append('request_id', requestId);
            formData.append('assigned_user_id', assignedUserId);

            try {
                const json = await postFormData(formData);

                if (!json || !json.success) {
                    showMsg(msg, json?.data?.message || 'Update failed.');
                    return;
                }

                window.location.reload();
            } catch (err) {
                console.error(err);
                showMsg(msg, 'Server error.');
            }
        });
    }

    // =========================================================
    // 3) ASSIGNED -> UNDER_REVIEW (status only)
    // =========================================================
    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('.hh-eval-pass-under-review');
        if (!btn) return;

        const card = btn.closest('.hh-card');
        const requestId = parseInt(btn.dataset.requestId || card?.dataset.requestId || '0', 10);
        const nonce = card?.dataset.nonce || getNonceFromAnyCard();
        if (!requestId || !nonce) return;

        btn.disabled = true;

        const formData = new FormData();
        formData.append('action', 'hh_eval_request_pass_under_review');
        formData.append('nonce', nonce);
        formData.append('request_id', requestId);

        try {
            const json = await postFormData(formData);

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

    // =========================================================
    // 4) UNDER_REVIEW -> (CONS. CONFIRMED / NOT CONSIGNED) (modal)
    //    YES: require lot_valuation + recommended_auction_id (via jQuery UI autocomplete)
    // =========================================================
    const consModal = qs('#hh-eval-consignment-modal');
    if (!consModal) return;

    const consBackdrop = qs('.hh-modal__backdrop', consModal);
    const consCancel = qs('#hh-eval-consignment-cancel');
    const consSave = qs('#hh-eval-consignment-save');
    const consMsg = qs('#hh-eval-consignment-msg');

    const consRequestId = qs('#hh-eval-consignment-request-id');
    const consAccepted = qs('#hh-eval-consignment-accepted');

    const btnYes = qs('#hh-eval-consignment-yes');
    const btnNo = qs('#hh-eval-consignment-no');

    const reasonWrap = qs('#hh-eval-consignment-reason-wrap');
    const reasonInput = qs('#hh-eval-consignment-reason');

    // YES-only fields
    const yesFieldsWrap = qs('#hh-eval-consignment-yes-fields');
    const valuationInput = qs('#hh-eval-lot-valuation');

    // IMPORTANT:
    // For jQuery UI autocomplete this MUST be an <input>, not a <select>.
    const auctionInput = qs('#hh-eval-recommended-auction');              // <input type="text">
    const auctionIdHidden = qs('#hh-eval-recommended-auction-id');        // <input type="hidden">

    function bindPoundPrefix(el) {
        if (!el) return;

        const normalize = () => {
            let v = (el.value || '');

            // quitar todos los £ y espacios
            v = v.replace(/£/g, '').trim();

            // dejar solo números y punto
            v = v.replace(/[^\d.]/g, '');

            // evitar múltiples puntos
            const firstDot = v.indexOf('.');
            if (firstDot !== -1) {
                v = v.substring(0, firstDot + 1) + v.substring(firstDot + 1).replace(/\./g, '');
            }

            el.value = '£' + v;
        };

        // inicial (por si el campo viene vacío)
        if (!el.value || !el.value.startsWith('£')) el.value = '£';

        el.addEventListener('focus', () => {
            if (!el.value || !el.value.startsWith('£')) el.value = '£';
            // cursor al final
            setTimeout(() => {
                const len = el.value.length;
                el.setSelectionRange(len, len);
            }, 0);
        });

        el.addEventListener('input', () => {
            const pos = el.selectionStart || 0;
            normalize();
            // evitar que el cursor se vaya antes del £
            const newPos = Math.max(1, pos);
            setTimeout(() => el.setSelectionRange(newPos, newPos), 0);
        });

        el.addEventListener('keydown', (e) => {
            // impedir borrar el símbolo
            if ((e.key === 'Backspace' || e.key === 'Delete') && (el.selectionStart || 0) <= 1) {
                e.preventDefault();
            }
        });
    }
    bindPoundPrefix(valuationInput);

    // Init Auction autocomplete (AJAX)
    function initAuctionAutocomplete() {
        if (!auctionInput) return;
        if (!(window.jQuery && window.jQuery.ui && window.jQuery.ui.autocomplete)) return;

        // Avoid double-init
        const $ = window.jQuery;
        const $input = $(auctionInput);
        if ($input.data('hh-auction-autocomplete-init')) return;
        $input.data('hh-auction-autocomplete-init', true);

        $input.autocomplete({
            minLength: 2,
            delay: 250,
            source: function (request, response) {
                const nonce = (window.HH_EVAL && window.HH_EVAL.auctionSearchNonce) ? window.HH_EVAL.auctionSearchNonce : getNonceFromAnyCard();

                const formData = new FormData();
                formData.append('action', 'hh_search_auctions');
                formData.append('nonce', nonce);
                formData.append('term', request.term || '');

                fetch(getAjaxUrl(), { method: 'POST', credentials: 'same-origin', body: formData })
                    .then(r => r.json())
                    .then(json => {
                        // Expected: { success:true, data:[{label,value,id}] }
                        if (!json || !json.success || !Array.isArray(json.data)) {
                            response([]);
                            return;
                        }
                        response(json.data);
                    })
                    .catch(() => response([]));
            },
            focus: function (event, ui) {
                // keep label in input while navigating
                event.preventDefault();
                $input.val(ui.item.label || ui.item.value || '');
                return false;
            },
            select: function (event, ui) {
                event.preventDefault();
                $input.val(ui.item.label || ui.item.value || '');
                if (auctionIdHidden) auctionIdHidden.value = String(ui.item.id || 0);
                return false;
            }
        });

        // If user types manually, invalidate selected id
        $input.on('input', function () {
            if (!auctionIdHidden) return;
            if (!this.value || this.value.length < 2) {
                auctionIdHidden.value = "0";
            } else {
                // user changed text: require re-select
                auctionIdHidden.value = "0";
            }
        });
    }
    initAuctionAutocomplete();

    function openConsModal(requestId) {
        if (consRequestId) consRequestId.value = String(requestId);
        if (consAccepted) consAccepted.value = "1";

        if (reasonInput) reasonInput.value = "";
        if (reasonWrap) reasonWrap.style.display = "none";

        if (valuationInput) valuationInput.value = "£";
        if (yesFieldsWrap) yesFieldsWrap.style.display = "none";

        if (auctionInput) auctionInput.value = "";
        if (auctionIdHidden) auctionIdHidden.value = "0";

        hideMsg(consMsg);
        if (consSave) consSave.disabled = false;

        // default YES
        setAccepted(true);

        consModal.style.display = "block";
    }

    function closeConsModal() {
        consModal.style.display = "none";
    }

    function setAccepted(isYes) {
        if (consAccepted) consAccepted.value = isYes ? "1" : "0";

        // YES: show valuation + auction input
        if (yesFieldsWrap) yesFieldsWrap.style.display = isYes ? "block" : "none";
        // NO: show reason
        if (reasonWrap) reasonWrap.style.display = isYes ? "none" : "block";

        if (btnYes) btnYes.classList.toggle('button-primary', isYes);
        if (btnNo) btnNo.classList.toggle('button-primary', !isYes);

        if (!isYes) {
            // if (valuationInput) valuationInput.value = "";
            if (valuationInput) valuationInput.value = "£";
            if (auctionInput) auctionInput.value = "";
            if (auctionIdHidden) auctionIdHidden.value = "0";
        } else {
            if (reasonInput) reasonInput.value = "";
        }

        hideMsg(consMsg);
    }

    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.hh-eval-consignment-decision');
        if (!btn) return;

        const card = btn.closest('.hh-card');
        const requestId = parseInt(btn.dataset.requestId || card?.dataset.requestId || '0', 10);
        if (!requestId) return;

        openConsModal(requestId);

        // Ensure autocomplete is available each time modal is opened
        initAuctionAutocomplete();
    });

    if (btnYes) btnYes.addEventListener('click', () => setAccepted(true));
    if (btnNo) btnNo.addEventListener('click', () => setAccepted(false));

    if (consBackdrop) consBackdrop.addEventListener('click', closeConsModal);
    if (consCancel) consCancel.addEventListener('click', closeConsModal);

    if (consSave) {
        consSave.addEventListener('click', async () => {
            const requestId = parseInt(consRequestId?.value || "0", 10);
            const accepted = (consAccepted?.value === "1");
            const nonce = getNonceFromAnyCard();

            if (!requestId || !nonce) return;

            hideMsg(consMsg);

            const reason = (reasonInput?.value || "").trim();

            // If NO: reason required
            if (!accepted && reason.length < 3) {
                showMsg(consMsg, "Reason is required when selecting No.");
                return;
            }

            // If YES: valuation + auction required
            let lotValuation = null;
            let recommendedAuctionId = 0;

            if (accepted) {
                // lotValuation = (valuationInput?.value || '').trim();
                lotValuation = (valuationInput?.value || '').replace('£', '').trim();

                // numeric with optional decimals
                const re = /^\d+(\.\d+)?$/;
                if (!lotValuation || !re.test(lotValuation)) {
                    showMsg(consMsg, "Lot valuation is required and must be numeric (use '.' for decimals).");
                    return;
                }

                recommendedAuctionId = parseInt(auctionIdHidden?.value || '0', 10);
                if (!recommendedAuctionId || recommendedAuctionId <= 0) {
                    showMsg(consMsg, "Please select a recommended auction from the list.");
                    return;
                }
            }

            consSave.disabled = true;

            const formData = new FormData();
            formData.append('action', 'hh_eval_request_set_consignment_result');
            formData.append('nonce', nonce);
            formData.append('request_id', requestId);
            formData.append('accepted', accepted ? '1' : '0');
            formData.append('reason', reason);

            if (accepted) {
                formData.append('lot_valuation', lotValuation);
                formData.append('recommended_auction_id', String(recommendedAuctionId));
            }

            try {
                const json = await postFormData(formData);

                if (!json || !json.success) {
                    showMsg(consMsg, json?.data?.message || "Update failed.");
                    consSave.disabled = false;
                    return;
                }

                window.location.reload();
            } catch (err) {
                console.error(err);
                showMsg(consMsg, "Server error.");
                consSave.disabled = false;
            }
        });
    }

    // =========================================================
    // 5) CONSIGNMENT_CONFIRMED -> IN_PROGRESS (status only)
    // =========================================================
    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('.hh-eval-pass-in-progress');
        if (!btn) return;

        const card = btn.closest('.hh-card');
        const requestId = parseInt(btn.dataset.requestId || card?.dataset.requestId || '0', 10);
        const nonce = card?.dataset.nonce || getNonceFromAnyCard();
        if (!requestId || !nonce) return;

        btn.disabled = true;

        const formData = new FormData();
        formData.append('action', 'hh_eval_request_pass_in_progress');
        formData.append('nonce', nonce);
        formData.append('request_id', requestId);

        try {
            const json = await postFormData(formData);

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

    // =========================================================
    // 6) IN_PROGRESS -> FINALISED (modal + AJAX vehicles search)
    // =========================================================
    const finModal = qs('#hh-eval-finalise-modal');
    if (!finModal) return;

    const finBackdrop = qs('.hh-modal__backdrop', finModal);
    const finCancel = qs('#hh-eval-finalise-cancel');
    const finSave = qs('#hh-eval-finalise-save');
    const finMsg = qs('#hh-eval-finalise-msg');

    const finRequestId = qs('#hh-eval-finalise-request-id');
    const finVehicleId = qs('#hh-eval-finalise-vehicle-id');
    const finSearch = qs('#hh-eval-finalise-vehicle-search');
    const finPreview = qs('#hh-eval-finalise-vehicle-preview');

    function openFinaliseModal(requestId) {
        if (finRequestId) finRequestId.value = String(requestId);
        if (finVehicleId) finVehicleId.value = "0";
        if (finSearch) finSearch.value = "";
        if (finPreview) finPreview.textContent = "—";
        hideMsg(finMsg);
        if (finSave) finSave.disabled = false;
        finModal.style.display = "block";
    }

    function closeFinaliseModal() {
        finModal.style.display = "none";
    }

    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.hh-eval-pass-finalised');
        if (!btn) return;

        const card = btn.closest('.hh-card');
        const requestId = parseInt(btn.dataset.requestId || card?.dataset.requestId || '0', 10);
        if (!requestId) return;

        openFinaliseModal(requestId);
    });

    if (finBackdrop) finBackdrop.addEventListener('click', closeFinaliseModal);
    if (finCancel) finCancel.addEventListener('click', closeFinaliseModal);

    // Vehicles autocomplete: searches vehicles by title (AJAX)
    if (finSearch && window.jQuery && window.jQuery.ui && window.jQuery.ui.autocomplete) {
        window.jQuery(finSearch).autocomplete({
            minLength: 2,
            delay: 250,
            source: function (request, response) {
                const nonce = getNonceFromAnyCard();
                const formData = new FormData();
                formData.append('action', 'hh_eval_vehicle_search');
                formData.append('nonce', nonce);
                formData.append('term', request.term || '');

                fetch(getAjaxUrl(), { method: 'POST', credentials: 'same-origin', body: formData })
                    .then(r => r.json())
                    .then(json => {
                        if (!json || !json.success || !Array.isArray(json.data)) {
                            response([]);
                            return;
                        }
                        response(json.data); // [{label, value, id}]
                    })
                    .catch(() => response([]));
            },
            focus: function (event, ui) {
                event.preventDefault();
                window.jQuery(finSearch).val(ui.item.label || ui.item.value || '');
                return false;
            },
            select: function (event, ui) {
                event.preventDefault();
                window.jQuery(finSearch).val(ui.item.label || ui.item.value || '');
                if (finVehicleId) finVehicleId.value = String(ui.item.id || 0);
                if (finPreview) finPreview.textContent = String(ui.item.id || '—');
                return false;
            }
        });

        // If user types manually, invalidate vehicle id
        window.jQuery(finSearch).on('input', function () {
            if (!finVehicleId) return;
            finVehicleId.value = "0";
            if (finPreview) finPreview.textContent = "—";
        });
    }

    if (finSave) {
        finSave.addEventListener('click', async () => {
            const requestId = parseInt(finRequestId?.value || "0", 10);
            const vehicleId = parseInt(finVehicleId?.value || "0", 10);
            const nonce = getNonceFromAnyCard();

            if (!requestId || !nonce) return;

            hideMsg(finMsg);

            if (!vehicleId || vehicleId <= 0) {
                showMsg(finMsg, "Please select a Vehicle first.");
                return;
            }

            finSave.disabled = true;

            const formData = new FormData();
            formData.append('action', 'hh_eval_request_pass_finalised');
            formData.append('nonce', nonce);
            formData.append('request_id', requestId);
            formData.append('vehicle_id', vehicleId);

            try {
                const json = await postFormData(formData);

                if (!json || !json.success) {
                    showMsg(finMsg, json?.data?.message || "Update failed.");
                    finSave.disabled = false;
                    return;
                }

                window.location.reload();
            } catch (err) {
                console.error(err);
                showMsg(finMsg, "Server error.");
                finSave.disabled = false;
            }
        });
    }
})();