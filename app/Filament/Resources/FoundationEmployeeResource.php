<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FoundationEmployeeResource\Pages;
use App\Filament\Resources\FoundationEmployeeResource\RelationManagers;
use App\Models\FoundationEmployee;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FoundationEmployeeResource extends Resource
{
    protected static ?string $model = FoundationEmployee::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Kepegawaian';
    protected static ?string $modelLabel = 'Pegawai & Guru';
    protected static ?string $pluralModelLabel = 'Data Pegawai & Guru';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('tenant_id')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('branch_unit')
                    ->required()
                    ->maxLength(100)
                    ->default('Kantor Pusat Yayasan'),
                Forms\Components\TextInput::make('nik')
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('nip_yayasan')
                    ->required()
                    ->maxLength(50),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('gender')
                    ->required(),
                Forms\Components\TextInput::make('employee_category')
                    ->required(),
                Forms\Components\TextInput::make('position')
                    ->required()
                    ->maxLength(100),
                Forms\Components\TextInput::make('department')
                    ->maxLength(100)
                    ->default(null),
                Forms\Components\TextInput::make('employment_status')
                    ->required(),
                Forms\Components\TextInput::make('phone')
                    ->tel()
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\Textarea::make('address')
                    ->columnSpanFull(),
                Forms\Components\DatePicker::make('join_date'),
                Forms\Components\DatePicker::make('tmt'),
                Forms\Components\TextInput::make('base_salary')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('custom_password')
                    ->password()
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\Toggle::make('is_active')
                    ->required(),
                Forms\Components\Textarea::make('photo_url')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tenant_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('branch_unit')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nik')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nip_yayasan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('gender'),
                Tables\Columns\TextColumn::make('employee_category'),
                Tables\Columns\TextColumn::make('position')
                    ->searchable(),
                Tables\Columns\TextColumn::make('department')
                    ->searchable(),
                Tables\Columns\TextColumn::make('employment_status'),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('join_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tmt')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('base_salary')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFoundationEmployees::route('/'),
            'create' => Pages\CreateFoundationEmployee::route('/create'),
            'edit' => Pages\EditFoundationEmployee::route('/{record}/edit'),
        ];
    }
}
