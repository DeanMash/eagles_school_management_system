{{--Library Management--}}
<li class="nav-item nav-item-submenu {{ in_array(Route::currentRouteName(), ['librarian.books.index', 'librarian.books.create', 'librarian.books.edit', 'librarian.books.show', 'librarian.transactions.index', 'librarian.transactions.overdue']) ? 'nav-item-expanded nav-item-open' : '' }}">
    <a href="#" class="nav-link"><i class="icon-book"></i> <span>Library</span></a>
    
    <ul class="nav nav-group-sub" data-submenu-title="Library Management">
        {{--Books--}}
        <li class="nav-item nav-item-submenu {{ in_array(Route::currentRouteName(), ['librarian.books.index', 'librarian.books.create', 'librarian.books.edit', 'librarian.books.show']) ? 'nav-item-expanded' : '' }}">
            <a href="#" class="nav-link {{ in_array(Route::currentRouteName(), ['librarian.books.index', 'librarian.books.create', 'librarian.books.edit', 'librarian.books.show']) ? 'active' : '' }}">Books</a>
            <ul class="nav nav-group-sub">
                <li class="nav-item">
                    <a href="{{ route('librarian.books.index') }}" class="nav-link {{ Route::is('librarian.books.index') ? 'active' : '' }}">All Books</a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('librarian.books.create') }}" class="nav-link {{ Route::is('librarian.books.create') ? 'active' : '' }}">Add New Book</a>
                </li>
            </ul>
        </li>
        
        {{--Transactions--}}
        <li class="nav-item nav-item-submenu {{ in_array(Route::currentRouteName(), ['librarian.transactions.index', 'librarian.transactions.overdue']) ? 'nav-item-expanded' : '' }}">
            <a href="#" class="nav-link {{ in_array(Route::currentRouteName(), ['librarian.transactions.index', 'librarian.transactions.overdue']) ? 'active' : '' }}">Transactions</a>
            <ul class="nav nav-group-sub">
                <li class="nav-item">
                    <a href="{{ route('librarian.transactions.index') }}" class="nav-link {{ Route::is('librarian.transactions.index') ? 'active' : '' }}">All Transactions</a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('librarian.transactions.overdue') }}" class="nav-link {{ Route::is('librarian.transactions.overdue') ? 'active' : '' }}">Overdue Books</a>
                </li>
            </ul>
        </li>
    </ul>
</li>
