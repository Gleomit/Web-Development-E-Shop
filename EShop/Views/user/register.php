<?php
echo \DF\Helpers\ViewHelpers\FormViewHelper::init()
    ->setMethod('POST')
    ->setAction(\DF\Services\RouteService::getUrl('home', ''))
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
