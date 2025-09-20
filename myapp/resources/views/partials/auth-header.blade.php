<nav>
    <ul class = 'flex gap-4'>
        <li>Dashboard<li>
        <li>
            <form action="{{url('logout')}}" method="post">
                @csrf
                <button type="submit">Logout</button>
            </form>
        <li>
    </ul>
</nav>
