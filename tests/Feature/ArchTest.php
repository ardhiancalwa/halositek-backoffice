<?php

/*
|--------------------------------------------------------------------------
| Architecture Tests
|--------------------------------------------------------------------------
|
| Enforce architectural conventions across the codebase.
|
*/

arch('actions must be final classes')
    ->expect('App\Actions')
    ->toBeFinal();

arch('DTOs must be readonly classes')
    ->expect('App\DTOs')
    ->toBeReadonly();

arch('no debugging statements')
    ->expect(['dd', 'dump', 'ray', 'var_dump', 'print_r'])
    ->not->toBeUsed();

arch('controllers must extend base controller')
    ->expect('App\Http\Controllers')
    ->toExtend('App\Http\Controllers\Controller');
