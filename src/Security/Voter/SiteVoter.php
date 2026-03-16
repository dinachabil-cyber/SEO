<?php

namespace App\Security\Voter;

use App\Entity\Site;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class SiteVoter extends Voter
{
    public const VIEW = 'VIEW';
    public const EDIT = 'EDIT';
    public const DELETE = 'DELETE';
    public const MANAGE = 'MANAGE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [self::VIEW, self::EDIT, self::DELETE, self::MANAGE])) {
            return false;
        }

        // only vote on Site objects inside this voter
        if (!$subject instanceof Site) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?\Symfony\Component\Security\Core\Authorization\Voter\Vote $vote = null): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        $site = $subject;

        // Admin users have full access to all sites
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return true;
        }

        // Access granted if the user is the owner of the site
        return $user === $site->getOwner();
    }
}
