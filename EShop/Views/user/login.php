<?php
    echo \DF\Helpers\ViewHelpers\FormViewHelper::init()
        ->initTextField()
        ->setName('username')
        ->create()
        ->initPasswordField()
        ->setName('password')
        ->create()
        ->initPasswordField()
        ->setName('confirmPassword')
        ->create()
        ->initSubmitButton()
        ->setValue('Register')
        ->create()
        ->render();
