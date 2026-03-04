# Action Classes

## Convention

- **Namespace**: `App\Actions\{Domain}\{ActionName}Action`
- **Must be `final`** (enforced by architecture tests)
- Single `execute()` method that accepts a DTO and returns a Model/result
- One action = one business operation

## Example

```php
final class CreateUserAction
{
    public function execute(CreateUserDTO $dto): User
    {
        return User::create([
            'name' => $dto->name,
            'email' => $dto->email,
            'password' => Hash::make($dto->password),
        ]);
    }
}
```

## Usage

```php
// In API Controller (injected via Laravel DI)
public function store(StoreUserRequest $request, CreateUserAction $action): JsonResponse
{
    $dto = CreateUserDTO::fromRequest($request);
    $user = $action->execute($dto);

    return response()->json($user, 201);
}
```

## Adding a New Action

1. Create file: `app/Actions/{Domain}/{ActionName}Action.php`
2. Make class `final`
3. Add `execute()` method with typed DTO parameter
4. Write test in `tests/Feature/Actions/`
