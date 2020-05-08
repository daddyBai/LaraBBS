@extends('layouts.app')

@section('title', $user->name . ' 的个人中心')

@section('content')

  <div class="container">
    <div class="col-md-8 offset-md-2">
      <div class="card">

        <div class="card-header">
          <h4><i class="glyphicon glyphicon-edit"></i>编辑个人资料</h4>
        </div>


        <div class="card-body">

          <form action="{{ route('users.update', $user->id) }}" method="post" accept-charset="UTF-8">

            {{ method_field('PUT') }}
            {{ csrf_field() }}

            @include('shared._error')

            <div class="from-group">
              <label for="name-field">用户名</label>
              <input type="text" class="form-control" name="name" id="name-field" value="{{ old('name', $user->name) }}">
            </div>

            <div class="from-group">
              <label for="email-field">邮 箱</label>
              <input type="text" class="form-control" name="email" id="email-field" value="{{ old('email', $user->email) }}">
            </div>

            <div class="from-group">
              <label for="introduction-field">个人简介</label>
              <textarea type="text" class="form-control" name="introduction" id="introduction-field" value="{{ old('introduction', $user->introduction) }}"></textarea>
            </div>

            <div class="well well-sm">
              <button type="submit" class="btn btn-primary">保存</button>
            </div>
          </form>
        </div>
      </div>

    </div>
  </div>

@endsection
