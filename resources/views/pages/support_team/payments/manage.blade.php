@extends('layouts.master')
@section('page_title', 'Student Payments')
@section('content')
    <div class="card">
        <div class="card-header header-elements-inline">
            <h5 class="card-title"><i class="icon-cash2 mr-2"></i> Student Payments</h5>
            {!! Qs::getPanelOptions() !!}
        </div>

        <div class="card-body">
            <form method="post" action="{{ route('payments.select_class') }}">
                @csrf
              <div class="row">
                  <div class="col-md-6 offset-md-3">
                      <div class="row">
                          <div class="col-md-10">
                              <div class="form-group">
                                  <label for="my_class_id" class="col-form-label font-weight-bold">Class:</label>
                                  <select required id="my_class_id" name="my_class_id" class="form-control select">
                                      <option value="">Select Class</option>
                                      @foreach($my_classes as $c)
                                          <option {{ ($selected && $my_class_id == $c->id) ? 'selected' : '' }} value="{{ $c->id }}">{{ $c->name }}</option>
                                      @endforeach
                                  </select>
                              </div>
                          </div>

                          <div class="col-md-2 mt-4">
                              <div class="text-right mt-1">
                                  <button type="submit" class="btn btn-primary">Submit <i class="icon-paperplane ml-2"></i></button>
                              </div>
                          </div>

                      </div>
                  </div>
              </div>

            </form>
        </div>
    </div>
    @if($selected)
        <div class="card">
            <div class="card-body">
                <table class="table datatable-button-html5-columns">
                    <thead>
                    <tr>
                        <th>S/N</th>
                        <th>Photo</th>
                        <th>Name</th>
                        <th>ADM_No</th>
                        <th>Balance ({{ $year }})</th>
                        <th>Parent / Next of Kin</th>
                        <th>Payments</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($students as $s)
                        @php
                            $balance = $student_balances[$s->user_id] ?? 0;
                            $parent = $s->my_parent;
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><img class="rounded-circle" style="height: 40px; width: 40px;" src="{{ $s->user->photo }}" alt="photo"></td>
                            <td>{{ $s->user->name }}</td>
                            <td>{{ $s->adm_no }}</td>
                            <td>
                                @if($balance > 0)
                                    <span class="text-danger font-weight-bold">{{ number_format($balance) }}</span>
                                @else
                                    <span class="text-success">Cleared</span>
                                @endif
                            </td>
                            <td>
                                @if($parent)
                                    <small title="Contact for fee notifications">
                                        @if($parent->phone)<i class="icon-phone"></i> {{ $parent->phone }}<br>@endif
                                        @if($parent->email)
                                            <i class="icon-mail5"></i> {{ $parent->email }}
                                            @if($balance > 0)
                                                <a href="mailto:{{ $parent->email }}?subject=Fee%20Reminder%20-%20{{ $s->user->name }}&body=Dear%20Parent%2C%0A%0AThis%20is%20a%20reminder%20regarding%20outstanding%20fees%20for%20{{ $s->user->name }}.%20Balance%3A%20{{ number_format($balance) }}.%0A%0APlease%20contact%20the%20school%20to%20arrange%20payment." class="btn btn-xs btn-outline-primary ml-1" title="Send fee reminder email"><i class="icon-paperplane"></i></a>
                                            @endif
                                        @endif
                                        @if(!$parent->phone && !$parent->email)<span class="text-muted">No contact</span>@endif
                                    </small>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('payments.invoice', [Qs::hash($s->user_id), $year]) }}" class="btn btn-success btn-sm" title="Make payment for {{ $year }}">
                                        <i class="icon-cash"></i> Make Payment
                                    </a>
                                    <div class="dropdown">
                                        <a href="#" class="btn btn-outline-secondary btn-sm dropdown-toggle" data-toggle="dropdown" title="View by year">
                                            <i class="icon-arrow-down5"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-left">
                                            <a href="{{ route('payments.invoice', [Qs::hash($s->user_id)]) }}" class="dropdown-item">All Payments</a>
                                            @foreach(Pay::getYears($s->user_id) as $py)
                                                @if($py)
                                                    <a href="{{ route('payments.invoice', [Qs::hash($s->user_id), $py]) }}" class="dropdown-item">{{ $py }}</a>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection
