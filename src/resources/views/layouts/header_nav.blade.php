<nav>
    <ul class="header-nav__content">
        @auth
        @if (Auth::user()->isAdmin())
        <li class="header-nav__item">
            <a href="/admin/attendance/list" class="header-nav__btn">勤怠一覧</a>
        </li>
        <li class="header-nav__item">
            <a href="/admin/staff/list" class="header-nav__btn">スタッフ一覧</a>
        </li>
        <li class="header-nav__item">
            <a href="/stamp_correction_request/list" class="header-nav__btn">申請一覧</a>
        </li>
        <li class="header-nav__item">
            <form action="/admin/logout" class="header-nav__form" method="post">
                @csrf
                <button class="header-nav__btn">ログアウト</button>
            </form>
        </li>
        @elseif(Auth::user()->isUser())
        <li class="header-nav__item">
            <a href="/attendance" class="header-nav__btn">勤怠</a>
        </li>
        <li class="header-nav__item">
            <a href="/attendance/list" class="header-nav__btn">勤怠一覧</a>
        </li>
        <li class="header-nav__item">
            <a href="/stamp_correction_request/list" class="header-nav__btn">申請</a>
        </li>
        <li class="header-nav__item">
            <form action="/logout" class="header-nav__form" method="post">
                @csrf
                <button class="header-nav__btn">ログアウト</button>
            </form>
        </li>
        @endif
        @endauth
    </ul>
</nav>