<script src="https://code.jquery.com/jquery-3.5.1.js"></script>
<script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.12.1/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function() {
        var table = $('#table').DataTable({
            processing: true,
            searching: false,
            order: [[0, 'desc']],
            deferRender: true,
            ajax: {
                url: '{{ url("master-items/search") }}',
                dataSrc: 'data',
                data: function(d) {
                    d.kode = $('#filter-kode').val();
                    d.nama = $('#filter-nama').val();
                    d.hargamin = $('#filter-harga-min').val();
                    d.hargamax = $('#filter-harga-max').val();
                },
                error: function(xhr) {
                    $('#loading-filter').hide();
                    var message = (xhr.responseJSON && xhr.responseJSON.message)
                        ? xhr.responseJSON.message
                        : 'Terjadi kesalahan server, tidak dapat mengambil data';
                    alert(message);
                }
            },
            columns: [
                { data: 'kode', render: DataTable.render.text() },
                { data: 'nama', render: DataTable.render.text() },
                {
                    data: 'categories',
                    orderable: false,
                    searchable: false,
                    render: function(categories) {
                        if (!categories || !categories.length) return '-';

                        return categories.map(function(category) {
                            return $('<div>').text(category.nama).html();
                        }).join(', ');
                    }
                },
                { data: 'jenis', render: DataTable.render.text() },
                { data: 'harga_beli', render: DataTable.render.number('.', ',', 0) },
                { data: 'harga_jual', render: DataTable.render.number('.', ',', 0) },
                { data: 'supplier', render: DataTable.render.text() },
                {
                    data: 'foto_barang_url',
                    orderable: false,
                    searchable: false,
                    render: function(url) {
                        if (!url) return '-';

                        // escape sebelum dimasukkan ke atribut src
                        var safeUrl = $('<div>').text(url).html();
                        return '<img src="' + safeUrl + '" alt="Foto" ' +
                            'class="img-thumbnail" loading="lazy" ' +
                            'style="width:48px;height:48px;object-fit:cover">';
                    }
                },
                {
                    data: 'kode',
                    orderable: false,
                    searchable: false,
                    render: function(kode) {
                        return '<a href="{{ url("master-items/view") }}/' +
                            encodeURIComponent(kode) +
                            '" class="btn btn-primary btn-sm">View</a>';
                    }
                }
            ]
        });

        // indikator loading milik filter
        table.on('preXhr.dt', function() {
            $('#loading-filter').show();
        }).on('xhr.dt', function() {
            $('#loading-filter').hide();
        });
    });

    $('.btn-get-data').on('click', function() {
        $('#table').DataTable().ajax.reload();
    });

    // Kirim filter yang sedang aktif ke endpoint export Excel.
    $('.btn-export-excel').on('click', function(e) {
        e.preventDefault();

        var params = $.param({
            kode: $('#filter-kode').val(),
            nama: $('#filter-nama').val(),
            hargamin: $('#filter-harga-min').val(),
            hargamax: $('#filter-harga-max').val()
        });

        var url = $(this).attr('href');

        window.location.href = params ? url + '?' + params : url;
    });

    // Polling status export yang diproses oleh queue.
    (function() {
        var $statusBox = $('#export-status');
        if (!$statusBox.length) return;

        var statusUrl = $statusBox.data('status-url');
        var $text = $('#export-status-text');
        var $download = $('#export-download-link');
        var timer = null;

        function poll() {
            $.getJSON(statusUrl)
                .done(function(data) {
                    if (data.status === 'completed') {
                        $statusBox
                            .removeClass('alert-info alert-danger')
                            .addClass('alert-success');
                        $text.text('Export selesai. File siap diunduh.');
                        $download.attr('href', data.download_url).removeClass('d-none');

                        if (timer) clearInterval(timer);
                    } else if (data.status === 'failed') {
                        $statusBox
                            .removeClass('alert-info alert-success')
                            .addClass('alert-danger');
                        $text.text('Export gagal: ' + (data.error || 'Terjadi kesalahan.'));
                        $download.addClass('d-none');

                        if (timer) clearInterval(timer);
                    } else if (data.status === 'processing') {
                        $text.text('Export sedang diproses...');
                    } else {
                        $text.text('Export menunggu antrian...');
                    }
                })
                .fail(function() {
                    $text.text('Tidak dapat memeriksa status export.');
                });
        }

        timer = setInterval(poll, 2000);
        poll();
    })();
</script>
