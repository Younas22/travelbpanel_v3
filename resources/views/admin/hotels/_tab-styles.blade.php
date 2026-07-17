<style>
/* Hotel Segmented Tabs */
.hotel-tabs {
    display: flex;
    gap: 0;
    background: #f1f5f9;
    border-radius: 10px;
    padding: 4px;
    width: fit-content;
    border: none;
    margin-bottom: 0;
}

.hotel-tabs .nav-item {
    list-style: none;
}

.hotel-tabs .nav-link {
    border: none !important;
    border-radius: 7px !important;
    padding: 9px 22px;
    font-size: 13.5px;
    font-weight: 500;
    color: #64748b;
    background: transparent;
    transition: all 0.18s ease;
    display: flex;
    align-items: center;
    gap: 7px;
    cursor: pointer;
    white-space: nowrap;
    text-decoration: none;
}

.hotel-tabs .nav-link i {
    font-size: 14px;
}

.hotel-tabs .nav-link:hover:not(.active):not(.tab-locked) {
    background: #e2e8f0;
    color: #334155;
}

.hotel-tabs .nav-link.active {
    background: #ffffff;
    color: #1e293b;
    font-weight: 600;
    box-shadow: 0 1px 4px rgba(0,0,0,0.12), 0 0 0 1px rgba(0,0,0,0.04);
}

.hotel-tabs .nav-link .badge {
    font-size: 11px;
    font-weight: 600;
    padding: 2px 7px;
    border-radius: 20px;
    background: #e2e8f0;
    color: #475569;
    transition: all 0.18s;
}

.hotel-tabs .nav-link.active .badge {
    background: #3b82f6;
    color: #fff;
}

.hotel-tabs .nav-link.tab-locked {
    opacity: 0.45;
    cursor: not-allowed;
}

.hotel-tabs .nav-link.tab-locked:hover {
    background: transparent;
    color: #64748b;
}
</style>
