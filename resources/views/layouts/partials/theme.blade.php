{{--
    Design System terpadu Teman Belajar Udinus.
    Di-include di layouts/app.blade.php supaya tersedia di semua halaman.
    Tone: navy profesional, clean. Token --tb-* sudah didefinisikan di :root app.blade.php.
--}}
<style>
    /* ============ PAGE HEAD ============ */
    .tb-page-head { display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 0.75rem; margin-bottom: 1.25rem; }
    .tb-page-head-text { min-width: 0; flex: 1 1 200px; }
    .tb-page-head-text h1 { font-size: clamp(1.1rem, 2.8vw, 1.35rem); font-weight: 800; color: var(--tb-ink); margin: 0 0 0.15rem; letter-spacing: -0.02em; line-height: 1.25; word-break: break-word; }
    .tb-page-head-text p { font-size: 0.82rem; color: var(--tb-muted); margin: 0; line-height: 1.45; }
    .tb-page-head-actions { display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap; }

    /* ============ CARD ============ */
    .tb-card {
        background: white; border: 1px solid var(--tb-primary-light);
        border-radius: 0.75rem; padding: 1.1rem;
    }
    .tb-card + .tb-card { margin-top: 1rem; }
    .tb-card-flush { padding: 0; overflow: hidden; }

    /* ============ SECTION HEAD (dalam card) ============ */
    .tb-section-head { display: flex; align-items: center; justify-content: space-between; gap: 0.65rem; margin-bottom: 0.95rem; }
    .tb-section-head-left { display: flex; align-items: center; gap: 0.65rem; min-width: 0; }
    .tb-section-icon {
        width: 36px; height: 36px; border-radius: 0.5rem; flex-shrink: 0;
        background: var(--tb-primary-light); color: var(--tb-primary);
        display: flex; align-items: center; justify-content: center; font-size: 1rem;
    }
    .tb-section-title { font-size: 0.92rem; font-weight: 700; color: var(--tb-ink); margin: 0; }
    .tb-section-desc { font-size: 0.76rem; color: var(--tb-muted); margin: 0.1rem 0 0; line-height: 1.4; }
    .tb-link-more {
        display: inline-flex; align-items: center; gap: 0.3rem;
        font-size: 0.78rem; color: var(--tb-primary); font-weight: 600;
        text-decoration: none; white-space: nowrap;
        transition: color 0.15s ease;
    }
    .tb-link-more svg { width: 0.85rem; height: 0.85rem; flex-shrink: 0; }
    .tb-link-more:hover { color: var(--tb-primary-dark); text-decoration: underline; }

    /* ============ BUTTONS ============ */
    .tb-btn {
        display: inline-flex; align-items: center; justify-content: center; gap: 0.45rem;
        min-height: 42px; height: auto; padding: 0.55rem 1.1rem;
        background: var(--tb-primary); color: white; border: 1px solid var(--tb-primary);
        border-radius: 0.5rem; font-size: 0.85rem; font-weight: 600; cursor: pointer;
        text-decoration: none; transition: background 0.15s ease, transform 0.1s ease;
        white-space: nowrap; line-height: 1.2;
    }
    .tb-btn:hover { background: var(--tb-primary-dark); border-color: var(--tb-primary-dark); color: white; }
    .tb-btn:active { transform: translateY(1px); }
    .tb-btn-sm { height: 36px; padding: 0 0.8rem; font-size: 0.8rem; }
    .tb-btn-outline { background: white; color: var(--tb-primary); }
    .tb-btn-outline:hover { background: var(--tb-primary-light); color: var(--tb-primary); border-color: var(--tb-primary); }
    .tb-btn-ghost { background: transparent; color: var(--tb-muted); border-color: var(--tb-primary-light); }
    .tb-btn-ghost:hover { background: var(--tb-primary-soft); color: var(--tb-ink); border-color: var(--tb-primary-light); }
    .tb-btn-danger { background: #c0392b; border-color: #c0392b; color: white; }
    .tb-btn-danger:hover { background: #962d22; border-color: #962d22; color: white; }
    .tb-btn-block { width: 100%; }

    /* ============ FORM ============ */
    .tb-label { font-size: 0.8rem; font-weight: 600; color: var(--tb-ink); margin: 0 0 0.4rem; display: block; }
    .tb-label .req { color: #c0392b; margin-left: 0.15rem; }
    .tb-input, .tb-select, .tb-textarea {
        width: 100%; height: 44px; padding: 0 0.85rem;
        border: 1px solid var(--tb-primary-light); border-radius: 0.5rem; background: white;
        font-size: 0.86rem; color: var(--tb-ink); outline: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .tb-textarea { height: auto; padding: 0.65rem 0.85rem; resize: vertical; line-height: 1.5; }
    .tb-select {
        appearance: none; cursor: pointer; padding-right: 2.2rem;
        background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath fill='none' stroke='%235a6b7d' stroke-width='1.5' d='M4 6l4 4 4-4'/%3E%3C/svg%3E");
        background-repeat: no-repeat; background-position: right 0.8rem center; background-size: 13px;
    }
    .tb-input:focus, .tb-select:focus, .tb-textarea:focus { border-color: var(--tb-primary); box-shadow: 0 0 0 3px rgba(11,37,91,0.10); }
    .tb-input.is-invalid, .tb-select.is-invalid, .tb-textarea.is-invalid { border-color: #c0392b; }
    .tb-input[readonly] { background: var(--tb-primary-soft); color: var(--tb-muted); }
    .tb-field-error { font-size: 0.75rem; color: #c0392b; margin-top: 0.35rem; font-weight: 500; }
    .tb-field-group { margin-bottom: 1rem; }
    .tb-field-group:last-child { margin-bottom: 0; }
    .tb-row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; }
    @media (max-width: 575.98px) { .tb-row-2 { grid-template-columns: 1fr; } }
    .tb-input-prefix { display: flex; align-items: stretch; }
    .tb-prefix-label {
        display: flex; align-items: center; padding: 0 0.65rem;
        border: 1px solid var(--tb-primary-light); border-right: none; border-radius: 0.5rem 0 0 0.5rem;
        background: var(--tb-primary-soft); color: var(--tb-muted); font-size: 0.85rem; font-weight: 600;
    }
    .tb-input-prefix .tb-input { border-radius: 0 0.5rem 0.5rem 0; }
    .tb-input-prefix:has(.is-invalid) .tb-prefix-label { border-color: #c0392b; }

    /* ============ CHECKBOX/OPTION CARDS ============ */
    .tb-opt-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0.5rem; }
    @media (max-width: 575.98px) { .tb-opt-grid { grid-template-columns: 1fr 1fr; } }
    .tb-opt {
        display: flex; align-items: center; gap: 0.5rem;
        padding: 0.6rem 0.75rem; background: var(--tb-primary-soft);
        border: 1px solid var(--tb-primary-light); border-radius: 0.5rem;
        cursor: pointer; transition: border-color 0.15s ease, background 0.15s ease;
        font-size: 0.8rem; color: var(--tb-ink); user-select: none; line-height: 1.3;
    }
    .tb-opt:hover { border-color: var(--tb-primary); background: white; }
    .tb-opt input { accent-color: var(--tb-primary); margin: 0; width: 15px; height: 15px; flex-shrink: 0; }
    .tb-opt:has(input:checked) { border-color: var(--tb-primary); background: var(--tb-primary-light); font-weight: 600; }

    /* ============ BADGE / CHIP ============ */
    .tb-chip {
        display: inline-flex; align-items: center; gap: 0.3rem;
        font-size: 0.72rem; padding: 0.25rem 0.55rem; border-radius: 0.35rem;
        background: var(--tb-primary-light); color: var(--tb-primary); font-weight: 600;
    }
    .tb-badge {
        display: inline-flex; align-items: center; gap: 0.35rem;
        font-size: 0.74rem; padding: 0.3rem 0.65rem; border-radius: 0.4rem; font-weight: 600;
    }
    .tb-badge-navy { background: var(--tb-primary-light); color: var(--tb-primary); }
    .tb-badge-warn { background: var(--tb-accent-light); color: var(--tb-accent-dark); }
    .tb-badge-success { background: #e3f7ec; color: #1d8a4e; }
    .tb-badge-danger { background: #fdeaea; color: #c0392b; }
    .tb-badge-muted { background: var(--tb-primary-soft); color: var(--tb-muted); }

    /* ============ STAT ============ */
    .tb-stat { background: white; border: 1px solid var(--tb-primary-light); border-radius: 0.6rem; padding: 0.85rem 1rem; }
    .tb-stat-row { display: flex; align-items: center; justify-content: space-between; }
    .tb-stat-num { font-size: 1.5rem; font-weight: 800; color: var(--tb-primary); line-height: 1.1; }
    .tb-stat-label { font-size: 0.74rem; color: var(--tb-muted); margin-top: 0.15rem; }
    .tb-stat-icon {
        width: 38px; height: 38px; border-radius: 0.5rem;
        background: var(--tb-primary-light); color: var(--tb-primary);
        display: flex; align-items: center; justify-content: center; font-size: 1.05rem; flex-shrink: 0;
    }

    /* ============ TABLE ============ */
    .tb-table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: 0.84rem; }
    .tb-table th {
        background: var(--tb-primary-soft); color: var(--tb-primary);
        font-size: 0.72rem; font-weight: 700; text-align: left;
        padding: 0.65rem 0.85rem; text-transform: uppercase; letter-spacing: 0.03em;
        border-bottom: 1px solid var(--tb-primary-light); white-space: nowrap;
    }
    .tb-table th:first-child { border-radius: 0.5rem 0 0 0; }
    .tb-table th:last-child { border-radius: 0 0.5rem 0 0; }
    .tb-table td { padding: 0.7rem 0.85rem; border-bottom: 1px solid var(--tb-primary-light); color: var(--tb-ink); vertical-align: middle; }
    .tb-table tbody tr:last-child td { border-bottom: none; }
    .tb-table tbody tr:hover { background: var(--tb-primary-soft); }
    .tb-table-wrap {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        overscroll-behavior-x: contain;
        max-width: 100%;
    }
    .tb-table-wrap .tb-table { min-width: 640px; }

    /* Action buttons di tabel: tetap rapi di layar sempit */
    .tb-table .tb-actions,
    .tb-table td .tb-btn-group,
    .tb-table td form {
        display: inline-flex;
        flex-wrap: wrap;
        gap: 0.35rem;
        align-items: center;
    }

    @media (max-width: 767.98px) {
        .tb-card { padding: 0.9rem; border-radius: 0.65rem; }
        .tb-section-head { flex-wrap: wrap; align-items: flex-start; }
        .tb-section-title { font-size: 0.88rem; }
        .tb-table { font-size: 0.8rem; }
        .tb-table th, .tb-table td { padding: 0.55rem 0.65rem; }
        .tb-table-wrap .tb-table { min-width: 560px; }
        .tb-btn { padding: 0.5rem 0.85rem; font-size: 0.82rem; }
        .tb-page-head { margin-bottom: 1rem; }
        .tb-empty { padding: 1.5rem 0.75rem; }
    }

    @media (max-width: 575.98px) {
        .tb-page-head-actions .tb-btn { width: 100%; }
        .tb-page-head-actions { width: 100%; }
    }

    /* ============ EMPTY STATE ============ */
    .tb-empty { text-align: center; padding: 2rem 1rem; }
    .tb-empty-icon {
        width: 52px; height: 52px; border-radius: 50%;
        background: var(--tb-primary-light); color: var(--tb-primary);
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 1.4rem; margin-bottom: 0.7rem;
    }
    .tb-empty-title { font-size: 0.95rem; font-weight: 700; color: var(--tb-ink); margin: 0 0 0.3rem; }
    .tb-empty-desc { font-size: 0.8rem; color: var(--tb-muted); margin: 0 0 1rem; line-height: 1.5; max-width: 360px; margin-left: auto; margin-right: auto; }

    /* ============ MISC ============ */
    .tb-muted { color: var(--tb-muted); }
    .tb-text-sm { font-size: 0.82rem; }
    .tb-back {
        display: inline-flex; align-items: center; gap: 0.35rem;
        color: var(--tb-muted); font-size: 0.82rem; font-weight: 500; text-decoration: none;
        margin-bottom: 0.75rem; padding: 0.35rem 0.6rem; border-radius: 0.4rem;
        transition: all 0.15s ease;
    }
    .tb-back:hover { color: var(--tb-primary); background: var(--tb-primary-soft); }
    .tb-divider { height: 1px; background: var(--tb-primary-light); border: none; margin: 1rem 0; }
</style>
