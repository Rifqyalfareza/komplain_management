<?php

namespace App\Filament\Resources\PurchaseResource\Pages;

use App\Filament\Resources\PurchaseResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreatePurchase extends CreateRecord
{
    protected static string $resource = PurchaseResource::class;

    protected function afterCreate(): void
    {
        Notification::make()
            ->title('Purchasecreated successfully')
            ->success()
            ->duration(3000) // 3 detik
            ->send();
    }
    protected function getCreatedNotification(): ?Notification
    {
        return null; // Matikan notifikasi default
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
