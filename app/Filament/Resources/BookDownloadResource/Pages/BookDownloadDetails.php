<?php

namespace App\Filament\Resources\BookDownloadResource\Pages;

use App\Filament\Resources\BookDownloadResource;
use App\Models\Book;
use App\Models\BookDownload;
use Filament\Resources\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BookDownloadDetails extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = BookDownloadResource::class;

    protected static string $view = 'filament.resources.book-download-resource.pages.book-download-details';

    public ?Book $book = null;
    public int $bookId;

    public function mount(int $bookId): void
    {
        $this->bookId = $bookId;
        $this->book = Book::findOrFail($bookId);
    }

    public function getTitle(): string
    {
        return "Download Statistics: {$this->book->title}";
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                BookDownload::query()
                    ->where('book_id', $this->bookId)
                    ->with(['user', 'book'])
            )
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Downloaded at')
                    ->since()
                    ->description(fn ($record) => $record->created_at->format('M d, Y g:i A')),
                Tables\Columns\TextColumn::make('user.name')
                    ->sortable()
                    ->label('User')
                    ->placeholder('Guest')
                    ->searchable(),
                Tables\Columns\TextColumn::make('ip_address')
                    ->searchable()
                    ->label('IP address')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('user_agent')
                    ->limit(50)
                    ->label('User agent')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrap(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\Filter::make('last_24_hours')
                    ->query(fn (Builder $query): Builder => $query->where('created_at', '>=', now()->subHours(24)))
                    ->label('Last 24 hours'),
                Tables\Filters\Filter::make('last_7_days')
                    ->query(fn (Builder $query): Builder => $query->where('created_at', '>=', now()->subDays(7)))
                    ->label('Last 7 days'),
                Tables\Filters\Filter::make('last_30_days')
                    ->query(fn (Builder $query): Builder => $query->where('created_at', '>=', now()->subDays(30)))
                    ->label('Last 30 days'),
            ])
            ->heading('Individual Download Records')
            ->description('All download events for this book');
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('view_book')
                ->label('View book page')
                ->icon('heroicon-o-eye')
                ->url(fn () => route('library.show', $this->book->slug))
                ->openUrlInNewTab(),
        ];
    }
}
