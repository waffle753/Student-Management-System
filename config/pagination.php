<?php

if (!function_exists('renderPagination')) {
    function renderPagination($currentPage, $totalPages, $offset, $recordsPerPage, $totalRecords, $label)
    {
        if ($totalRecords === 0) {
            return;
        }

        $pageUrl = function ($page) {
            $params = $_GET;
            $params['list_page'] = $page;
            return '?' . http_build_query($params);
        };

        $firstPage = max(1, $currentPage - 2);
        $lastPage = min($totalPages, $currentPage + 2);
        ?>
        <div class="pagination-container d-flex justify-content-between align-items-center flex-wrap mt-3 pt-3 border-top">
            <div class="pagination-info text-muted">
                Showing <strong><?= $offset + 1 ?></strong> -
                <strong><?= min($offset + $recordsPerPage, $totalRecords) ?></strong>
                of <strong><?= $totalRecords ?></strong> <?= htmlspecialchars($label) ?>
            </div>
            <nav aria-label="<?= htmlspecialchars($label) ?> pagination">
                <ul class="pagination mb-0">
                    <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                        <?php if ($currentPage > 1): ?>
                            <a class="page-link" href="<?= htmlspecialchars($pageUrl($currentPage - 1)) ?>">Previous</a>
                        <?php else: ?>
                            <span class="page-link">Previous</span>
                        <?php endif; ?>
                    </li>
                    <?php for ($page = $firstPage; $page <= $lastPage; $page++): ?>
                        <li class="page-item <?= $page === $currentPage ? 'active' : '' ?>">
                            <a class="page-link" href="<?= htmlspecialchars($pageUrl($page)) ?>"><?= $page ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                        <?php if ($currentPage < $totalPages): ?>
                            <a class="page-link" href="<?= htmlspecialchars($pageUrl($currentPage + 1)) ?>">Next</a>
                        <?php else: ?>
                            <span class="page-link">Next</span>
                        <?php endif; ?>
                    </li>
                </ul>
            </nav>
        </div>
        <?php
    }
}
