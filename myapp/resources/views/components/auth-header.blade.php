<nav class = 'flex flex-col gap-4 '>
    <ul class = 'flex gap-4'>
        <li class = 'flex gap-4'>
            <img src="{{asset('pfp-placeholder.jpg')}}" alt="User" height="30px" width="30px">
            <span>{{auth()->user()->name}}</span>
        </li>
        <li>
            <form action="{{url('logout')}}" method="post">
                @csrf
                <button type="submit">Logout</button>
            </form>
        </li>
    </ul>
    <!-- Sub header -->
    <ul class = 'flex gap-4'>
        <li>
            <a href="{{url('/dashboard')}}">Dashboard</a>
        </li>
        <li>
            <a href="{{url('/courses')}}">Courses</a>
        </li>
        <li>
            <a href=""></a>
        </li>
        <li>
            <a href=""></a>
        </li>
        <li>
            <a href=""></a>
        </li>
    </ul>
</nav>
