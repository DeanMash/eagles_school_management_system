{{--Timetable--}}
<li class="nav-item">
    <a href="{{ route('student.timetable_grid') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['student.timetable_grid', 'student.weekly_timetable']) ? 'active' : '' }}"><i class="icon-calendar52"></i> Timetable</a>
</li>
{{--Events--}}
<li class="nav-item">
    <a href="{{ route('events.index') }}" class="nav-link {{ Route::is('events.*') ? 'active' : '' }}"><i class="icon-calendar5"></i> Events</a>
</li>
{{--Marksheet--}}
<li class="nav-item">
    <a href="{{ route('marks.year_selector', Qs::hash(Auth::user()->id)) }}" class="nav-link {{ in_array(Route::currentRouteName(), ['marks.show', 'marks.year_selector', 'pins.enter']) ? 'active' : '' }}"><i class="icon-book"></i> Marksheet</a>
</li>
