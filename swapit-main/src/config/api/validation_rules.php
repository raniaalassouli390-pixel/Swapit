<?php

return [

    'auth' => [
        'POST' => [
            'email'    => ['required' => true, 'type' => 'email'],
            'password' => ['required' => true, 'type' => 'string', 'min' => 8],
            'username' => ['type' => 'string', 'min' => 3, 'max' => 50, 'sanitize' => true],
        ],
    ],

    'listings' => [
        'POST' => [
            'title'       => ['required' => true, 'type' => 'string', 'max' => 255, 'sanitize' => true],
            'type'        => ['required' => true, 'in' => ['sale', 'swap', 'auction']],
            'condition'   => ['required' => true, 'in' => ['new', 'like_new', 'good', 'fair', 'for_parts']],
            'description' => ['type' => 'string', 'sanitize' => true],
            'price'       => ['type' => 'float',  'min' => 0],
            'category_id' => ['type' => 'int',    'min' => 1],
            'city'        => ['type' => 'string', 'sanitize' => true],
            'swap_notes'  => ['type' => 'string', 'sanitize' => true],
        ],
        'PUT' => [
            'title'       => ['type' => 'string', 'max' => 255, 'sanitize' => true],
            'condition'   => ['in' => ['new', 'like_new', 'good', 'fair', 'for_parts']],
            'status'      => ['in' => ['active', 'draft']],
            'price'       => ['type' => 'float', 'min' => 0],
            'city'        => ['type' => 'string', 'sanitize' => true],
            'description' => ['type' => 'string', 'sanitize' => true],
        ],
    ],

    'bids' => [
        'POST' => [
            'auction_id' => ['required' => true, 'type' => 'int',   'min' => 1],
            'amount'     => ['required' => true, 'type' => 'float', 'min' => 0],
        ],
    ],

    'categories' => [
        'POST' => [
            'name' => ['required' => true, 'type' => 'string', 'max' => 100, 'sanitize' => true],
            'slug' => ['required' => true, 'type' => 'string', 'max' => 100],
        ],
        'PUT' => [
            'name' => ['type' => 'string', 'max' => 100, 'sanitize' => true],
            'slug' => ['type' => 'string', 'max' => 100],
        ],
    ],

    'messages' => [
        'POST' => [
            'receiver_id' => ['required' => true, 'type' => 'int',    'min' => 1],
            'body'        => ['required' => true, 'type' => 'string', 'min' => 1, 'max' => 2000, 'sanitize' => true],
        ],
    ],

    'reviews' => [
        'POST' => [
            'user_id' => ['required' => true, 'type' => 'int', 'min' => 1],
            'rating'  => ['required' => true, 'type' => 'int', 'min' => 1, 'max' => 5],
            'comment' => ['type' => 'string', 'max' => 1000, 'sanitize' => true],
        ],
    ],

    'swaps' => [
        'POST' => [
            'offered_listing' => ['required' => true, 'type' => 'int',    'min' => 1],
            'wanted_listing'  => ['required' => true, 'type' => 'int',    'min' => 1],
            'message'         => ['type' => 'string', 'max' => 1000, 'sanitize' => true],
        ],
        'PUT' => [
            'status' => ['required' => true, 'in' => ['accepted', 'rejected', 'cancelled']],
        ],
    ],

    'users' => [
        'PUT' => [
            'first_name' => ['type' => 'string', 'max' => 50,  'sanitize' => true],
            'last_name'  => ['type' => 'string', 'max' => 50,  'sanitize' => true],
            'bio'        => ['type' => 'string', 'max' => 500, 'sanitize' => true],
            'city'       => ['type' => 'string', 'max' => 100, 'sanitize' => true],
        ],
    ],

    'wishlist' => [
        'POST' => [
            'listing_id' => ['required' => true, 'type' => 'int', 'min' => 1],
        ],
    ],

    'admin' => [
        'PUT' => [
            'status' => ['required' => true, 'type' => 'string', 'sanitize' => true],
        ],
    ],

];
