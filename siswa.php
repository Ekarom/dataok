
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
                    <div class="position-relative d-inline-block">
                        <img id="view_photo" 
                             src="" 
                             class="rounded-circle img-thumbnail shadow-sm" 
                             style="width: 160px; height: 160px; object-fit: cover; border: 4px solid #fff;" 
                             alt="Foto Siswa" 
                             onerror="this.onerror=null;this.src='https://placehold.co/160x160/EFEFEF/AAAAAA&text=No+Image';">
                        
                        <button type="button" 
                                class="btn btn-primary btn-sm position-absolute" 
                                id="btn_ganti_foto"
                                style="bottom: 15px; right: 10px; border-radius: 50%; width: 40px; height: 40px; border: 3px solid #fff; box-shadow: 0 4px 8px rgba(0,0,0,0.15);"
                                title="Pilih Foto">
                            <i class="fas fa-camera"></i>
                        </button>
                        
                        <input type="file" id="ganti_foto_input" style="display: none;" accept="image/*">
                        <input type="hidden" id="view_id">
                    </div>
                </div>
                <center>
                NIS : <br><span class="badge bg-menu-gradient" style="font-size: 15px;"><span id="view_nis"></span></span><br>
                Nama : <br><span class="badge bg-menu-gradient" style="font-size: 15px;"><span id="view_pd"></span></span><br>
                Kelas : <br><span class="badge bg-menu-gradient" style="font-size: 15px;"><span id="view_kelas"></span></span>
                </center>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary custom" data-dismiss="modal">Tutup</button>
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
                    $('#view_id').val(data.id);
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

    // Handle "Ganti Foto" button click
    $('#btn_ganti_foto').on('click', function() {
        $('#ganti_foto_input').click();
    });

    // Handle file selection and upload
    $('#ganti_foto_input').on('change', function() {
        const file = this.files[0];
        const id = $('#view_id').val();
        
        if (!file) return;

        const formData = new FormData();
        formData.append('action', 'ganti_foto');
        formData.append('id', id);
        formData.append('photo', file);

        const btn = $('#btn_ganti_foto');
        const originalHtml = btn.html();
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Loading...');

        $.ajax({
            url: 'proses_siswa.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success') {
                    toastr.success(res.message);
                    // Update photo in modal
                    $('#view_photo').attr('src', PHOTO_PATH + res.new_photo + '?t=' + new Date().getTime());
                    // Reload data in table to reflect change
                    muatData(currentPage, searchKeyword);
                } else {
                    toastr.error(res.message);
                }
            },
            error: function(xhr) {
                toastr.error('Gagal mengupload foto.');
                console.error(xhr.responseText);
            },
            complete: function() {
                btn.prop('disabled', false).html(originalHtml);
                $('#ganti_foto_input').val(''); // Reset input
            }
        });
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
