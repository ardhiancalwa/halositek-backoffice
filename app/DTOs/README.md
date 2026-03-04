# Data Transfer Objects (DTOs)

## Convention

- **Namespace**: `App\DTOs\{Domain}\{ActionName}DTO`
- **Must be `readonly`** (enforced by architecture tests)
- Constructor with typed public properties
- Factory methods: `fromRequest()`, `fromArray()`

## Example

```php
final readonly class CreateUserDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            name: $request->validated('name'),
            email: $request->validated('email'),
            password: $request->validated('password'),
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            email: $data['email'],
            password: $data['password'],
        );
    }
}
```

## Adding a New DTO

1. Create file: `app/DTOs/{Domain}/{Name}DTO.php`
2. Make class `final readonly`
3. Add typed constructor properties
4. Add `fromRequest()` and/or `fromArray()` factory methods
