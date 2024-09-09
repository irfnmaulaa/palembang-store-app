<div class="dropdown">
    <button
        class="btn btn-success btn-lg dropdown-toggle"
        type="button"
        data-mdb-dropdown-init
        data-mdb-ripple-init
        aria-expanded="false"
        id=""
    >
        <i class="fas fa-file-export me-2"></i> Ekspor
    </button>
    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
        <li>
            <a class="dropdown-item" href="{{route('admin.'. $table .'.export', collect(@$param?$param:[])->merge(['type' => 'excel'])->all())}}">
                <i class="fas fa-file-excel me-1"></i> File Excel
            </a>
        </li>
        <li>
            <a class="dropdown-item" href="{{route('admin.'. $table .'.export', collect(@$param?$param:[])->merge(['type' => 'pdf'])->all())}}">
                <i class="fas fa-file-pdf me-1"></i> File PDF
            </a>
        </li>
        <li>
            <a class="dropdown-item" href="{{route('admin.'. $table .'.export', collect(@$param?$param:[])->merge(['type' => 'csv'])->all())}}">
                <i class="fas fa-file-csv me-1"></i> File CSV
            </a>
        </li>
    </ul>
</div>
