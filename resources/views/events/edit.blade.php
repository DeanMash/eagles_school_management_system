@extends('layouts.master')

@section('page_title', 'Edit Event')
@section('content')

<div class="card">
    <div class="card-header header-elements-inline">
        <h5 class="card-title">Edit Event</h5>
        <div class="header-elements">
            <a href="{{ route('events.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Events
            </a>
        </div>
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route('events.update', $event) }}">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-md-8">
                    <div class="form-group row">
                        <label for="title" class="col-lg-3 col-form-label font-weight-semibold">Title <span class="text-danger">*</span></label>
                        <div class="col-lg-9">
                            <input name="title" id="title" value="{{ old('title', $event->title) }}" required type="text" class="form-control" placeholder="Event title">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="description" class="col-lg-3 col-form-label font-weight-semibold">Description</label>
                        <div class="col-lg-9">
                            <textarea name="description" id="description" class="form-control" rows="4" placeholder="Event description">{{ old('description', $event->description) }}</textarea>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="event_date" class="col-lg-3 col-form-label font-weight-semibold">Date <span class="text-danger">*</span></label>
                        <div class="col-lg-9">
                            <input name="event_date" id="event_date" value="{{ old('event_date', $event->event_date->format('Y-m-d')) }}" required type="date" class="form-control">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="start_time" class="col-lg-3 col-form-label font-weight-semibold">Start Time</label>
                        <div class="col-lg-9">
                            <input name="start_time" id="start_time" value="{{ old('start_time', $event->start_time ? $event->start_time->format('H:i') : '') }}" type="time" class="form-control" placeholder="HH:MM">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="end_time" class="col-lg-3 col-form-label font-weight-semibold">End Time</label>
                        <div class="col-lg-9">
                            <input name="end_time" id="end_time" value="{{ old('end_time', $event->end_time ? $event->end_time->format('H:i') : '') }}" type="time" class="form-control" placeholder="HH:MM">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="venue" class="col-lg-3 col-form-label font-weight-semibold">Venue</label>
                        <div class="col-lg-9">
                            <input name="venue" id="venue" value="{{ old('venue', $event->venue) }}" type="text" class="form-control" placeholder="e.g. Main Hall">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="event_type" class="col-lg-3 col-form-label font-weight-semibold">Event Type <span class="text-danger">*</span></label>
                        <div class="col-lg-9">
                            <select name="event_type" id="event_type" required class="form-control">
                                <option value="">Select type...</option>
                                <option value="academic" {{ old('event_type', $event->event_type) == 'academic' ? 'selected' : '' }}>Academic</option>
                                <option value="exam" {{ old('event_type', $event->event_type) == 'exam' ? 'selected' : '' }}>Exam</option>
                                <option value="sports" {{ old('event_type', $event->event_type) == 'sports' ? 'selected' : '' }}>Sports</option>
                                <option value="cultural" {{ old('event_type', $event->event_type) == 'cultural' ? 'selected' : '' }}>Cultural</option>
                                <option value="holiday" {{ old('event_type', $event->event_type) == 'holiday' ? 'selected' : '' }}>Holiday</option>
                                <option value="meeting" {{ old('event_type', $event->event_type) == 'meeting' ? 'selected' : '' }}>Meeting</option>
                                <option value="submission" {{ old('event_type', $event->event_type) == 'submission' ? 'selected' : '' }}>Submission</option>
                                <option value="workshop" {{ old('event_type', $event->event_type) == 'workshop' ? 'selected' : '' }}>Workshop</option>
                                <option value="event" {{ old('event_type', $event->event_type) == 'event' ? 'selected' : '' }}>Event</option>
                                <option value="other" {{ old('event_type', $event->event_type) == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="is_public" class="col-lg-3 col-form-label font-weight-semibold">Visibility</label>
                        <div class="col-lg-9">
                            <div class="form-check">
                                <input type="hidden" name="is_public" value="0">
                                <input type="checkbox" name="is_public" id="is_public" value="1" {{ old('is_public', $event->is_public) ? 'checked' : '' }} class="form-check-input">
                                <label class="form-check-label" for="is_public">Public (visible to all users)</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-right mt-3">
                <a href="{{ route('events.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Event
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
