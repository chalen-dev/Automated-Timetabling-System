@props(['professor', 'courses'])

<div x-data="{ showCreate: {{ session('showCreate') ? 'true' : 'false' }} }">
    <div class="flex flex- items-between gap-10 justify-around">
        <h1>Specializations</h1>
        <button @click="showCreate = true" class="px-3 py-1">Add New</button>

        <!--Create Specialization Form Popup-->
        <div x-show="showCreate">
            <x-specializations.create :professor="$professor" :courses="$courses" />
        </div>
    </div>
    <table class="w-full">
        <thead>
            <tr>
                <td>
                    Course Name
                </td>
                <td>
                    Course Title
                </td>
                <td>

                </td>
            </tr>
        </thead>
    </table>
        @foreach($professor->specializations as $specialization)
        <tbody>
            <tr>
                <td>{{$specialization->course_name}}</td>
                <td>{{$specialization->course_title}}</td>
                <td>
                    <a href='{{route('records.professors.specializations.destroy', $specialization)}}'>Delete</a>
                </td>
            </tr>
        </tbody>

        @endforeach
</div>

