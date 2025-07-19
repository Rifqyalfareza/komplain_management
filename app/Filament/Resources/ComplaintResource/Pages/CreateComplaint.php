<?php

namespace App\Filament\Resources\ComplaintResource\Pages;

use Filament\Actions;
use App\Models\Purchase;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\ComplaintResource;

class CreateComplaint extends CreateRecord
{
    protected static string $resource = ComplaintResource::class;

    public function mount(): void
    {
        parent::mount();

        $this->form->fill([
            'purchase_id' => request()->query('purchase_id'),
        ]);
    }
    protected function afterCreate(): void
    {
        Notification::make()
            ->title('Complaint created successfully')
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
