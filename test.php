<!-- Content Wrapper -->
<div class="content-wrapper">
    <!-- Content Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Management Siswa</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="?">Home</a></li>
                        <li class="breadcrumb-item active">Siswa</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <!-- Card Header -->
                        <div class="card-header">
                            <button type="button" class="btn btn-danger btn-sm float-right" id="hapusSemuaBtn">
                                <i class="fas fa-trash"></i> Hapus Semua Siswa
                            </button>                            
                        </div>
                        <!-- Card Body -->
                        <div class="card-body">
                            <div class="row align-items-center mb-3">
                                <!-- Limit Selection -->
                                <div class="col-md-4 mb-2 mb-md-0">
                                    <div class="d-flex align-items-center">
                                        <select class="form-control form-control-sm" 
                                                id="records-per-page" 
                                                style="width: 200px;">
                                            <!--<option value="5">5 per page</option>-->
                                            <option value="10">10 per page</option>
                                            <option value="25">25 per page</option>
                                            <option value="50">50 per page</option>
                                            <option value="100">100 per page</option>
                                            <option value="150">150 per page</option>
                                            <option value="200">200 per page</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Search -->
                                <div class="col-md-2 offset-md-6">
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-search text-primary"></i></span>
                                        </div>
                                        <input type="text" 
                                               id="searchInput" 
                                               class="form-control form-control-sm" 
                                               placeholder="Cari Nama atau NIS">
                                    </div>
                                </div>
                            </div>

                            <!-- Notification Area -->
                            <div id="notification-area" class="mb-3"></div>

                            <!-- Table Container -->
                            <div class="table-responsive" id="siswa-container">
                                <!-- Data loaded via AJAX -->
                            </div>
                        </div>
                        
                        <!-- Card Footer - Pagination -->
                        <div class="card-footer d-flex justify-content-center" id="pagination-container">
                            <!-- Pagination loaded via AJAX -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>


<!-- Modal: View Student Detail -->
<div class="modal fade" 
     id="viewModal" 
     tabindex="-1" 
     aria-labelledby="viewModalLabel" 
     aria-hidden="true" 
     data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-menu-gradient">
                <h5 class="modal-title" id="viewModalLabel">Detail Siswa</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Student Photo -->
                <div class="text-center mb-4">
                    <img id="view_photo" 
                         src="" 
                         class="rounded-circle img-thumbnail" 
                         style="width: 150px; height: 150px; object-fit: cover;" 
                         alt="Foto Siswa" 
                         onerror="this.onerror=null;this.src='https://placehold.co/150x150/EFEFEF/AAAAAA&text=No+Image';">
                </div>
                
                <!-- Student Details -->
                <dl class="row">
                    <dt class="col-sm-4">NAMA SISWA</dt>
                    <dd class="col-sm-8"><span id="view_pd"></span></dd>
                    
                    <dt class="col-sm-4">NIS</dt>
                    <dd class="col-sm-8"><span id="view_nis"></span></dd>
                    
                    <dt class="col-sm-4">KELAS</dt>
                    <dd class="col-sm-8"><span id="view_kelas"></span></dd>
                </dl>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-flat" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>


