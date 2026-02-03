{{--Timetable--}}
<li class="nav-item">
    <a href="{{ route('teacher.timetable_grid') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['teacher.timetable_grid', 'teacher.weekly_timetable']) ? 'active' : '' }}"><i class="icon-calendar52"></i> Timetable</a>
</li>
{{--Events--}}
<li class="nav-item">
    <a href="{{ route('events.index') }}" class="nav-link {{ Route::is('events.*') ? 'active' : '' }}"><i class="icon-calendar5"></i> Events</a>
</li>
