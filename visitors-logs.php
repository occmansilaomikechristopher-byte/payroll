<style>
    .vlog-site-code { background:#394b7c; color:#fff; padding:2px 7px; border-radius:3px; font-size:11px; font-weight:700; display:inline-block; margin-bottom:3px; font-family:monospace; }
    .vlog-site-name { font-size:12px; font-weight:600; color:#222; }
    .vlog-site-addr { font-size:11px; color:#888; }
    .vlog-name { font-weight:600; font-size:13px; }
    .vlog-company { font-size:12px; color:#555; }
    .vlog-date { font-weight:600; color:#394b7c; font-size:13px; }
    .vlog-date small { display:block; font-size:11px; color:#888; font-weight:400; }
    .vlog-thumb { width:38px; height:38px; object-fit:cover; border-radius:4px; border:2px solid #d0d7ee; cursor:pointer; transition:transform .15s; }
    .vlog-thumb:hover { transform:scale(1.1); border-color:#394b7c; }
    .vlog-filter-bar { background:#394b7c; color:#fff; border-radius:4px; padding:10px 16px; margin-bottom:10px; display:flex; align-items:center; gap:12px; flex-wrap:wrap; }
    .vlog-filter-bar .lbl { font-size:11px; opacity:.75; }
    .vlog-filter-bar .val { font-weight:700; font-size:13px; }
    .vlog-empty { text-align:center; padding:3.5rem 1rem 3rem; background:#f7f8fc; border-radius:8px; border:2px dashed #c5cde8; }
    .vlog-empty .vlog-empty-icon { width:64px; height:64px; border-radius:50%; background:#eef0f8; border:2px solid #d0d7ee; display:flex; align-items:center; justify-content:center; margin:0 auto 14px; }
    .vlog-empty .vlog-empty-icon i { font-size:28px; color:#394b7c; opacity:.6; }
    .vlog-empty h6 { color:#394b7c; font-weight:700; margin-bottom:6px; font-size:15px; }
    .vlog-empty p { color:#888; font-size:12px; margin-bottom:14px; }
    .vlog-empty-btn { display:inline-flex; align-items:center; gap:6px; padding:6px 16px; border-radius:4px; background:#394b7c; color:#fff; font-size:12px; font-weight:600; border:none; cursor:pointer; }
    .vlog-empty-btn:hover { background:#2d3d66; color:#fff; }
    #table-logs thead th { background-color:#394b7c !important; border-color:#2d3d66 !important; color:#fff !important; }
</style>

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                        <h4 class="mb-sm-0">
                            <i class="ri-user-search-line me-2" style="color:#394b7c;"></i>Visitors Log
                        </h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript:void(0);">Pages</a></li>
                                <li class="breadcrumb-item active">Visitors Log</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <div class="card" style="border-top:3px solid #394b7c;">
                    <div class="card-header align-items-center d-flex py-2">
                        <h4 class="card-title mb-0 flex-grow-1">
                            <i class="ri-user-search-line me-2" style="color:#394b7c;"></i>Visitors Log List
                        </h4>
                        <div class="d-flex gap-2">
                            <?php if (isset($_GET['from'])): ?>
                                <a href="index.php?page=visitors-logs" class="btn btn-sm btn-outline-secondary">
                                    <i class="ri-close-line me-1"></i>Clear
                                </a>
                            <?php endif; ?>
                            <button type="button" class="btn btn-sm text-white" style="background:#394b7c;border-color:#394b7c;"
                                data-bs-toggle="modal" data-bs-target="#modal-filter-add">
                                <i class="ri-filter-3-line me-1"></i>Filter
                            </button>
                        </div>
                    </div>

                    <div class="card-body">
                        <?php if (isset($_GET['from'])): ?>

                            <!-- Filter summary bar -->
                            <div class="vlog-filter-bar">
                                <i class="ri-filter-3-line" style="font-size:16px;opacity:.8;"></i>
                                <div>
                                    <div class="lbl">Date Range</div>
                                    <div class="val">
                                        <?= date('M d, Y', strtotime($_GET['from'])) ?> &ndash; <?= date('M d, Y', strtotime($_GET['to'])) ?>
                                    </div>
                                </div>
                                <?php if (!empty($_GET['site_id'])): ?>
                                    <div style="height:30px;width:1px;background:rgba(255,255,255,.3);"></div>
                                    <?php
                                    $sel_site = $conn->query("SELECT site_name FROM sites WHERE id=" . intval($_GET['site_id']))->fetch_assoc();
                                    ?>
                                    <div>
                                        <div class="lbl">Site</div>
                                        <div class="val"><?= htmlspecialchars($sel_site['site_name'] ?? '—') ?></div>
                                    </div>
                                <?php endif; ?>
                                <button type="button" class="btn btn-sm ms-auto" style="background:rgba(255,255,255,.2);color:#fff;border:1px solid rgba(255,255,255,.4);"
                                    data-bs-toggle="modal" data-bs-target="#modal-filter-add">
                                    <i class="ri-edit-2-line me-1"></i>Modify
                                </button>
                            </div>

                            <!-- Table -->
                            <div class="table-responsive">
                                <table id="table-logs" class="table table-hover table-bordered align-middle">
                                    <thead>
                                        <tr>
                                            <th style="width:60px;" class="text-center"><i class="ri-image-line me-1"></i>Photo</th>
                                            <th><i class="ri-map-pin-2-line me-1"></i>Site</th>
                                            <th style="width:160px;"><i class="ri-calendar-2-line me-1"></i>Date Visited</th>
                                            <th><i class="ri-user-3-line me-1"></i>Name</th>
                                            <th><i class="ri-building-2-line me-1"></i>Company</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $site_query = isset($_GET['site_id']) && $_GET['site_id'] !== ''
                                            ? "AND site_id = '" . intval($_GET['site_id']) . "'"
                                            : '';
                                        $from = mysqli_real_escape_string($conn, $_GET['from']);
                                        $to   = mysqli_real_escape_string($conn, $_GET['to']);
                                        $logs = $conn->query("SELECT visitors_logs.*, sites.site_name, sites.site_address, sites.site_code
                                            FROM visitors_logs
                                            LEFT JOIN sites ON visitors_logs.site_id = sites.id
                                            WHERE date_visited BETWEEN '$from' AND '$to 23:59:59'
                                            $site_query
                                            ORDER BY visitors_logs.date_visited DESC");
                                        if ($logs->num_rows > 0):
                                            while ($row = $logs->fetch_assoc()):
                                        ?>
                                            <tr>
                                                <td class="text-center">
                                                    <img class="vlog-thumb"
                                                        src="uploads/<?= htmlspecialchars($row['image']) ?>"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#lightboxModal"
                                                        data-src="uploads/<?= htmlspecialchars($row['image']) ?>"
                                                        alt="Visitor photo">
                                                </td>
                                                <td>
                                                    <span class="vlog-site-code"><?= htmlspecialchars($row['site_code']) ?></span>
                                                    <div class="vlog-site-name"><?= htmlspecialchars($row['site_name']) ?></div>
                                                    <div class="vlog-site-addr"><i class="ri-map-pin-line me-1"></i><?= htmlspecialchars($row['site_address']) ?></div>
                                                </td>
                                                <td>
                                                    <div class="vlog-date">
                                                        <?= date('M j, Y', strtotime($row['date_visited'])) ?>
                                                        <small><?= date('g:i a', strtotime($row['date_visited'])) ?> &bull; <?= date('l', strtotime($row['date_visited'])) ?></small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="vlog-name"><i class="ri-user-3-line me-1 text-muted"></i><?= htmlspecialchars($row['name']) ?></div>
                                                </td>
                                                <td>
                                                    <div class="vlog-company"><i class="ri-building-2-line me-1 text-muted"></i><?= htmlspecialchars($row['company']) ?></div>
                                                </td>
                                            </tr>
                                        <?php
                                            endwhile;
                                        else:
                                        ?>
                                            <tr>
                                                <td colspan="5">
                                                    <div class="vlog-empty">
                                                        <div class="vlog-empty-icon"><i class="ri-search-line"></i></div>
                                                        <h6>No Visitor Records Found</h6>
                                                        <p>No visitors logged within the selected date range.<br>Try adjusting your filter criteria.</p>
                                                        <button class="vlog-empty-btn" data-bs-toggle="modal" data-bs-target="#modal-filter-add">
                                                            <i class="ri-edit-2-line"></i> Modify Filter
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>

                        <?php else: ?>

                            <!-- Empty / no filter state -->
                            <div class="vlog-empty">
                                <div class="vlog-empty-icon"><i class="ri-user-search-line"></i></div>
                                <h6>No Filter Applied</h6>
                                <p>Select a date range and site to display<br>visitor log records.</p>
                                <button class="vlog-empty-btn" data-bs-toggle="modal" data-bs-target="#modal-filter-add">
                                    <i class="ri-filter-3-line"></i> Apply Filter
                                </button>
                            </div>

                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Lightbox Modal -->
<div class="modal fade" id="lightboxModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title mb-0">
                    <i class="ri-image-line me-2" style="color:#394b7c;"></i>Visitor Photo
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-2 text-center">
                <img id="lightboxImage" class="img-fluid rounded" src="" alt="Visitor photo" style="max-height:420px;">
            </div>
        </div>
    </div>
</div>

<?php include 'component/filter_logs.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Lightbox — read src from data-src attribute
    var lightboxModal = document.getElementById('lightboxModal');
    if (lightboxModal) {
        lightboxModal.addEventListener('show.bs.modal', function (e) {
            var img = e.relatedTarget;
            document.getElementById('lightboxImage').src = img ? img.getAttribute('data-src') : '';
        });
    }
});
</script>
