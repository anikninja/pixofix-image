<?php

return [
    'avatar_column' => 'avatar_url',
    'disk' => 'public',
    'visibility' => 'public', // or replace by filesystem disk visibility with fallback value
    'show_custom_fields' => true,
    'custom_fields' => [
        'address' => [
            'type' => 'textarea', // required
            'label' => 'Address', // required
            'placeholder' => 'Type your address here', // optional
            'id' => 'address', // optional
            'column_span' => 'full', // optional
            'autocomplete' => false, // optional
        ],
        'phone' => [
            'type' => 'text', // required
            'label' => 'Phone', // required
            'placeholder' => 'Your phone number here', // optional
            'id' => 'phone', // optional
            'prefix_icon' => 'heroicon-o-phone',
            'column_span' => 'full', // optional
            'autocomplete' => false, // optional
        ],
        'linkedin' => [
            'type' => 'text', // required
            'label' => 'LinkedIn Profile', // required
            'placeholder' => 'Put your linkedin profile link here', // optional
            'id' => 'linkedin-profile', // optional
            'prefix_icon' => 'heroicon-o-user',
            'column_span' => 'full', // optional
            'autocomplete' => false, // optional
        ],
        'facebook' => [
            'type' => 'text', // required
            'label' => 'Facebook Profile', // required
            'placeholder' => 'Put your facebook profile link here', // optional
            'id' => 'facebook-profile', // optional
            'prefix_icon' => 'heroicon-o-user',
            'column_span' => 'full', // optional
            'autocomplete' => false, // optional
        ],        
    ]
];
