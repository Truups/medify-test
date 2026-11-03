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
                url: '{{ url("category-items/search") }}',
                dataSrc: 'data',
                data: function(d) {
                    d.kode = $('#filter-kode').val();
                    d.nama = $('#filter-nama').val();
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
                    data: 'kode',
                    orderable: false,
                    searchable: false,
                    render: function(kode, type, row) {
                        return '<a href="{{ url("category-items/view") }}/' +
                            encodeURIComponent(kode) +
                            '" class="btn btn-primary btn-sm">View</a> ' +
                            '<a href="{{ url("category-items/form/edit") }}/' + row.id +
                            '" class="btn btn-info btn-sm">Edit</a> ' +
                            '<a href="{{ url("category-items/delete") }}/' + row.id +
                            '" class="btn btn-danger btn-sm" ' +
                            'onclick="return confirm(\'Are you sure you want to delete this item?\');">Delete</a>';
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
</script>
