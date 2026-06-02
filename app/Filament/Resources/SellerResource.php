<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SellerResource\Pages;
use App\Models\Seller;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\SelectFilter;

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
            TextInput::make('bio')->columnSpanFull(),
            TextInput::make('payout_method')->label('Payout details')->columnSpanFull(),
            Toggle::make('verified'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('shop_name')->searchable()->sortable(),
                TextColumn::make('phone')->copyable(),
                TextColumn::make('email')->placeholder('—')->copyable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('city')->placeholder('—'),
                TextColumn::make('listings_count')
                    ->label('Listings')
                    ->counts('listings')
                    ->sortable(),
                TextColumn::make('payout_method')
                    ->label('Payout details')
                    ->placeholder('Not set')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'approved'  => 'success',
                        'suspended' => 'danger',
                        default     => 'warning',
                    }),
                IconColumn::make('verified')->boolean(),
                TextColumn::make('created_at')->since()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options(['pending' => 'Pending', 'approved' => 'Approved', 'suspended' => 'Suspended']),
            ])
            ->actions([
                Action::make('approve')
                    ->label('Approve')
                    ->color('success')
                    ->icon('heroicon-o-check')
                    ->visible(fn (Seller $s) => $s->status === 'pending')
                    ->requiresConfirmation()
                    ->action(function (Seller $s) {
                        $s->update(['status' => 'approved']);
                        Notification::make()->title('Seller approved')->success()->send();
                    }),

                Action::make('verify')
                    ->label('Verify')
                    ->color('success')
                    ->icon('heroicon-o-shield-check')
                    ->visible(fn (Seller $s) => !$s->verified)
                    ->requiresConfirmation()
                    ->action(function (Seller $s) {
                        $s->update(['verified' => true]);
                        Notification::make()->title('Seller verified')->success()->send();
                    }),

                Action::make('suspend')
                    ->label('Suspend')
                    ->color('danger')
                    ->icon('heroicon-o-no-symbol')
                    ->visible(fn (Seller $s) => $s->status !== 'suspended')
                    ->requiresConfirmation()
                    ->action(function (Seller $s) {
                        $s->update(['status' => 'suspended']);
                        Notification::make()->title('Seller suspended')->danger()->send();
                    }),

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
