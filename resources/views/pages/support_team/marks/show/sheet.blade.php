<table class="table table-bordered table-responsive text-center">
    <thead>
    <tr>
        <th rowspan="2">S/N</th>
        <th rowspan="2">SUBJECTS</th>
        <th rowspan="2">CA1<br>(20)</th>
        <th rowspan="2">CA2<br>(20)</th>
        <th rowspan="2">COURSEWORK<br>(40)</th>
        <th rowspan="2">EXAMS<br>(60)</th>
        <th rowspan="2">TOTAL<br>(100)</th>

        {{--@if($ex->term == 3) --}}{{-- 3rd Term --}}{{--
        <th rowspan="2">TOTAL <br>(100%) 3<sup>RD</sup> TERM</th>
        <th rowspan="2">1<sup>ST</sup> <br> TERM</th>
        <th rowspan="2">2<sup>ND</sup> <br> TERM</th>
        <th rowspan="2">CUM (300%) <br> 1<sup>ST</sup> + 2<sup>ND</sup> + 3<sup>RD</sup></th>
        <th rowspan="2">CUM AVE</th>
        @endif--}}

        <th rowspan="2">TEACHER</th>
        <th rowspan="2">GRADE</th>
        <th rowspan="2">SUBJECT <br> POSITION</th>
        <th rowspan="2">REMARKS</th>
    </tr>
    </thead>

    <tbody>
    @foreach($subjects as $sub)
        @php
            $subjectMarks = $marks->where('subject_id', $sub->id)->where('exam_id', $ex->id);
            $hasMarks = $subjectMarks->count() > 0;
        @endphp
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td class="text-left"><strong>{{ $sub->name }}</strong></td>
            @if($hasMarks)
                @foreach($subjectMarks as $mk)
                    <td>{{ ($mk->t1) ?: '-' }}</td>
                    <td>{{ ($mk->t2) ?: '-' }}</td>
                    <td><strong>{{ ($mk->tca) ?: (($mk->t1 && $mk->t2) ? (intval($mk->t1) + intval($mk->t2)) : '-') }}</strong></td>
                    <td>{{ ($mk->exm) ?: '-' }}</td>
                    <td>
                        @if($ex->term === 1) {{ ($mk->tex1) ?: '-' }}
                        @elseif ($ex->term === 2) {{ ($mk->tex2) ?: '-' }}
                        @elseif ($ex->term === 3) {{ ($mk->tex3) ?: '-' }}
                        @else {{ '-' }}
                        @endif
                    </td>

                    {{--Teacher Column--}}
                    <td class="text-left">
                        @if($sub->teacher)
                            <small>{{ $sub->teacher->name }}</small>
                        @else
                            <small class="text-muted">-</small>
                        @endif
                    </td>

                    {{--3rd Term--}}
                    {{-- @if($ex->term == 3)
                         <td>{{ $mk->tex3 ?: '-' }}</td>
                         <td>{{ Mk::getSubTotalTerm($student_id, $sub->id, 1, $mk->my_class_id, $year) }}</td>
                         <td>{{ Mk::getSubTotalTerm($student_id, $sub->id, 2, $mk->my_class_id, $year) }}</td>
                         <td>{{ $mk->cum ?: '-' }}</td>
                         <td>{{ $mk->cum_ave ?: '-' }}</td>
                     @endif--}}

                    {{--Grade, Subject Position & Remarks--}}
                    <td>{{ ($mk->grade) ? $mk->grade->name : '-' }}</td>
                    <td>{!! ($mk->grade) ? Mk::getSuffix($mk->sub_pos) : '-' !!}</td>
                    <td>{{ ($mk->grade) ? $mk->grade->remark : '-' }}</td>
                @endforeach
            @else
                {{-- No marks recorded yet for this subject --}}
                <td colspan="3" class="text-muted">-</td>
                <td colspan="3" class="text-muted">-</td>
                <td class="text-left">
                    @if($sub->teacher)
                        <small>{{ $sub->teacher->name }}</small>
                    @else
                        <small class="text-muted">-</small>
                    @endif
                </td>
                <td colspan="3" class="text-muted"><small>No marks recorded</small></td>
            @endif
        </tr>
    @endforeach
    <tr>
        <td colspan="5"><strong>TOTAL SCORES OBTAINED: </strong> {{ $exr->total }}</td>
        <td colspan="3"><strong>FINAL AVERAGE: </strong> {{ $exr->ave }}</td>
        <td colspan="3"><strong>CLASS AVERAGE: </strong> {{ $exr->class_ave }}</td>
    </tr>
    </tbody>
</table>
