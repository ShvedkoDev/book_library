<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class BulkEditBooks extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-table-cells';

    protected static ?string $navigationGroup = 'Library';

    protected static ?int $navigationSort = 10;

    protected static ?string $title = 'Bulk edit books';

    protected static string $view = 'filament.pages.bulk-edit-books';
}
