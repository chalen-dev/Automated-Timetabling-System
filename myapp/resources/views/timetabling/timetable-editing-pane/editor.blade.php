{{-- resources/views/timetabling/timetable-editing-pane/editor.blade.php --}}
@extends('app')

@section('title', $timetable->timetable_name)

@section('content')
    <div class="w-full">

        <livewire:timetabling.editor.legend/>

        <livewire:timetabling.editor.tools/>

        <livewire:timetabling.editor.courses-tray/>

        <livewire:timetabling.editor.timetable-canvas :timetable="$timetable" />



    </div>
@endsection
