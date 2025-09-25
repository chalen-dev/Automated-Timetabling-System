<nav class = 'flex flex-col gap-4 '>
    <ul class = 'flex gap-4'>
        <li class = 'flex gap-4'>
            <img src="{{asset('pfp-placeholder.jpg')}}" alt="User" height="30px" width="30px">
            <span>{{auth()->user()->name}}</span>
        </li>
        <li>
            <form action="{{route('logout')}}" method="post">
                @csrf
                <button type="submit">Logout</button>
            </form>
        </li>
    </ul>
    <!-- Sub header -->
    <ul class = 'flex gap-4'>
        <li>
            <a href="{{route('dashboard.index')}}">Dashboard</a>
        </li>
        <li>
            <a href="{{route('courses.index')}}">Courses</a>
        </li>
        <li>
            <a href="{{route('ClassSections.index')}}">Sessions</a>
        </li>
        <li>
            <a href="">Professors</a>
        </li>
        <li>
            <a href="">Rooms</a>
        </li>
    </ul>
</nav>
