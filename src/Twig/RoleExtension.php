<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class RoleExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            // If your filter generates SAFE HTML, you should add a third
            // parameter: ['is_safe' => ['html']]
            // Reference: https://twig.symfony.com/doc/2.x/advanced.html#automatic-escaping
            new TwigFilter('renameRole', [$this, 'renameRole']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('function_name', [$this, 'doSomething']),
        ];
    }

    public function renameRole(string $value) : string
    {
        if ( $value === 'ROLE_ADMIN' ) {
            $value = 'Administrateur';
        } else if ($value === 'ROLE_MODERATOR'){
            $value = 'Modérateur';
        } else if ($value === 'ROLE_USER'){
            $value = 'Utilisateur';
        }


        return ($value);
    }
}
