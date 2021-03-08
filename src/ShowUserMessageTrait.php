<?php

declare(strict_types=1);

namespace usermessageforatk;


trait ShowUserMessageTrait
{

    public function addUserMessageModal(array $defaults = []): ?UserMessageModal
    {
        //messages already displayed in this session? do not add Modal again to save performance
        if ($_SESSION['MESSAGES_FOR_USER_DISPLAYED'] ?? false) {
            return null;
        }

        return UserMessageModal::addTo($this->layout, $defaults);
    }
}