<script>
$(document).ready(function() {
    // ==========================================
    // INITIALIZATION
    // ==========================================
    // const viewModal = new bootstrap.Modal(document.getElementById('viewModal'));
    const PHOTO_PATH = 'file/fotopd/';
    let currentPage = 1;
    let searchKeyword = '';
    let searchTimeout;

    // ==========================================
    // UTILITY FUNCTIONS
    // ==========================================
    
    /**
     * Display temporary notification in UI
     * @param {string} pesan - Message to display
     * @param {string} status - 'success' or 'error' for alert color
     */
    function tampilkanNotifikasi(pesan, status) {
        const alertClass = status === 'success' ? 'alert-success' : 'alert-danger';
        const notifHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                ${pesan}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>`;
        $('#notification-area').html(notifHtml);

        setTimeout(() => {
            $('#notification-area .alert').alert('close');
        }, 4000);
    }

    /**
     * Load student data from server using AJAX
     * @param {number} page - Page number to load
     * @param {string} search - Search keyword
     */
    function muatData(page, search = '') {
        currentPage = page;
        searchKeyword = search;
        
        const spinner = `
            <div class="d-flex justify-content-center my-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
            </div>`;
        $('#siswa-container').html(spinner);
        $('#pagination-container').html('');

        $.ajax({
            url: 'proses_siswa.php',
            type: 'POST',
            data: {
                action: 'muat',
                page: currentPage,
                search: searchKeyword,
                limit: $('#records-per-page').val()
            },
            dataType: 'json',
            success: function(response) {
                $('#siswa-container').html(response.table);
                $('#pagination-container').html(response.pagination);

                // If current page is empty after delete, go back one page
                if ($('#siswa-container').find('tbody tr').length === 0 && currentPage > 1) {
                    muatData(currentPage - 1, searchKeyword);
                }
            },
            error: function(xhr) {
                $('#siswa-container').html('<div class="alert alert-danger">Gagal memuat data. Periksa konsol untuk detail.</div>');
                console.error("AJAX Error:", xhr.responseText);
            }
        });
    }

    // ==========================================
    // EVENT LISTENERS
    // ==========================================
    
    // Load data on first load
    muatData(currentPage);

    // Limit change
    $('#records-per-page').on('change', function() {
        muatData(1, searchKeyword);
    });

    // Search with debounce to reduce server requests
    $('#searchInput').on('keyup', function() {
        clearTimeout(searchTimeout);
        const keyword = $(this).val();
        searchTimeout = setTimeout(() => {
            muatData(1, keyword);
        }, 300); // Wait 300ms after user stops typing
    });

    // Pagination link click
    $('#pagination-container').on('click', 'a.page-link', function(e) {
        e.preventDefault();
        const pageItem = $(this).parent();
        if (pageItem.hasClass('disabled') || pageItem.hasClass('active')) {
            return;
        }
        muatData($(this).data('page'), searchKeyword);
    });

    // "Delete All Data" button
    $('#hapusSemuaBtn').on('click', function() {
        if (!confirm('Apakah Anda benar-benar yakin? Semua data siswa akan dihapus secara permanen. Tindakan ini tidak dapat dipulihkan.')) {
            return;
        }

        const btn = $(this);
        const originalText = btn.html();
        btn.prop('disabled', true).html(`<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menghapus...`);

        $.ajax({
            url: 'proses_siswa.php',
            type: 'POST',
            data: {
                action: 'hapus_semua'
            },
            dataType: 'json',
            success: function(res) {
                tampilkanNotifikasi(res.message, res.status);
                if (res.status === 'success') {
                    muatData(1, ''); // Reload data from first page
                }
            },
            error: function() {
                tampilkanNotifikasi('Terjadi kesalahan saat mencoba menghapus semua data.', 'error');
            },
            complete: function() {
                btn.prop('disabled', false).html(originalText);
            }
        });
    });

    // Event delegation for view and delete buttons in table
    $('#siswa-container').on('click', '.tombol-view, .tombol-hapus', function() {
        const id = $(this).data('id');
        const action = $(this).hasClass('tombol-view') ? 'view' : 'hapus';

        if (action === 'view') {
            $.ajax({
                url: 'proses_siswa.php',
                type: 'POST',
                data: {
                    action: 'ambil',
                    id: id
                },
                dataType: 'json',
                success: function(data) {
                    const photoUrl = (data.photo) ? PHOTO_PATH + data.photo : 'https://placehold.co/150x150/EFEFEF/AAAAAA&text=No+Image';
                    $('#view_photo').attr('src', photoUrl);
                    $('#view_pd').text(data.pd || '-');
                    $('#view_nis').text(data.nis || '-');
                    $('#view_kelas').text(data.kelas || '-');
                    $('#viewModalLabel').text('Detail Siswa: ' + (data.pd || ''));
                    $('#viewModal').modal('show');
                },
                error: function() {
                    tampilkanNotifikasi('Gagal mengambil detail data siswa.', 'error');
                }
            });
        } else if (action === 'hapus') {
            const btn = $(this);
            const originalIcon = btn.html();
            btn.prop('disabled', true).html(`<span class="spinner-border spinner-border-sm"></span>`);

            $.ajax({
                url: 'proses_siswa.php',
                type: 'POST',
                data: {
                    action: 'hapus',
                    id: id
                },
                dataType: 'json',
                success: function(res) {
                    tampilkanNotifikasi(res.message, res.status);
                    if (res.status === 'success') {
                        muatData(currentPage, searchKeyword);
                    }
                },
                error: function() {
                    tampilkanNotifikasi('Gagal menghapus data.', 'error');
                },
                complete: function() {
                    // Restore button to original state only if delete failed
                    if (!btn.closest('tr').is(':hidden')) {
                        btn.prop('disabled', false).html(originalIcon);
                    }
                }
            });
        }
    });

    // ==========================================
    // DRAGGABLE MODAL FUNCTIONALITY
    // ==========================================
    function makeModalsDraggable() {
        let activeModalDialog = null;
        let offset = { x: 0, y: 0 };

        // Unbind previous events to prevent stacking if script is reloaded
        $(document).off('mousedown.modalDrag mousemove.modalDrag mouseup.modalDrag');

        $(document).on('mousedown.modalDrag', '.modal-header', function(e) {
            const modalHeader = $(this);
            const modalDialog = modalHeader.closest('.modal-dialog');
            
            if (modalDialog.length) {
                activeModalDialog = modalDialog[0];
                const rect = activeModalDialog.getBoundingClientRect();
                offset.x = e.clientX - rect.left;
                offset.y = e.clientY - rect.top;
                
                modalHeader.css('cursor', 'grabbing');
                $('body').addClass('dragging');
                
                // Prevent text selection while dragging
                e.preventDefault();
            }
        });

        $(document).on('mousemove.modalDrag', function(e) {
            if (activeModalDialog) {
                e.preventDefault();
                // Reset margin for absolute positioning behavior
                $(activeModalDialog).css({
                    margin: 0,
                    top: (e.clientY - offset.y) + 'px',
                    left: (e.clientX - offset.x) + 'px'
                });
            }
        });

        $(document).on('mouseup.modalDrag', function() {
            if (activeModalDialog) {
                $(activeModalDialog).find('.modal-header').css('cursor', 'grab');
                activeModalDialog = null;
                $('body').removeClass('dragging');
            }
        });
    }

    makeModalsDraggable();
});
</script>
