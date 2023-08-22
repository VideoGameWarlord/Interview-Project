@extends('layouts.app')

@section('content')
    <h1>Edit Client</h1>
    
    <div>
        @if($errors->any())
        <ul>
            @foreach($errors->all() as $error)
                <li>{{$error}}</li>
            @endforeach
        </ul>
        @endif
    </div>

    <form action="{{ route('clients.update', $client->id) }}" method="POST">
        @csrf
        @method('PUT')
        <label for="first_name">First Name:</label>
        <input type="text" name="first_name" value="{{ $client->first_name }}" required>
        <label for="last_name">Last Name:</label>
        <input type="text" name="last_name" value="{{ $client->last_name }}" required>
        <label for="gender">Gender:</label>
        <select name="gender" required>
            <option value="Male" {{ $client->gender == 'Male' ? 'selected' : '' }}>Male</option>
            <option value="Female" {{ $client->gender == 'Female' ? 'selected' : '' }}>Female</option>
        </select>
        <label for="ip_address">IP Address:</label>
        <input type="text" name="ip_address" value="{{ $client->ip_address }}">
        <button type="submit">Update</button>
    </form>
@endsection