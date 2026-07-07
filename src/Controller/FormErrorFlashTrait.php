<?php

namespace App\Controller;

use Symfony\Component\Form\FormInterface;

trait FormErrorFlashTrait
{
    protected function flashFormErrors(FormInterface $form, string $fallbackMessage): void
    {
        $errors = (string) $form->getErrors(true, false);

        if ($errors) {
            $this->addFlash('error', $errors);
        }

        $this->addFlash('error', $fallbackMessage);
    }
}
