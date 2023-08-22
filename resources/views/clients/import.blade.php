@extends('layouts.app')

@section('content')
    <h1>Import Clients</h1>
    <form action="{{ route('clients.import') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="file" name="file" accept=".csv" required>
        <button type="submit">Import</button>
    </form>
@endsection