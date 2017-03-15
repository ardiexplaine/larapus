@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <ul class="breadcrumb">
                <li><a href="{{ url('/home') }}">Dashboard</a></li>
                <li><a href="{{ url('/admin/books') }}">Buku</a></li>
                <li class="active">Ubah Buku</li>
            </ul>

            <div class="panel panel-default">
                <div class="panel-heading">
                    <h2 class="panel-title">
                        <h2 class="panel-title">Ubah Buku</h2>
                    </h2>

                    <div class="panel-body">
                        {!! Form::model($book, ['url' => route('books.update', $book->id), 'method' => 'put', 'files' => 'true', 'class' => 'form-horizontal']) !!}
                        @include('books._form')
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection