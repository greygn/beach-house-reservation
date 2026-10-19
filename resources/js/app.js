const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

async function request(method, url, body) {
    const isForm = body instanceof FormData;
    const headers = {
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrf,
        'X-Requested-With': 'XMLHttpRequest',
    };

    // FormData sets its own multipart Content-Type with boundary.
    if (!isForm) headers['Content-Type'] = 'application/json';

    const res = await fetch(url, {
        method,
        credentials: 'same-origin',
        headers,
        body: body === undefined ? undefined : isForm ? body : JSON.stringify(body),
    });

    let data = null;
    try {
        data = await res.json();
    } catch {
        // 204: no body
    }

    if (!res.ok) {
        throw { status: res.status, data };
    }

    return data;
}

function errorText(e) {
    const d = e?.data ?? {};
    const list = d.errors ? Object.values(d.errors).flat() : [];

    if (list.length) return list.join(' ');
    if (e?.status === 403) return 'Недостаточно прав.';
    if (e?.status === 401) return 'Нужно войти.';
    if (e?.status === 419) return 'Сессия истекла. Обновите страницу.';
    if (e?.status === 429) return 'Слишком много запросов. Попробуйте позже.';

    return d.message || 'Не удалось выполнить запрос.';
}

function showError(form, text) {
    const box = form.querySelector('[data-error]') || document.getElementById('page-error');
    if (!box) return;
    box.textContent = text;
    box.classList.remove('hidden');
}

function hideError(form) {
    (form.querySelector('[data-error]') || document.getElementById('page-error'))?.classList.add('hidden');
}

// Plain form: every named field goes to JSON; checkbox -> boolean.
function formPayload(form) {
    const body = {};
    for (const el of form.elements) {
        if (!el.name || el.disabled) continue;
        body[el.name] = el.type === 'checkbox' ? el.checked : el.value;
    }
    return body;
}

function bookingPayload(form) {
    const addons = [];
    form.querySelectorAll('[data-addon]').forEach((row) => {
        const box = row.querySelector('input[type="checkbox"]');
        if (box.checked) {
            addons.push({
                addon_id: Number(box.value),
                quantity: Number(row.querySelector('input[type="number"]').value || 1),
            });
        }
    });

    return {
        house_id: Number(form.elements.house_id.value),
        time_start: form.elements.time_start.value,
        time_end: form.elements.time_end.value,
        addons,
    };
}

document.addEventListener('submit', async (e) => {
    const form = e.target.closest('form[data-api]');
    if (!form) return;

    e.preventDefault();

    if (form.dataset.confirm && !window.confirm(form.dataset.confirm)) return;

    hideError(form);
    const button = e.submitter;
    if (button) button.disabled = true;

    const method = form.dataset.method || 'POST';

    try {
        let body;
        let httpMethod = method;

        if (method === 'DELETE') {
            body = undefined;
        } else if (form.dataset.multipart !== undefined) {
            // PHP does not parse multipart bodies of PATCH/PUT: send POST and spoof the method.
            body = new FormData(form);
            if (method !== 'POST') {
                body.append('_method', method);
                httpMethod = 'POST';
            }
        } else {
            body = form.dataset.handler === 'booking' ? bookingPayload(form) : formPayload(form);
        }

        await request(httpMethod, form.dataset.api, body);

        if (form.dataset.redirect) {
            window.location.href = form.dataset.redirect;
        } else {
            window.location.reload();
        }
    } catch (err) {
        showError(form, errorText(err));
        if (button) button.disabled = false;
    }
});

// Booking form: availability of addons for the chosen period + price estimate.
function initBookingForm(form) {
    const start = form.elements.time_start;
    const end = form.elements.time_end;
    const priceHour = Number(form.dataset.priceHour);
    const priceDay = Number(form.dataset.priceDay);
    const estimate = form.querySelector('[data-estimate]');
    const rows = [...form.querySelectorAll('[data-addon]')];

    const hoursBetween = () => {
        const ms = new Date(end.value) - new Date(start.value);
        return ms > 0 ? Math.ceil(ms / 3600000) : 0;
    };

    function recount() {
        const hours = hoursBetween();
        if (!hours) {
            estimate.textContent = '';
            return;
        }

        let total = Math.floor(hours / 24) * priceDay + (hours % 24) * priceHour;

        rows.forEach((row) => {
            const box = row.querySelector('input[type="checkbox"]');
            const qty = Number(row.querySelector('input[type="number"]').value || 1);
            if (box.checked) total += Number(row.dataset.price) * hours * qty;
        });

        estimate.textContent = `Ориентировочно: ${total.toFixed(2)}`;
    }

    async function loadAvailability() {
        if (!start.value || !end.value || hoursBetween() === 0) return;

        try {
            const params = new URLSearchParams({ time_start: start.value, time_end: end.value });
            const list = await request('GET', `/api/addons/availability?${params}`);

            rows.forEach((row) => {
                const info = list.find((a) => a.id === Number(row.dataset.addon));
                const free = info ? info.available : 0;
                const box = row.querySelector('input[type="checkbox"]');
                const qty = row.querySelector('input[type="number"]');

                row.querySelector('[data-free]').textContent = free > 0 ? `свободно: ${free}` : 'нет в наличии';
                qty.max = Math.max(free, 1);
                box.disabled = free === 0;
                if (free === 0) box.checked = false;
                if (Number(qty.value) > free && free > 0) qty.value = free;
            });
        } catch {
            // the server will validate on submit anyway
        }

        recount();
    }

    [start, end].forEach((el) => el.addEventListener('change', loadAvailability));
    form.addEventListener('change', recount);
    form.addEventListener('input', recount);
}

document.querySelectorAll('form[data-handler="booking"]').forEach(initBookingForm);
