@extends('layouts.master')
@section('page_title', 'Edit Class - '.$c->name)
@section('content')

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title">Edit Class</h6>
            {!! Qs::getPanelOptions() !!}
        </div>

        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <form class="ajax-update" data-reload="#page-header" method="post" action="{{ route('classes.update', $c->id) }}">
                        @csrf @method('PUT')
                        <div class="form-group row">
                            <label class="col-lg-3 col-form-label font-weight-semibold">Name <span class="text-danger">*</span></label>
                            <div class="col-lg-9">
                                <input name="name" value="{{ $c->name }}" required type="text" class="form-control" placeholder="Name of Class">
                            </div>
                        </div>

                      {{--
                      <div class="form-group row">
                            <label for="teacher_id" class="col-lg-3 col-form-label font-weight-semibold">Teacher</label>
                            <div class="col-lg-9">
                                <select data-placeholder="Select Teacher" class="form-control select-search" name="teacher_id" id="teacher_id">
                                    <option value=""></option>
                                    @foreach($teachers as $t)
                                        <option {{ $c->teacher_id == $t->id ? 'selected' : '' }} value="{{ Qs::hash($t->id) }}">{{ $t->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                      --}}

                        <div class="form-group row">
                            <label for="class_type_id" class="col-lg-3 col-form-label font-weight-semibold">Class Type</label>
                            <div class="col-lg-9">
                                <input class="form-control" disabled="disabled" value="{{ $c->class_type->name }}" title="Class Type" type="text">
                            </div>
                        </div>

                        @if(isset($sections) && $sections->count() > 0)
                        <hr class="my-4">
                        <h6 class="font-weight-semibold mb-3">Class Teachers (by Section)</h6>
                        @foreach($sections as $section)
                        <div class="form-group row">
                            <label class="col-lg-3 col-form-label">{{ $section->name }}</label>
                            <div class="col-lg-9">
                                <select name="section_teachers[{{ $section->id }}]" class="form-control select-search">
                                    <option value="">-- No teacher --</option>
                                    @foreach($teachers as $t)
                                        <option value="{{ Qs::hash($t->id) }}" {{ $section->teacher_id == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        @endforeach
                        @endif

                        <div class="text-right">
                            <button type="submit" class="btn btn-primary">Submit form <i class="icon-paperplane ml-2"></i></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{--Class Edit Ends--}}

@endsection
