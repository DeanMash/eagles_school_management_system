@extends('layouts.master')

@section('page_title', 'Events Management')
@section('content')

<div class="card">
    <div class="card-header header-elements-inline">
        <h5 class="card-title">School Events</h5>
        <div class="header-elements">
            <a href="{{ route('events.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Event
            </a>
        </div>
    </div>

    <div class="card-body">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Event</th>
                    <th>Type</th>
                    <th>Venue</th>
                    <th>Visibility</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($events as $event)
                <tr>
                    <td>{{ $event->event_date->format('M d, Y') }}</td>
                    <td>
                        <strong>{{ $event->title }}</strong>
                        @if($event->description)
                        <br><small class="text-muted">{{ \Illuminate\Support\Str::limit($event->description, 50) }}</small>
                        @endif
                    </td>
                    <td>
                        <span class="badge" style="background-color: {{ getEventColor($event->event_type) }}; color: white;">
                            {{ ucfirst($event->event_type) }}
                        </span>
                    </td>
                    <td>{{ $event->venue ?? 'TBA' }}</td>
                    <td>
                        <span class="badge badge-{{ $event->is_public ? 'success' : 'warning' }}">
                            {{ $event->is_public ? 'Public' : 'Private' }}
                        </span>
                    </td>
                    <td>
                        <div class="btn-group">
                            <a href="#" class="btn btn-sm btn-info view-event" data-id="{{ $event->id }}">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('events.edit', $event) }}" class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('events.destroy', $event) }}" method="POST" style="display:inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this event?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{ $events->links() }}
    </div>
</div>

<!-- Event Detail Modal -->
<div class="modal fade" id="eventDetailModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Event Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="eventDetailContent">
                Loading...
            </div>
        </div>
    </div>
</div>

@endsection

@push('js')
<script>
$(document).ready(function() {
    $('.view-event').click(function(e) {
        e.preventDefault();
        var eventId = $(this).data('id');
        
        $.ajax({
            url: '/events/' + eventId,
            method: 'GET',
            success: function(event) {
                var html = `
                    <h6>${event.title}</h6>
                    <p><strong>Date:</strong> ${event.event_date}</p>
                    ${event.start_time ? `<p><strong>Time:</strong> ${event.start_time} ${event.end_time ? ' - ' + event.end_time : ''}</p>` : ''}
                    ${event.venue ? `<p><strong>Venue:</strong> ${event.venue}</p>` : ''}
                    ${event.description ? `<p><strong>Description:</strong><br>${event.description}</p>` : ''}
                    <p><strong>Type:</strong> <span class="badge">${event.event_type}</span></p>
                    <p><strong>Visibility:</strong> ${event.is_public ? 'Public' : 'Private'}</p>
                `;
                
                $('#eventDetailContent').html(html);
                $('#eventDetailModal').modal('show');
            }
        });
    });
});
</script>
@endpush