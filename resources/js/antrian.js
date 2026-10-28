function daftarChannel(value) {
    if (!Array.isArray(value) || value.length === 0
        || value.some((name) => typeof name !== 'string' || name.length === 0)) {
        throw new Error('Invalid queue channels');
    }

    return [...new Set(value)];
}

export function aktifkanAntrian(root, {
    echo = globalThis.Echo,
    request = globalThis.fetch?.bind(globalThis),
    browser = globalThis,
} = {}) {
    const content = root.querySelector('[data-antrian-content]');
    const status = root.querySelector('[data-antrian-status]');
    const connection = echo?.connector?.pusher?.connection;
    const idle = { refresh: async () => {}, stop: () => {} };
    let desired;

    try {
        desired = daftarChannel(JSON.parse(root.dataset.channels));
        if (!/^App\.Models\.User\.[1-9]\d*$/.test(desired[0])) {
            throw new Error('Invalid queue owner');
        }
    } catch {
        if (status) status.textContent = 'Pembaruan otomatis belum tersedia. Muat ulang halaman.';
        return idle;
    }

    if (!content || !status || !connection || typeof request !== 'function') {
        if (status) status.textContent = 'Pembaruan otomatis belum tersedia. Muat ulang halaman.';
        return idle;
    }

    const owner = desired[0];
    const subscriptions = new Map();
    const doc = browser.document;
    let stopped = false;
    let dirty = false;
    let running = null;
    let pendingAbort = null;
    let fetchFailed = false;

    function tampilkanStatus() {
        if (stopped) return;
        const entries = [...subscriptions.values()];

        if (fetchFailed) {
            status.textContent = 'Data antrean belum bisa diperbarui. Coba muat ulang halaman.';
        } else if (['disconnected', 'unavailable', 'failed'].includes(connection.state)) {
            status.textContent = 'Pembaruan otomatis terputus. Menunggu sambungan kembali...';
        } else if (entries.some((entry) => entry.failed)) {
            status.textContent = 'Pembaruan otomatis belum tersambung. Coba muat ulang halaman.';
        } else if (connection.state === 'connected' && entries.length > 0
            && entries.every((entry) => entry.ready)) {
            status.textContent = 'Antrean tersambung. Pembaruan otomatis aktif.';
        } else {
            status.textContent = 'Menghubungkan pembaruan antrean...';
        }
    }

    function sinkronkanChannel(names) {
        const allowed = new Set(names);

        for (const name of subscriptions.keys()) {
            if (!allowed.has(name)) {
                subscriptions.delete(name);
                echo.leave(name);
            }
        }

        for (const name of allowed) {
            if (subscriptions.has(name)) continue;

            const entry = { ready: false, failed: false };
            subscriptions.set(name, entry);

            echo.private(name)
                .listen('.antrian.diperbarui', () => {
                    if (!stopped && subscriptions.get(name) === entry) void refresh();
                })
                .subscribed(() => {
                    if (stopped || subscriptions.get(name) !== entry) return;
                    entry.ready = true;
                    entry.failed = false;
                    tampilkanStatus();
                    void refresh();
                })
                .error(() => {
                    if (stopped || subscriptions.get(name) !== entry) return;
                    entry.ready = false;
                    entry.failed = true;
                    tampilkanStatus();
                    void refresh();
                });
        }
    }

    function stop(message) {
        if (stopped) return;

        stopped = true;
        pendingAbort?.abort();

        for (const name of subscriptions.keys()) echo.leave(name);
        subscriptions.clear();

        connection.unbind('state_change', perubahanKoneksi);
        doc?.removeEventListener('visibilitychange', saatTerlihat);
        browser.removeEventListener('pagehide', saatKeluar);

        if (message) {
            content.innerHTML = '';
            status.textContent = message;
        }
    }

    function refresh() {
        if (stopped) return Promise.resolve();

        dirty = true;
        if (running) return running;

        running = Promise.resolve().then(async () => {
            try {
                while (dirty && !stopped) {
                    dirty = false;

                    const aborter = new AbortController();
                    pendingAbort = aborter;

                    const timer = browser.setTimeout(() => aborter.abort(), 10000);
                    let data;

                    try {
                        const response = await request(root.dataset.snapshotUrl, {
                            headers: {
                                Accept: 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            credentials: 'same-origin',
                            cache: 'no-store',
                            signal: aborter.signal,
                        });

                        if (stopped) return;

                        if ([401, 403, 419].includes(response.status) || response.redirected) {
                            stop('Sesi berubah atau berakhir. Muat ulang halaman untuk melanjutkan.');
                            return;
                        }

                        if (!response.ok) throw new Error('Queue request failed');

                        data = await response.json();
                    } finally {
                        browser.clearTimeout(timer);
                        if (pendingAbort === aborter) pendingAbort = null;
                    }

                    if (stopped) return;

                    const names = daftarChannel(data.channels);

                    if (names[0] !== owner) {
                        stop('Sesi akun berubah. Muat ulang halaman untuk melanjutkan.');
                        return;
                    }

                    if (typeof data.html !== 'string') {
                        throw new Error('Invalid queue response');
                    }

                    if (dirty) continue;

                    content.innerHTML = data.html;
                    desired = names;
                    root.dataset.channels = JSON.stringify(names);
                    sinkronkanChannel(names);
                    fetchFailed = false;
                }
            } catch {
                if (!stopped) fetchFailed = true;
            }
        }).finally(() => {
            running = null;
            tampilkanStatus();

            if (dirty && !stopped) void refresh();
        });

        return running;
    }

    function perubahanKoneksi({ current }) {
        if (stopped) return;

        if (current !== 'connected') {
            for (const entry of subscriptions.values()) entry.ready = false;
        }

        tampilkanStatus();

        if (current === 'connected') void refresh();
    }

    function saatTerlihat() {
        if (stopped || doc.visibilityState !== 'visible') return;

        for (const [name, entry] of subscriptions) {
            if (entry.failed) {
                subscriptions.delete(name);
                echo.leave(name);
            }
        }

        sinkronkanChannel(desired);
        void refresh();
    }

    function saatKeluar() {
        stop();
    }

    connection.bind('state_change', perubahanKoneksi);
    doc?.addEventListener('visibilitychange', saatTerlihat);
    browser.addEventListener('pagehide', saatKeluar);

    sinkronkanChannel(desired);
    tampilkanStatus();
    void refresh();

    return { refresh, stop };
}

if (typeof document !== 'undefined') {
    const mounted = new WeakMap();

    const mulai = () => {
        document.querySelectorAll('[data-antrian-live]').forEach((root) => {
            mounted.get(root)?.stop();
            mounted.set(root, aktifkanAntrian(root));
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', mulai, { once: true });
    } else {
        mulai();
    }

    globalThis.addEventListener('pageshow', (event) => {
        if (event.persisted) mulai();
    });
}