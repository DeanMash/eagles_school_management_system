@extends('layouts.master')
@section('page_title', 'Add New Book')
@section('content')

<div class="card">
    <div class="card-header header-elements-inline">
        <h6 class="card-title">Add New Book</h6>
        <div class="header-elements">
            <a href="{{ route('librarian.books.index') }}" class="btn btn-secondary">
                <i class="icon-arrow-left7"></i> Back to Books
            </a>
        </div>
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route('librarian.books.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>ISBN <span class="text-danger">*</span></label>
                        <input type="text" name="isbn" class="form-control" value="{{ old('isbn') }}" placeholder="ISBN Number">
                        @error('isbn')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label>Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" value="{{ old('title') }}" required placeholder="Book Title">
                        @error('title')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Author <span class="text-danger">*</span></label>
                        <input type="text" name="author" class="form-control" value="{{ old('author') }}" required placeholder="Author Name">
                        @error('author')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label>Publisher</label>
                        <input type="text" name="publisher" class="form-control" value="{{ old('publisher') }}" placeholder="Publisher Name">
                        @error('publisher')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Year Published</label>
                        <input type="number" name="year_published" class="form-control" value="{{ old('year_published') }}" placeholder="Year" min="1000" max="{{ date('Y') }}">
                        @error('year_published')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label>Category <span class="text-danger">*</span></label>
                        <select name="category" class="form-control" required>
                            <option value="">Select Category</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}" {{ old('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                            @endforeach
                        </select>
                        @error('category')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label>Number of Copies <span class="text-danger">*</span></label>
                        <input type="number" name="copies" class="form-control" value="{{ old('copies', 1) }}" required min="1" max="1000">
                        @error('copies')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Shelf Number</label>
                        <input type="text" name="shelf_number" class="form-control" value="{{ old('shelf_number') }}" placeholder="Shelf Number">
                        @error('shelf_number')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label>Book Cover</label>
                        <input type="file" name="book_cover" class="form-control" accept="image/*">
                        @error('book_cover')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                        <small class="form-text text-muted">Max size: 2MB. Supported formats: JPG, PNG, GIF</small>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="4" placeholder="Book description...">{{ old('description') }}</textarea>
                @error('description')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="text-right">
                <button type="submit" class="btn btn-primary">
                    <i class="icon-check"></i> Add Book
                </button>
                <a href="{{ route('librarian.books.index') }}" class="btn btn-secondary">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

@endsection
