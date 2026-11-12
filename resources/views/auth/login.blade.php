<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sign in · Rice Harvest &amp; Sales</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Fraunces:opsz,wght@9..144,600;9..144,700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
<div class="login">
  <section class="login-art">
    <svg viewBox="0 0 800 900" preserveAspectRatio="xMidYMax slice" aria-hidden="true">
      <circle cx="600" cy="170" r="70" fill="#fff6c9" opacity=".85"/>
      <path d="M0 360 Q200 300 400 350 T800 330 V900 H0Z" fill="#8fbd55" opacity=".55"/>
      <g stroke="#2d6a35" stroke-width="3" fill="none" opacity=".55">
        @for($i = 0; $i < 9; $i++)<path d="M-20 {{ 470 + $i*44 }} Q400 {{ 420 + $i*44 }} 820 {{ 470 + $i*44 }}"/>@endfor
      </g>
      <g fill="#d6a62b">
        @for($i = 0; $i < 14; $i++)
          <g transform="translate({{ 60 + $i*52 }} {{ 560 + ($i % 3)*38 }}) rotate({{ ($i % 5) * 4 - 8 }})"><path d="M0 0 C-6 -40 -4 -90 0 -120 C4 -90 6 -40 0 0Z"/><ellipse cx="0" cy="-118" rx="9" ry="22"/></g>
        @endfor
      </g>
    </svg>
    <div class="txt">
      <h1>From the paddy to the payment.</h1>
      <p>Track harvests, stock on hand, customers, sales and farm expenses in one organized place.</p>
    </div>
  </section>
  <section class="login-form">
    <form class="login-card" method="POST" action="{{ route('login') }}">
      @csrf
      <h2>Sign in</h2>
      <p class="muted" style="margin:0 0 22px">Use your username or email address.</p>
      <x-field name="login" label="Username or email" :value="old('login')" required autofocus autocomplete="username"/>
      <x-field name="password" label="Password" type="password" required autocomplete="current-password"/>
      <button class="btn btn-primary btn-block" type="submit">Sign in</button>
    </form>
  </section>
</div>
</body>
</html>
