@extends('adminlte::page')

@section('title', 'Laporan Produksi')

@section('content')

    <div class="row">

        <div class="col-md-4">

            <div class="small-box bg-info">

                <div class="inner">

                    <h3>

                        {{ $report['books_in'] }}

                    </h3>

                    <p>

                        Naskah Masuk

                    </p>

                </div>

            </div>

        </div>

        <div class="col-md-4">

            <div class="small-box bg-success">

                <div class="inner">

                    <h3>

                        {{ $report['completed'] }}

                    </h3>

                    <p>

                        Produksi Selesai

                    </p>

                </div>

            </div>

        </div>

        <div class="col-md-4">

            <div class="small-box bg-primary">

                <div class="inner">

                    <h3>

                        {{ $report['isbn_approved'] }}

                    </h3>

                    <p>

                        ISBN Terbit

                    </p>

                </div>

            </div>

        </div>

    </div>

@endsection
