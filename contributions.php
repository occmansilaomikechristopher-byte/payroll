<style>
    .contribution-name {
        color: #344054;
        font-size: 13px;
        font-weight: 600;
    }

    .contribution-icon {
        align-items: center;
        background: #e6f5f3;
        border-radius: 8px;
        color: #219688;
        display: inline-flex;
        height: 32px;
        justify-content: center;
        width: 32px;
    }

    #data-table thead th {
        background-color: #219688 !important;
        border-color: #176358 !important;
        color: #fff !important;
        font-size: 12px;
        letter-spacing: .02em;
        text-transform: uppercase;
    }

    #data-table tbody tr:hover td {
        background: #f0faf9;
    }
</style>

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                        <h4 class="mb-sm-0">
                            <i class="ri-hand-coin-line me-2" style="color:#219688;"></i>Contributions
                        </h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript:void(0);">Pages</a></li>
                                <li class="breadcrumb-item active">Contributions</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <div class="card" style="border-top:3px solid #219688;">
                    <div class="card-header align-items-center d-flex py-2">
                        <h4 class="card-title mb-0 flex-grow-1">
                            <i class="ri-hand-coin-line me-2" style="color:#219688;"></i>Contribution List
                            <?php
                            $query = $conn->query("SELECT contribution FROM contributions ORDER BY contribution ASC");
                            $contributions = [];
                            $contributionsQueryError = $query === false;
                            if (!$contributionsQueryError) {
                                while ($row = $query->fetch_assoc()) {
                                    $contributions[] = $row;
                                }
                            }
                            ?>
                            <span class="badge ms-1" style="background:#e6f5f3;color:#219688;font-size:11px;font-weight:700;vertical-align:middle;">
                                <?= count($contributions) ?>
                            </span>
                        </h4>
                    </div>

                    <div class="card-body">
                        <?php if ($contributionsQueryError): ?>
                            <div class="alert alert-danger d-flex align-items-center gap-2 mb-3" role="alert">
                                <i class="ri-error-warning-line"></i>
                                Unable to load contributions. Please try again.
                            </div>
                        <?php endif; ?>
                        <div class="table-responsive mt-2 mb-1">
                            <table id="data-table" class="table table-hover table-bordered dt-responsive nowrap align-middle">
                                <thead>
                                    <tr>
                                        <th><i class="ri-hand-coin-line me-1"></i>Contribution</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($contributions) > 0): ?>
                                        <?php foreach ($contributions as $row): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <span class="contribution-icon"><i class="ri-shield-check-line"></i></span>
                                                        <span class="contribution-name"><?= htmlspecialchars($row['contribution'], ENT_QUOTES, 'UTF-8') ?></span>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td class="text-center text-muted py-4">
                                                <i class="ri-inbox-line d-block fs-24 mb-2"></i>
                                                No contributions found.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
