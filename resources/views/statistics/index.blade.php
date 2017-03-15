@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <ul class="breadcrumb">
                <li><a href="{{ url('/home') }}">Dashboard</a></li>
                <li class="active">Data Peminjaman</li>
            </ul>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h2 class="panel-title">Data Peminjaman</h2>
                </div>

                <div class="panel-body">
                    {!! $html->table(['class'=>'table table-striped']) !!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    {!! $html->scripts() !!}
    <script>
        $(function() {
            $('\
              <div id="filter_status" class="dataTables_length">\
                <label>Status \
                    <select name="filter_status" aria-controls="filter_status" \
                        class="form-control input-sm" style="width: auto;">\
                            <option value="all" selected="selected">Semua</option>\
                            <option value="returned">Sudah Dikembalikan</option>\
                            <option value="not-returned">Belum Dikembalikan</option>\
                    </select>\
                </label>\
              </div>\
            ').insertAfter('.dataTables_length');
            
            $("#dataTableBuilder").on('preXhr.dt', function(e, settings, data) { 
                data.status = $('select[name="filter_status"]').val();
            });

            $('select[name="filter_status"]').change(function() {
                window.LaravelDataTables["dataTableBuilder"].ajax.reload();
            });
        });
    </script>
@endsection