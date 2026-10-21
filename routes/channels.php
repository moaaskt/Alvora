<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels (D-02 / Multi-Tenant Isolation)
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

// Canal privado da empresa (Gestores de RH da respectiva empresa)
Broadcast::channel('company.{companyId}', function (User $user, $companyId) {
    if ($user->isAlvoraAdmin()) {
        return true;
    }

    return (int) $user->company_id === (int) $companyId && $user->isCompanyAdmin();
});

// Canal privado do aluno/colaborador
Broadcast::channel('user.{userId}', function (User $user, $userId) {
    return (int) $user->id === (int) $userId;
});
