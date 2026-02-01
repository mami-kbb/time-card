<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>coachtech勤怠管理アプリ</title>
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    @yield('css')
</head>

<body>
    <header class="header">
        <div class="header-inner">
            <h1 class="header__heading"><img src="{{ asset('/images/coachtech_header_logo.png') }}" alt="ヘッダーロゴ"></h1>
            <div class="header__nav">
                @yield('nav')
            </div>
        </div>
    </header>
    <main class="content">
        @yield('content')
    </main>
</body>

</html>