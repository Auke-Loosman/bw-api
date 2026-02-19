<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final class MessageInput
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public string $subject;

    #[Assert\NotBlank]
    public string $message;

    #[Assert\NotBlank]
    public string $date;

    #[Assert\NotBlank]
    public string $senderName;

    #[Assert\Choice(choices: ['incoming', 'outgoing', 'task'])]
    public string $type;
}
