<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SellerResource\Pages;
use App\Models\Seller;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;

class SellerResource extends Resource
{
    protected static ?string $model = Seller::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Sellers';
    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('shop_name')->required(),
            TextInput::make('phone')->required(),
            TextInput::make('email')->email(),
            Select::make('status')
                ->options(['pending' => 'Pending', 'approved' => 'Approved', 'suspended' => 'Suspended'])
                ->required(),
            TextInput::make('city'),
            Toggle::make('verified'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('shop_name')->searchable()->sortable(),
                TextColumn::make('phone'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'approved'  => 'success',
                        'suspended' => 'danger',
                        default     => 'warning',
                    }),
                IconColumn::make('verified')->boolean(),
                TextColumn::make('city'),
                TextColumn::make('created_at')->since()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Action::make('approve')
                    ->label('Approve')
                    ->color('success')
                    ->visible(fn (Seller $s) => $s->status === 'pending')
                    ->action(fn (Seller $s) => $s->update(['status' => 'approved'])),

                Action::make('suspend')
                    ->label('Suspend')
                    ->color('danger')
                    ->visible(fn (Seller $s) => $s->status !== 'suspended')
                    ->requiresConfirmation()
                    ->action(fn (Seller $s) => $s->update(['status' => 'suspended'])),

                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSellers::route('/'),
            'edit'  => Pages\EditSeller::route('/{record}/edit'),
        ];
    }
}
