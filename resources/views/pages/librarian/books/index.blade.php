@extends('layouts.master')
@section('page_title', 'Books Management')
@section('content')

<div class="card">
    <div class="card-header header-elements-inline">
        <h6 class="card-title">Books Management</h6>
        <div class="header-elements">
            <a href="{{ route('librarian.books.create') }}" class="btn btn-primary">
                <i class="icon-plus-circle2"></i> Add New Book
            </a>
        </div>
    </div>

    <div class="card-body">
        <!-- Search Form -->
        <form method="GET" action="{{ route('librarian.books.search') }}" class="mb-4">
            <div class="row">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search by title, author, ISBN..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="category" class="form-control">
                        <option value="all">All Categories</option>
                        @foreach(['Fiction', 'Non-Fiction', 'Science', 'Mathematics', 'History', 'Geography', 'Literature', 'Reference', 'Biography', 'Art', 'Technology', 'Other'] as $cat)
                            <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-control">
                        <option value="all">All Status</option>
                        <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>Available</option>
                        <option value="checked_out" {{ request('status') == 'checked_out' ? 'selected' : '' }}>Checked Out</option>
                        <option value="reserved" {{ request('status') == 'reserved' ? 'selected' : '' }}>Reserved</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-info btn-block">
                        <i class="icon-search4"></i> Search
                    </button>
                </div>
            </div>
        </form>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                {{ session('error') }}
            </div>
        @endif

        <!-- Books Table -->
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>S/N</th>
                        <th>Cover</th>
                        <th>Title</th>
                        <th>Author</th>
                        <th>ISBN</th>
                        <th>Category</th>
                        <th>Copies</th>
                        <th>Available</th>
                        <th>Status</th>
                        <th>Issued</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($books as $book)
                        <tr>
                            <td>{{ $loop->iteration + ($books->currentPage() - 1) * $books->perPage() }}</td>
                            <td>
                                @if($book->book_cover)
                                    <img src="{{ asset('storage/' . $book->book_cover) }}" alt="Cover" style="width: 50px; height: 70px; object-fit: cover;">
                                @else
                                    <div class="bg-secondary text-white text-center" style="width: 50px; height: 70px; line-height: 70px;">
                                        <i class="icon-book"></i>
                                    </div>
                                @endif
                            </td>
                            <td><strong>{{ $book->title }}</strong></td>
                            <td>{{ $book->author }}</td>
                            <td>{{ $book->isbn ?? 'N/A' }}</td>
                            <td><span class="badge badge-info">{{ $book->category }}</span></td>
                            <td>{{ $book->copies }}</td>
                            <td>
                                <span class="badge {{ $book->available_copies > 0 ? 'badge-success' : 'badge-danger' }}">
                                    {{ $book->available_copies }}
                                </span>
                            </td>
                            <td>
                                @if($book->status == 'available')
                                    <span class="badge badge-success">Available</span>
                                @elseif($book->status == 'checked_out')
                                    <span class="badge badge-warning">Checked Out</span>
                                @else
                                    <span class="badge badge-secondary">{{ ucfirst($book->status) }}</span>
                                @endif
                            </td>
                            <td>{{ $book->transactions_count ?? 0 }}</td>
                            <td>
                                <div class="list-icons">
                                    <div class="dropdown">
                                        <a href="#" class="list-icons-item" data-toggle="dropdown">
                                            <i class="icon-menu9"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <a href="{{ route('librarian.books.show', $book->id) }}" class="dropdown-item">
                                                <i class="icon-eye"></i> View Details
                                            </a>
                                            @if($book->isAvailable())
                                                <a href="{{ route('librarian.books.issue', $book->id) }}" class="dropdown-item">
                                                    <i class="icon-user-check"></i> Issue Book
                                                </a>
                                            @endif
                                            <a href="{{ route('librarian.books.edit', $book->id) }}" class="dropdown-item">
                                                <i class="icon-pencil"></i> Edit
                                            </a>
                                            <form action="{{ route('librarian.books.destroy', $book->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this book?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="icon-trash"></i> Delete
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center">
                                <div class="alert alert-info">
                                    <i class="icon-info"></i> No books found. 
                                    <a href="{{ route('librarian.books.create') }}">Add your first book</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-3">
            {{ $books->links() }}
        </div>
    </div>
</div>

@endsection
