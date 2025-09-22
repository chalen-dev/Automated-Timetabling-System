<nav>
    <ul class = 'flex gap-4'>
        <li>Dashboard<li>
        <li class = 'flex gap-4'>
            <img src="{{asset('pfp-placeholder.jpg')}}" alt="User" height="30px" width="30px">
            <span>{{auth()->user()->name}}</span>
        </li>
        <li>
            <form action="{{url('logout')}}" method="post">
                @csrf
                <button type="submit">Logout</button>
            </form>
        <li>
    </ul>
</nav>
