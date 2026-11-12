<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Dashboard') · Rice Harvest &amp; Sales</title>
  <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/app.css') }}">
  @stack('head')
</head>
<body>
@php($me = auth()->user())
<div class="app">
  <aside class="sidebar">
    <div class="brand">
      <div class="logo"><x-icon name="leaf"/></div>
      <div><b>Rice Harvest<br>&amp; Sales</b><small>Monitoring System</small></div>
    </div>
    <nav class="nav">
      <a href="{{ route('dashboard') }}" @class(['active' => request()->routeIs('dashboard')])><x-icon name="home"/> Dashboard</a>

      <div class="nav-title">Farm records</div>
      <a href="{{ route('farms.index') }}" @class(['active' => request()->routeIs('farms.*')])><x-icon name="pin"/> Farms</a>
      <a href="{{ route('varieties.index') }}" @class(['active' => request()->routeIs('varieties.*')])><x-icon name="leaf"/> Rice Varieties</a>
      <a href="{{ route('harvests.index') }}" @class(['active' => request()->routeIs('harvests.*')])><x-icon name="layers"/> Harvests</a>
      <a href="{{ route('inventory.index') }}" @class(['active' => request()->routeIs('inventory.*')])><x-icon name="package"/> Inventory</a>

      <div class="nav-title">Sales &amp; finance</div>
      <a href="{{ route('customers.index') }}" @class(['active' => request()->routeIs('customers.*')])><x-icon name="users"/> Customers</a>
      <a href="{{ route('sales.index') }}" @class(['active' => request()->routeIs('sales.*')])><x-icon name="cart"/> Sales</a>
      <a href="{{ route('expenses.index') }}" @class(['active' => request()->routeIs('expenses.*')])><x-icon name="wallet"/> Expenses</a>

      <div class="nav-title">Reports</div>
      @foreach(\App\Http\Controllers\ReportController::TYPES as $key => $label)
        <a href="{{ route('reports.show', $key) }}" @class(['active' => request()->is("reports/$key")])><x-icon name="chart"/> {{ $label }}</a>
      @endforeach

      @if($me->isAdmin())
        <div class="nav-title">Administration</div>
        <a href="{{ route('users.index') }}" @class(['active' => request()->routeIs('users.*')])><x-icon name="shield"/> User Accounts</a>
      @endif
    </nav>
    <div class="side-user">
      <b>{{ $me->full_name }}</b><span class="muted" style="color:#aac2a5">{{ '@'.$me->username }}</span><br>
      <span class="role">{{ $me->role === 'Admin' ? 'Farm Owner / Admin' : 'Farm Staff' }}</span>
      <form method="POST" action="{{ route('logout') }}">@csrf<button class="btn btn-sm"><x-icon name="logout"/> Sign out</button></form>
    </div>
  </aside>

  <div class="main">
    <header class="topbar">
      <button class="btn btn-icon menu-btn" type="button" aria-label="Menu"><x-icon name="menu"/></button>
      <span class="muted">Welcome back, <b style="color:var(--forest)">{{ strtok($me->full_name, ' ') }}</b></span>
      <span class="date">{{ now()->format('l, F j, Y') }}</span>
    </header>
    <main class="content">
      @include('layouts.flash')
      @yield('content')
    </main>
  </div>
</div>
<script src="{{ asset('js/app.js') }}"></script>
@stack('scripts')
</body>
</html>
